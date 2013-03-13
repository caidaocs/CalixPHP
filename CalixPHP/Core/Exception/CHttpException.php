<?php
 /*
  *  @date 2012-5-22
  *  @author wuguoqing, Calix
  *  @Blog: http://blog.163.com/wu_guoqing/
  *
  */
  
class CHttpException extends CException
{
	public function __construct($code, $thrower)
	{
		$this->code=$code;
		$this->_thrower=$thrower;
	}
}