<?php
 /*
  *  @date 2012-5-21
  *  @author wuguoqing, Calix
  *  @Blog: http://blog.163.com/wu_guoqing/
  *
  */
  
class CApplication implements IApplication
{
	protected $_components = array();
	protected $_appPath;
	protected $_appStruct;
	protected $_handleCharset;
	
	
	
	public function __construct()
	{
		$this->_init();
		$this->_initSystemHandler();
		$this->_loadComponents();
		Calix::setApplication($this);		
	}
	
	
	
	/**
	 * 初始化应用目录结构 ...
	 *
	 */
	protected function _init()
	{
		if(defined("R_APP_PATH")){
			define("APP_PATH",rtrim(realpath(".").DIRECTORY_SEPARATOR.R_APP_PATH));
		}else{
			define("APP_PATH",rtrim(realpath(".")));
		}
		$this->_appPath=APP_PATH;

		$this->_appStruct=$this->_defaultStruct();
	}
	
	
	
	/**
	 * 取得应用某一项资源的路径...
	 * @param $dir
	 */
	public function getAppSourcePath($s,$bareName=false)
	{
		if(isset($this->_appStruct[$s])){
			if($bareName==false){
				return $this->_appPath.DIRECTORY_SEPARATOR.$this->_appStruct[$s];
			}else{
				return $this->_appStruct[$s];
			}
		}
		return NULL;
	}
	
	
	
	/**
	 * 设置应用目录中允许自动加载的路径 ...
	 * @return Array 允许自动加载的应用路径
	 */
	public function getAutoloadPaths()
	{
		return array(
			$this->getAppSourcePath('Model'),
			$this->getAppSourcePath('Action'),
			$this->getAppSourcePath('Component'),
		);
	}
	
	
	
	/**
	 * 初始化系统错误及异常处理组件 ...
	 *
	 */
	protected function _initSystemHandler()
	{
		$this->_handleCharset='utf-8';
		error_reporting(E_ALL^E_STRICT);
		set_error_handler(array($this,"errorHandle"),error_reporting());
		set_exception_handler(array($this,"exceptionHandle"));
	}
	
	
	
	/**
	 * 应用产生错误的处理方法 ...
	 * @param $code
	 * @param $message
	 * @param $file
	 * @param $line
	 */
	public function errorHandle($code,$message,$file,$line)
	{
		$this->_setHandleCharset();
		$handler=$this->getComponent("ErrorHandler");
		if(!empty($handler)){
			$handler->handle(new CErrorEvent($code, $message, $file, $line,$this));
		}else{
			$this->displayError($code,$message,$file,$line);
		}
		die();
	}
	
	
	/**
	 * 错误报告，在没有或者尚未为应用对象加载错误处理组件时，调用此方法 ...
	 * @param $code
	 * @param $message
	 * @param $file
	 * @param $line
	 */
	public function displayError($code,$message,$file,$line)
	{
		if(CALIX_DEBUG){
			echo "<h1>PHP Error [$code]</h1>\n";
			echo "<p>$message ($file:$line)</p>\n";
			echo '<pre>';

			$trace=debug_backtrace();
			if(count($trace)>3){
				$trace=array_slice($trace,3);
			}
			foreach($trace as $i=>$t)
			{
				if(!isset($t['file'])){
					$t['file']='unknown';
				}
				if(!isset($t['line'])){
					$t['line']=0;
				}
				if(!isset($t['function'])){
					$t['function']='unknown';
				}
				echo "#$i {$t['file']}({$t['line']}): ";
				if(isset($t['object']) && is_object($t['object'])){
					echo get_class($t['object']).'->';
				}
				echo "{$t['function']}()<br>\n";
			}

			echo '</pre>';
		}else{
			echo '<h1>Error 500 内部错误。</h1>';
		}
	}
	
	
	
	/**
	 *  应用产生异常的处理方法...
	 * @param Exception $exception
	 */
	public function exceptionHandle($exception)
	{
		$this->_setHandleCharset();
		$handler=$this->getComponent("ExceptionHandler");
		if(!empty($handler)){
			$handler->handle($exception);
		}else{
			$this->displayException($exception);
		}
		die();
	}
	
	
	
