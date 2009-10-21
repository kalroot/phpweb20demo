<?php

function smarty_function_get_tag_summary($params, $smarty)
{
	$db = Zend_Controller_Front::getInstance()->getParam('bootstrap')
					->getPluginResource('db')->getDbAdapter();
	$user_id = (int)$params['user_id'];
	
	$summary = DatabaseObject_BlogPost::GetTagSummary($db, $user_id);
	
	if (isset($params['assign']) && strlen($params['assign']) > 0)
		$smarty->assign($params['assign'], $summary);
}

?>