<?php

// Paths
define('APP_PATH', realpath(__DIR__.'/../app'));

// Composer and namespaces
$loader = require APP_PATH.'/../vendor/autoload.php'; 
$loader->add('Sso', APP_PATH.'/src');

// Configuration
$yaml	=	new Symfony\Component\Yaml\Parser();

try
{
	$conf	=	$yaml->parse(file_get_contents(APP_PATH.'/../config.yml'));
}
catch (ParseException $e)
{
	die($e->getMessage());
}

// Session cookie info
$cookie_params	=	session_get_cookie_params();
session_name($conf['sso']['cookie']['name']);
session_set_cookie_params($cookie_params['lifetime'], '/', $conf['sso']['cookie']['domain'], (bool) $conf['sso']['ssl'], true);

// App and its providers
$app	=	new Silex\Application(); 

$app->register(new Silex\Provider\SessionServiceProvider())
	->register(new Sso\Provider\GoogleClientProvider(), [
	'gauth.client_id'		=>	$conf['google']['client_id'],
	'gauth.client_secret'	=>	$conf['google']['client_secret'],
	'gauth.redirect_uri'	=>	$conf['google']['redirect_uri'],
	'gauth.scope'			=>	$conf['google']['scope'],
]);

// Mustache service
$app['mustache']	=	$app->share(function()
{
	return new Mustache_Engine();
});

// Conf handler
$app['conf']	=	$app->share(function() use($conf)
{
	if( ! isset($conf['permissions']) || ! is_array($conf['permissions']))
		$conf['permissions']	=	[];
	
	return $conf;
});

/**
 * Routing
 */

// Nginx "auth_request" backend
$app->get('/', 'Sso\\Controller\\Check::index');

// Login view
$app->get('/login', 'Sso\\Controller\\Oauth::login');

// Oauth Callback
$app->get('/oauth2callback', 'Sso\\Controller\\Oauth::callback');

$app['debug']	=	$conf['sso']['debug'];

$app->run();