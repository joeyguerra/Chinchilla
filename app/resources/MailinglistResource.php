<?php
class_exists("AppResource") || require("AppResource.php");
class_exists("Tag") || require("models/Tag.php");
class_exists("Contact") || require("models/Contact.php");
class MailinglistResource extends AppResource{
	public function __construct(){
		parent::__construct();
	}
	public function post($email){
		$message = $this->add_to_list($email);
		App::set_user_message($message);
		$view = "mailinglist/index";
		$this->title = "Mailing list";
		$this->output = View::render("mailinglist/index", $this);
		return View::render_layout("default", $this);
	}
	
	private function add_to_list($email){
		$email = trim($email);
		if(strlen($email) === 0) return "I need your email address";
		$matches = array();
		$did_match = preg_match("/^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,4}$/", $email, $matches);
		if(!$did_match) return "That email address doesn't appear to be of a valid format. Please review it and try again.";
		try{
			$query = new Query(new Contact(array("email"=>$email, "owner_id"=>AppResource::$member->id)));
			$sql = "select c.* from contacts c inner join tags t on t.object_id=c.ROWID and t.object_type='contact' where c.email=:email and t.name = 'mailing list' and c.owner_id=:owner_id limit 0, 1";
			
			$contact = $query->execute(Repo::get_provider(), new Contact(), $sql);
			if(count($contact) > 0) return "You're already in our mailing list";
			$contact = save_object::execute(new Contact(array("name"=>$email, "email"=>$email, "owner_id"=>(int)AppResource::$member->id)));
			$tag = new Tag(array("name"=>"mailing list", "object_id"=>$contact->id, "object_type"=>"contact", "owner_id"=>AppResource::$member->id));
			$tag = save_object::execute($tag);
		}catch(RepoException $e){
			var_dump($e);
		}
				
		return "Thank you {$matches[0]} for your interest.";
	}
}