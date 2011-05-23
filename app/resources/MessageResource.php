<?php
class_exists("AppResource") || require("AppResource.php");
class_exists("AuthController") || require("controllers/AuthController.php");
class_exists("Contact") || require("models/Contact.php");
class MessageResource extends AppResource{
	public function __construct(){
		parent::__construct();
		$this->contacts = array();
		$this->messages = array();
		if(!AuthController::is_authed()){
			$this->set_unauthed("Please signin.");
		}
	}
	public $message;
	public $contacts;
	public $contact_ids;
	public function get($contact_ids = array()){
		$this->contact_ids = is_array($contact_ids) ? $contact_ids : null;
		$this->contacts = Contact::find_owned_by(AuthController::$current_user->id);
		$this->title = "Send a message";
		$this->output = View::render('message/index', $this);
		return View::render_layout('default', $this);
	}
}