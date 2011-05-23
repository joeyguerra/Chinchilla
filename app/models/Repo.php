<?php
class Repo{
	public function __construct(){}
	public function __destruct(){
		self::$db = null;
	}
	public static $db;
	public static function get_provider(){
		if(self::$db != null) return self::$db;
		self::$db = new PDO("sqlite:" . Settings::$storage_provider->path);
		return self::$db;
	}
	public static function save($obj){
		if((int)$obj->id > 0){
			$query = new UpdateQuery($obj);
		}else{
			$query = new InsertQuery($obj);
		}
		$result = $query->execute(self::get_provider(), $obj);
		return $obj;
	}
	public static function delete($obj){
		$id = (int)$obj->id;
		if($id == 0) return null;
		$query = new DeleteQuery($obj);
		$result = $query->execute(self::$db, $obj);
		return $id;
	}
	public static function execute($query, $obj = null){
		return self::find($query, $obj);
	}
	public static function find($query, $obj = null){
		$cmd = self::get_provider()->prepare($query);
		if($cmd === false) var_dump(self::get_provider()->errorInfo());		
		$result = null;
		if($obj !== null){
			$properties = get_object_vars($obj);
			foreach($properties as $key=>$value){
				if(strpos($query, ":$key") !== false){
					$value = $obj->$key;
					if(is_bool($value)){
						$value = $value ? 1 : 0;
					}
					$cmd->bindValue(":$key", $value);
				}
			}			
			$result = $cmd->execute();
			$result = new RepoPopulator($cmd);
		}else{
			$result = $cmd->execute();
			$result = new RepoPopulator($cmd);
		}
		return $result;
	}
}
class RepoPopulator{
	public function __construct($cmd = null){
		$this->cmd = $cmd;
	}
	private function populate($obj, $target){
		$properties = get_object_vars($obj);
		foreach($properties as $key=>$value){
			$target->{$key} = $value;
		}
		return $target;
	}
	public function first($target = null){
		if($this->cmd === null) return null;
		$obj = $this->cmd->fetchObject();
		if($obj === false) return null;
		$obj = (object)$obj;
		if($target !== null){
			$obj = $this->populate($obj, $target);
		}
		return $obj;
	}
	public function to_list($target = null){
		if($this->cmd === null) return null;
		$list = array();
		
		if($target !== null){
			$class = new ReflectionClass($target);
			while($obj = $this->cmd->fetchObject()){
				$list[] = $this->populate((object)$obj, $class->newInstance());
			}
		}else{
			while($obj = $this->cmd->fetchObject()){
				$list[] = (object)$obj;
			}			
		}
		if(count($list) === 0) return null;
		return $list;
	}
}
class RepoException extends Exception{
	public function __construction($message, $code, $previous=null){
		$this->code = $code;
		$this->message = $message;
		$this->previous = $previous;
	}
}

class Query{
	public function __construct($obj){
		$this->obj = $obj;
	}
	public $obj;
	public $error_info;
	public function bind($cmd, $query, $properties){
		foreach($properties as $property){
			$name = $property->getName();
			if(strpos($query, ":$name") !== false){
				$value = $this->obj->{$name};
				if(is_bool($value)){
					$value = $value ? 1 : 0;
				}
				// If an error occurred here, it's likely that a property is misspelled.
				$cmd->bindValue(":$name", $value);						
			}
		}
		return $cmd;
	}
	public function is_public($prop){
		return $prop->isPublic();
	}
	public function to_property_name($prop){
		return $prop->getName();
	}
	
