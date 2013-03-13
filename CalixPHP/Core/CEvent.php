<?php
 /*
  *  @date 2012-5-21
  *  @author wuguoqing, Calix
  *  @Blog: http://blog.163.com/wu_guoqing/
  *
  */
  
class CEvent
{
	protected $_sender;
	
	public function __construct($sender)
	{
		$this->_sender=$sender;
	}
	
	public function getHandler()
	{
		return $this->_sender;
	}
}