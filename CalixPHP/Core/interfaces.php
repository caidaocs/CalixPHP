<?php
 /*
  *  @date 2012-5-21
  *  @author wuguoqing, Calix
  *  @Blog: http://blog.163.com/wu_guoqing/
  *
  */
  

interface IApplication
{
	function run();
}

interface IURL
{
	function getUrlInfo();
}

interface IRouter
{
	/**
	 * 接收处理成固定格式的当前URL信息
	 * @param Array $urlInfo
	 */
	function receiveUrlInfo($urlInfo);
	
	
	/**
	 * 尝试寻找一条能正确找到控制器的路径
	 *
	 */
	function findRoute();
	
	
	/**
	 * 获得当前控制器名称
	 *
	 */
	function getController();
	
	
	/**
	 * 若有分模块，则获得当前模块名称
	 *
	 */
	function getGroup();
	
	
	/**
	 * 获得当前要执行的控制器动作
	 *
	 */
	function getAction();
	
	
	
	/**
	 * 获得请求参数
	 *
	 */
	function getArgs();
}

interface IConfig
{
	function getConfigs();
	function getConfig($module);
	function getUserConfig($fileName);
}


interface IErrorHandler
{
	function handle($event);
}

interface IExceptionHandler
{
	function handle($event);
}

interface IController
{
	function run($actionName);
}

interface IAction
{
	function run();
}

interface IDbConnetion
{
	function createDbCommand($sql=NULL);
}

interface IDbCommand
{
	
	
	/**
	 * 设置命令语句
	 *
	 */
	function setText($sql);
	
	/**
	 * 获得命令语句
	 *
	 */
	function getText();
	
	
	/**
	 * 声明命令
	 *
	 */
	function prepare();
	
	
	/**
	 * 获取命令声明句柄
	 *
	 */
	function getStatement();
	
	
	
	/**
	 * 取消命令声明
	 */
	function cancel();

	
	
	/**
	 * 执行当前声明 
	 * 
	 */
	function execute();
	
	
	
	/**
	 * 返回datareader
	 *
	 */
	function query();
	
	
	/**
	 * 以二维数组形式返回结果集
	 *
	 */
	function queryAll();
	
	
	
	/**
	 * 绑定参数至声明
	 * @param $name
	 * @param $value
	 * @param $data_type
	 * @param $length
	 */
	function bindParam($name,&$value);
	
	
	
	/**
	 * 设置声明参数值
	 * @param $name
	 * @param $value
	 * @param $data_type
	 */
	function bindValue($name,$value);
	
	
	
	/**
	 * 批量设置参数值
	 * @param Array $values
	 */
	function bindValues($values);
	
	
	
	/**
	 * 清空命令已有数据
	 */
	function reset();	
	
		
	
	/**
	 * 获取当前命令连接
	 *
	 */
	function getConnection();
}

interface IRender
{
	function render($data,$tpl=NULL);
}

interface IRenderStrategy
{
	function assign($name,$value);
	function display($tpl=NULL);
}