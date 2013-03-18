<?php
 /*
  *  @date 2012-5-24
  *  @author wuguoqing, Calix
  *  @Blog: http://blog.163.com/wu_guoqing/
  *
  */
  
class CDbCommand implements IDbCommand
{
	
	/**
	 * 数据库连接对象
	 * @var CDbConnection
	 */
	protected $_connection;
	
	
	/**
	 * 当前命令语句
	 * @var String
	 */
	protected $_text;
	
	
	/**
	 * 上次命令语句
	 * @var String
	 */
	protected $_lastText;
	
	/**
	 * 命令语句组成
	 * @var Array
	 */
	protected $_query=array();
	
	
	/**
	 * 命令声明
	 * @var PDOStatement
	 */
	protected $_statement;
	
	
	/**
	 * 命令声明已绑定的参数
	 * @var Array
	 */
	protected $_params=array();
	
	
	
	/**
	 * 查询结果获取模式
	 */
	protected $_fetchMode=PDO::FETCH_ASSOC;
	
	
	
	/**
	 * 构造DbCommand，可从DbConnection中通过createCommand()方法获得实例。
	 * @param CDbConnection $connection
	 * @param $mixed 
	 */
	public function __construct(CDbConnection $connection,$text)
	{
		$this->_connection=$connection;
		if(is_array($text)){
			$this->_query=$text;
		}else{
			$this->_text=$text;
		}
	}
	
	

	/**
	 * 
	 * @see IDbCommand::setText()
	 * @return CDbCommand $this
	 */
	public function setText($sql)
	{
		$this->_text=$sql;
		$this->cancel();
		return $this;
	}
	
	
	
	
	/**
	 * @see IDbCommand::cancel()
	 */
	public function cancel()
	{
		if(isset($this->_statement)){
			$this->_statement=NULL;
		}
	}
	
	
	
	
	/**
	 * @see IDbCommand::getText()
	 */
	public function getText()
	{
		if(empty($this->_text)&&!empty($this->_query)){
			//把$this->_query[]中的各部分组成sql语句。默认为查询方案的$sql。			
			$sql=$this->_buildDefaultQueryString();		
			$this->setText($sql);
		}
		
		return $this->_text;
	}
	
	public function getLastText()
	{
		return $this->_lastText;
	}
	
	
	
	
	/**
	 * @see IDbCommand::query()
	 */
	public function query()
	{		
		$sql=$this->_buildDefaultQueryString();	
		$this->setText($sql);
		
		$result=$this->execute();
		$rt=NULL;
		if($result){
			$rt=new CDbDataReader($this);
		}
		$this->reset();
		return $rt;
	}
	
	
	
	
	/**
	 * @see IDbCommand::queryAll()
	 */
	public function queryAll()
	{
		$sql=$this->_buildDefaultQueryString();		
		$this->setText($sql);		
		$result=$this->execute();
		$rt=NULL;
		if($result){
			$rt=$this->_statement->fetchAll($this->_fetchMode);
		}
		$this->reset();
		return $rt;
	}
	
	

	
	/**
	 * 根据$this->_query，构造默认的查询语句。
	 * @throws CDbException
	 */
	protected function _buildDefaultQueryString()
	{
		
		if(empty($this->_query)){
			throw new CDbException("查询指令为空！", $this);
		}
		
		$sql='';
		$sql.='SELECT ';		
		
		//字段部分
		if(isset($this->_query['fields'])){
			
			$sql.=$this->_query['fields'];
		}else{
			$sql.='*';
		}
		$sql.=' ';
		
		//数据表部分
		if(empty($this->_query['table'])){
			throw new CDbException("数据表名不能为空！", $this);
		}
		$sql.='FROM ';
		$sql.=$this->_query['table'];
		$sql.=' ';
		
		
		//条件部分
		if(!empty($this->_query['conditions'])){
			$sql.='WHERE ';
			$sql.=$this->_query['conditions'];
			$sql.=' ';
		}
		
		//顺序部分
		if(!empty($this->_query['order'])){
			$sql.='ORDER BY ';
			$sql.=$this->_query['order'];
			$sql.=' ';
		}
		
		
		//限制部分
		if(!empty($this->_query['limit'])){
			$sql.='LIMIT ';
			$sql.=$this->_query['limit'];
			$sql.=' ';
		}
	
		return $sql;	
	}
	
	
	
