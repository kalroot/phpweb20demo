<?php

require_once('Zend/Loader/Autoloader.php');
$loader = Zend_Loader_Autoloader::getInstance();
$loader->setFallbackAutoloader(true);

$mail = new Zend_Mail();
$mail->addTo('admin@phpweb20.com')
	->setFrom('google@phpweb20.com')
	->setSubject('subject')
	->setBodyText('this is body')
	->send();

?>
