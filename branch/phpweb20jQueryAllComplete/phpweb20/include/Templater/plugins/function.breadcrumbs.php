<?php

function smarty_function_breadcrumbs($params, $smarty)
{
	$defaultParams = array('trail'     => array(),
                           'separator' => ' &gt; ',
                           'truncate'  => 40);
	
	foreach ($defaultParams as $k => $v)
	{
		if (!isset($params[$k]))
			$params[$k] = $v;
    }
	
	if ($params['truncate'] > 0)
		require_once $smarty->_get_plugin_filepath('modifier', 'truncate');
		
	$links = array();
    $numSteps = count($params['trail']);
    for ($i = 0; $i < $numSteps; $i++)
	{
		$step = $params['trail'][$i];
		
		if ($params['truncate'] > 0)
			$step['title'] = smarty_modifier_truncate($step['title'], $params['truncate']);
		
		if (strlen($step['link']) > 0 && $i < $numSteps - 1)
		{
			$links[] = sprintf('<a href="%s" title="%s">%s</a>',
                                htmlspecialchars($step['link']),
                                htmlspecialchars($step['title']),
                                htmlspecialchars($step['title']));
        }
		else
		{
			$links[] = htmlspecialchars($step['title']);
        }
	}
	
	return join($params['separator'], $links);
}

?>