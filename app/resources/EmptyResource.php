<?php
class_exists("AppResource") || require("AppResource.php");
class EmptyResource extends AppResource{
	public function get(){
		return "";
	}
}