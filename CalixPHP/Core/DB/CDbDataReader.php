<?php
 /*
  *  @date 2012-5-24
  *  @author wuguoqing, Calix
  *  @Blog: http://blog.163.com/wu_guoqing/
  *
  */
  
class CDbDataReader 
{
	protected $_command;
	
	public function __construct(CDbCommand $c)
	{
		$this->_command=$c;
	}
}