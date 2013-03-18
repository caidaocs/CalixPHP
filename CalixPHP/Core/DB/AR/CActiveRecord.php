<?php
 /*
  *  @date 2012-5-27
  *  @author wuguoqing, Calix
  *  @Blog: http://blog.163.com/wu_guoqing/
  *
  */
  
class CActiveRecord extends CModel
{	
	
	/**
	 * 
	 * 对应的数据表名，无前缀
	 * @var String
	 */
	protected $_tableName;
	
	
	
	/**
	 * 
	 * 数据表全名
	 * @var String
	 */
	protected $_fullTableName;
	
	
	
	
	/**
	 * 
	 * 数据库前缀
	 * @var String
	 */
	protected $_tablePreFix;
	
	
	
	/**
	 * 
	 * 应用的数据库组件
	 * @var CDbConnection
	 */
	protected $_db;

	
	
	/**
	 * 
	 * 数据库命令对象
	 * @var CDbCommand
	 */
	protected $_dbCommand;
	
	
	
	/**
	 * 有关数据表字段的信息
	 * @var Array
	 */
	protected $_fields=array();
	
	
	
	/**
	 * 数据库主键，单字段主键则$_pk为字符串类型，组合主键则为数组
	 * @var mixed
	 */
	protected $_pk;
	
	
	
	/**
	 * 数据
	 * @var Array
	 */
	protected $_attributes=array();
	
	
	
	/**
	 * 当前的记录状态
	 * @var 
	 */
	protected $_recordState;
	
	const NEW_RECORD=1;//新记录
	const OLD_RECORD=2;//旧记录
	
	
	
	
	/**
	 * 与其它模型的关联
	 * array(
	 * 		'Profile'=>array(
	 * 			'type'=>self::HAS_ONE,
	 * 			'class_name'=>'Profile'
	 * 			'foreign_key'=>'user_id',
	 * 		),
	 * 		'Entry'=>array(
	 * 			'type'=>self::HAS_MANY,
	 * 			'class_name'=>'Entry',
	 * 			'foreign_key'=>'user_id',
	 * 		),
	 * 		'Major'=>array(
	 * 			'type'=>self::BELONG_TO,
	 * 			'class_name'=>'Major',
	 * 			'foreign_key'=>'major_id',
	 * 		),	
	 * 		'Group'=>array(
	 * 			'type'=>self::MANY_TO_MANY,
	 * 			'class_name'=>'Group',
	 * 			'foreign_key'=>'user_id',
	 * 			'relation_foreign_key'=>'group_id',
	 * 			'middle_table'=>'user_group',
	 * 		),
	 * );
	 * @var Array
	 */
	protected $_relations=array();
	
	const HAS_ONE=1;		//一对一，一般是后者附属前者的关系
	const HAS_MANY=2;		//一对多	
	const BELONG_TO=3;		//多对一
	const MANY_TO_MANY=4;	//多对多
	
	protected $_with;
	
	
	
	/**
	 * 构造函数
	 *
	 */
	public function __construct($inits=NULL)
	{
		if(isset($inits)&&!empty($inits)){
			if(is_array($inits)){
				if(!empty($inits['name'])){
					$this->_name=$inits['name'];
				}
				if(!empty($inits['prefix'])){
					$this->_tablePreFix=$inits['prefix'];
				}
			}else{
				$this->_name=(string)$inits;
			}
		}
		
		$this->_initDb();
		$this->_initModelName();
		$this->_initTable();
		$this->_recordState=self::NEW_RECORD;
	}
	
	
	