	/**
	 * 返回记录数量
	 *
	 */
	public function count()
	{
		$sql='';
		$sql='SELECT COUNT(*) ';
		
		//数据表部分
		if(empty($this->_query['table'])){
			throw new CDbException("数据表名不能为空！", $this);
		}
		$sql.='FROM ';
		$sql.=$this->_query['table'];
		$sql.=' ';
		
		//条件部分
		if(!empty($this->_query['conditions'])){
			$sql.='WHERE ';
			$sql.=$this->_query['conditions'];
			$sql.=' ';
		}
		
		$this->setText($sql);
		$result=$this->execute();
		
		$count=0;
		if($result){
			$count=$this->_statement->fetchColumn();
		}
		$this->reset();
		return $count;
	}
	
	
	
	/**
	 * @param Array $datas
	 * @param String $table
	 * @throws CDbException
	 * @return 影响行数
	 */
	public function insert($datas,$table=NULL)
	{
		
		if(isset($table)){
			$this->from($table);
		}
		if(empty($this->_query['table'])){
			throw new CDbException("无法插入数据，没有指定要插入的数据表。", $this);
		}
			
		$fields=array();
		$value_fields=array();
		$values=array();
		if(is_array($datas)&&count($datas)>0){
			foreach ($datas as $k=>$v)
			{
				$fields[]=$k;
				$value_fields[]=':'.$k;
				$values[':'.$k]=$v;
			}
		}
			
		$sql='';
		$sql.='INSERT INTO '.$this->_query['table'].
					' ('.implode(',', $fields).') VALUES('.implode(',', $value_fields).');';	

		$result=$this->setText($sql)->execute($values);
		$rt=0;
		if($result){
			$rt=$this->_statement->rowCount();
		}
		
		$this->reset();
		return $rt;
		
	}
	
	
	
	
	/**
	 * 更新操作
	 * @param Array $datas
	 * @param  $table
	 * @throws CDbException
	 * @return 影响行数
	 */
	public function update($datas,$table=NULL)
	{
		if(isset($table)){
			$this->from($table);
		}
		if(empty($this->_query['table'])){
			throw new CDbException("无法更新数据，没有指定要更新的数据表。", $this);
		}
			
		$matches=array();
		$values=array();
		if(is_array($datas)&&count($datas)>0){
			foreach ($datas as $k=>$v)
			{
				$matches[]=$k."=:{$k}";
				$values[':'.$k]=$v;
			}
		}
			
		$sql='';
		$sql.='UPDATE '.$this->_query['table'].
				' SET '.implode(',', $matches);	
			
		if(!empty($this->_query['conditions'])){
			$sql.=' WHERE '.$this->_query['conditions'];
		}
		
		$result=$this->setText($sql)->execute($values);
		
		$rt=$result?$this->_statement->rowCount():0;
		
		$this->reset();
		
		return $rt;
	}
	
	
	
	
	/**
	 * 删除操作
	 * @param $table
	 * @throws CDbException
	 * @return 影响行数
	 */
	public function delete($table=NULL)
	{	
		if(isset($table)){
			$this->from($table);
		}
		
		if(empty($this->_query['table'])){
			throw new CDbException("删除操作失败！没有指定数据表！", $this);
		}
		
		$sql='';
		$sql.='DELETE FROM '.$this->_query['table'];
		if(!empty($this->_query['conditions'])){
			$sql.=' WHERE '.$this->_query['conditions'];
		}
		
		$result=$this->setText($sql)->execute();
		$rt=$result?$this->_statement->rowCount():0;	
		$this->reset();
		return $rt;
	}
	
	
	