	/**
	 * 显示异常报告，在没有或者尚未为应用对象加载异常处理组件时，调用此方法 ...
	 * @param $exception
	 */
	public function displayException($exception)
	{
		if(CALIX_DEBUG){
			echo '<h1>'.get_class($exception)."</h1>\n";
			echo '<p>'.$exception->getMessage().' ('.$exception->getFile().':'.$exception->getLine().')</p>';
			echo '<pre>'.$exception->getTraceAsString().'</pre>';
		}else{
			echo '<h1>'.get_class($exception)."</h1>\n";
			echo '<p>'.$exception->getMessage().'</p>';
		}
	}
	
	/**
	 * 设置错误信息编码
	 *
	 */
	protected function _setHandleCharset()
	{
		if(!headers_sent()){
			//错误信息用utf-8编写。
			header("Content-Type:text/html;charset={$this->_handleCharset}"); 
		}
	}
	
	/**
	 * 加载核心组件 ...
	 *
	 */
	protected function _loadComponents()
	{
		if(!empty($this->_coreComponents)){
			foreach ($this->_coreComponents as $k=>$v) {
				$this->_components[$k]=$this->getComponent($k);
			}
		}
	}
	
	
	/**
	 * 获得组件
	 * @param $component
	 * @throws CSystemException
	 * @return $mix
	 * 
	 */
	public function getComponent($component)
	{
		if(!isset($this->_coreComponents[$component])){
			throw new CSystemException("{应用必需组件中不包含组件{$component}}", $this);
		}
		if(!isset($this->_components[$component])
			&&isset($this->_coreComponents[$component])){
			$this->_components[$component]=Calix::createComponent($this->_coreComponents[$component]);
			
		}
		return $this->_components[$component];
	}
	
	
	public function setComponent($name,$component)
	{
		if(!isset($this->_components[$name])){
			$this->_components[$name]=$component;
		}
	}
	
	protected function _initComponents()
	{
		//初始化核心组件
		$config=$this->getComponent("Config");
		$url=$this->getComponent("URL");
		$router=$this->getComponent("Router");
		
		 //初始化配置组件
		$config->init(array(
			'config_path'=>$this->getAppSourcePath('Config'),
			'config_mainfile'=>$this->_appStruct['ConfigMainFileName'],	
		));
		
		 //初始化URL组件
		$url->init($config->getConfig('url'));
		
		 //初始化路由组件
		$router_init_configs=$config->getConfig('route');
		if(empty($router_init_configs)||!is_array($router_init_configs)){
			$router_init_configs=array();
		}
		$router_init_configs=array_merge($router_init_configs,
								array('controller_path'=>$this->getAppSourcePath('Controller')));
		$router->init($router_init_configs);	

		
		//是否自动加载DB组件
		$db_configs=$config->getConfig('db');
		if(isset($db_configs['auto_connect'])
			&&$db_configs['auto_connect']===true){
			$db=Calix::createComponent(array('class'=>'CDbConnection'));
			$db->init($db_configs);
			$this->_components['DB']=$db;
		}
		
		
		
		
	}
	
	
	
	/**
	 * 返回DB组件
	 * @return CDbConnection
	 */
	public function getDb()
	{
		if(!isset($this->_components['DB'])){
			$config=$this->getComponent('Config');
			$db_config=$config->getConfig('db');
			$db=Calix::createComponent(array('class'=>'CDbConnection'));
			$db->init($db_config);
			$this->_components['DB']=$db;
		}
		return $this->_components['DB'];		
	}
	
	
	/**
	 * 处理请求准备工作 ...
	 *
	 */
	protected function _beginRequest()
	{
		$this->_initComponents();	
	}
	
	
	