	public function execute($db, $target, $query = null){
		$class = null;
		$properties = null;
		$list = array();		
		if($this->obj !== null){
			$class = new ReflectionClass($this->obj);			
			$table_name = strtolower(String::pluralize($class->getName()));
			$properties = $class->getProperties();
			$properties = array_filter($properties, array($this, "is_public"));
			$query = $query === null ? $this->build_query($properties, $table_name) : $query;
			$cmd = $db->prepare($query);
			$this->error_info = $db->errorInfo();
			if($cmd === false) throw new RepoException($this->error_info[0] . ":" . $this->error_info[2], $this->error_info[1]);
			$cmd = $this->bind($cmd, $query, $properties);			
			$this->error_info = $db->errorInfo();
			if($cmd === false) throw new RepoException($this->error_info[0] . ":" . $this->error_info[2], $this->error_info[1]);
			$result = $cmd->execute();			
			$this->error_info = $db->errorInfo();
			if(count($this->error_info) > 1 && $this->error_info[1] !== null){
				throw new RepoException($this->error_info[0] . ":" . $this->error_info[2], $this->error_info[1]);
			}
			$target = $target === null ? $this->obj : $target;
			if($target !== null){
				$class = new ReflectionClass($target);
				$class_name = $class->getName();				
				while($obj = $cmd->fetchObject()){
					$list[] = ModelFactory::populate_single((object)$obj, $class->newInstance());
				}				
			}
			$cmd = null;
			return $list;
		}
		
		$result = $db->query($query);
		$this->error_info = $db->errorInfo();
		if(count($this->error_info) > 1 && $this->error_info[1] !== null){
			throw new RepoException($this->error_info[0] . ":" . $this->error_info[2], $this->error_info[1]);
		}
		while($row = $result->fetchObject()){
			$list[] = $row;
		}
		return $list;
	}
}
class UpdateQuery extends Query{
	public function __construct($obj){
		parent::__construct($obj);
	}
	public function build_query($properties, $table_name){
		$property = array_pop($properties);
		$name = $property->getName();
		$query = "update $table_name set $name=:$name";		
		while($property = array_pop($properties)){
			$name = $property->getName();
			if($name == "id") continue;
			$query .= ",$name=:$name";
		}		
		$query .= " where ROWID=:id";
		return $query;
	}
}
class InsertQuery extends Query{
	public function __construct($obj){
		parent::__construct($obj);
	}
	public function execute($db, $query = null){
		parent::execute($db, $query);		
		$query = "select last_insert_rowid() as id";
		$result = $db->query($query);
		$row = $result->fetchObject();
		$this->obj->id = (int)$row->id;
		return $this->obj;
	}
	public function build_query($properties, $table_name){
		$property = array_pop($properties);
		$name = $property->getName();		
		$query = "insert into $table_name (%s) values (%s)";
		$keys = $name;
		$values = ":$name";
		while($property = array_pop($properties)){
			$name = $property->getName();
			if($name == "id") continue;
			$keys .= ",$name";
			$values .= ",:$name";
		}		
		$query = sprintf($query, $keys, $values);
		return $query;
	}
}
class DeleteQuery extends Query{
	public function __construct($obj){
		parent::__construct($obj);
	}
	public function build_query($properties, $table_name){
		$query = "delete from $table_name where ROWID=:id";
		return $query;
	}
}

class FindQuery extends Query{
	public function __construct($by, $obj, $options = null){
		parent::__construct($obj);
		$this->by = $by;
		$this->options = $options;
	}
	public $by;
	public $options;
	public function build_query($by, $table_name){
		$query = "select ROWID as id, * from $table_name" . ($by != null ? " where " . $by : null);
		if($this->options !== null){
			$query .= " {$this->options}";
		}
		return $query;
	}
	public function execute($db, $target){
		$class = new ReflectionClass($this->obj);
		$table_name = strtolower(String::pluralize($class->getName()));
		$properties = $class->getProperties();			
		$query = $this->build_query($this->by, $table_name);
		$cmd = $db->prepare($query);
		if($cmd === false){
			$cmd = NotificationCenter::post("query_failed", $this, (object)array("db"=>$db, "query"=>$query, "obj"=>$this->obj, "cmd"=>$cmd));
		}
		$cmd = $this->bind($cmd, $query, $properties);
		$this->error_info = $db->errorInfo();
		if($cmd === false) throw new RepoException($this->error_info[0] . ":" . $this->error_info[2], $this->error_info[1]);
		$result = $cmd->execute();
		$this->error_info = $db->errorInfo();
		if(count($this->error_info) > 1 && $this->error_info[1] !== null){
			throw new RepoException($this->error_info[0] . ":" . $this->error_info[2], $this->error_info[1]);
		}
		$list = array();
		while($obj = $cmd->fetchObject()){
			$class = new ReflectionClass($target);
			$list[] = ModelFactory::populate_single((object)$obj, $class->newInstance());
		}
		$cmd = null;
		if(count($list) === 0) return null;
		//if(count($list) === 1) return $list[0];
		return $list;
	}
}

