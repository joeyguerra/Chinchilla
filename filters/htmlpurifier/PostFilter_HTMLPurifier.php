<?php
interface_exists('IPostFilter') || require('lib/filters/IPostFilter.php');
class_exists('HTMLPurifier') || require('HTMLPurifier.standalone.php');
class PostFilter_HTMLPurifier implements IPostFilter{
	
	public function __construct(){}
	public function __destruct(){}
	
	public function execute($text){
		$purifier = new HTMLPurifier();
	  	return $purifier->purify($text);
	}
}