1<?php

class Pw_Application_Resource_Config extends Zend_Application_Resource_ResourceAbstract
{
	protected $_config;

	public function init()
	{
		return $this->getConfig();
	}

	public function getConfig()
	{
		if ($this->_config === null)
		{
			$options = $this->getOptions();
			$this->_config = new Zend_Config($options);
		}

		return $this->_config;
	}
}

?>