<?php
	class Log{
		public function __construct($path, $level = 5, $to_console=false, $prefix = null){
			$this->path = $path;
			$this->level = $level;
			$this->to_console = $to_console;
			$this->prefix = $prefix;
		}
		public $path;
		protected $file_path;
		public $level;
		public $to_console;
		public $prefix;
		public function LogEventHasOccurred($info, $sender){
			$this->writeLine(get_class($sender) . '->' . $info);
		}
		public function writeLine($message){
			$this->write($message);
		}
		public function delete(){
			unlink($this->file_path);
		}
		public function write($message){
			if($this->level > 0){
				if(!$this->to_console){
					$file_name = $this->getFileName();
					if(!file_exists($this->path)){
						mkdir($this->path, 0777);
						chmod($this->path, 0777);
					}
					if(is_writable($this->path)){
						$this->file_path = $this->path . '/'. $file_name;
						if(!file_exists($this->file_path)){
							$handle = fopen($this->file_path, "a+b");
							chmod($this->file_path, 0777);
							fclose($handle);
						}
						$handle = fopen($this->file_path, "a+b");
						fwrite($handle, $this->getTimestamp() . " - " . $this->prefix . $message . '
');
						fclose($handle);
					}else{
						throw new Exception("Log file is not writable: '{$this->file_path}'. The current path is: {$_SERVER['SCRIPT_FILENAME']}.");
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