<?php
 /*
  *  @date 2012-5-22
  *  @author wuguoqing, Calix
  *  @Blog: http://blog.163.com/wu_guoqing/
  *
  */
  
class IndexController extends CController
{
	public function __construct()
	{
		$this->_actions['update']=new UpdateAction($this);	
	}
	
	
	public function indexAction()
	{
		echo 123;
	}
	
	public function welcomeAction()
	{
		echo 1;
	}
	
	public function adminAction()
	{
		echo "Admin..";
	}
	
	public function insertAction()
	{
		echo "Insert..";
	}
	
	protected function aAction()
	{
		echo "I am protected..";
	}
	
	public function validateRules()
	{
		return array(
			array(
				'actions'=>array('admin','insert','update'),
				'validators'=>array('mustLogin'),
				'callback'=>'getOut'
			),
			array(
				'actions'=>array('admin','update'),
				'validators'=>array('onlyOneself'),
				'callback'=>'notYours',
			),
		);
	}
	
	protected function mustLogin()
	{
		if($_GET['login']){
			return true;
		}else{
			return false;
		}
	}
	
	protected function getOut()
	{
		echo "Get out!";
	}
	
	protected function onlyOneself()
	{
		if($_GET['myself']){
			return true;
		}else{
			return false;
		}
	}
	
	protected function notYours()
	{
		echo "You are not me!!";
	}
	
}