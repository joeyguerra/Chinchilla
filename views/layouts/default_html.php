<!DOCTYPE html>
<html>
	<head>
        <title>Chinchilla: {$title}</title>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
		<meta name="author" content="Joey Guerra" />
		<meta name="keywords" content="restful framework, php, design patterns, oo principles" />
		<meta name="description" content="Chinchilla is a RESTful framework written in PHP." />		
		<link rel="icon" type="image/png" href="images/favicon.png" />	
		<link rel="stylesheet" href="<?php echo FrontController::urlFor('themes');?>css/boilerplate/screen.css" type="text/css" media="screen, projection" />
		<link rel="stylesheet" href="<?php echo FrontController::urlFor('themes');?>css/boilerplate/print.css" type="text/css" media="print" />
		<!--[if lt IE 8]>
		  <link rel="stylesheet" href="<?php echo FrontController::urlFor('themes');?>css/boilerplate/ie.css" type="text/css" media="screen, projection" />
		<![endif]-->
		<link rel="stylesheet" type="text/css" href="<?php echo FrontController::urlFor('themes');?>css/default.css" />
		{$resource_css}
		{$resource_js}
		<script type="text/javascript" src="<?php echo FrontController::urlFor('js');?>default.js"></script>
    </head>
    <body>
		<header id="header">
			<h1 id="logo"><a href="<?php echo FrontController::urlFor(null);?>" title="Go to the Chinchilla home page"><span>Chinchilla</span></a></h1>
			<p id="slogan">a RESTful framework</p>
			<nav>
				<a href="<?php echo FrontController::urlFor('example.html');?>" title="example on how to use the Chinchilla framework to return HTML">example.html</a>
				<a href="<?php echo FrontController::urlFor('example.xml');?>" title="example on how to use the Chinchilla framework to return XML">example.xml</a>
				<a href="<?php echo FrontController::urlFor('example.json');?>" title="example on how to use the Chinchilla framework to return JSON">example.json</a>
				<a href="<?php echo FrontController::urlFor('example.json');?>" id="ajax_link" title="example on how to use the Chinchilla framework to return JSON for an AJAX request">ajax example.json</a>
			</nav>			
		</header>
		<section id="body">
			<div id="user_message" style="display:none;"></div>
			{$output}
		</section>
		<footer id="footer">
			<small>Chinchilla</small>
		</footer>
    </body>
	<script type="text/javascript">
	var user_message = chin.get_element('user_message');
		function on_return(request){
			var response = chin.to_json(request.responseText);
			user_message.innerHTML = decodeURIComponent(response.message.replace(/\+/gi, ' '));
		}
		
		function clicked(e){
			chin.stop(e);
			if(chin.is_hidden(user_message)){
				var request = new chin.ajax({method: 'get', DONE: [window, on_return]});
				request.send(e.target.href);
				chin.show(user_message);
			}else{
				chin.hide(user_message);
			}
		}
		
		chin.main = function(){
			var link = chin.get_element('ajax_link');
			var user_message = chin.get_element('user_message');
			chin.observe(link, 'click', clicked);
		}

		chin.observe(window, 'load', chin.main);
		
	</script>
</html>