	/**
	 * 设置字段部分
	 * @param  $fields
	 * @return CDbCommand
	 */
	public function select($fields)
	{
		
		//这里也应该对字段进行一些处理
		$field=NULL;
		if(is_array($fields)&&count($fields)>0){
			$field=implode(',', $fields);
		}elseif(!empty($fields)){
			$field=$fields;	
		}
		
		if(!empty($field)){
			$this->_query['fields']=$field;
		}
		
		return $this;
	}
	
	
	
	
	/**
	 * 设置表名
	 * @param  $table
	 * @param  boolean $auto_prefix 是否根据配置文件自动加前缀，默认为不加。
	 * @return CDbCommand
	 */
	public function from($table,$auto_prefix=false)
	{
		if(!empty($table)&&is_string($table)){
			if($auto_prefix===true){
				$pre=$this->getConnection()->getConfig('table_prefix');
				$this->_query['table']=$pre.$table;
			}else{
				$this->_query['table']=$table;
			}
		}
		return $this;
	}
	
	
	
	
	/**
	 * 设置表名
	 * @param  $table
	 * @param  boolean $auto_prefix 是否根据配置文件自动加前缀，默认为不加。
	 * @return CDbCommand
	 */
	public function into($table,$auto_prefix=false)
	{
		return $this->from($table,$auto_prefix);
	}
	
	
	
	
	/**
	 * 设置条件部分  默认以 and 连接
	 * @param  $condition
	 * @param  $value
	 * @param  $link
	 * @return CDbCommand
	 */
	public function where($condition,$value=NULL,$link=1)
	{
		if(empty($condition)){
			throw new CDbException("第一个参数不能为空！", $this);
		}
		
		if(is_array($condition)){
			foreach ($condition as $k=>$v)
			{
				$this->where($k,$v,$link);
			}
			return $this;
		}
		
		
		//构造条件
		$con='';
		if(isset($value)){
			$value=$this->_quoteValue($value);
			$con=$condition.'='.$value;
		}else{
				$con=$condition;
		}	

		
		//连接条件
		if(!empty($this->_query['conditions'])){
			$link=intval($link);
			if($link===1){
				$this->_query['conditions'].=' AND '.$con;
			}else{
				$this->_query['conditions'].=' OR '.$con;
			}
		}else{
			$this->_query['conditions']=$con;
		}		
		return $this;
	}
	
	
	
	/**
	 * 设置条件，以or连接
	 * @param $condition
	 * @param $value
	 * @return CDbCommand
	 */
	public function or_where($condition,$value=NULL)
	{
		return $this->where($condition,$value,2);
	}
	
	

	
	/**
	 * 设置IN查询条件，以AND 连接
	 * @param $field
	 * @param Array $values
	 * @param $link
	 * @param boolean $oppo
	 * @return CDbCommand
	 */
	public function where_in($field,$values,$link=1,$oppo=false)
	{
		if(empty($field)||empty($values)||!is_array($values)){
			throw new CException("where_in()方法参数错误！", $this);
		}
		foreach ($values as $k=>$v)
		{
			$values[$k]=$this->_quoteValue($v);
		}
		
		$con='';
		if($oppo===true){
			$con=$field.' NOT IN ('.implode(',', $values).')';
		}else{
			$con=$field.' IN ('.implode(',', $values).')';
		}
		
		if(!empty($this->_query['conditions'])){
			if($link===1){
				$this->_query['conditions'].=' AND '.$con;
			}else{
				$this->_query['conditions'].=' OR '.$con;
			}
		}else{
			$this->_query['conditions']=$con;
		}
		return $this;
	}
	
	
	
	
	/**
	 * 设置IN查询条件，以or连接
	 * @param  $field
	 * @param  $values
	 * @return CDbCommand
	 */
	public function or_where_in($field,$values)
	{
		return $this->where_in($field, $values,2);
	}
	
	
	
	
	/**
	 * 设置NOT IN查询条件，以AND 连接
	 * @param  $field
	 * @param  $values
	 * @return CDbCommand
	 */
	public function where_not_in($field,$values)
	{
		return $this->where_in($field, $values,1,true);
	}
	
	
	
	
	
	/**
	 * 设置NOT IN查询条件，以AND 连接
	 * @param $field
	 * @param $values
	 * @return CDbCommand
	 */
	public function or_where_not_in($field,$values)
	{
		return $this->where_in($field, $values,2,true);
	}
	
	
	
