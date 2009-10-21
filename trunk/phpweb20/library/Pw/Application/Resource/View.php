<?php

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