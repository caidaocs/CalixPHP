<?php
 /*
  *  @date 2012-5-21
  *  @author wuguoqing, Calix
  *  @Blog: http://blog.163.com/wu_guoqing/
  *
  */
  
class CConfig extends CComponent implements IConfig
{
	protected $_configPath;
	protected $_mainFile;
	protected $_configs;
	
	/**
	 *
	 * @see CComponent::init()
	 */
	public function init(Array $configs)
	{
		if(isset($configs['config_path'])){
			$this->_configPath=$configs['config_path'];
		}else{
			throw new 
				CComponentException("初始化Config组件失败，初始化参数中必须包含配置路径信息！",$this);
		}
		
		$this->_mainFile=isset($configs['config_mainfile'])?$configs['config_mainfile']:'main';
		parent::init();
	}
	
	/**
	 * 读取主配置文件配置
	 * @see IConfig::getConfigs()
	 */
	public function getConfigs()
	{
		return $this->_getConfig();
	}
	
	/**
	 * 读取主配置文件中某一模块的配置信息
	 * @see IConfig::getConfig()
	 */
	public function getConfig($module)
	{
		return $this->_getConfig($module);
	}
	
	/**
	 * 读取用户自定义的配置文件
	 * @see IConfig::getUserConfig()
	 */
	public function getUserConfig($fileName)
	{
		return $this->_getConfig(NULL,$fileName);
	}
	
	
	/**
	 * 读取配置文件
	 * @param  $module
	 * @param  $fileName
	 * @throws CComponentException
	 */
	protected function _getConfig($module=NULL,$fileName=NULL)
	{
		if(empty($fileName)){
			$fileName=$this->_mainFile;
		}
		if(!isset($this->_configs[$fileName])){
			$file_path=$this->_configPath.DIRECTORY_SEPARATOR.$fileName.".php";
			if(!file_exists($file_path)){
				throw new CComponentException("读取配置文件失败！找不到配置文件：{$file_path}。",$this);
			}
			$this->_configs[$fileName]=include($file_path);			
		}
		if(empty($this->_configs[$fileName])){
			return NULL;
		}
		if(empty($module)){
			return $this->_configs[$fileName];
		}elseif(!isset($this->_configs[$fileName][$module])){
			return NULL;
		}else{
			return $this->_configs[$fileName][$module];
		}
		
	}
}