	/**
	 * 设置LIKE 查询条件 以AND 连接
	 * @param $condition
	 * @param $value
	 * @param int $link
	 * @param boolean $oppo
	 * @throws CDbException
	 * @return CDbCommand
	 */
	public function like($condition,$value=NULL,$link=1,$oppo=false)
	{
		if(empty($condition)){
			throw new CDbException("第一个参数不能为空！", $this);
		}
		
		if(is_array($condition)){
			foreach ($condition as $k=>$v)
			{
				$this->like($k,$v,$link,$oppo);
			}
			return $this;
		}
		
		
		//构造条件
		$con='';
		if(isset($value)){
			$value=$this->_quoteValue($value);
			if($oppo===true){
				$con=$condition.' NOT LIKE '.$value;
			}
			$con=$condition.' LIKE '.$value;
		}else{
				$con=$condition;
		}	

		
		//连接条件
		if(!empty($this->_query['conditions'])){
			$link=intval($link);
			if($link===1){
				$this->_query['conditions'].=' AND '.$con;
			}else{
				$this->_query['conditions'].=' OR '.$con;
			}
		}else{
			$this->_query['conditions']=$con;
		}		
		return $this;
	}
	
	
	
	
	/**
	 * 设置LIKE 查询条件 ，以OR连接
	 * @param $condition
	 * @param $value
	 * @return CDbCommand
	 */
	public function or_like($condition,$value=NULL)
	{
		return $this->like($condition,$value,2);
	}
	
	
	
	/**
	 * 设置NOT LIKE 查询条件 以AND 连接
	 * @param  $condition
	 * @param  $value
	 * @return CDbCommand
	 */
	public function not_like($condition,$value=NULL)
	{
		return $this->like($condition,$value,1,true);
	}
	
	
	
	/**
	 * 设置NOT LIKE 查询条件 以OR 连接
	 * @param  $condition
	 * @param  $value
	 * @return CDbCommand
	 */
	public function or_not_like($condition,$value=NULL)
	{
		return $this->like($condition,$value,2,true);
	}
	
	
	
	/**
	 * 
	 * @param  $value
	 * @return mixed 
	 */
	protected function _quoteValue($value)
	{
		if(is_int($value)||is_float($value)){
			return $value;
		}else{
			$value=(string)$value;
			$result=$this->getConnection()->getPdo()->quote($value);
			if($result){
				$value=$result;
			}else{
				$value="'" . addcslashes(str_replace("'", "''", $value), "\000\n\r\\\032") . "'";
			}
				
			return $value;
		}
	}
	
	
	
	/**
	 * 设置查询条件，调用该方法会把之前设置的查询条件全部覆盖。
	 * @param Array $condition
	 * @return CDbCommand
	 */
	public function setCondition($condition)
	{
		$this->_query['conditions']=$this->_parseCondition($condition);
		return $this;
	}
	
	
	
	/**
	 * 把数组格式的查询条件转换成字符串
	 * @param $condition
	 * @return String
	 */
	protected function _parseCondition($condition)
	{
		$condition_sql="";
		if(empty($condition)||!is_array($condition)){
			throw new CDbException("查询条件格式错误！", $this);
		}
		
		$op="AND";
		if(isset($condition[0])&&in_array($condition[0], array("OR","AND"))){
			$op=$condition[0];
			unset($condition[0]); 
		}
		
		if(empty($condition)){
			throw new CDbException("无效的查询条件。", $this);
		}
		
		foreach ($condition as $k=>$v)
		{
			if(is_array($v)){
				$condition[$k]=" ( ".$this->_parseCondition($v)." ) ";
			}else{
				if(!is_numeric($k)){
					$condition[$k]=$k."=".$this->_quoteValue($v);
				}
			}
		}
		
		$condition_sql.=implode(" ".$op." ", $condition);
		return $condition_sql;
		
	}
	
	
	/**
	 * 设置顺序
	 * @param  $order
	 * @return CDbCommand
	 */
	public function orderBy($order)
	{
		if(!empty($order)){
			$this->_query['order']=$order;
		}
		
		return $this;
	}
	
	
	