	/**
	 * 初始化数据库对象
	 *
	 */
	protected function _initDb()
	{
		$this->_db=Calix::getApp()->getDb();
		$this->_dbCommand=$this->_db->createDbCommand();
	}
	
	
	
	
	/**
	 * 初始化模型名称
	 *
	 */
	protected function _initModelName()
	{
		$this->_name=$this->getModelName();
	}
	
	
	
	
	/**
	 * 初始化模型对应的数据表信息
	 *
	 */
	protected function _initTable()
	{
		//数据表名
		$this->_tableName=$this->_parseName($this->_name);
			
		//前缀
		if(empty($this->_tablePreFix)){
			$this->_tablePreFix=$this->_db->getConfig('table_prefix');

		}	
		//数据表全名
		$this->_fullTableName=$this->_tablePreFix.$this->_tableName;	
		
		//字段信息
		$this->_fields=$this->_dbCommand->from($this->_fullTableName)->getFields();
		
		
		//主键信息
		if(empty($this->_pk)){
			$pk=array();
			if(!empty($this->_fields)){
				foreach ($this->_fields as $k=>$v)
				{
					if($v['pk']==true){
						$pk[$k]=$k;
					}
				}
			}
			if(!empty($pk)&&count($pk)===1){
				$this->_pk=implode('', $pk);
			}else{
				$this->_pk=$pk;
			}
		}
		
		
	}
	
	
	
	
	/**
	 * 把驼峰命名转换成  下划线 _ 命名法
	 * @param  $name
	 * @return String 返回符合格式的字符串
	 */
	protected function _parseName($name)
	{
		if(empty($name)){
			return '';
		}else{
			$name=trim(strtolower(preg_replace('/[A-Z]/', '_\\0', $name)),'_');
		}
		
		return $name;
	}
	
	
	
	/**
	 * 设置属性值
	 * @param $name
	 * @param $value
	 */
	public function __set($name,$value)
	{
		$this->_attributes[$name]=$value;
	}
	
	
	
	/**
	 * 获得属性值
	 * @param $name
	 */
	public function __get($name)
	{
		$rt=NULL;
		if(isset($this->_attributes[$name])){
			$rt=$this->_attributes[$name];
		}
		
		return $rt;
	}
	
	
	
	/**
	 * 判断是否有该属性
	 * @param  $name
	 */
	public function __isset($name)
	{
		return isset($this->_attributes[$name]);
	}
	
	
	
	
	/**
	 * 删除属性
	 * @param $name
	 */
	public function __unset($name)
	{
		unset($this->_attributes[$name]);
	}
	
	
	
	
	/**
	 * 当调用不存在的方法时，自动判断并进行相应的操作。
	 * @param $method
	 * @param $args
	 */
	public function __call($method,$args)
	{
		//如果是进行查询操作。
		$situation1=substr($method, 0,9);				
		if($situation1==='findAllBy')
		{
			if(!isset($args[0])){
				throw new CDbException("{$method}方法必须有且仅有一个参数！", $this);
			}
			$attributes=array();
			$attr_string=substr($method, 9);
			if(empty($attr_string)){
				throw new CDbException(get_class($this)."找不到方法{$method}",
					 $this);
			}
			
			$link='';			
			if(strpos($attr_string,'And')!==false){
				//如果有And连接
				$attributes=explode('And', $attr_string);
				$link='AND';
			}elseif(strpos($attr_string, 'Or')!==false){
				//如果用Or连接
				$attributes=explode('Or', $attr_string);
				$link='OR';
			}else{
				$attributes=array($attr_string);
			}
			
			
			//如果是通过单个属性进行查询
			if($link===''){
				$field=$this->_parseName($attributes[0]);
				if(!isset($this->_fields[$field])){
					throw new CDbException("{$field}不是有效的字段名称！", $this);
				}else{
					//如果是数组，则进行 IN 查询。
					if(is_array($args[0])){
						$this->_dbCommand->where_in($field, $args[0]);
					}else{
						$this->_dbCommand->where($field,$args[0]);
					}
				}
			}else{
				//若是通过多个属性进行组合查询
				
				$cons=array();//待构造的查询条件。
				//对所属性数组进行遍历
				foreach ($attributes as $i=>$attribute_name)
				{
					//转换成下划线命名规则
					$attribute_name=$this->_parseName($attribute_name);
					
					//如果这个属性并不是数据表的某个字段
					if(!isset($this->_fields[$attribute_name])){
						throw new CDbException("{$attribute_name}不是有效的字段名称！", 
							$this);
					}
					
					if(isset($args[0][$attribute_name])){
						$cons[$attribute_name]=$args[0][$attribute_name];
					}
				}
				
				
				if(empty($cons)){
					return array();
				}else{
					if($link==='AND'){
						$this->_dbCommand->where($cons);
					}else{
						$this->_dbCommand->where($cons,NULL,2);
					}
				}
			}
			
			$this->_dbCommand->from($this->_fullTableName);
			return $this->_dbCommand->queryAll();	
		}
		
		
		throw new CDbException("找不到方法{$method}", $this);
	}
	
	
	
