<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
	protected function _initApplicationAutoload()
	{
		$autoloader = new Zend_Application_Module_Autoloader(array(
            'namespace' => '',
            'basePath'  => APPLICATION_PATH,
        ));
	}

	protected function _initLibraryAutoload()
	{
		$loader = Zend_Loader_Autoloader::getInstance();
		$loader->setFallbackAutoloader(true);
	}

	protected function _initDbAdapter()
	{
		$this->bootstrap('db');
		$dbAdapter = $this->getResource('db');
		Zend_Registry::set('db', $dbAdapter);
	}

	protected function _initConfig()
	{
		$config = new Zend_Config_Ini(APPLICATION_PATH . '/../settings.ini', APPLICATION_ENV);
		Zend_Registry::set('config', $config);
	}

	protected function _initView()
	{
		// 如希望在分发前端控制器前修改ViewRenderer设定，可采用下面的两种方法：
		// 1. 创建实例并注册自己的ViewRenderer对象，然后传入到助手经纪人。
		// $viewRenderer = new Zend_Controller_Action_Helper_ViewRenderer();
		// $viewRenderer->setView($view)
        //				->setViewSuffix('php');
		// Zend_Controller_Action_HelperBroker::addHelper($viewRenderer);

		// 2. 通过助手经纪人即时的初始化并/或获取ViewRenderer对象。
		// $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
		// $viewRenderer->setView($view)
        //				->setViewSuffix('php');

		// phpweb20原始采用第一种方法，这里采取第二种方法
		$view = new Templater();

		$viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('ViewRenderer');
		$viewRenderer->setView(new Templater())
					 ->setViewSuffix('tpl');
	}

	protected function _initRouter()
	{
		$this->bootstrap('frontController');
		$front = $this->getResource('frontController');

		$route = new Zend_Controller_Router_Route('user/:username/:action/*',
												  array('controller' => 'user',
														'action'	 => 'index'));
		$front->getRouter()->addRoute('user', $route);

		$route = new Zend_Controller_Router_Route('user/:username/view/:url/*',
												  array('controller' => 'user',
														'action'	 => 'view'));
		$front->getRouter()->addRoute('post', $route);

		$route = new Zend_Controller_Router_Route('user/:username/archive/:year/:month/*',
												  array('controller' => 'user',
														'action'	 => 'archive'));
		$front->getRouter()->addRoute('archive', $route);

		$route = new Zend_Controller_Router_Route('user/:username/tag/:tag/*',
												  array('controller' => 'user',
														'action'	 => 'tag'));
		$front->getRouter()->addRoute('tagspace', $route);

		$auth = Zend_Auth::getInstance();
		$auth->setStorage(new Zend_Auth_Storage_Session());

		$front->registerPlugin(new CustomControllerAclManager($auth));
	}

	protected function _initLog()
	{
		$config = Zend_Registry::get('config');
		$logger = new Zend_Log(new Zend_Log_Writer_Stream($config->logging->file));
		$writer = new EmailLogger($config->logging->email);
		$writer->addFilter(new Zend_Log_Filter_Priority(Zend_Log::CRIT));
		$logger->addWriter($writer);
		Zend_Registry::set('logger', $logger);
	}
}

