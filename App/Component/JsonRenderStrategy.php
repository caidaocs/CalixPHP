<?php
class JsonRenderStrategy implements IRenderStrategy
{
	public $doc;
	
	public function assign($name, $value)
	{
		$this->doc[$name]=$value;
	}
	
	public function display($tpl=NULL)
	{
		if($this->doc!==NULL){
			echo json_encode($this->doc);
		}
	}
}