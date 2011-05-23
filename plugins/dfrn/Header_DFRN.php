<?php
class Header_DFRN{
	public function __construct(){}
	public function __destruct(){}
	public function execute($output){
		$head = <<<eos
<meta name="dfrn-template" content="%s" />
<link rel="alternate" type="application/atom+xml" href="%s/dfrn_poll" />
<link rel="dfrn-request" href="%s/dfrn_request" />
<link rel="dfrn-confirm" href="%s/dfrn_confirm" />
<link rel="dfrn-notify" href="%s/dfrn_notify" />
<link rel="dfrn-poll" href="%s/dfrn_poll" />
</head>	
eos;
		$url = App::url_for(AppResource::$member->signin);
		$head = sprintf($head, App::url_for(null) . '%s/profile', $url, $url, $url, $url, $url);
		$output = str_replace('</head>', $head, $output);
		return $output;
	}
}