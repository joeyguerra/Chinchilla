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
    </head>
    <body>
		<header id="header">
			<h1 id="logo"><a href="<?php echo FrontController::urlFor(null);?>" title="Go to the Chinchilla home page"><span>Chinchilla</span></a></h1>
			<p id="slogan">a RESTful framework</p>
		</header>
		<section id="body">
			{$output}
		</section>
		<footer id="footer">
			<small>&copy; Joey Guerra</small>
		</footer>
    </body>
</html>