	/**
	 * 设置限制
	 * @param  $count
	 * @param  $offset
	 * @return CDbCommand
	 */
	public function limit($count,$offset=NULL)
	{
		if(isset($offset)){
			$this->_query['limit']=intval($offset).','.intval($count);		
		}else{
			$this->_query['limit']=intval($count);
		}
		return $this;
	}
	
	
	/**
	 * 
	 * @see IDbCommand::reset()
	 */
	public function reset()
	{
		$this->_lastText=$this->_text;
		$this->_text=NULL;
		$this->_query=array();
		$this->_params=array();
		$this->_statement=NULL;
	}
	
	
	/**
	 * 
	 * @see IDbCommand::execute()
	 * @return true or false
	 */
	public function execute($params=array())
	{
		$this->prepare();
		try{
			if($params===array()){
				$rs=$this->_statement->execute();
			}else{
				$this->_params=$params;
				$rs=$this->_statement->execute($params);
			}
		}catch (PDOException $e){
			throw new CDbException("数据库命令操作失败！异常信息：{$e->getMessage()}", $this);
		}
		
		return $rs;
	}
	
	
	
	
	/**
	 * 
	 * @see IDbCommand::prepare()
	 */
	public function prepare()
	{
		if(!isset($this->_statement)){
			$this->_statement=$this->getConnection()->getPdo()->prepare($this->getText());
		}
	}
	
	
	
	
	/**
	 * @see IDbCommand::getConnection()
	 * @return CDbConnection
	 */
	public function getConnection()
	{
		return $this->_connection;
	}
	
	
	
	
	/**
	 * @see IDbCommand::getStatement()
	 * @return PDOStatement
	 */
	public function getStatement()
	{
		return $this->_statement;
	}
	
	
	
	
	
	/**
	 * @see IDbCommand::bindParam()
	 */
	public function bindParam($name, &$value)
	{
		$this->prepare();
		$this->_params[$name]=&$value;
		$this->_statement->bindParam($name, $value);
	}
	
	
	
	/**
	 * @see IDbCommand::bindValue()
	 */
	public function bindValue($name, $value)
	{
		$this->prepare();
		$this->_params[$name]=$value;
		$this->_statement->bindValue($name, $value);
	}
	
	
	
	/**
	 * @see IDbCommand::bindValues()
	 */
	public function bindValues($values)
	{
		if(!is_array($values)){
			throw new CDbException("方法bindValues接收的参数必须是数组！", $this);
		}else{
			foreach ($values as $k=>$v) {
				$this->bindValue($k, $v);
			}
		}
	}
	
	
	
	/**
	 * 数据表字段信息
	 * @param  $table
	 */
	public function getFields($table=NULL)
	{
		if(isset($table)){
			$this->from($table);
		}
		
		if(empty($this->_query['table'])){
			throw new CDbException("数据表名不能为空！", $this);
		}
		
		$sql='SHOW COLUMNS FROM '.$this->_query['table'];
		
		$this->setText($sql)->execute();
		
		$result=$this->_statement->fetchAll($this->_fetchMode);
		
		$rt=array();
		if(!empty($result)){
			
			foreach ($result as $k=>$v) 
			{
				$rt[$v['Field']]=array(
					'name'=>$v['Field'],
					'type'=>$v['Type'],
					'notnull'=>(bool) (strtolower($v['Null']) === 'no'),
					'autoinc'=>(bool) (strtolower($v['Extra']) == 'auto_increment'),
					'default'=>$v['Default'],
					'pk'=>(bool) (strtolower($v['Key']) == 'pri'),
				);
			}						
		}
		
		$this->reset();
		return $rt;
	}
	
	
	/**
	 * 获得当前命令组成
	 *
	 */
	public function getQuery()
	{
		return $this->_query;
	}
	
	
	
	/**
	 * 设置当前命令组成
	 * @param Array $conditions
	 * @return CDbCommand
	 */
	public function setQuery($conditions)
	{
		if(!is_array($conditions)){
			return $this;
		}
		
		$this->_query=$conditions;
		return $this;
	}
	
}