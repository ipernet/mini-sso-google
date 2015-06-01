<?php

namespace Sso\Model;

class User
{
	protected $_g_id_user;
	protected $_g_name;
	protected $_g_domain;
	protected $_g_email;
			
	public function __construct($g_client)
	{
		$plus	=	new \Google_Service_Plus($g_client);

		$me		=	$plus->people->get('me');

		if($me instanceof \Google_Service_Plus_Person)
			$this->setUser($me);
	}
	
	public function setUser($me)
	{
		$this->_g_id_user	=	$me->id;
		$this->_g_name		=	$me->displayName;
		$this->_g_domain	=	$me->domain;

		foreach($me->getEmails() as $email)
		{
			if($email->type === 'account')
			{
				$this->_g_email	=	$email['value'];
				break;
			}
		}
		
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
}
