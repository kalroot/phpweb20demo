<?php

function smarty_function_imagefilename($params, $smarty)
{
	$config = Zend_Registry::get('config');
	$attachmentsPath = $config->paths->attachments;
	
	if (!isset($params['id']))
		return '/attachments/noimage.jpg';

	if (!isset($params['name']))
		return '/attachments/noimage.jpg';

	if (!isset($params['type']))
		return '/attachments/noimage.jpg';

	$completePath = sprintf('%s/%d%s.%s', 
							$attachmentsPath, $params['id'], $params['name'], $params['type']);

	$path = sprintf('/attachments/%d%s.%s', 
					$params['id'], $params['name'], $params['type']);
				
	if (!file_exists($completePath) || !is_readable($completePath))
		return '/attachments/noimage.jpg';

	return $path;
}

?>
