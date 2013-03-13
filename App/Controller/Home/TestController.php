<?php
 /*
  *  @date 2012-5-23
  *  @author wuguoqing, Calix
  *  @Blog: http://blog.163.com/wu_guoqing/
  *
  */
  
class TestController extends CController
{
	public function __construct()
	{
		$this->_actions['update']=new UpdateAction($this);
	}
	
	
}