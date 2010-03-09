<?php
	class Log{
		public function __construct($path, $level = 5, $to_console=false, $prefix = null){
			$this->path = $path;
			$this->level = $level;
			$this->to_console = $to_console;
			$this->prefix = $prefix;
		}
		public $path;
		public $level;
		public $to_console;
		public $prefix;
		public function LogEventHasOccurred($info, $sender){
			$this->writeLine(get_class($sender) . '->' . $info);
		}
		public function writeLine($message){
			$this->write($message);
		}
		public function write($message){
			if($this->level > 0){
				if(!$this->to_console){
					$file_name = $this->getFileName();
					if(!file_exists($this->path)){
						throw new Exception("The path: '$this->path' , doesn't exist.");						
					}
					if(is_writable($this->path)){
						$handle = fopen($this->path . '/'. $file_name, "ab");
						
						fwrite($handle, $this->getTimestamp() . " - " . $this->prefix . $message . '
');
						fclose($handle);						
					}else{
						throw new Exception("Log file is not writable: '{$this->path}{$file_name}'. The current path is: {$_SERVER['SCRIPT_FILENAME']}.");
					}
				}else{
					error_log(sprintf('%s : %s', $this->prefix, $message));
				}
			}
		}
		private function getTimestamp(){
			return date("g:i:s A");
		}
		private function getFileName(){
			return date("Ymd") . ".txt";
		}
	}
?>