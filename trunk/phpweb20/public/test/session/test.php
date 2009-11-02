<?php

set_include_path(implode(PATH_SEPARATOR, array(
    realpath('D:/www/phpweb20/library'),
    get_include_path(),
)));

require_once 'Zend/Loader/Autoloader.php';
Zend_Loader_Autoloader::getInstance()->setFallbackAutoloader(true);

Zend_Session::start();
Zend_Session::rememberMe();

?>