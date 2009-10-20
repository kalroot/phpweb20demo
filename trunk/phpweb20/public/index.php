<?php

// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    get_include_path(),
)));

/** Zend_Application */
require_once 'Zend/Application.php';

// Create application, bootstrap, and run
$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/../settings.ini'
	// 默认配置文件放在application/configs目录下，文件名为application.ini
);
$application->bootstrap()
            ->run();

/*
require_once('Zend/Loader/Autoloader.php');
$loader = Zend_Loader_Autoloader::getInstance();
$loader->setFallbackAutoloader(true);

$logger = new Zend_Log(new Zend_Log_Writer_Null());

try
{
	$writer = new EmailLogger($_SERVER['SERVER_ADMIN']);
	$writer->addFilter(new Zend_Log_Filter_Priority(Zend_Log::CRIT));
	$logger->addWriter($writer);

	$configFile = '';
	if (isset($_SERVER['APP_CONFIG_FILE']))
		$configFile = basename($_SERVER['APP_CONFIG_FILE']);

	if (strlen($configFile) == 0)
		$configFile = 'settings.ini';

	$configSection = '';
	if (isset($_SERVER['APP_CONFIG_SECTION']))
		$configSection = basename($_SERVER['APP_CONFIG_SECTION']);

	if (strlen($configSection) == 0)
		$configSection = 'production';

	$config = new Zend_Config_Ini('../' . $configFile, $configSection);
	Zend_Registry::set('config', $config);

	$logger->addWriter(new Zend_Log_Writer_Stream($config->logging->file));
	$writer->setEmail($config->logging->email);

	Zend_Registry::set('logger', $logger);

	$params = array('host'		=> $config->database->hostname,
					'username'	=> $config->database->username,
					'password'	=> $config->database->password,
					'dbname'	=> $config->database->database);

	$db = Zend_Db::factory($config->database->type, $params);
	$db->getConnection();

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
}
catch (Exception $ex)
{
	$logger->emerg($ex->getMessage());

	header('Location: /error.html');
	exit;
}*/