<?php
class_exists("AppResource") || require("AppResource.php");
class_exists("AuthController") || require("controllers/AuthController.php");
class TableResource extends AppResource{
	public function __construct(){
		parent::__construct();
		if(!AuthController::is_authed()){
			$this->set_unauthed("Please signin.");
		}
	}
	public $columns;
	public $table_name;
	public $results;
	public function get(){
		$query = new Query((object)array("cid"=>0, "name"=>null, "type"=>null, "notnull"=>null, "dflt_value"=>null, "pk"=>null));
		$view = "db/table";
		$this->columns = array();
		if($this->request->path[0] !== null){
			$this->table_name = $this->request->path[0];
			$this->columns = $query->execute(Repo::get_provider(), null, "pragma table_info({$this->table_name})");
			$sql = "select * from {$this->table_name}";
			$class_name = ucwords(String::singularize($this->table_name));
			if(!class_exists($class_name)){
				$file_name = sprintf(App::dirname() . '/models/%s.php', $class_name);
				if(file_exists($file_name)){
					require($file_name);
				}else{
					$class_text = <<<eos
<?php
class $class_name extends ChinObject{
	public function __construct(\$values = array()){
		parent::__construct(\$values);
	}
	%s;
}
eos;
					$field_def = array();
					foreach($this->columns as $column){
						$field_def[] = "public \${$column->name}";
					}
					$class_text = sprintf($class_text, implode(";", $field_def));
					file_put_contents("app/models/temp/$class_name.php", $class_text);
					require("app/models/temp/$class_name.php");
					unlink("app/models/temp/$class_name.php");
				}
			}
			$this->results = find_by::execute(null, new $class_name());
		}else{
			$view = "db/add_table";
		}
		$this->columns = array_map(function($col){
			return (object)array("name"=>$col->name, "type"=>$col->type, "dflt_value"=>$col->dflt_value, "notnull"=>(int)$col->notnull === 0);
		}, $this->columns);
		$this->output = View::render($view, $this);
		return View::render_layout("default", $this);
	}
	public function delete($table_name, $id = null){
		$query = new Query(null);
		if($this->request->path[0] !== null){
			if($id !== null){
				$table_name = $this->request->path[0];
				$did_error = $query->execute(Repo::get_provider(), null, "delete from $table_name where ROWID=$id");
				$this->set_redirect_to("table/$table_name");
				return null;
			}
		}else{
			$view = "db/index";
			$query->execute(Repo::get_provider(), null, "drop table $table_name");
			$this->set_redirect_to("db");
			$this->output = View::render($view, $this);
		}
		return View::render_layout("default", $this);
	}
	public function put($table_name, $columns){
		$query = new Query(null);
		$view = "db/table";
		$db = Repo::get_provider();
		$did_error = false;
		$column_query = new Query((object)array("name"=>""));
		$this->columns = $column_query->execute($db, null, "pragma table_info({$table_name})");
		$alter_sql = "alter table $table_name rename to {$table_name}_temp;";
		$alter_sql .= "create table $table_name (%s);";
		$alter_sql .= "insert into $table_name (%s) select %s from {$table_name}_temp;";
		$alter_sql .= "drop table {$table_name}_temp;commit;";
		$column_definitions = array();
		$this->columns = array_map(function($col){
			return array("name"=>$col->name, "type"=>$col->type, "dflt_value"=>$col->dflt_value, "notnull"=>(int)$col->notnull === 1);
		}, $this->columns);
		
		foreach($columns as $key=>$field){
			$name = $field["name"];
			$type = $field["type"];
			$dflt_value = $field["dflt_value"];
			$notnull = (array_key_exists("notnull", $field) && $field["notnull"] === "true" ? true : false);
			$column_definitions[] = "$name $type " . ($notnull ? "not null" : "null") . (strlen($dflt_value) > 0 ? " default $dflt_value" : null);
		}
		$this->columns = array_filter($this->columns, function($value) use($columns){
			$found = array();
			$found = array_filter($columns, function($c) use ($value){
				return $c["name"] === $value["name"];
			});
			return (count($found) > 0);
		});
		$this->columns = array_map(function($c){
			return $c["name"];
		}, $this->columns);
		$alter_sql = sprintf($alter_sql, implode(",", $column_definitions), implode(",", $this->columns), implode(",", $this->columns));
		$query->execute($db, null, "begin transaction");
		$query->execute($db, null, "alter table $table_name rename to {$table_name}_temp");
		$query->execute($db, null, sprintf("create table $table_name (%s)", implode(",", $column_definitions)));
		$query->execute($db, null, sprintf("insert into $table_name (%s) select %s from {$table_name}_temp", implode(",", $this->columns), implode(",", $this->columns)));
		$query->execute($db, null, "drop table {$table_name}_temp");
		$did_error = $query->execute($db, null, "commit");
		//$did_error = $query->execute($db, $alter_sql);
		App::add_user_message($alter_sql);
		App::add_user_message($did_error ? "Update succeeded" : "Update failed" . ": " . json_encode($db->error_info));
		$this->set_redirect_to("table/$table_name");
		if(!$did_error){
			$query = new Query((object)array("cid"=>0, "name"=>null, "type"=>null, "notnull"=>null, "dflt_value"=>null, "pk"=>null));
			$this->table_name = $table_name;
			$this->columns = $query->execute($db, null, "pragma table_info({$this->table_name})");
		}
		$this->output = View::render($view, $this);
		return View::render_layout("default", $this);
	}
	
}