<?php
 /*
  *  @date 2012-5-22
  *  @author wuguoqing, Calix
  *  @Blog: http://blog.163.com/wu_guoqing/
  *
  */
  
class CController extends CComponent implements IController
{
	protected $_actions;
	protected $_actionID;
	
	
	/**
	 * 渲染组件
	 * @var IRender
	 */
	protected $_render;
	
	
	
	public function init($configs)
	{
		if(!is_array($configs)){
			throw new CComponentException("初始化控制器组件失败！初始化参数必须为数组！", $this);
		}
		
		if(!isset($configs['render'])){
			throw new CComponentException("初始化控制器组件失败！控制器组件要求获得Render渲染组件！", $this);
		}
		
		$this->_render=$configs['render'];
		parent::init();
	}
	
	
	/**
	 * 执行方法
	 * @see IController::run()
	 */
	public function run($actionName)
	{
		$actionName=$this->_trim($actionName);
		
		if(empty($actionName)){
			throw new CComponentException("动作名不得为空！", $this);
		}
			
		$this->_actionID=$actionName."Action";
		
		if(method_exists($this, $this->_actionID)){//如果控制器存在此方法
			
			if($this->_actionAvailable($this->_actionID)==false){//判断方法是否供外部调用 
				throw new CComponentException("不能访问控制器的非公开方法：{$this->_actionID}", $this);
			}else{
				$validate_info=$this->_actionVaildator();
				if($validate_info['pass']==false){//验证不通过，调用callback
					if(!method_exists($this, $validate_info['callback'])){
						throw new CComponentException("不存在验证返回方法{$validate_info['callback']}",
						 $this);
					}else{
						$this->$validate_info['callback']();
					}
				}else{//验证通过，调用此方法
					$action=$this->_actionID;
					$this->$action();
				}
			}
		}elseif($this->_actionExsit()){//如果控制器有此action
			$validate_info=$this->_actionVaildator();
			if($validate_info['pass']==false){
				if(!method_exists($this, $validate_info['callback'])){
					throw new CComponentException("不存在验证返回方法{$validate_info['callback']}",
						$this);
				}else{
					$this->$validate_info['callback']();
				}
			}else{//验证通过，调用 此action的run 方法执行操作。
				$actionName=substr($this->_actionID, 0,-6);
				$action=$this->_actions[$actionName];
				$action->run();
			}	
		}else{
			throw new CComponentException(get_class($this)."找不到动作{$this->_actionID}",$this);
		}		
	}
	
	
	/**
	 * 对方法名进行修剪处理
	 * @param  $actionName
	 */
	protected function _trim($actionName)
	{
		if(empty($actionName)){
			return '';
		}
		return ltrim($actionName,"_");
	}
	
	
	
	/*
	 * 判断动作是否可供外部调用
	 */
	protected function _actionAvailable($actionID)
	{
		$rc=new ReflectionClass(get_class($this));
		$rm=$rc->getMethod($actionID);
		return $rm->isPublic()&&$rm->isUserDefined()&&
				!$rm->isAbstract()&&!$rm->isStatic()&&
				!$rm->isConstructor();
	}
	
	
	
	
	/**
	 * 判断控制器是否有相应的action对象
	 * 
	 * @throws CComponentException
	 */
	protected function _actionExsit()
	{
		$actionName=substr($this->_actionID, 0,-6);	
		if(isset($this->_actions[$actionName])){
			$action=$this->_actions[$actionName];
			if(($action instanceof CAction)==false){
				throw new CComponentException("{$this->_actionID}必须继承CAction类！",$this);
			}else{
				return true;
			}	
		}
		
		return false;
	}
	
	
	
	/**
	 *  设置动作访问验证规则
	 *
	 */
	public function validateRules()
	{
		return array(
		/*	'rule1'=>array(
				'actions'=>array(),
				'validators'=>array(),
				'callback'=>'',
			),
		*/
		);
	}
	
	
	
	/**
	 * 检验当前动作是否通过验证，并返回验证结果。
	 * @return $info
	 * @throws CComponentException
	 */
	protected function _actionVaildator()
	{
		$rules=$this->validateRules();
		$info=array(
			'pass'=>true,
		);
		if(!empty($rules)&&is_array($rules)){
			//寻找出验证该动作的规则
			$found=false;
			
			//去掉末尾的Action后缀
			$action_name=substr($this->_actionID, 0,-6);
			
			//找出与当前动作相关的规则
			$relative_rules=array();
			$i=0;
			foreach($rules as $rule)
			{
				$check=$this->_checkRuleFormat($rule);
				if($check){
					 //如果此规则中包含此动作，或者此规则是对全部动作进行验证					
					if(in_array($action_name, $rule['actions'])||
						in_array('*', $rule['actions'])){
						$found=true;
						$relative_rules[$i]['validators']=$rule['validators'];
						$relative_rules[$i]['callback']=$rule['callback'];
						$i++;
					}
				}
			}
			
			//如果找到了相应规则，则验证，并构造返回信息。
			if($found==true){
				//对每一条规则进行验证
				foreach ($relative_rules as $rule)
				{
					$pass=true;
					$validators=$rule['validators'];
					$callback=$rule['callback'];
					//遍历规则的验证器
					foreach ($validators as $v)
					{
						if(method_exists($this, $v)){
							//如果当前规则中有一个验证器不通过，则此动作无法通过，
							//callback设为当前规则的callback。
							if($this->$v()==false){
								$pass=false;
								break 2;
							}
						}else{
							throw new CComponentException(get_class($this)."不存在验证器{$v}", $this);
						}
					}
					
				}
				if($pass==true){
					$info['pass']=true;
				}else{
					$info['pass']=false;
					$info['callback']=$callback;
				}
			}else{
				$info['pass']=true;
			}			
		}
		return $info;
	}
	
	
	
	
	/**
	 * 验证某个规则是否符合格式要求
	 * @param  $rule
	 * @throws CComponentException
	 */
	protected function _checkRuleFormat($rule)
	{	
		if(!empty($rule)&&is_array($rule)){
			$legal=true;
			$illegal_field='';
			if(empty($rule['actions'])||!is_array($rule['actions'])){
				$legal=false;
				$illegal_field.=' actions(非空数组) ';
			}
			if(empty($rule['validators'])||!is_array($rule['validators'])){
				$legal=false;
				$illegal_field.=' validators(非空数组) ';
			}
			if(empty($rule['callback'])||!is_string($rule['callback'])){
				$legal=false;
				$illegal_field.=' callback(非空字符串) ';
			}
			
			if($legal!==true){
				throw new CComponentException("验证规则格式错误！错误项：{$illegal_field}", $this);
			}else{
				return true;
			}
			
		}else{
			throw new CComponentException("验证规则 validateRule 必须为数组！", $this);
		}
	}
	
	
	/**
	 * 变量分配
	 * @param $name
	 * @param $value
	 */
	public function assign($name,$value)
	{
		$this->_render->assign($name, $value);
	}
	
	
	/**
	 * 视图显示
	 * @param $tpl
	 */
	public function display($tpl)
	{
		$this->_render->display($tpl);
	}
	
	
	/**
	 * 视图渲染
	 * @param  $tpl
	 */
	public function render($tpl)
	{
		$this->_render->render($tpl);
	}
}