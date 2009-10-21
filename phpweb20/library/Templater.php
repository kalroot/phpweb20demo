<?php

class Templater extends Zend_View_Abstract
{
	protected $_path;
	protected $_engine;
	
	public function __construct()
	{
		//此处不可使用如下方法获得bootstrap
		//$bootstrap = Zend_Controller_Front::getInstance()->getParam('bootstrap');
		//因为是application->run阶段才$front->setParam('bootstrap', $this); 见Bootstrap.php的76行
		//在具体的Controller实现的时候，就可以使用此种方法。
		//若此时采用此种方法获取bootstrap，获取的bootstrap为空，如下：
		//Zend_Debug::dump(Zend_Controller_Front::getInstance()->getParam('bootstrap'));

		//所以此处采用的办法是从Resource把config传过来
		$config = Zend_Registry::get('config');
		
		require_once('Smarty/Smarty.class.php');
		
		$this->_engine = new Smarty();
		$this->_engine->template_dir = APPLICATION_PATH . '/views';
		$this->_engine->compile_dir = sprintf('%s/tmp/templates_c', $config->paths->data);
		$this->_engine->cache_dir = sprintf('%s/tmp/cache_dir', $config->paths->data);
		
		$this->_engine->plugins_dir = array(APPLICATION_PATH . '/../library/SmartyPlugins', 'plugins');
	}
	
	public function getEngine()
	{
		return $this->_engine;
	}
	public function __set($key, $val)
	{
		$this->_engine->assign($key, $val);
	}
	public function __get($key)
	{
		return $this->_engine->get_template_vars($key);
	}
	
	public function __isset($key)
	{
		return $this->_engine->get_template_vars($key) !== null;
	}
	public function __unset($key)
	{
		$this->_engine->clear_assign($key);
	}
	public function assign($spec, $value = null)
	{
		if (is_array($spec))
		{
			$this->_engine->assign($spec);
			return;
		}
		
		$this->_engine->assign($spec, $value);
	}
	public function clearVars()
	{
		$this->_engine->clear_all_assign();
	}
	public function render($name)
	{
		return $this->_engine->fetch(strtolower($name));
	}
	public function _run()
	{ }
}	

?>