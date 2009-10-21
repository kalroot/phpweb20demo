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

//此处采用的是phpweb20 14章采用的方法，14张将错误氛围派发前错误以及派发中错误
//派发前的错误就是此处的try catch块，派发中的错误即ErrorController类
//如果此处不使用try catch块，那么派发前的错误将直接显示在屏幕上
//使用此处的try catch块，派发前的出现错误将记录日志，并将页面转到error.html上

require_once 'Zend/Loader/Autoloader.php';
Zend_Loader_Autoloader::getInstance()->setFallbackAutoloader(true);

$logger = new Zend_Log(new Zend_Log_Writer_Null());

try
{
	$writer = new EmailLogger($_SERVER['SERVER_ADMIN']);
    $writer->addFilter(new Zend_Log_Filter_Priority(Zend_Log::CRIT));
    $logger->addWriter($writer);

	$config = new Zend_Config_Ini(APPLICATION_PATH . "/../config.ini", 'config');
	Zend_Registry::set('config', $config);

	$logger->addWriter(new Zend_Log_Writer_Stream($config->logging->file));
	$writer->setEmail($config->logging->email);

	Zend_Registry::set('logger', $logger);


	/** Zend_Application */
	//require_once 'Zend/Application.php';

	// Create application, bootstrap, and run
	$application = new Zend_Application(
		APPLICATION_ENV,
		APPLICATION_PATH . '/../settings.ini'
	// 默认配置文件放在application/configs目录下，文件名为application.ini
	);

//把自己定义的类文件都放到library目录的Pw目录下，然后在配置文件中写入autoloadernamespaces.Pw = "Pw_"就可以自动加载了
//这里没有namespace前缀，所以要使用如下方法完成自动加载：
//$application->getAutoloader()->setFallbackAutoloader(true);

//Zend_Application_Module_Autoloader有一定的局限性，默认情况下他只能加载models, forms等系统定义的文件夹，自定义的不可以。
//所以此处application下只有controllers以及views文件夹，models下的DatabaseObject全部移至library

	$application->Bootstrap()->run();
}
catch (Exception $ex)
{
	$logger->emerg($ex->getMessage());

	header('Location: /error.html');
	exit;
}