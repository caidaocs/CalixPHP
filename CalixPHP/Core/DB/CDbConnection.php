<?php
 /*
  *  @date 2012-5-24
  *  @author wuguoqing, Calix
  *  @Blog: http://blog.163.com/wu_guoqing/
  *
  */

class CDbConnection extends CComponent implements IDbConnetion
{
	
	/**
	 * PDO实例对象
	 */
	protected $_pdo;
	
	/**
	 * 连接字符串
	 */
	protected $_dsn;
	
	protected $_username;
	protected $_password;
	protected $_charset;
	
	
	/**
	 * 连接状态
	 * @var boolean
	 */
	protected $_connection=false;
	
	
	/**
	 * 用户配置信息 ...
	 * @var Array
	 */
	protected $_configs;
	
	public function __construct()
	{
		
	}
	
	
	
	/**
	 * 初始化数据库连接类
	 * @see CComponent::init()
	 */
	public function init($configs)
	{
		if(!is_array($configs)){
			throw new CDbException("初始化数据库连接失败！配置信息必须为数组类型！", $this);
		}		
		
		if(empty($configs['dsn'])){
			throw new CDbException("初始化数据库连接失败！数据源为空！", $this);
		}
		
		$this->_dsn=$configs['dsn'];
		$this->_username=$configs['username'];
		$this->_password=$configs['password'];	
			
		if(!empty($configs['charset'])){
			$this->_charset=$configs['charset'];
		}
		
		$this->open();
		$this->_configs=$configs;
		parent::init();		
	}
	
	
	
	/**
	 * 打开数据库连接，即获得pdo实例
	 *
	 */
	public function open()
	{
		$this->_pdo=$this->_getPdoInstance();
		$this->_initConnection($this->_pdo);
		$this->_connection=true;
	}
	
	
	
	/**
	 * 构造并返回pdo实例
	 * @return PDO $pdo
	 */
	protected function _getPdoInstance()
	{
		try {
			$pdo=new PDO($this->_dsn, $this->_username, $this->_password);
		}catch (PDOException $e){
			throw new CDbException($e->getMessage(), $this);
		}
		
		return $pdo;
	}
	
	
	
	/**
	 * 初始化连接
	 * @param PDO $pdo
	 */
	protected function _initConnection(PDO $pdo)
	{
		if(!empty($this->_charset)){
			$pdo->exec("SET NAMES {$this->_charset}");
		}
	}
	
	
		
	/**
	 * 获取当前Pdo
	 * @return PDO $pdo
	 */
	public function getPdo()
	{
		return $this->_pdo;
	}
	
	
	public function getConfig($item)
	{
		$rt=NULL;
		if(isset($this->_configs[$item])){
			$rt=$this->_configs[$item];
		}
		return $rt;
	}
	
	
	/**
	 * 
	 * @see IDbConnetion::createDbCommand()
	 * @return CDbCommand
	 */
	public function createDbCommand($sql=NULL)
	{
		return new CDbCommand($this,$sql);
	}
	
	
}