	/**
	 * 处理请求 ...
	 * @throws CHttpException
	 * @throws CSystemException
	 */
	protected function _processRequest()
	{
		$url=$this->getComponent("URL");
		$router=$this->getComponent("Router");
		
		if($url->isInited()===false){
			throw new CSystemException("URL组件尚未完成初始化工作！", $this);
		}elseif($router->isInited()===false){
			throw new CSystemException("Router组件尚未完成初始化工作！", $this);
		}
		
		$router->receiveUrlInfo($url->getUrlInfo());
		try {
			//寻找路由过程中抛出的异常在产生模式下视为HTTP 404 类型的请求异常。
			$router->findRoute();
		}catch (CException $e){
			if(CALIX_DEFUG){
				$this->exceptionHandle($e);
			}else{
				throw new CHttpException(404,$this);
			}
		}
		
		$c=$router->getController();
		$a=$router->getAction();
		$controller=new $c();
		if(!($controller instanceof CController)){
			if(CALIX_DEFUG){
				throw new CComponentException("控制器{$c}必须继承CController", $this);
			}else{
					throw new CHttpException(500,$this);
			}
		}
		
		//动态初始化渲染组件 V===========================
		$config=$this->getComponent("Config");
		$render_configs=$config->getConfig('view');
			//模板路径
		$group=$router->getGroup();
		if(!empty($group)){
			$tpl_dir=$this->getAppSourcePath('View').DIRECTORY_SEPARATOR.$group;
		}else{
			$tpl_dir=$this->getAppSourcePath('View');
		}
		
		
			
		$ngin_dir=$this->getAppSourcePath('Cache').DIRECTORY_SEPARATOR.
					$this->getAppSourcePath('TplNgin',true);//缓存资源中模板引擎的路径
				
		$cache_dir=$ngin_dir.DIRECTORY_SEPARATOR.
					$this->getAppSourcePath('CacheDir',true);//缓存路径 
					
				
		$compile_dir=$ngin_dir.DIRECTORY_SEPARATOR.
						$this->getAppSourcePath('CompileDir',true);//编译路径 

			//如果路径不存在，则自动创建			
		foreach (array($ngin_dir,$cache_dir,$compile_dir) as $dir)
		{
			if(!is_dir($dir)){
				mkdir($dir,0777,true);
			}
		}				
					
		$render_app_configs=array(
			'tpl_dir'=>$tpl_dir,
			'cache_dir'=>$cache_dir,
			'compile_dir'=>$compile_dir,
						
		);
		
		$render_configs=array_merge($render_configs,$render_app_configs);
		$render=$this->getComponent("Render");
		$render->init($render_configs);
		
		
		//动态初始化控制器组件 C============================
		$controller_configs=array(
			'render'=>$render,
		);
		$controller->init($controller_configs);
		
		$this->setComponent('Controller', $controller);
		try {
			$controller->run($a);
		}catch (CException $e){
		if(CALIX_DEFUG){
				$this->exceptionHandle($e);
			}else{
				throw new CHttpException(404,$this);
			}
		}			
	}
	
	
	
	/**
	 * 运行应用
	 * @see IApplication::run()
	 */
	public function run()
	{				
		$this->_beginRequest();				
		$this->_processRequest();
	}
	
	
	
	/**
	 * 默认的目录结构 ...
	 *
	 */
	protected function _defaultStruct()
	{
		return array(
		    //缓存
			'Cache'=>'Cache',
			'TplNgin'=>'TplNgin',
			'CacheDir'=>'Cache',
			'CompileDir'=>'Compile',
			
		    //配置
			'Config'=>'Conf',
			'ConfigMainFileName'=>'main',
			
		    //控制器
			'Controller'=>'Controller',
				
			//动作
			'Action'=>'Action',	
		
			//扩展
			'Ext'=>'EXT',
			'Helper'=>'Helper',
			'Lib'=>'Lib',
			
			'Model'=>'Model',
			'Public'=>'Public',
			'View'=>'View',
				
			//自定义组件
			'Component'=>'Component',
		);
	}
	
	
	
	
	//需要加载的核心组件
	protected $_coreComponents=array(
		'ErrorHandler'=>array(
			'class'=>'CErrorHandler',
		),
		'ExceptionHandler'=>array(
			'class'=>'CExceptionHandler',
		),	
		'Config'=>array(
			'class'=>'CConfig',
		),
		'URL'=>array(
			'class'=>'CURL',
		),
		'Router'=>array(
			'class'=>'CRouter',
		),
		'Render'=>array(
			'class'=>'CRender',
		)
	);
}