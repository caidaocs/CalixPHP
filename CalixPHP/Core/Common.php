<?php
 /*
  *  @date 2012-5-27
  *  @author wuguoqing, Calix
  *  @Blog: http://blog.163.com/wu_guoqing/
  *
  */
  


/**
 * 获取相应的AR，基本的CRUD模型。
 * @param String $model
 * @return  CActiveRecord
 */
function M($model)
{
	return CModel::getSimpleCRUDModel($model);
}



/**
 * 返回该模型的一个实例 
 * @param String $model
 * @return CActiveRecord
 */
function D($model)
{
	$model.='Model';
	return new $model();
}


/**
 * 读取配置文件
 * @param  $filename
 */
function C($filename)
{
	$config=Calix::getApp()->getComponent("Config");
	return $config->getUserConfig($filename);
}

/**
 * 获得客户端输入
 * @param  $keyname
 */
function I($keyname)
{
	if(!isset($_GET[$keyname])){
		if(!isset($_POST[$keyname])){
			return NULL;
		}else{
			return $_POST[$keyname];
		}
	}else{
		return $_GET[$keyname];
	}
}

/**
 * 加载Helper
 * @param  $filename
 */
function H($filename)
{
	Calix::importExtHelper($filename);
}