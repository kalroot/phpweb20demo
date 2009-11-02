1<?php

class Pw_Application_Resource_Logger extends Zend_Application_Resource_ResourceAbstract
{
	protected $_logger;

	public function init()
	{
		return $this->getLogger();
	}

	public function getLogger()
	{
		if ($this->_logger === null)
		{
			$this->_logger = new Zend_Log(new Zend_Log_Writer_Null());
			$options = $this->getOptions();

			if (!empty($options['file']))
				$this->_logger->addWriter(new Zend_Log_Writer_Stream($options['file']));
			else
				$this->_logger->addWriter(new Zend_Log_Writer_Stream('D:/www/phpweb20/data/logs/debug.log'));

			if (!empty($options['email']))
			{
				$writer = new EmailLogger($options['email']);
				$writer->addFilter(new Zend_Log_Filter_Priority(Zend_Log::CRIT));
				$this->_logger->addWriter($writer);
			}
		}
		
		return $this->_logger;
	}
}

?>