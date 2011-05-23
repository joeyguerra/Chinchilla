<?php
class_exists("ModelFactory") || require("ModelFactory.php");
class Media extends ChinObject{
	public function __construct($values = array()){
		$this->id = 0;
		$this->owner_id = 0;
		parent::__construct($values);
	}
	public $description;
	public $id;
	public $owner_id;
	public $src;
	private static $images;
	const PATH = "media";
	private static function delete_empty_folder($file_name_with_path){
		$parts = explode(DIRECTORY_SEPARATOR, $file_name_with_path);
		array_pop($parts);
		$folder = implode(DIRECTORY_SEPARATOR, $parts);
		$files = scandir($folder);
		if(count($files) === 2){
			do{
				rmdir(implode(DIRECTORY_SEPARATOR, $parts));
				$name = array_pop($parts);
			}while(is_numeric($name));
		}
	}
	private static function traverse($path){
		$root = ($path == null ? PATH : $path);
		if(!file_exists($root)){
			mkdir($root, 0777);
		}
		$folder = dir($root);
		if($folder != null){
			while (false !== ($entry = $folder->read())){
				if(strpos($entry, '.') !== 0){
					$file_name = $folder->path .'/'. $entry;					
					if(is_dir($file_name)){
						self::traverse($file_name);						
					}else{						
						self::$images[] = new Media(array("src"=>$file_name));
					}
				}
			}
			$folder->close();
		}
	}		
	public static function delete($file_name_with_path){
		$file_name_with_path = str_replace("/", DIRECTORY_SEPARATOR, $file_name_with_path);
		self::notify('will_delete_file', new Media(), $file_name_with_path);
		$did_delete = false;
		
		if(file_exists($file_name_with_path)){
			$did_delete = unlink($file_name_with_path);
		}
		self::delete_empty_folder($file_name_with_path);
		return $did_delete;
	}
	public static function find_all($path = null){
		$root = ($path == null ? PATH : $path);
		self::$images = array();
		if(file_exists($root)){
			self::traverse($root);
		}
		return self::$images;
	}
	public static function find_owned_by($owner_id){
		$medium = Repo::find("select ROWID as id, * from posts where type='attachment' and owner_id=:owner_id", (object)array("owner_id"=>(int)$owner_id)->to_list(new Media());
		return $medium;
	}
	
}