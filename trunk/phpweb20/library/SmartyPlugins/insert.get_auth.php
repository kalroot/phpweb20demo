<?php

function smarty_insert_get_auth($params, $smarty)
{
	require_once $smarty->_get_plugin_filepath('function', 'geturl');
	
	if (isset($params['section']) && strlen($params['section']) > 0)
		$section = $params['section'];
		
	$auth = Zend_Auth::getInstance();
	if ($auth->hasIdentity())
	{
		$html = '<li';
		if ($section == 'account')
			$html .= ' class="active">';
		else
			$html .= '>';
		
		$html .= '<a href="' . smarty_function_geturl(array('controller' => 'account'), $smarty);
		$html .= '">Your Account</a></li>';

		$html .= '<li';
		if ($section == 'blogmanager')
			$html .= ' class="active">';
		else
			$html .= '>';

		$html .= '<a href="' . smarty_function_geturl(array('controller' => 'blogmanager'), $smarty);
		$html .= '">Your Blog</a></li>';

		$html .= '<li><a href="' .
				smarty_function_geturl(array('controller' => 'account', 'action' => 'logout'), $smarty) .
				'">Logout</a></li>';
		return $html;
	}
	else
	{
		$html = '<li';
		if ($section == 'register')
			$html .= ' class="active">';
		else
			$html .= '>';
		
		$html .= '<a href="' . 
				smarty_function_geturl(array('controller' => 'account', 'action' => 'register'), $smarty);
		$html .= '">Register</a></li>';

		$html .= '<li';
		if ($section == 'login')
			$html .= ' class="active">';
		else
			$html .= '>';

		$html .= '<a href="' . smarty_function_geturl(array('controller' => 'account', 'action' => 'login'), $smarty) .
				'">login</a></li>';
		return $html;
	}
}

?>