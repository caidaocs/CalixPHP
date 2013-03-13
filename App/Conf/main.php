<?php
 /*
  *  @date 2012-5-10
  *  @author wuguoqing, Calix
  *  @Blog: http://blog.163.com/wu_guoqing/
  *
  */
  
return array(
	
	/**
	 * 数据库模块
	 */	
	'db'=>array(
		'dsn'=>'mysql:host=localhost;dbname=dinnerdate',
		'username'=>'root',
		'password'=>'',
		'table_prefix'=>'',
		'charset'=>'utf8',
		'auto_connect'=>false,
	),
	
	
	
	/**
	 * 路由模块
	 * group_mode：是否启用分组
	 * group_list：分组列表 (分组模式下使用)
	 * default_group：默认分组 (分组模式下使用)
	 */
	'route'=>array(
		'default_controller'=>'Index',
		'default_action'=>'index',	
		'group_mode'=>true,
		'group_list'=>array('Home','Admin'),
		'default_group'=>'Home',
	),
	
	

	
	
	/**
	 * URL模块
	 * 
	 * 关于format:
	 * 0为一般模式：?g=Admin&c=Index&a=view&arg1=1&arg2=2
	 * 1为：/Admin/Index/view
	 * 2为：r=/Admin/Index/view
	 * 
	 * 关于queryer：
	 * 此项仅在模式2情况中使用
	 * ?r=Home/Index/welcome
	 * 
	 * 关于group,controller,action:
	 * 仅在模型0中使用
	 * 
	 * 
	 */
	'url'=>array(  
		'format'=>2,
		'queryer'=>'r',
		'group'=>'g',
		'controller'=>'c',
		'action'=>'a',
	
		/*
		'filter'=>array(
			''
		),
		*/
	),
	
	
	/**
	 * 视图模块
	 * ngin：是否使用模板引擎
	 */
	'view'=>array(
		'ngin'=>true,
		'caching'=>false,
		'cache_lifetime'=>0,
		'left_delimiter'=>'{(',
		'right_delimiter'=>')}',
	),
	
);