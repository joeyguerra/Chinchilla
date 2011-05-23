<?php
class_exists("AppResource") || require("AppResource.php");
class_exists("AuthController") || require("controllers/AuthController.php");
class_exists("Contact") || require("models/Contact.php");
class AddressbookResource extends AppResource{
	public function __construct(){
		parent::__construct();
		$this->contacts = array();
		if(!AuthController::is_authed()){
			$this->set_unauthed("Please signin to see your addressbook.");
		}
	}
	public $message;
	public $contacts;
	public $tags;
	public function get($tag = null){
		if(strlen($tag) > 50) $tag = null;
		if($tag !== null && $tag !== "addressbook"){
			$matches = array();
			preg_match_all("/\w+/", $tag, $matches);
			$tag = implode(" ", $matches[0]);
			$this->contacts = Contact::find_tagged($tag, self::$member->id);
		}else{
			$this->contacts = Contact::find_owned_by(self::$member->id);
		}
		class_exists("Tag") || require("models/Tag.php");
		if(!$this->contacts) $this->contacts = array();
		if(!is_array($this->contacts)) $this->contacts = array($this->contacts);
		$this->tags = Tag::find_for_contacts(AuthController::$current_user->id);
		if(!$this->tags) $this->tags = array();
		if(!is_array($this->tags)) $this->tags = array($this->tags);
		$this->title = "Your addressbook";
		$this->output = View::render('addressbook/index', $this);
		return View::render_layout('default', $this);
	}
}