<?php

namespace Sso\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class Check
{
	public function index(Request $request, Application $app)
	{		
		$user	=	$app['session']->get('user');
	
		if($user)
		{
			if($request->get('jsonp_callback') !== null)
			{
				$res	=	new JsonResponse($user);
				
				return $res->setCallback($request->get('jsonp_callback'));
			
			}
			else
				return new JsonResponse($user);
		}
		return $app->abort(401);
	}
}
