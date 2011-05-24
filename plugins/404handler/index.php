<?php
class NotfoundResource extends Resource{
	public function file_not_found($publisher, $info){
		$this->request = $info;
		$this->resource_name = $this->request->resource_name;
		$this->file_type = $this->request->file_type;		
		$this->output = View::render_absolute("plugins/404handler/views/notfound", $this);			
		return View::render_layout("default", $this);
	}
}
NotificationCenter::add(new NotfoundResource(), "file_not_found");
