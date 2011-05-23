<?php
class_exists("AppResource") || require("AppResource.php");
class_exists("AuthController") || require("controllers/AuthController.php");
class_exists("Contact") || require("models/Contact.php");
class_exists("Message") || require("models/Message.php");
class_exists("Outbox") || require("models/Outbox.php");
class MessagesResource extends AppResource{
	public function __construct(){
		parent::__construct();
		$this->contacts = array();
		$this->contact_ids = array();
		if(!AuthController::is_authed()){
			$this->set_unauthed("Please signin.");
		}
		$this->errors = array();
	}
	public $message;
	public $contacts;
	public $contact_ids;
	public $messages;
	public $errors;
	public function get(){
		$this->contacts = Contact::find_owned_by(AuthController::$current_user->id);
		if($this->contacts === null) $this->contacts = array();
		$this->messages = Message::find_owned_by(AuthController::$current_user->id);
		if($this->messages === null) $this->messages = array();
		$this->title = "Messages";
		$this->output = View::render('message/index', $this);
		return View::render_layout('default', $this);
	}
	public function post($message, $contact_ids = null){
		$contact_ids = is_array($contact_ids) ? $contact_ids : null;
		if($contact_ids !== null){
			for($i=0; $i<count($contact_ids)-1; $i++){
				$contact_ids[$i] = (int)$contact_ids[$i];
			}
			$this->contacts = Contact::find_by_ids($contact_ids, AuthController::$current_user->id);
			if($this->contacts === null) $this->contacts = array();
			foreach($this->contacts as $contact){
				$outbox = new Outbox(array("message"=>$message, "owner_id"=>(int)AuthController::$current_user->id, "sent"=>time(), "recipient"=>$contact->url));
				save_object::execute($outbox);
				$message_strategy = $this->send_message($message, null, $contact, AuthController::$current_user);
				if($message_strategy->error !== null) $this->errors[] = "Failed to send to {$contact->name}. {$message_strategy->error}";
			}
			if(count($this->errors) > 0){
				App::set_user_message(View::render("error/list", $this));
			}else{
				if(count($this->contacts) > 1){
					App::set_user_message("Messages were sent");
				}else{
					App::set_user_message("Message was sent");
				}
			}
			$this->set_redirect_to(AuthController::$current_user->signin . "/message");
			$this->output = View::render("message/index", $this);
			return View::render_layout("default", $this);
		}
	}
	
	private function send_message($message, $subject, $contact, $from){
		$message_strategy = new MessageStrategy($message, $subject, $contact, $from);
		$did_send = $message_strategy->send();
		return $message_strategy;
	}
}
