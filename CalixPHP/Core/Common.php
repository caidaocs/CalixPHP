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