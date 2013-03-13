<?php
 /*
  *  @date 2012-5-10
  *  @author wuguoqing, Calix
  *  @Blog: http://blog.163.com/wu_guoqing/
  *
  */
  
abstract class CRouterStrategy
{
	protected $_router;
	protected static $_strategies;
	protected static $_mappings;
	
	abstract function findRoute(CRouter $router);
	
	
	public static function getStrategy($mode=0)
	{
		if(!isset(self::$_mappings)){
			self::$_mappings=self::_mapping();
		}		
		if(!isset(self::$_mappings[$mode])){
			$mode=0;
		}		
		$strategy=self::$_mappings[$mode];
		if(!isset(self::$_strategies[$mode])){
			self::$_strategies[$mode]=new $strategy();
		}
				
		return self::$_strategies[$mode];
	}
	
	protected static function _mapping()
	{
		return array(
			0=>"CNoGroups",
			1=>"CGroups",
		);
	}
}


class CNoGroups extends CRouterStrategy
{
	public function findRoute(CRouter $router)
	{
		$this->_router=$router;
		$url_info=$this->_router->getUrlInfo();
		$count=count($url_info);
		$controller=NULL;
		$action=NULL;
		$args=array();
		$defalut_controller=$this->_router->getConfigs('default_controller');
		$defalut_action=$this->_router->getConfigs('default_action');
		if($count==1){			
			$controller=$url_info[0];
			$action=$defalut_action;
		}else if($count>1){
			$controller=$url_info[0];
			$action=$url_info[1];
			for($i=2;$i<$count;++$i)
			{
				if($i%2!=0){
					continue;
				}else{
					$args[$url_info[$i]]=isset($url_info[$i+1])?$url_info[$i+1]:NULL;
				}
			}
			$_GET=array_merge($_GET,$args);		
		}else{
			$controller=$defalut_controller;
			$action=$defalut_action;
		}
		
		//对controller进行检验
		$controller=$controller."Controller";		
		$controller_path=$this->_router->getConfigs("controller_path");
		$controller_file=$controller_path.DIRECTORY_SEPARATOR.$controller.".php";
		if(!file_exists($controller_file)){
			throw new CComponentException("找不到控制器文件：{$controller_file}",$this->_router);

		}
		include($controller_file);
		if(!class_exists($controller,false)){
			throw new CComponentException("控制器文件：{$controller_file}
							中找不到相应的控制器：{$controller}",$this->_router);
		}
		
		$this->_router->setController($controller);
		$this->_router->setAction($action);
		$this->_router->setArgs($args);
		return true;
	}
}

class CGroups extends CRouterStrategy
{
	public function findRoute(CRouter $router)
	{
		$this->_router=$router;
		$url_info=$this->_router->getUrlInfo();
		$count=count($url_info);
		
		$group_list=$this->_router->getConfigs("group_list");
		$default_group=$this->_router->getConfigs("default_group");
		$default_controller=$this->_router->getConfigs("default_controller");
		$default_action=$this->_router->getConfigs("default_action");
		$group=NULL;
		$controller=NULL;
		$action=NULL;
		$controller_path=$this->_router->getConfigs("controller_path");
		
		if($count>=1){						
			//当至少有一个路由参数时
			
			$args=array();
			
			//此做法可以兼容在确定某些路由参数是分组或控制器或方法的情况下。
			if($count>=3&&(is_null($url_info[0])||
							is_null($url_info[1])||
							is_null($url_info[2]))){
				$url_info[0]=is_null($url_info[0])||(!in)?$default_group:$url_info[0];
				$url_info[1]=is_null($url_info[1])?$default_controller:$url_info[1];
				$url_info[2]=is_null($url_info[2])?$default_action:$url_info[2];
				if(!in_array($url_info[0], $group_list)){
					throw new CComponentException("路由错误：找不到模块{$url_info[0]}",$this->_router);
				}
			}
			
			if(in_array($url_info[0], $group_list)){
				//如果是请求分组
				$group=$url_info[0];
				$controller=isset($url_info[1])?$url_info[1]:$default_controller;
				$action=isset($url_info[2])?$url_info[2]:$default_action;
				
				for($i=3;$i<$count;++$i)
				{
					if($i%2==0){
						continue;
					}else{
						$args[$url_info[$i]]=isset($url_info[$i+1])?$url_info[$i+1]:NULL;
					}
				}
				
			}else{
				//如果不是请求分组
				
				//默认分组是否有$url_info[0]的控制器
				$controller_file=$controller_path.DIRECTORY_SEPARATOR.
									$default_group.DIRECTORY_SEPARATOR.$url_info[0]."Controller.php";
				if(file_exists($controller_file)){
					//如果有该控制器，则判断当前为请求默认分组
					$group=$default_group;
					$controller=$url_info[0];
					$action=isset($url_info[1])?$url_info[1]:$default_action;
					
					//获得$_GET参数
					for($i=2;$i<$count;++$i)
					{
						if($i%2==0){
							$args[$url_info[$i]]=isset($url_info[$i+1])?$url_info[$i+1]:NULL;
						}else{
							continue;
						}
					}
				}else{
					//如果没有该控制器，则请求出错
					throw new CComponentException("路由错误：找不到模块{$url_info[0]}",$this->_router);
				}	
			}	
			//合并由URL传递的参数，通过GET方式获得。
			$_GET=array_merge($_GET,$args);
		}else{
			//如果没有路由参数，则判断为请求默认分组，默认控制器和方法
			$group=$default_group;
			$controller=$default_controller;
			$action=$default_action;
		}
		
		//是否有该控制器文件？
		$controller=$controller."Controller";
		$controller_file=$controller_path.DIRECTORY_SEPARATOR.
									$group.DIRECTORY_SEPARATOR.$controller.".php";
		if(file_exists($controller_file)){
			include($controller_file);
			if(!class_exists($controller,false)){
				throw new CComponentException("控制器文件：{$controller_file}
								中找不到相应的控制器：{$controller}",$this->_router);
			}
		
			$this->_router->setGroup($group);
			$this->_router->setController($controller);
			$this->_router->setAction($action);
			if(isset($args))
				$this->_router->setArgs($args);
			return true;
		}else{
			throw new CComponentException("路由错误：在指定{$group}下找不到控制器文件：
							{$controller}",$this->_router);
		}
									
	}
}