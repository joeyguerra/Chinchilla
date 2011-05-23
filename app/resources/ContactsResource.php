<?php
class_exists("AppResource") || require("AppResource.php");
class_exists("Contact") || require("models/Contact.php");
class_exists("AuthController") || require("controllers/AuthController.php");
class ContactsResource extends AppResource{
	public function __construct(){
		parent::__construct();
		if(!AuthController::is_authed()){
			$this->set_unauthed("Please signin.");
		}
	}
	public $contacts;
	public $contact;
	public $legend;
	
	public function get(){		
		$this->contacts = Contact::find_owned_by(AuthController::$current_user->id);
		if(!is_array($this->contacts)) $this->contacts = array($this->contacts);
		$view = "contact/index";
		$this->output = View::render($view, $this);
		return View::render_layout('default', $this);
	}
	
	public function post(Contact $contact){
		$this->contact = new Contact(array("name"=>$contact->name, "owner_id"=>AuthController::$current_user->id
			, "email"=>$contact->email, "url"=>$contact->url));
		save_object::execute($this->contact);
		$this->set_redirect_to(AuthController::$current_user->signin . "/addressbook");
		$this->output = View::render("contact/show", $this);
		return View::render_layout("default", $this);
	}
}