class FindQueryWithLimit extends Query{
	public function __construct(FindQuery $query, $page, $limit){
		$this->find_query = $query;
		$this->page = $page;
		$this->limit = $limit;
	}
	public $find_query;	
	private $page;
	private $limit;
	public function build_query($by, $table_name){
		$query = $this->find_query->build_query($by, $table_name);
		$query .= " limit {$this->page}, {$this->limit}";
		return $query;
	}
	public function execute($db){
		$class = new ReflectionClass($this->find_query->obj);
		$table_name = strtolower(String::pluralize($class->getName()));
		$properties = $class->getProperties();
		$query = $this->build_query($this->find_query->by, $table_name);
		$cmd = $db->prepare($query);		
		$this->error_info = $db->errorInfo();
		if(count($this->error_info) > 1 && $this->error_info[1] !== null){
			throw new RepoException($this->error_info[0] . ":" . $this->error_info[2], $this->error_info[1]);
		}
		$cmd = $this->find_query->bind($cmd, $query, $properties);
		$result = $cmd->execute();
		$list = array();
		$class_name = $class->getName();
		while($this->find_query->obj = $cmd->fetchObject()){			
			$list[] = ModelFactory::populate_single((object)$this->find_query->obj, new $class_name());
		}
		$cmd = null;
		return $list;
	}
	
}
class Count{
	public $total;
}
class CountQuery extends Query{
	public function __construct($by, $obj){
		parent::__construct($obj);
		$this->by = $by;
	}
	public $by;
	public function build_query($by, $table_name){
		$query = "select count(1) as total from $table_name where " . $this->by;
		return $query;
	}
	public function execute($db){
		$class = new ReflectionClass($this->obj);
		$table_name = strtolower(String::pluralize($class->getName()));
		$properties = $class->getProperties();
		$query = $this->build_query($this->by, $table_name);
		$cmd = $db->prepare($query);
		$cmd = $this->bind($cmd, $query, $properties);
		$this->error_info = $db->errorInfo();
		if($cmd === false) throw new RepoException($this->error_info[0] . ":" . $this->error_info[2], $this->error_info[1]);
		$result = $cmd->execute();
		$this->error_info = $db->errorInfo();
		if(count($this->error_info) > 1 && $this->error_info[1] !== null){
			throw new RepoException($this->error_info[0] . ":" . $this->error_info[2], $this->error_info[1]);
		}
		$list = array();
		$class_name = $class->getName();
		$this->obj = $cmd->fetchObject();
		$count = ModelFactory::populate_single((object)$this->obj, new Count());
		$cmd = null;
		return $count;
	}
}


class save_object extends Repo{
	public static function execute($obj){
		$db = self::get_provider();
		$class = new ReflectionClass($obj);
		$properties = $class->getProperties();
		if((int)$obj->id > 0){
			$query = new UpdateQuery($obj);
		}else{
			$query = new InsertQuery($obj);
		}
		$result = $query->execute($db, $obj);
		return $obj;
	}
}

class delete_object extends Repo{
	public static function execute($obj){
		$id = (int)$obj->id;
		if($id == 0) return null;
		$db = self::get_provider();
		$query = new DeleteQuery($obj);
		$result = $query->execute($db, $obj);
		return $id;
	}
}
class find_count_by extends Repo{
	public static function execute($by, $obj){
		$db = self::get_provider();
		$query = new CountQuery($by, $obj);
		$list = $query->execute($db, $obj);
		return $list;
	}
}
class find_by_with_limit extends Repo{
	public static function execute($by, $obj, $page, $limit, $options = null){
		$db = self::get_provider();
		$query = new FindQueryWithLimit(new FindQuery($by, $obj, $options), $page, $limit);
		$list = $query->execute($db, $obj);
		return $list;
	}
}
class find_one_by extends Repo{
	public static function execute($by, $obj, $target = null){
		$db = self::get_provider();
		$query = new FindQuery($by, $obj);
		$list = $query->execute($db, $target === null ? $obj : $target);
		if(count($list) === 0) return null;
		if(count($list) >= 1) return $list[0];
	}
}
class find_by extends Repo{
	public static function execute($by, $obj, $target = null){
		$db = self::get_provider();
		$query = new FindQuery($by, $obj);
		$list = $query->execute($db, $target === null ? $obj : $target);
		return $list;
	}
}