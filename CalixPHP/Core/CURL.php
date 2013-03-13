<?php
 /*
  *  @date 2012-5-21
  *  @author wuguoqing, Calix
  *  @Blog: http://blog.163.com/wu_guoqing/
  *
  */
  
class CURL extends CComponent implements IURL
{
	private $_strategy;
	private $_configs;
	private $_urlInfo;
	
	public function init(Array $configs)
	{
		$this->_initConfigs($configs);
		$this->_initStrategy();
		parent::init();
	}
	
	/**
	 * 策略模式 按其strategy得到urlInfo，返回格式为array(xx,xx,xx);
	 * @see IURL::getUrlInfo()
	 */
	public function getUrlInfo()
	{
		if(!isset($this->_urlInfo)){
			$this->_strategy->getUrlInfo($this);
		}	
		return $this->_urlInfo;
	}
	
	/**
	 * 设置urlInfo ...
	 * @param array $info
	 */
	public function setUrlInfo(Array $info)
	{
		$this->_urlInfo=$info;
	}
	
	/**
	 * 根据所配置的url格式初始化strategy
	 * 
	 */
	protected function _initStrategy()
	{
		if(!isset($this->_configs['format'])||$this->_configs['format']===""){
			$default_configs=$this->_defaultConfigs();
			$this->_configs['format']=$default_configs['format'];
		}
		$this->_strategy=CUrlStrategy::getStrategy($this->_configs['format']);
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
	 * 在用户没有在配置文件中定义url模块配置的情况下使用的默认配置
	 */
	protected function _defaultConfigs()
	{
		return array(
			'format'=>1,
			'queryer'=>'r',
			'group'=>'g',
			'controller'=>'c',
			'action'=>'a',
		);
	}
	
	/**
	 * 提供CURL的一些配置
	 *
	 */
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
	
}