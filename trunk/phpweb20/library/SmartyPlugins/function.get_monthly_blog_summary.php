<?php

function smarty_function_get_monthly_blog_summary($params, $smarty)
{
	$options = array();
	
	if (isset($params['user_id']))
		$options['user_id'] = (int)$params['user_id'];
	
	if (isset($params['liveOnly']) && $params['liveOnly'])
		$options['status'] = Model_DatabaseObject_BlogPost::STATUS_LIVE;
	
	$db = Zend_Registry::get('db');
	
	$summary = Model_DatabaseObject_BlogPost::GetMonthlySummary($db, $options);
	
	if (isset($params['assign']) && strlen($params['assign']) > 0)
		$smarty->assign($params['assign'], $summary);
}

?>