<?php
 /*
  *  @date 2012-5-21
  *  @author wuguoqing, Calix
  *  @Blog: http://blog.163.com/wu_guoqing/
  *
  */
  
class CException extends Exception
{
	protected $_thrower;
	
	public function __construct($message,$thrower)
	{
		$this->message=$message;
		$this->_thrower=$thrower;
	}
	
	
	public function getThrower()
	{
		return $this->_thrower;
	}
}