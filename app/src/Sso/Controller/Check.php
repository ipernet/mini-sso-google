<?php

namespace Sso\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\JsonResponse;

class Check
{
	public function index(Application $app)
	{		
		$user	=	$app['session']->get('user');
	
		return ! $user ? $app->abort(401) : (new JsonResponse())->setData($user);
	}
}
