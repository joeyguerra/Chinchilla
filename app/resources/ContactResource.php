<?php
class_exists("AppResource") || require("AppResource.php");
class_exists("AuthController") || require("controllers/AuthController.php");
class_exists("Contact") || require("models/Contact.php");
class ContactResource extends AppResource{
	public function __construct(){
		parent::__construct();
		if(!AuthController::is_authed()){
			$this->set_unauthed("Please signin.");
		}
	}
	public $contact;
	public function get(Contact $contact){
		$this->contact = Contact::find_by_id((int)$contact->id, AuthController::$current_user->id);
		$view = "contact/show";
		$this->legend = "Edit this contact";
		if($this->contact === null) $this->contact = new Contact(array("id"=>0, "name"=>"New contact"));
		$this->title = $this->contact->name;
		if(AuthController::is_authed()){
			$view = "contact/edit";
			$this->legend = $this->contact->id === 0 ? "Add a new contact" : "Edit this contact";			
		}else{
			$this->set_not_found();
		}		
		$this->output = View::render($view, $this);
		return View::render_layout("default", $this);
	}
	public function put(Contact $contact){
		$this->contact = Contact::find_by_id((int)$contact->id, AuthController::$current_user->id);
		if($this->contact !== null){
			$this->contact = new Contact(array("id"=>(int)$contact->id, "name"=>$contact->name, "owner_id"=>AuthController::$current_user->id, "email"=>$contact->email, "url"=>$contact->url));
			save_object::execute($this->contact);
			App::set_user_message("Contact was updated.");
		}else{
			App::set_user_message("Invalid credentials");
			$this->set_not_found("Contact not found.");
		}
		$this->set_redirect_to(AuthController::$current_user->signin . "/addressbook");
		$this->output = View::render("contact/show", $this);
		return View::render_layout("default", $this);
	}
	
}