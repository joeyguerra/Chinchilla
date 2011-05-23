<?php
class_exists("AppResource") || require("AppResource.php");
class_exists("AuthController") || require("controllers/AuthController.php");
class_exists("Post") || require("models/Post.php");
class PhotosResource extends AppResource{
	public function __construct(){
		parent::__construct();
		if(!AuthController::is_authed()){
			$this->set_unauthed("Please signin.");
		}
	}
	public $media;
	public function get(){
		$this->title = "Your photos library";
		$this->media = Post::find_public_attachments_owned_by(AuthController::$current_user->id, 0, 5);
		if($this->media === null) $this->media = array();
		$this->output = View::render('media/index', $this);
		return View::render_layout('default', $this);
	}
	
	private static function make_thumbnail($file, $to_width = 150){
		$extension = pathinfo($file, PATHINFO_EXTENSION);
		$image = null;
		$fn_name = null;
		if(in_array($extension, array("jpg", "jpeg", "JPG"))){
			$image = imagecreatefromjpeg($file);
			$fn_name = "imagejpeg";
		}else if($extension == "png"){
			$image = imagecreatefrompng($file);
			$fn_name = "imagepng";
		}else if($extension == "gif"){
			$image = imagecreatefromgif($file);
			$fn_name = "imagegif";
		}else{
			error_log("tried to upload $file and it's not supported");
			throw new Exception("File type isn't supported. Only jpg, png and gif.");
		}
		$width = imagesx($image);
		$height = imagesy($image);
		$aspect_ratio = $width / $height;
		$to_height = $to_width / $aspect_ratio;
		$thumbnail = imagecreatetruecolor($to_width, $to_height);
		imagecopyresampled($thumbnail, $image, 0, 0, 0, 0, $to_width, $to_height, $width, $height);
		return (object)array("thumbnail"=>$thumbnail, "type"=>$extension, "create_fn"=>$fn_name);
	}
	public static function generate_thumbnail($file_path, $thumbnail_folder){
		$thumbnail = self::make_thumbnail($file_path);
		$info = pathinfo($file_path);
		$file_path = $thumbnail_folder . "/" . $info["filename"] . "." . $info["extension"];
		call_user_func_array($thumbnail->create_fn, array($thumbnail->thumbnail, $file_path));
		return $file_path;
	}
}