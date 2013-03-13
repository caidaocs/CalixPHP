<?php
 /*
  *  @date 2012-5-25
  *  @author wuguoqing, Calix
  *  @Blog: http://blog.163.com/wu_guoqing/
  *
  */
  
class CRender extends CComponent implements IRender
{
	protected $_strategy;
	
	protected $_configs;
	
	
	public function init($configs)
	{
		if(!is_array($configs)){
			throw new CComponentException("初始化Render组件失败！初始化参数必须为数组！", $this);
		}
		$this->_configs=$this->_defaultConfigs();
		$this->_configs=array_merge($this->_configs,$configs);
		$this->_initStrategy();
		parent::init();
	}
	
	
	protected function _initStrategy()
	{
		if(isset($this->_configs['ngin'])&&$this->_configs['ngin']==true){
			$this->_strategy=new Smarty();
			if(!isset($this->_configs['tpl_dir'])){
				throw new CComponentException("组件要求必须获得模板目录信息！", $this);
			}
			if(!isset($this->_configs['compile_dir'])){
				throw new CComponentException("组件要求必须获得编译目录信息！", $this);
			}
			if(!isset($this->_configs['cache_dir'])){
				throw new CComponentException("组件要求必须获得缓存目录信息！", $this);
			}
			
			//此三项由App提供
			$this->_strategy->template_dir=$this->_configs['tpl_dir'];
			$this->_strategy->compile_dir=$this->_configs['compile_dir'];
			$this->_strategy->cache_dir=$this->_configs['cache_dir'];
			
			//以下可由开发人员提供。
			$this->_strategy->caching=$this->_configs['caching'];
			$this->_strategy->cache_lifetime=$this->_configs['cache_lifetime'];
			$this->_strategy->left_delimiter=$this->_configs['left_delimiter'];
			$this->_strategy->right_delimiter=$this->_configs['right_delimiter'];
		}else{
			throw new CComponentException("暂时不开放不采用模板引擎的方案。", $this);
		}
	}
	
	
	public function assign($name,$value)
	{
		$this->_strategy->assign($name,$value);
	}
	
	
	public function display($tpl)
	{
		$this->_strategy->display($tpl);
	}
	
	public function render($tpl)
	{
		throw new CComponentException("暂时不开放不采用模板引擎的方案。", $this);
	}
	
	protected function _defaultConfigs()
	{
		return array(
			'ngin'=>true,
			'debuging'=>CALIX_DEFUG,
			'caching'=>false,
			'cache_lifetime'=>0,
			'left_delimiter'=>'{(',
			'right_delimiter'=>')}',
		);
	}
}