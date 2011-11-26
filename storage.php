<?php
class storage{
	function __construct($options = null){
		if($options === null) $options = array();
		$ref = new ReflectionClass($this);
		$this->class_name = $ref->getName();
		$this->table_name = array_key_exists("table_name", $options) && $options["table_name"] !== null ? $options["table_name"] :  string::pluralize($this->class_name);
		$this->primary_key_field = array_key_exists("primary_key_field", $options) && $options["primary_key_field"] !== null ? $options["primary_key_field"] : "id";		
		$this->provider = new PDO("sqlite:" . (array_key_exists("connection_string", $options) && $options["connection_string"] !== null ? $options["connection_string"] : filter_center::publish("should_get_storage_provider", $this, $this)));
		$this->delegate = array_key_exists("delegate", $options) && $options["delegate"] !== null ? $options["delegate"] : null;
	}
	private $class_name;
	public $table_name;
	public $primary_key_field;
	public $provider;
	protected $delegate;
	private $schema;
	static function __callStatic($name, $args){
		if(strpos($name, "find_") === false) return;
		$name = str_replace("find_", "", $name);
		$db = new storage(array("table_name"=>$name));		
		$where = null;
		$order_by = null;
		$limit = 0;
		$offset = 0;
		$columns = "ROWID as id, *";
		$params = null;
		if($args !== null && count($args) > 0 && $args[0] !== null){
			$arg = $args[0];
			$where = property_exists($arg, "where") ? $arg->where : $where;
			$order_by = property_exists($arg, "order_by") ? $arg->order_by : $order_by;
			$limit = property_exists($arg, "limit") ? $arg->limit : $limit;
			$offset = property_exists($arg, "offset") ? $arg->offset : $offset;
			$columns = property_exists($arg, "columns") ? $arg->columns : $columns;
			$params = property_exists($arg, "args") ? $arg->args : $params;
		}
		$list = $db->all($where, $order_by, $limit, $offset, $columns, $params);
		return $list !== null ? $list : array();
	}
	function get_schema($table_name){
		$this->schema = $this->query("pragma table_info($table_name)", null, function($obj){
			return new column($obj);
		});
		return $this->schema;
	}
	
	function create_from($col){
		$schema = $this->get_schema();
		$obj = new stdClass();
		foreach($col as $key=>$val){
			foreach($schema as $col){
				if($key === $col->COLUMN_NAME){
					$obj->{$key} = $val;
				}
			}
		}
		return $obj;
	}
	function default_value($column){
		$result = null;
		if(strlen($column->COLUMN_DEFAULT)) return null;
		if($column->COLUMN_DEFAULT == "getdate()" || $column->COLUMN_DEFAULT == "(getdate())") return date();
		if($column->COLUMN_DEFAULT == "newid()") return uniqid();
		return str_replace(")", "", str_replace("(", "", $column->COLUMN_DEFAULT));
	}
	function prototype(){
		$schema = $this->get_schema();
		$obj = new stdClass();
		foreach($schema as $col){
			$obj->{$col->COLUMN_NAME} = $this->default_value($col);
		}
		$obj->_table = $this;
		return $obj;
	}
	function query($sql, $args, $delegate = null){
		$cmd = $this->create_command($sql, $args);			
		$result = $cmd->execute();		
		$error_info = $this->provider->errorInfo();
		$list = array();
		if(count($error_info) > 1 && $error_info[1] !== null){
			throw new Exception($error_info[0] . ":" . $error_info[2], $error_info[1]);
		}
		while($obj = $cmd->fetchObject()){
			if($delegate !== null) $list[] = $delegate($obj);
			else $list[] = self::to_object((object)$obj, new stdClass());
		}
		$cmd = null;
		return count($list) > 0 ? $list : null;
	}
	function query_with_provider($sql, $provider, $args, $delegate = null){
		$class_name = get_class($this);
		$cmd = $this->create_command_with_provider($sql, $provider, $args);
		$result = $cmd->execute();			
		$error_info = $this->provider->errorInfo();
		if(count($error_info) > 1 && $error_info[1] !== null){
			throw new Exception($error_info[0] . ":" . $error_info[2], $error_info[1]);
		}
		while($obj = $cmd->fetchObject()){
			if($delegate !== null) $list[] = $delegate((object)$obj);
			else $list[] = self::to_object((object)$obj, new $class_name());
		}
		return $list;
	}
	static function to_object($obj, $target){
		if($obj === false) return null;
		$properties = get_object_vars($obj);
		foreach($properties as $key=>$value){
			if(strpos($key, "->") !== false){
				list($name, $field) = explode("->", $key);
				$target->{$name}->{$field} = $value;				
			}else{
				$target->{$key} = $value;				
			}
		}
		return $target;
	}	
	