	/**
	 * 持久化保存对象数据。
	 * return 成功保存的记录数。
	 *
	 */
	public function save()
	{
		$datas=$this->_getPersistentDatas($this->_attributes);
		
		//如果是新数据，则插入到数据库中
		if($this->_recordState===self::NEW_RECORD){
			$count=$this->_insert($datas);
			if($count>0){
				$this->_recordState=self::OLD_RECORD;
				if(!is_array($this->_pk)){
					$this->_attributes[$this->_pk]=$this->_db->getPdo()->lastInsertId();
				}else{
					foreach ($this->_pk as $pk)
					{
						if($this->_fields[$pk]['autoinc']===true){
							$this->_attributes[$pk]=$this->_db->
														getPdo()->lastInsertId();
							break;
						}
					}
				}
			}	
			return $count;
		}elseif($this->_recordState===self::OLD_RECORD){
			return $this->_update($datas);
		}
		
		return 0;
	}
	
	
	
	/**
	 * 插入数据
	 * @param $datas
	 * @return 影响行数
	 */
	protected function _insert($datas)
	{
		$rt=$this->_dbCommand->into($this->_fullTableName)->insert($datas);
		return $rt;
	}
	

	
	
	/**
	 * 更新当前对象数据
	 * @param  $datas
	 * @return 影响行数
	 */
	protected function _update($datas)
	{
		if(empty($this->_pk)||$this->_recordState!==self::OLD_RECORD){
			return 0;
		}
		
		if(is_array($this->_pk)){
			$conditions=array();
			foreach ($this->_pk as $pk)
			{
				$conditions[$pk]=$this->$pk;
			}
			$this->_dbCommand->where($conditions);
		}else{
			$ar_pk=$this->_pk;
			if(!isset($this->$ar_pk)){
				return 0;
			}
			$this->_dbCommand->where($this->_pk,$this->$ar_pk);
		}
		
		$rt=$this->_dbCommand->update($datas,$this->_fullTableName);
		
		return isset($rt)?$rt:0;
	}
	
	
	
	/**
	 * 添加记录
	 * @param  $datas
	 */
	public function add($datas)
	{
		if(empty($datas)||!is_array($datas)){
			return 0;
		}
		
		$datas=$this->_getPersistentDatas($datas);
		$rt=$this->_insert($datas);
		return $rt;
	}
	
	
		
	/**
	 * 批量添加记录
	 * @param  $datas
	 */
	public function addAll($datas)
	{
		if(empty($datas)||!is_array($datas)){
			return 0;
		}
		$count=0;
		foreach ($datas as $data)
		{
			$result=$this->add($data);
			$count+=$result;
		}
		
		return $count;
	}
	
	
	
	/**
	 * 删除当前对象数据
	 * @return 影响行数
	 */
	public function delete()
	{
		if($this->_recordState!==self::OLD_RECORD||empty($this->_pk)){
			return 0;
		}
		
		if(is_array($this->_pk)){
			$conditions=$this->_getCompPkConditions($this->_attributes);
			if($conditions==false){
				return 0;
			}
			$this->_dbCommand->where($conditions);
		}else{
			$ar_pk=$this->_pk;
			if(!isset($this->$ar_pk)){
				return 0;
			}
			$this->_dbCommand->where($this->_pk,$this->$ar_pk);
		}
		
		$rt=$this->_dbCommand->delete($this->_fullTableName);
		if($rt>0){
			$this->_recordState=self::NEW_RECORD;
		}
		return $rt;
	}
	
	
	
	/**
	 * 无需载入对象的情况下通过PK删除。注：如果主键是单字段，则可批量删除。
	 * @param  $conditions
	 * @return 删除的行数。
	 */
	public function deleteByPk($conditions)
	{
		if(empty($this->_pk)){
			return 0;
		}
		
		if(is_array($this->_pk)){
			$cons=$this->_getCompPkConditions($conditions);
			if($cons==false){
				return 0;
			}
			
			$this->_dbCommand->where($cons);
		}else{
			//如果不是组合主键，则视为单字段主键
			
			//如果条件是数组，则用IN删除。
			if(is_array($conditions)){
				$this->_dbCommand->where_in($this->_pk, $conditions);
			}else{
				//否则，直接按PK值删除
				$this->_dbCommand->where($this->_pk,$conditions);
				
			}
			
		}
		
		$rt=$this->_dbCommand->delete($this->_fullTableName);
		return isset($rt)?$rt:0;
	}
	
	
	
