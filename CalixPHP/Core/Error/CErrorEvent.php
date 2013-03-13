<?php
 /*
  *  @date 2012-5-21
  *  @author wuguoqing, Calix
  *  @Blog: http://blog.163.com/wu_guoqing/
  *
  */
  
class CErrorEvent extends CEvent
{
	public $code;
	public $message;
	public $file;
	public $line;
	
	public function __construct($code,$message,$file,$line,$sender=NULL)
	{
		$this->code=$code;
		$this->message=$message;
		$this->file=$file;
		$this->line=$line;
		parent::__construct($sender);	
	}
}