<?php

namespace Sso\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class Oauth
{
	public function login(Application $app)
	{		
		// From URL
		$from	=	filter_input(INPUT_GET, 'from', FILTER_VALIDATE_URL);

		if( ! $from)
			$app->abort(401, 'Unknown broker URL');
		
		// Already Oauthed? 
		if($app['session']->get('user'))
			return $app->redirect($from);

		$app['g_client']->setState(self::base64UrlEncode(json_encode(['from' => $from])));

		$content	=	$app['mustache']->render(
			file_get_contents(APP_PATH.'/views/pages/home.html'),
			[
				'base_url'	=>	$app['conf']['sso']['base_url'],
				'url'		=>	$app['g_client']->createAuthUrl()
			]
		);

		return $app['mustache']->render(
			file_get_contents(APP_PATH.'/views/layouts/index.html'), 
			[
				'base_url'	=>	$app['conf']['sso']['base_url'],
				'content'	=>	$content
			]
		);
	}
	
	public function callback(Request $request, Application $app)
	{
		$params	=	$request->query->all();
	
		try
		{
			if(empty($params['code']))
				throw new \Exception('Invalid OAuth code.');

			if(empty($params['state']))
				throw new \Exception('Invalid state parameter.');

			$state	=	json_decode(self::base64UrlDecode($params['state']), true);

			if( ! is_array($state) || ! isset($state['from']) || ! filter_var($state['from'], FILTER_VALIDATE_URL))
				throw new \Exception('Invalid broker URL.');

			$app['g_client']->authenticate($params['code']);

			if( ! $app['g_client']->getAccessToken())
				throw new Exception('Invalid access token.');
				
			$me		=	new \Sso\Model\User($app['g_client']);

			if( ! $me->getId())
				throw new \Exception('Invalid user');

			// Whitelisted domain?
			if( ! empty($app['conf']['google']['domains']) && is_array($app['conf']['google']['domains']))
			{
				$valid	=	false;

				foreach($app['conf']['google']['domains'] as $domain)
				{
					if(preg_match('#'.preg_quote($domain).'$#', $me->getDomain()) === 1)
					{
						$valid	=	true;
						break;
					}
				}

				if( ! $valid)
					throw new \Exception('User domain does not match any of the accepted domains.');
			}

			// Save a session for further checks
			$app['session']->set('user', [
				'id'		=>	$me->getId(),
				'name'		=>	$me->getName(),
				'domain'	=>	$me->getDomain(),
				'email'		=>	$me->getEmail(),
			]);

			return $app->redirect($state['from']);
		}
		catch (\Exception $e)
		{
			return $app->abort(403, $e->getMessage());
		}
	}
	
	public static function base64UrlEncode($inputStr)
	{
		return strtr(base64_encode($inputStr), '+/=', '-_,');
	}
	
	public static function base64UrlDecode($base64)
	{
	  return base64_decode(strtr($base64, '-_', '+/'));
	}
}
