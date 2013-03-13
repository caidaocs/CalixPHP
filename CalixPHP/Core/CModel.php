<?php
 /*
  *  @date 2012-5-25
  *  @author wuguoqing, Calix
  *  @Blog: http://blog.163.com/wu_guoqing/
  *
  */
  
abstract class CModel
{			
	/**
	 * 
	 * 当前模型的名字
	 * @var String 
	 */
	protected $_name;
	
	
	
	
	/**
	 * 取得模型名字
	 * @throws CException
	 * @return String
	 */
	protected function getModelName()
	{
		if(empty($this->_name)){
			$subfix=substr(get_class($this), -5);
			if($subfix!=='Model'){
				throw new CException("无法获得模型名称，没有配置模型名称的情况下，模型类必须以‘Model’为后缀",
					 $this);
			}
			
			$name=substr(get_class($this), 0,-5);
			if(!empty($name)){
				$name=trim($name);
			}
			
			if($name===''){
				throw new CException("模型名称为空！", $this);
			}
			
			$this->_name=$name;
		}
		
		return $this->_name;
	}
	
	
	
	
	/**
	 * 返回相应数据表的AR作为模型
	 * @param $model
	 * @param
	 */
	public static function getSimpleCRUDModel($model)
	{
		if(empty($model)||!is_string($model)){
			return NULL;
		}
		
		return new CActiveRecord($model);
	}
	
	
}