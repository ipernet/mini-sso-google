<?php

namespace Sso\Model;

class User
{
	protected $_g_id_user;
	protected $_g_name;
	protected $_g_domain;
	protected $_g_email;
	protected $_g_token;
			
	protected function __construct()
	{
		
	}
	
	public static function withGoogleClient(\Google_Client $g_client)
	{
		$user	=	new User();
		
		$plus	=	new \Google_Service_Plus($g_client);

		$me		=	$plus->people->get('me');

		if($me instanceof \Google_Service_Plus_Person)
		{
			$user->setId($me->id);
			$user->setName($me->displayName);
			$user->setDomain($me->domain);
			$user->setToken($g_client->getAccessToken());

			foreach($me->getEmails() as $email)
			{
				if($email->type === 'account')
				{
					$user->setEmail($email['value']);
					break;
				}
			}
		}
		
		return $user;
	}
	
	public static function withSession($session)
	{
		$user	=	new static();
		
		$user->setId($session['id']);
		$user->setName($session['name']);
		$user->setDomain($session['domain']);
		$user->setToken($session['token']);
		$user->setEmail($session['email']);

		return $user;
	}
	
	public function setId($id)
	{
		$this->_g_id_user	=	$id;
	}
	
	public function setName($name)
	{
		$this->_g_name	=	$name;
	}
	
	public function setDomain($domain)
	{
		$this->_g_domain	=	$domain;
	}
	
	public function setEmail($email)
	{
		$this->_g_email	=	$email;
	}
	
	public function setToken($token)
	{
		$this->_g_token	=	$token;
	}
	
	public function getId()
	{
		return $this->_g_id_user;
	}
	
	public function getName()
	{
		return $this->_g_name;
	}
	
	public function getDomain()
	{
		return $this->_g_domain;
	}
	
	public function getEmail()
	{
		return $this->_g_email;
	}
	
	public function getToken()
	{
		return $this->_g_token;
	}
	
	public function getSession()
	{
		return [
			'id'		=>	$this->getId(),
			'name'		=>	$this->getName(),
			'domain'	=>	$this->getDomain(),
			'email'		=>	$this->getEmail(),
			'token'		=>	$this->getToken(),
		];
	}
	
	public function getApi()
	{
		return [
			'id'		=>	$this->getId(),
			'name'		=>	$this->getName(),
			'domain'	=>	$this->getDomain(),
			'email'		=>	$this->getEmail()
		];
	}
	
	public function hasAccess($permissions, $broker)
	{		
		// By default, all signed-in users are granted access
		$access_granted	=	true;
			
		// Permissions set, deny all by default
		if(isset($permissions[$broker]))
		{
			// User allowed explicitly or denied implicitly?
			if(isset($permissions[$broker]['allow']) && is_array($permissions[$broker]['allow']))
			{
				$access_granted	=	in_array($this->getEmail(), $permissions[$broker]['allow']);
			}
			else if(isset($permissions[$broker]['deny']) && is_array($permissions[$broker]['deny']))
			{
				// No allow directive but a deny one. Is the user denied explicitly?
				$access_granted	=	! in_array($this->getEmail(), $permissions[$broker]['deny']);
			}
			else
			{
				// No allow or deny, granted by default
				$access_granted	=	true;
			}
		}
		
		return $access_granted;
	}
}
