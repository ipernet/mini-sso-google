<?php

namespace Sso\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;

class GoogleClientProvider implements ServiceProviderInterface
{
	public function register(Application $app)
	{		
		$app['g_client'] = $app->share(function() use ($app)
		{
			$g_client	=	new \Google_Client();
			
			$g_client->setClientId($app['gauth.client_id']);
			$g_client->setClientSecret($app['gauth.client_secret']);
			$g_client->setRedirectUri($app['gauth.redirect_uri']);
			$g_client->addScope($app['gauth.scope']);
			
			return $g_client;
        });
	}
	
	public function boot(Application $app)
    {
		
    }
}