	/**
	 +-------------------------------------
	 * 无需载入对象的情况下通过条件删除
	 * 参数可以是以下的形式：
	 * 1.deleteAll('class_id',1);
	 * 2.deleteAll('class_id=1');
	 * 3.deleteAll(array('class_id'=>1,'sex'=>'男'));第一个元素是数组，则以AND 连接。
	 +-----------------------------------------
	 * @param $conditions
	 * @return 删除的行数。
	 */
	public function deleteAll($conditions,$value=NULL)
	{
		return $this->_dbCommand->where($conditions,$value)->
					from($this->_fullTableName)->delete();
	}
	
	
	
	
	
	/**
	 * 无需载入当前对象的情况下通过PK更新
	 * @param $pk
	 * @param $datas
	 * @return 更新的行数
	 */
	public function updateByPk($pk,$datas)
	{
		if(empty($this->_pk)){
			return 0;
		}
		
		if(is_array($this->_pk)){
			$conditions=$this->_getCompPkConditions($pk);
			if(empty($conditions)){
				return 0;
			}		
			
			$this->_dbCommand->where($conditions);
		}else{
			$this->_dbCommand->where($this->_pk,$pk);
		}
		
		$datas=$this->_getPersistentDatas($datas);
		return $this->_dbCommand->update($datas,$this->_fullTableName);
	}
	

	
	
	/**
	 * 无需载入当前对象的情况下通过条件更新
	 * @param $datas
	 * @return 更新的行数
	 */
	public function updateAll($datas,$conditions,$value=NULL)
	{
		$datas=$this->_getPersistentDatas($datas);
		return $this->_dbCommand->where($conditions,$value)->
					update($datas,$this->_fullTableName);
	}
	
	
	
	
	/**
	 * 按主键查找某条记录
	 * @param $conditions
	 */
	public function find($conditions)
	{
		if(empty($this->_pk)){
			return NULL;
		}
		
		if(is_array($this->_pk)){
			$cons=$this->_getCompPkConditions($conditions);
			if($cons==false){
				return 0;
			}
			$this->_dbCommand->where($cons);
		}else{
			
			$this->_dbCommand->where($this->_pk,$conditions);
		}
		
		$rows=$this->_dbCommand->from($this->_fullTableName)->queryAll();
	
		$row=array();
		if(!empty($rows)&&is_array($rows)){
			$row=$rows[0];
		}		
		
		//关联处理
		$this->_findRelations($row);
		
		$this->_attributes=array_merge($this->_attributes,$row);
		$this->_recordState=self::OLD_RECORD;
		
		return $row;
	}
	
	
	
