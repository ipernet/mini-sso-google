<?php

namespace Sso\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class Check
{
	public function index(Request $request, Application $app)
	{		
		$user_session	=	$app['session']->get('user');
	
		if($user_session)
		{
			$user			=	\Sso\Model\User::withSession($user_session);
					
			// By default, all signed-in users are granted access
			$access_granted	=	true;
			
			// Per domain permissions
			$broker	=	$request->server->get('HTTP_X_BROKER_DOMAIN');
					
			if($broker !== null)
				$access_granted	=	$user->hasAccess($app['conf']['permissions'], $broker);
						
			if( ! $access_granted)
				return $app->abort(401);
			
			if($request->get('jsonp_callback') !== null)
			{
				$res	=	new JsonResponse($user);
				
				return $res->setCallback($request->get('jsonp_callback'));
			
			}
			else
				return new JsonResponse($user->getApi());
		}
		
		return $app->abort(401);
	}
}
