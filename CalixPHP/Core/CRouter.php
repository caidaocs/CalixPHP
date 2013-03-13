<?php
 /*
  *  @date 2012-5-21
  *  @author wuguoqing, Calix
  *  @Blog: http://blog.163.com/wu_guoqing/
  *
  */
  
class CRouter extends CComponent implements IRouter
{
	private $_urlInfo;
	private $_stategy;
	private $_configs;
	private $_controllerPath;
	private $_group;
	private $_controller;
	private $_action;
	private $_args;
	
	
	public function init($configs)
	{
		if(!isset($configs['controller_path'])){
			throw new
				 CComponentException("初始化Router组件失败！初始化参数中必须包括控制器路径信息！",$this);
		}else{
			$this->_controllerPath=$configs['controller_path'];
		}
		
		$this->_initConfigs($configs);
		$this->_initStrategy();
		parent::init();
	}
	
	public function receiveUrlInfo($info)
	{
		$this->_urlInfo=$info;
	}
	
	public function getUrlInfo()
	{
		return $this->_urlInfo;
	}
	
	
	
	public function findRoute()
	{
		return $this->_stategy->findRoute($this);
	}

	
	/**
	 * 根据所配置初始化strategy
	 * 
	 */
	protected function _initStrategy()
	{
		$mode=$this->getConfigs('group_mode');
		$mode=$mode?1:0;
		$this->_stategy=CRouterStrategy::getStrategy($mode);
	}
	
	/**
	 * 初始化配置参数
	 */
	protected function _initConfigs($configs)
	{
		$this->_configs=$configs;
		if(empty($this->_configs)){
			$this->_defaultConfigs();
		}	
	}
	
	
	/**
	 * 默认配置
	 *
	 */
	private function _defaultConfigs()
	{
		return array(
			'default_controller'=>'Index',
			'default_action'=>'welcome',		
			'group_mode'=>false,//是否启用分组
			'group_list'=>array('Index','Admin'),//分组模式下使用
			'default_group'=>'Index',//分组模式下使用
		);
	}
	
	public function getConfigs($item=NULL)
	{
		$rt=NULL;
		if(isset($item)){
			if(isset($this->_configs[$item])){
				$rt=$this->_configs[$item];
			}
		}else{
			$rt=NULL;
		}
		
		return $rt;
	}
	
	/**
	 * @return the $_group
	 */
	public function getGroup() {
		return $this->_group;
	}

	/**
	 * @return the $_controller
	 */
	public function getController() {
		return $this->_controller;
	}

	/**
	 * @return the $_action
	 */
	public function getAction() {
		return $this->_action;
	}

	/**
	 * @return the $_args
	 */
	public function getArgs() {
		return $this->_args;
	}

	/**
	 * @param $_group
	 */
	public function setGroup($_group) {
		$this->_group = $_group;
	}

	/**
	 * @param $_controller
	 */
	public function setController($_controller) {
		$this->_controller = $_controller;
	}

	/**
	 * @param $_action
	 */
	public function setAction($_action) {
		$this->_action = $_action;
	}

	/**
	 * @param  $_args
	 */
	public function setArgs($_args) {
		$this->_args = $_args;
	}

	
	
	
}