<?php
 /*
  *  @date 2012-5-21
  *  @author wuguoqing, Calix
  *  @Blog: http://blog.163.com/wu_guoqing/
  *
  */
  
class CExceptionHandler extends CComponent implements IExceptionHandler
{
	public function __construct()
	{
		$this->init();
	}
	
	/**
	 * @param CException
	 * @see IExceptionHandler::handle()
	 */
	public function handle($exception)
	{
		if(CALIX_DEFUG){
			echo '<h1>'.get_class($exception);
			if($exception instanceof CException){
				$throw=$exception->getThrower();
			}
			if(isset($throw)){
				echo " From ".get_class($exception->getThrower());
			}
			echo "</h1>\n";
			echo '<p>'.$exception->getMessage().
					' ('.$exception->getFile().':'.$exception->getLine().')</p>';
			echo '<pre>'.$exception->getTraceAsString().'</pre>';		
		}else{
			if($exception instanceof CHttpException){
				echo '<h1>Error '.$exception->getCode().'</h1>';
			}else{
				echo '<h1>Error 500 内部错误。</h1>';
			}
		}
	}
}