<?php

class CustomControllerAclManager extends Zend_Controller_Plugin_Abstract
{
	private $_defaultRole = 'guest';
	private $_authController = array('controller'	=> 'account',
									 'action'		=> 'login');
	
	public function __construct()
	{
		$this->auth = Zend_Auth::getInstance();
		$this->auth->setStorage(new AuthSession());
		$this->acl = new Zend_Acl();
		
		// add the different user roles
		$this->acl->addRole(new Zend_Acl_Role($this->_defaultRole));
		$this->acl->addRole(new Zend_Acl_Role('member'));
		$this->acl->addRole(new Zend_Acl_Role('administrator'), 'member');
		
		// add the resources we want to have control over
		$this->acl->add(new Zend_Acl_Resource('account'));
		$this->acl->add(new Zend_Acl_Resource('blogmanager'));
		$this->acl->add(new Zend_Acl_Resource('admin'));
		
		// allow access to everything for all users by default
		// except for the account management and administration areas
		$this->acl->allow();
		$this->acl->deny(null, 'account');
		$this->acl->deny(null, 'blogmanager');
		$this->acl->deny(null, 'admin');
		
		// add an exception so guests can log in or register
		// in order to gain privilege
		$this->acl->allow('guest', 'account', array('login',
													'fetchpassword',
													'register',
													'registercomplete'));
		
		// allow members access to the account management area
		$this->acl->allow('member', 'account');
		$this->acl->allow('member', 'blogmanager');
		
		// allow administrators access to the admin area
		$this->acl->allow('administrator', 'admin');
	}
	
	public function preDispatch(Zend_Controller_Request_Abstract $request)
	{
		if ($this->auth->hasIdentity())
			$role = $this->auth->getIdentity()->user_type;
		else
			$role = $this->_defaultRole;
		
		if (!$this->acl->hasRole($role))
			$role = $this->_defaultRole;
		
		$resource = $request->controller;
		$privilege = $request->action;
		
		if (!$this->acl->has($resource))
			$resource = null;
		
		if (!$this->acl->isAllowed($role, $resource, $privilege))
		{
			$request->setControllerName($this->_authController['controller']);
			$request->setActionName($this->_authController['action']);
		}
	}
}

?>