<?php
 /*
  *  @date 2012-5-21
  *  @author wuguoqing, Calix
  *  @Blog: http://blog.163.com/wu_guoqing
  *
  */
  
class CErrorHandler extends CComponent implements IErrorHandler
{
	protected $_error;
	
	public function __construct()
	{
		$this->init();
	}
	
	/**
	 * 错误处理
	 * @see IErrorHandler::handle()
	 */
	public function handle($event)
	{
		$trace=debug_backtrace();
		if(count($trace)>3){
			$trace=array_slice($trace, 3);
		}
		$traceRecord='';
		foreach($trace as $i=>$t)
		{
			if(!isset($t['file']))
				$trace[$i]['file']='unknown';

			if(!isset($t['line']))
				$trace[$i]['line']=0;

			if(!isset($t['function']))
				$trace[$i]['function']='unknown';

			$traceRecord.="#$i {$trace[$i]['file']}({$trace[$i]['line']}): ";
			if(isset($t['object']) && is_object($t['object']))
				$traceRecord.=get_class($t['object']).'->';
			$traceRecord.="{$trace[$i]['function']}()<br>\n";

			unset($trace[$i]['object']);
		}
		
		$this->_error=array(
			'code'=>$event->code,
			'message'=>$event->message,
			'file'=>$event->file,
			'line'=>$event->line,
			'trace'=>$traceRecord
		);
		
		if(CALIX_DEFUG){
			$this->handleInDebug($this->_error);
		}else{
			$this->handleInPro($this->_error);
		}
	}

	/**
	 * 调试模式下错误处理方法...
	 * @param $error
	 */
	public function handleInDebug($error)
	{	
		echo "<h1>PHP Error [{$error['code']}]</h1>\n";
		echo "<p>{$error['message']} ({$error['file']}:{$error['line']})</p>\n";
		echo '<pre>';
		echo $error['trace'];
		echo '</pre>';	
	}
	
	public function handleInPro($error)
	{
		echo '<h1>Error 500 内部错误。</h1>';
	}
}