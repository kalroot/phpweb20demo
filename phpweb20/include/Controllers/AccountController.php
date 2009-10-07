<?php

class AccountController extends CustomControllerAction
{
	public function init()
	{
		parent::init();
		$this->breadcrumbs->addStep('Account', $this->getUrl(null, 'account'));
	}
	
	public function registerAction()
	{
		$request = $this->getRequest();
		
		$fp = new FormProcessor_UserRegistration($this->db);
		$validate = $request->isXmlHttpRequest();
		
		if ($request->isPost())
		{
			if ($validate)
			{
				$fp->validateOnly(true);
				$fp->process($request);
			}
			else if ($fp->process($request))
			{
				$session = new Zend_Session_Namespace('registration');
				$session->user_id = $fp->user->getId();
				$this->_redirect($this->getUrl('registercomplete'));
			}
		}
		
		if ($validate)
		{
			if ($fp->hasError())
				$json = array('errors' => $fp->getErrors());
			else
				$json = array('errors' => null);
			$this->sendJson($json);
		}
		else
		{
			$this->breadcrumbs->addStep('Create an Account');
			$this->view->fp = $fp;
		}
	}
	
	public function registercompleteAction()
	{
		$session = new Zend_Session_Namespace('registration');
		
		$user = new DatabaseObject_User($this->db);
		if (!$user->load($session->user_id))
		{
			$this->_forward('register');
			return;
		}
		
		$this->breadcrumbs->addStep('Create an Account', $this->getUrl('register'));
		$this->breadcrumbs->addStep('Account Created');
		$this->view->user = $user;
	}
	
	public function loginAction()
	{
		$auth = Zend_Auth::getInstance();
		if ($auth->hasIdentity())
			$this->_redirect($this->getUrl());
		
		$request = $this->getRequest();
		
		$redirect = $request->getPost('redirect');
		if (strlen($redirect) == 0)
			$redirect = $request->getServer('REQUEST_URI');
		if (strlen($redirect) == 0)
			$redirect = $this->getUrl();
			
		$errors = array();
		
		if ($request->isPost())
		{
			$username = $request->getPost('username');
			$password = $request->getPost('password');
			
			if (strlen($username) == 0)
				$errors['username'] = 'Required field must not be blank';
			if (strlen($password) == 0)
				$errors['password'] = 'Required field must not be blank';
			
			if (count($errors) == 0)
			{
				$adapter = new Zend_Auth_Adapter_DbTable($this->db,
														 'users',
														 'username',
														 'password',
														 'md5(?)');
				$adapter->setIdentity($username);
				$adapter->setCredential($password);
				
				$result = $auth->authenticate($adapter);
				if ($result->isValid())
				{
					$user = new DatabaseObject_User($this->db);
					$user->load($adapter->getResultRowObject()->user_id);
					$user->loginSuccess();
					
					$identity = $user->createAuthIdentity();
					$auth->getStorage()->write($identity);
					$this->_redirect($redirect);
				}
				
				DatabaseObject_User::LoginFailure($username, $result->getCode());
				$errors['username'] = 'Your login details were invalid';
			}
		}
		
		$this->breadcrumbs->addStep('Login');
		$this->view->errors = $errors;
		$this->view->redirect = $redirect;
	}
	
	public function logoutAction()
	{
		Zend_Auth::getInstance()->clearIdentity();
		$this->_redirect($this->getUrl('login'));
	}
	
	public function fetchpasswordAction()
	{
		if (Zend_Auth::getInstance()->hasIdentity())
			$this->_redirect($this->getUrl());
		
		$errors = array();
		
		$action = $this->getRequest()->getQuery('action');
		
		if ($this->getRequest()->isPost())
			$action = 'submit';
		
		switch ($action)
		{
			case 'submit':
				$username = trim($this->getRequest()->getPost('username'));
				if (strlen($username) == 0)
				{
					$errors['username'] = 'Required field must not be blank';
				}
				else
				{
					$user = new DatabaseObject_User($this->db);
					if ($user->load($username, 'username'))
					{
						$user->fetchPassword();
						$url = $this->getUrl('fetchpassword') . '?action=complete';
						$this->_redirect($url);
					}
					else
					{
						$errors['username'] = 'Specified user not found';
					}
				}
				break;
				
			case 'complete':
				break;
			
			case 'confirm':
				$id = $this->getRequest()->getQuery('id');
				$key = $this->getRequest()->getQuery('key');
				
				$user = new DatabaseObject_User($this->db);
				if (!$user->load($id))
					$errors['confirm'] = 'Error confirming new password';
				else if (!$user->confirmNewPassword($key))
					$errors['confirm'] = 'Error confirming new password';
				break;
		}
		
		$this->breadcrumbs->addStep('Login', $this->getUrl('login'));
        $this->breadcrumbs->addStep('Fetch Password');
		$this->view->errors = $errors;
		$this->view->action = $action;
	}
	
	public function detailsAction()
	{
		$auth = Zend_Auth::getInstance();
		
		$fp = new FormProcessor_UserDetails($this->db,
                                            $auth->getIdentity()->user_id);
		
		if ($this->getRequest()->isPost())
		{
			if ($fp->process($this->getRequest()))
			{
				$auth->getStorage()->write($fp->user->createAuthIdentity());
                $this->_redirect($this->getUrl('detailscomplete'));
            }
        }

		$this->breadcrumbs->addStep('Your Account Details');
        $this->view->fp = $fp;
	}
	
	public function detailscompleteAction()
    {
		$user = new DatabaseObject_User($this->db);
        $user->load(Zend_Auth::getInstance()->getIdentity()->user_id);

		$this->breadcrumbs->addStep('Your Account Details', $this->getUrl('details'));
        $this->breadcrumbs->addStep('Details Updated');
        $this->view->user = $user;
    }
	
	public function indexAction()
	{
	}
}

?>