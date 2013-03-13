<?php
 /*
  *  @date 2012-5-22
  *  @author wuguoqing, Calix
  *  @Blog: http://blog.163.com/wu_guoqing/
  *
  */
  
Abstract class CAction
{
	protected $_controller;
	
	
	
	/**
	 * 构造函数，绑定从属控制器
	 * @param CController $controller
	 */
	public function __construct($controller)
	{
		$this->_controller=$controller;	
	}

	
	/**
	 * 主执行方法，子类必须继续
	 *
	 */
	abstract function run();
}