<?php
	class Hashtable{
		public function __construct(){
			$this->_list = array();
		}
		public function __destruct(){}
		
		private $_list;
		public function getList(){
			return $this->_list;
		}
		public function offsetExists($key){
			return array_key_exists($key, $this->_list);
		}
		public function offsetGet($key){
			if($this->offsetExists($key)){
				return $this->_list[$key];
			}else{
				return null;
			}
		}
		public function offsetSet($key, $val){
			$this->_list[$key] = $val;
		}
		public function length(){
			return count($this->_list);
		}
	}
?>