	/**
	 * 按查询条件返回所有结果
	 * @param $query
	 */
	public function findAll($query)
	{	
		$this->_dbCommand->setQuery($query);
		if(isset($query['conditions'])){
			$con=array();
			if(!is_array($query['conditions'])){
				$con[]=$query['conditions'];
			}else{
				$con=$query['conditions'];
			}
			
			$this->_dbCommand->setCondition($con);
			unset($query['conditions']);
		}
		
		return $this->_dbCommand->from($this->_fullTableName)->queryAll();
	}
	

	
	/**
	 * 获得$datas中能够持久化的数据
	 * @param $datas
	 * @return Array $datas 返回过滤不能保存到数据库中的数据后的新数组 
	 */
	protected function _getPersistentDatas($datas)
	{
		if(empty($this->_fields)){
			return array();
		}
		
		$fields=array_keys($this->_fields);
		foreach ($datas as $k=>$v)
		{
			if(!in_array($k,$fields)){
				unset($datas[$k]);
			}
		}
		
		return $datas;
	}

	
	
	
	/**
	 * 整理并返回组合主键的查询条件
	 * @param  $conditions
	 */
	protected function _getCompPkConditions($conditions)
	{
		if(!is_array($conditions)||!is_array($this->_pk)){
			return false;
		}
		
		$cons=array();
		foreach ($this->_pk as $pk)
		{
			if(!isset($conditions[$pk])){
				return false;
			}
			$cons[$pk]=$conditions[$pk];
		}
		
		return $cons;
	}
	
	

	
	/**
	 * 设置当前操作涉及的关联
	 * @param $relation
	 * @return CActiveRecord
	 */
	public function with($relation)
	{
		if(empty($relation)){
			return $this;
		}
		
		if($relation===true){
			if(isset($this->_relations)){
				$this->_with=array_keys($this->_relations);
				
			}
		}else{		
			if(is_array($relation)){
				$this->_with=$relation;
			}else{
				$this->_with[]=(string)$relation;
			}
		}
		
		return $this;
	}
	
	
	
	
	/**
	 * 寻找关联模型数据
	 * @param  $row
	 * @throws CDbException
	 */
	protected function _findRelations(&$row)
	{
		if(empty($row)||empty($this->_with)){
			
			return;
		}
		

		foreach ($this->_with as $relation_name)
		{
			if(!isset($this->_relations[$relation_name])){
				continue;
			}
			
			
			$relation=$this->_relations[$relation_name];
			if(empty($relation['type'])){
				throw new CDbException("关联：{$relation_name} 没有指定关联类型！", 
							$this);
			}
			
			//关联的模型
			$class_name=isset($relation['class_name'])?
							$relation['class_name']:$relation_name;
			
			//取得关联模型基础对象实例
			$relation_model=M($class_name);
			$relation_datas=array();				
			
			if($relation['type']===self::HAS_ONE){	
				//一对一关系=======================================
				//外键
				if(isset($relation['foreign_key'])){
					$foreign_key=$relation['foreign_key'];
				}else{
					if(is_array($this->_pk)){
						$foreign_key=$this->_pk;
					}else{
						$foreign_key=$this->_tableName."_id";
					}
				}
								
				if(is_array($foreign_key)){
					//如果外键是数组，则必须与当前模型的主键名字一样。
					$cons=$this->_getCompPkConditions($row);
									
				}else{
					$cons[$foreign_key]=isset($row[$this->_pk])?$row[$this->_pk]:NULL;
				}
				
				if(empty($cons)){
						return;
				}					
				$query['conditions']=$cons;
				$relation_datas=$relation_model->findAll($query);
				if(!empty($relation_datas)){
					$row[$relation_name]=$relation_datas[0];
				}					
			}elseif($relation['type']===self::HAS_MANY){
				//一对多关系=================================
				//外键
				if(isset($relation['foreign_key'])){
					$foreign_key=$relation['foreign_key'];
				}else{
					if(is_array($this->_pk)){
						$foreign_key=$this->_pk;
					}else{
						$foreign_key=$this->_tableName."_id";
					}
				}
								
				if(is_array($foreign_key)){
					//如果外键是数组，则必须与当前模型的主键名字一样。
					$cons=$this->_getCompPkConditions($row);
									
				}else{
					$cons[$foreign_key]=isset($row[$this->_pk])?$row[$this->_pk]:NULL;
				}
				
				if(empty($cons)){
						return;
				}					
				$query['conditions']=$cons;
				$relation_datas=$relation_model->findAll($query);
				if(!empty($relation_datas)){
					$row[$relation_name]=$relation_datas;
				}
			}elseif($relation['type']===self::BELONG_TO){
				//多对一关系=======================================
				//外键
				if(isset($relation['foreign_key'])){
					$foreign_key=$relation['foreign_key'];
				}else{
					if(is_array($relation_model->getPk())){
						$foreign_key=$relation_model->getPk();
					}else{
						$foreign_key=$relation_model->getTableName()."_id";						
					}
				}
				
				
				if(is_array($foreign_key)){
					foreach ($foreign_key as $k)
					{
						if(!isset($this->_fields[$k])){
							throw new CDbException(get_class($this)."模型中关联{$relation_name}外键定义错误！",
										 $this);
						}
						$cons[$k]=isset($row[$k])?$row[$k]:'';
					}
					
					$relation_datas=$relation_model->find($cons);
				}else{
					if(!isset($this->_fields[$foreign_key])){
						throw new CDbException(get_class($this)."模型中关联{$relation_name}外键定义错误！",
										 $this);
					}
					if(isset($row[$foreign_key])){
						$relation_datas=$relation_model->find($row[$foreign_key]);
					}
				}
				
				$row[$relation_name]=$relation_datas;
				
			}elseif ($relation['type']===self::MANY_TO_MANY) {
				
			}else{
				
			}									
		}		
	}
	
	
	/**
	 * 取得主键
	 * @return mixed
	 */
	public function getPk()
	{
		return $this->_pk;
	}
	
	
	/**
	 * 取得相应数据表名
	 * @return String 
	 */
	public function getTableName()
	{
		return $this->_tableName;
	}
	
}