<?php
 /*
  *  @date 2012-5-21
  *  @author wuguoqing, Calix
  *  @Blog: http://blog.163.com/wu_guoqing/
  *
  */

define("CALIX_PATH", rtrim(dirname(__FILE__)));
class Calix
{
	private static $_app;
	private static $_calixPath=CALIX_PATH;
	private static $_importedClasses;
	
	public static function systemRun()
	{
		$app=new CApplication();
		$app->run();
	}

	
	/**
	 * 安装应用
	 * @param CApplication $app
	 */
	public static function setApplication(CApplication $app)
	{
		if(!isset(self::$_app)){
			self::$_app=$app;
			self::$_allowAutoloadPaths=array_merge(self::$_allowAutoloadPaths,
													self::$_app->getAutoloadPaths());
		}
	}
	
	
	/**
	 * 获得当前应用
	 * @return CApplication $app
	 */
	public static function getApp()
	{
		return self::$_app;
	}
	
	
	/**
	 * 创建组件 ...
	 * @param array $configs
	 * @throws CSystemException
	 */
	public static function createComponent(Array $configs)
	{
		if(!isset($configs['class'])){
			throw new CSystemException("创建组件异常，数组必须包括键为'class'的元素！",$this);
		}
		
		$class=$configs['class'];
		unset($configs['class']);
		
		if(($n=func_num_args())>1){
			$args=func_get_args();
			if($n==2){
				$component=new $class($args[1]);
			}elseif($n==3){
				$component=new $class($args[1],$args[2]);
			}elseif($n==4){
				$component=new $class($args[1],$args[2],$args[3]);
			}else{
				unset($args[0]);
				$rc=new ReflectionClass($class);
				$component=call_user_func_array(array($rc,'newInstance'),$args);
			}
		}else{
			$component=new $class();
		}
		
		if(count($configs)>0){
			foreach($configs as $k=>$v)
			{
				$component->$k=$v;
			}
		}
		
		return $component;
	}
	
	
	
	/**
	 * 自动加载器...
	 * @param  $class
	 */
	public static function autoload($class)
	{
		//===========兼容第三方类库的自动加载器========
		$allow=true;
		$allow=self::_beforeAutoload($class);		
		if($allow==false){
			//如果不允许Calix加载，则交给下一个加载器。
			return;
		}
			
		
		//===========Calix加载工作开始============
		if(isset(self::$_coreClassPaths[$class])){
			include(self::$_calixPath.DIRECTORY_SEPARATOR.self::$_coreClassPaths[$class]);
		}else{
			if(!empty(self::$_allowAutoloadPaths)){
				foreach (self::$_allowAutoloadPaths as $includePath)
				{
					if(file_exists($includePath.DIRECTORY_SEPARATOR.$class.".php")){
						include($includePath.DIRECTORY_SEPARATOR.$class.".php");	
						break;
					}					
				}
			}
		}		
		$found=class_exists($class,false)||interface_exists($class,false);
		if(!$found){
			trigger_error("Calix：加载失败！找不到类{$class}");
		}
	}	
	
	
	
	
	/**
	 * 自动加载前的工作，判断是否应该由第三方自动加载器加载该类
	 * @param $class
	 * @return boolean
	 */
	private static function _beforeAutoload($class)
	{
		$_class = strtolower($class);
		$allow_calixload=true;
		
		//以下为判断是否应该把加载工作交给各种第三方类库的自动加载器
    	if (defined("SMARTY_SPL_AUTOLOAD")&&
    		(substr($_class, 0, 16) === 'smarty_internal_' || $_class == 'smarty_security')) {
    		//兼容Smarty	
    		//如果是与Smarty相关的类，且存在Smarty自动加载器，则此加载工作交给Smarty处理
    		$allow_calixload=false;
			
		}	
		return $allow_calixload;
	}
	
	
	//引入文件
	public static function import($filename,$path)
	{
		static $_import=array();
		$import_key=md5($filename.$path);
		if(isset($_import[$import_key])){
			return;
		}else{
			require($path.DIRECTORY_SEPARATOR.$filename);
		}
	}
	
	public static function importExtLib($filename)
	{
		self::import($filename, self::getApp()->getAppSourcePath("Ext").
									DIRECTORY_SEPARATOR.
									self::getApp()->getAppSourcePath("Lib",true));
	}
	
	public static function importExtHelper($filename)
	{
		self::import($filename, self::getApp()->getAppSourcePath("Ext").
				DIRECTORY_SEPARATOR.
				self::getApp()->getAppSourcePath("Helper",true));
	}
	
	/**
	 * 
	 * 系统核心类 ...
	 * @var 
	 */
	private static $_coreClassPaths=array(
		'CComponent'=>'Core/CComponent.php',
		'CApplication'=>'Core/CApplication.php',
		'CConfig'=>'Core/CConfig.php',
		'CErrorEvent'=>'Core/Error/CErrorEvent.php',
		'CErrorHandler'=>'Core/Error/CErrorHandler.php',
		'CEvent'=>'Core/CEvent.php',
		'CException'=>'Core/Exception/CException.php',
		'CComponentException'=>'Core/Exception/CComponentException.php',
		'CHttpException'=>'Core/Exception/CHttpException.php',
		'CDbException'=>'Core/Exception/CDbException.php',
		'CExceptionHandler'=>'Core/Exception/CExceptionHandler.php',
		'CSystemException'=>'Core/Exception/CSystemException.php',
		'CRouter'=>'Core/CRouter.php',
		'CRouterStrategy'=>'Core/CRouterStrategy.php',
		'CURL'=>'Core/CURL.php',
		'CUrlStrategy'=>'Core/CUrlStrategy.php',
		'CUrlFilter'=>'Core/CUrlFilter.php',
		'CController'=>'Core/CController.php',
		'CAction'=>'Core/CAction.php',
		'CDbConnection'=>'Core/DB/CDbConnection.php',
		'CDbCommand'=>'Core/DB/CDbCommand.php',
		'CDbDataReader'=>'Core/DB/CDbDataReader.php',
		'CModel'=>'Core/CModel.php',
		'CActiveRecord'=>'Core/DB/AR/CActiveRecord.php',
		'CRender'=>'Core/Render/CRender.php',
		'Smarty'=>'Core/Render/Tools/Smarty/Smarty.class.php',
	);
	
	//绝对路径
	private static $_allowAutoloadPaths=array(		
	);
	
}
require 'Core/interfaces.php';
require 'Core/Common.php';
spl_autoload_register(array("Calix","autoload"));