<?php
 /*
  *  @date 2012-5-10
  *  @author wuguoqing, Calix
  *  @Blog: http://blog.163.com/wu_guoqing/
  *
  */
  
abstract class CUrlStrategy
{
	protected $_filter;
	protected $_url;
	protected static $_strategies;
	protected static $_mappings;
	
	public function __construct()
	{
		$this->_initFilter();
	}
	
	protected function _initFilter()
	{
		$this->_filter=new CUrlFilter();
	}
	
	public static function getStrategy($format=1)
	{
		if(!isset(self::$_mappings)){
			self::$_mappings=self::_mapping();
		}		
		if(!isset(self::$_mappings[$format])){
			$format=1;
		}		
		$strategy=self::$_mappings[$format];
		if(!isset(self::$_strategies[$format])){
			self::$_strategies[$format]=new $strategy();
		}
				
		return self::$_strategies[$format];
	}
	
	protected static function _mapping()
	{
		return array(
			"0"=>"CNormalUrl",
			"1"=>"CSimpleUrl",
			"2"=>"CQueryUrl",
		);
	}
	
	/**
	 * 子类必须覆盖该函数 ...
	 * @param CUrl $url
	 */
	abstract function getUrlInfo($url);
}




class CNormalUrl extends CUrlStrategy
{
	public function filte()
	{
		foreach ($_GET as $k=>$v)
		{
			$_GET[$k]=$this->_filter->filte($v);
		}
	}
	
	/**
	 * 根据当前url情况，为url设置urlInfo
	 * @param CUrl $url
	 */
	public function getUrlInfo($url)
	{
		$this->_url=$url;

		$info=array();
		$g=$this->_url->getConfigs('group');
		$c=$this->_url->getConfigs('controller');
		$a=$this->_url->getConfigs('action');
		
		foreach (array(isset($_GET[$g])?$_GET[$g]:NULL,
			isset($_GET[$c])?$_GET[$c]:NULL,isset($_GET[$a])?$_GET[$a]:NULL) as $item)
		{
			if(!empty($item)){
				$info[]=$item;
			}else{
				$info[]=NULL;
			}
		}
		
		foreach($_GET as $k=>$v)
		{
			if($k!=$g&&$k!=$c&&$k!=$a){
				$info[]=$k;
				$info[]=$v;
			}
		}
		
		$this->_url->setUrlInfo($info);
	}
}




class CSimpleUrl extends CUrlStrategy
{
	private $_pathInfo;
	
	public function trimUrl($url)
	{
		if(!empty($url)){
			$url=trim($url,'/');
		}
		return $url;
	}
	
	public function filte()
	{
		$this->_filter->filte($this->_pathInfo);
	}
	
	public function getUrlInfo($url)
	{
		$this->_url=$url;
		$this->_pathInfo=$_SERVER['PATH_INFO'];
		$this->_pathInfo=$this->trimUrl($this->_pathInfo);
		$this->filte();
		$info=array();
		if(!empty($this->_pathInfo)){
			$info=explode('/', $this->_pathInfo);
		}
		$this->_url->setUrlInfo($info);
	}
	
}


class CQueryUrl extends CUrlStrategy
{
	private $_pathInfo;
	
	
	public function trimUrl($url)
	{
		if(!empty($url)){
			$url=trim($url,'/');
		}
		return $url;
	}
	
	public function filte()
	{
		$this->_filter->filte($this->_pathInfo);
	}
	
	public function getUrlInfo($url)
	{
		$this->_url=$url;
		$this->_pathInfo=empty($_GET[$this->_url->getConfigs('queryer')])?
			"":$_GET[$this->_url->getConfigs('queryer')];
		$this->_pathInfo=$this->trimUrl($this->_pathInfo);
		$this->filte();
		$info=array();
		if(!empty($this->_pathInfo)){
			$info=explode('/', $this->_pathInfo);
		}
		$this->_url->setUrlInfo($info);
	}
}