	static function populate($list, $inst){
		$ubounds = count($list);
		for($i = 0; $i < $ubounds; $i++){
			$list[$i] = self::to_object($list[$i], $inst);
		}
		return $list;
	}
	function create_command($sql, $args){
		$cmd = $this->provider->prepare($sql);
		$error_info = $this->provider->errorInfo();
		if($cmd === false) throw new Exception($error_info[0] . ":" . $error_info[2], $error_info[1]);
		if($args !== null){
			$cmd = $this->bind($cmd, $sql, $args);
		}
		$error_info = $this->provider->errorInfo();
		if($cmd === false) throw new Exception($error_info[0] . ":" . $error_info[2], $error_info[1]);
		return $cmd;
	}
	function create_command_with_provider($sql, $provider, $args){
		$cmd = $provider->prepare($sql);
		$error_info = $provider->errorInfo();
		if($cmd === false) throw new Exception($error_info[0] . ":" . $error_info[2], $error_info[1]);
		if($args !== null){
			$cmd = $this->bind($cmd, $sql, $args);			
		}
		$error_info = $provider->errorInfo();
		if($cmd === false) throw new Exception($error_info[0] . ":" . $error_info[2], $error_info[1]);
		return $cmd;
	}
	private function bind($cmd, $sql, $obj){
		$args = array();
		if(is_array($obj)){
			$args = $obj;
		}else{
			$args = get_object_vars($obj);
		}
		foreach($args as $key=>$value){
			if(strpos($sql, ":$key") !== false){
				if(is_bool($value)){
					$value = $value ? 1 : 0;
				}
				$cmd->bindValue(":$key", $value);
			}
		}
		return $cmd;
	}
	function build_commands($things){
		$commands = array();
		foreach($things as $item){
			if($this->has_primary_key($item)){
				array_push($commands, $this->create_update_command($item, $this->get_primary_key($item)));
			}else{
				array_push($commands, $this->create_insert_command($item));
			}
		}
		return $commands;
	}
	function open_connection(){
		$provider = new PDO("sqlite:" . storage_provider());
		return $provider;
	}
	function save($things){
		if(!is_array($things)) throw new Exception("storage::save expects an array.");
		$commands = $this->build_commands($things);
		return $this->execute_commands($commands);
	}
	function execute_command($command, $transaction_on = true){
		return $this->execute_commands(array($command), $transaction_on);
	}
	function execute($sql, $args, $transaction_on = true){
		return $this->execute_command($this->create_command($sql, $args), $transaction_on);
	}
	function execute_commands($commands, $transaction_on = true){
		$errors = array();
		$list = array();
		$records = array();
		if($transaction_on){
			$this->provider->beginTransaction();			
		}
		foreach($commands as $cmd){
			$result = $cmd->execute();
			$error_info = $this->provider->errorInfo();
			if(count($error_info) > 1 && $error_info[1] !== null){
				array_push($errors, $error_info);
			}
			while($obj = $cmd->fetchObject()){
				$records[] = self::to_object((object)$obj, new stdClass());
			}
			if(count($records) > 0) $list[] = $records;
			$records = array();
		}
		if($transaction_on){
			if(count($errors) > 0){
				$this->provider->rollBack();
			}else{
				$this->provider->commit();
			}			
		}
		return $list;
	}
	function has_primary_key($obj){
		return $obj->{$this->primary_key_field} != null;
	}
	function get_primary_key($obj){
		return $obj->{$this->primary_key_field};
	}
	function create_insert_command($args){
		if(!is_array($args)){
			$args = get_object_vars($args);
		}
		$sql = "insert into {$this->table_name} (%s) values (%s)";
		$keys = array();
		$values = array();
		foreach($args as $key=>$val){
			if($key == $this->primary_key_field) continue;
			$keys[] = "$key";
			$values[] = ":$key";
		}
		$sql = sprintf($sql, implode(",", $keys), implode(",",$values));
		$cmd = $this->create_command($sql, $args);
		return $cmd;
	}
	function create_update_command($obj, $primary_key_value){
		$properties = get_object_vars($obj);
		$sql = "update {$this->table_name} set %s where ROWID=:{$this->primary_key_field}";
		$params = array();
		foreach($properties as $key=>$val){
			if($key == $this->primary_key_field) continue;
			$params[] = "$key=:$key";
		}		
		$sql = sprintf($sql, implode(",", $params));
		$cmd = $this->create_command($sql, $obj);
		return $cmd;
	}
	function create_delete_command($args, $where = null){
		$sql = "delete from {$this->table_name} where ";
		$args = is_array($args) ? $args : array($args);
		if($where === null){
			$where = sprintf("ROWID=:%s", $this->primary_key_field);
		}
		$sql .= $where;
		$cmd = $this->create_command($sql, $args);
		return $cmd;
	}
	function insert($obj, $delegate = null){
		$cmd = $this->create_insert_command($obj);
		$this->execute_command($cmd);
		$sql = "select last_insert_rowid() as id";
		$list = $this->query($sql, null, $delegate);
		$obj->{$this->primary_key_field} = (int)$list[0]->id;
		return $obj;
	}
	function update($obj, $primary_key_value){
		return $this->execute_command($this->create_update_command($obj, $primary_key_value));
	}
	function delete($args, $where = null){
		return $this->execute_command($this->create_delete_command($args, $where));
	}    
	function all($where = null, $order_by = null, $limit = 0, $offset = 0, $columns = "*", $args = null, $delegate = null){
		$sql = $this->build_select($where, $order_by, $limit, $offset);
		return $this->query(sprintf($sql, $columns, $this->table_name), $args, $delegate);
	}
	function build_select($where, $order_by, $limit, $offset){
		$sql = "select %s from %s";
		if($where != null) $sql .= " where $where";
		if($order_by !== null) $sql .= " order by $order_by";
		if($limit > 0) $sql .= " limit $limit";
		if($offset > 0) $sql .= " offset $offset";
		return $sql;
	}
	function single($where, $args, $delegate = null){
		$sql = sprintf("select ROWID as id, * from %s where %s", $this->table_name, $where);
		$list = $this->query($sql, $args, $delegate);
		if($list == null) return null;
		return $list[0];
	}
}
