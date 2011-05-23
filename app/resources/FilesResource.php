<?php
	class_exists("AppResource") || require("AppResource.php");
	class_exists("AuthController") || require("controllers/AuthController.php");
	class_exists("PhotosResource") || require("PhotosResource.php");
	class FilesResource extends AppResource{
		public function __construct($attributes = null){
			parent::__construct($attributes);
			if(!AuthController::is_authed()){
				$this->set_unauthed("Please signin.");
			}
		}
		public function __destruct(){
			parent::__destruct();
		}
		public static $ALLOWED = array("image/jpg", "image/jpeg", "image/gif", "image/png");
		public $files;
		public $result;
		public function get(){
			$this->title = "Upload files";
			$this->output = View::render('file/index', $this);
			return View::render_layout('default', $this);
		}
		public function post($files = null, $callback = "file_did_upload"){
			if($files === null) return null;
			
			$names = $files["name"];
			$types = $files["type"];
			$tmp_names = $files["tmp_name"];
			$errors = $files["error"];
			$sizes = $files["size"];
			$ubounds = count($names);
			$this->result = array();
			for($i = 0; $i < $ubounds; $i++){
				$name = $names[$i];
				$type = $types[$i];
				$tmp_name = $tmp_names[$i];
				$error = $errors[$i];
				$size = $sizes[$i];
				$result = $this->save_file((object)array("name"=>$name, "type"=>$type, "tmp_name"=>$tmp_name, "error"=>$error, "size"=>$size));
				if($result->error === null){
					$thumbnail_folder = explode("/", str_replace(App::url_for(null), "", $result->file_path));
					array_pop($thumbnail_folder);
					$thumbnail_folder = implode("/", $thumbnail_folder) . "/thumbnails";
					if(!file_exists($thumbnail_folder)){
						mkdir($thumbnail_folder, 0777, true);
					}
					$thumbnail_file = PhotosResource::generate_thumbnail($result->file_path, $thumbnail_folder);
					$result->file_path = App::url_for($thumbnail_file);
					//{"name":"'.$file['name'].'","type":"'.$file['type'].'","size":"'.$file['size'].'"}'
					$this->result[] = (object)array("name"=>$name, "type"=>$type, "size"=>$size, "thumbnail_src"=>$result->file_path);
				}
			}
			/*["name"]=>
		  ["type"]=>
		  string(10) "text/plain"
		  ["tmp_name"]=>
		  string(26) "/private/var/tmp/php7ObsWD"
		  ["error"]=>
		  int(0)
		  ["size"]=>*/
			$view = "file/index";
			$this->output = View::render($view, $this);
			return View::render_layout("default", $this);
		}
		private function save_file($file){
			if(!in_array($file->type, self::$ALLOWED)) return false;
			$error = null;			
			if(is_uploaded_file($file->tmp_name)){
				$file_type = $this->get_file_type($file);
				$file_path = $this->create_and_get_file_path($file, $file_type);
				$did_move = move_uploaded_file($file->tmp_name, $file_path);
				if($did_move === false){
					$error = "Failed to move the file to " . $file_path . ". You should check the folder permissions, making sure it is writable. The error number returned is " . $file->error;
				}
			}
			return (object)array("file_path"=>App::url_for($file_path), "error"=>$error);
		}
		private function get_file_type($file){
			$file_type = explode("/", $file->type);
			$file_type = $file_type[1];
			$file_type = str_replace("jpeg", "jpg", $file_type);
			return $file_type;
		}
		private function get_upload_folder(){
			return sprintf("media/%s/%s", AuthController::$current_user->signin, date("Y"));
		}
		private function create_and_get_file_path($file, $file_type){
			$file_name = preg_replace("/\.*/", "", uniqid(null, true));
			$folder = $this->get_upload_folder();
			if(!file_exists($folder)){
				mkdir($folder, 0777, true);
			}
			$folder .= sprintf("/%s", date("n"));

			if(!file_exists($folder)){
				mkdir($folder, 0777, true);
			}
			$folder .= sprintf("/%s", date("j"));
			if(!file_exists($folder)){
				mkdir($folder, 0777, true);
			}
			$path = sprintf("%s/%s.%s", $folder, $file_name, $file_type);
			return $path;
		}
		private function get_thumbnail_width($path){
			$size = getimagesize($path);
			return $size[0] / 4;
		}
	}

?>