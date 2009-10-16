<?php

require_once('Zend/Loader/Autoloader.php');
$loader = Zend_Loader_Autoloader::getInstance();
$loader->setFallbackAutoloader(true);

$config = new Zend_Config_Ini('../settings.ini', 'development');
Zend_Registry::set('config', $config);

$logger = new Zend_Log(new Zend_Log_Writer_Stream($config->logging->file));

try
{
	$writer = new EmailLogger($config->logging->email);
	$writer->addFilter(new Zend_Log_Filter_Priority(Zend_Log::CRIT));
	$logger->addWriter($writer);
}
catch (Exception $ex)
{
	
}

Zend_Registry::set('logger', $logger);

$logger->crit('Test message');

$params = array('host'		=> $config->database->hostname,
				'username'	=> $config->database->username,
				'password'	=> $config->database->password,
				'dbname'	=> $config->database->database);

$db = Zend_Db::factory($config->database->type, $params);
Zend_Registry::set('db', $db);

$auth = Zend_Auth::getInstance();
$auth->setStorage(new Zend_Auth_Storage_Session());

$controller = Zend_Controller_Front::getInstance();
$controller->setControllerDirectory($config->paths->base . '/include/Controllers');
$controller->registerPlugin(new CustomControllerAclManager($auth));

$vr = new Zend_Controller_Action_Helper_ViewRenderer();
$vr->setView(new Templater());
$vr->setViewSuffix('tpl');
Zend_Controller_Action_HelperBroker::addHelper($vr);

$route = new Zend_Controller_Router_Route('user/:username/:action/*',
										  array('controller' => 'user',
										        'action'	 => 'index'));
$controller->getRouter()->addRoute('user', $route);

$route = new Zend_Controller_Router_Route('user/:username/view/:url/*',
										  array('controller' => 'user',
										  		'action'	 => 'view'));
$controller->getRouter()->addRoute('post', $route);

$route = new Zend_Controller_Router_Route('user/:username/archive/:year/:month/*',
										  array('controller' => 'user',
										  		'action'	 => 'archive'));
$controller->getRouter()->addRoute('archive', $route);

$route = new Zend_Controller_Router_Route('user/:username/tag/:tag/*',
										  array('controller' => 'user',
										  		'action'	 => 'tag'));
$controller->getRouter()->addRoute('tagspace', $route);

$controller->dispatch();

?>