<?php
 /*
  *  @date 2012-5-21
  *  @author wuguoqing, Calix
  *  @Blog: http://blog.163.com/wu_guoqing/
  *
  */
  
abstract class CComponent
{
	protected $_isInited=false;
	
	public function init()
	{
		$this->_isInited=true;
	}
	
	public function isInited()
	{
		return $this->_isInited;
	}
}