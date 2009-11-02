1<?php

// 如希望在分发前端控制器前修改ViewRenderer设定，可采用下面的两种方法：
// 1. 创建实例并注册自己的ViewRenderer对象，然后传入到助手经纪人。
// $viewRenderer = new Zend_Controller_Action_Helper_ViewRenderer();
// $viewRenderer->setView($view)
//				->setViewSuffix('php');
// Zend_Controller_Action_HelperBroker::addHelper($viewRenderer);

// 2. 通过助手经纪人即时的初始化并/或获取ViewRenderer对象。
// $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
// $viewRenderer->setView($view)
//				->setViewSuffix('php');

// phpweb20原始采用第一种方法，这里采取第二种方法

class Pw_Application_Resource_View extends Zend_Application_Resource_ResourceAbstract
{
	protected $_view;

	public function init()
	{
		if ($this->_view === null)
		{
			$_view = new Templater();

			$viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('ViewRenderer');
			$viewRenderer->setView($_view)
						 ->setViewSuffix('tpl');
		}

		return $this->_view;
	}
}

?>