<?php
 /*
  *  @date 2012-5-27
  *  @author wuguoqing, Calix
  *  @Blog: http://blog.163.com/wu_guoqing/
  *
  */
  
class UserModel extends CActiveRecord
{
	protected $_relations=array(
		'Profile'=>array(
			'type'=>self::HAS_ONE,
		),
		'Entry'=>array(
			'type'=>self::HAS_MANY,
		),
		'Class'=>array(
			'type'=>self::BELONG_TO,
		),
	);
	
	
}