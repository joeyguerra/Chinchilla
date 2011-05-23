<?php
class Header_OpenId{
	public function __construct(){}
	public function __destruct(){}
	public function execute($output){
		$head = <<<eos
<link rel="openid2.provider" href="%s/">
</head>	
eos;
		$url = App::url_for(AppResource::$member->signin);
		$head = sprintf($head, App::url_for('openid.txt'));
		$output = str_replace('</head>', $head, $output);
		return $output;
	}
}