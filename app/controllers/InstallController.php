<?php

class InstallController{
	public function query_failed($publisher, $info){
		$error = $info->db->errorInfo();
		$table_name = preg_replace("/no such table: /", "", $error[2]);
		if($table_name === $error[2]) return;
		$this->create_table($table_name, $info);
		return $info->db->prepare($info->query);
	}
	private function create_table($table_name, $info){		
		if($table_name === "contacts"){
			$query = "create table if not exists contacts (name varchar(255), owner_id int, url varchar(255), email varchar(255))";
			$index_query = "create unique index if not exists indx_contacts on contacts (name asc, owner_id asc)";
			$url_index_query = "create index if not exists indx_contacts_url on contacts (url asc)";
			$email_index_query = "create index if not exists indx_contacts_email on contacts (email asc)";
			$result = $info->db->query($query);
			$result = $info->db->query($index_query);
			$result = $info->db->query($url_index_query);
			$result = $info->db->query($email_index_query);
		}
		
	}
}