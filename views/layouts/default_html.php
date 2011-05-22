<!DOCTYPE html>
<html>
	<head>
        <title>Chinchilla: <?php echo $title;?></title>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
		<meta name="author" content="Joey Guerra" />
		<meta name="keywords" content="restful framework, php, design patterns, oo principles" />
		<meta name="description" content="Chinchilla is a RESTful framework written in PHP." />		
		<link rel="icon" type="image/png" href="images/favicon.png" />	
		<link rel="stylesheet" type="text/css" href="<?php echo App::url_for_theme(null);?>css/default.css" />
		<?php echo $resource_css;?>
		<?php echo $resource_js;?>
		<script type="text/javascript" src="<?php echo App::url_for(null);?>js/default.js"></script>
    </head>
    <body>
		<header id="header">
			<h1 id="logo"><a href="<?php echo App::url_for(null);?>" title="Go to the Chinchilla home page"><span>Chinchilla</span></a></h1>
			<aside>a RESTful framework</aside>
			<nav>
			<?php if($this->name !== 'example'):?>
				<a href="<?php echo App::url_for('example.html');?>" title="example on how to use the Chinchilla framework to return HTML">example.html</a>
			<?php else:?>
				<span>example</span>
			<?php endif;?>
				<a href="<?php echo App::url_for('example.xml');?>" title="example on how to use the Chinchilla framework to return XML">example.xml</a>
				<a href="<?php echo App::url_for('example.json');?>" title="example on how to use the Chinchilla framework to return JSON">example.json</a>
				<a href="<?php echo App::url_for('example.json');?>" id="ajax_link" title="example on how to use the Chinchilla framework to return JSON for an AJAX request">ajax example.json</a>
			</nav>			
		</header>
		<section>
			<div id="user_message" style="display:none;"></div>
			<?php echo $output;?>
		</section>
		<footer id="footer">
			<small>Chinchilla</small>
			<p><?php echo round(memory_get_peak_usage() / 1024 / 1024, 2);?> megabytes of memory</p>
		</footer>
    </body>
	<script type="text/javascript">
	var user_message = chin.get_element('user_message');
		function on_return(request){
			var response = chin.to_json(request.responseText);
			user_message.innerHTML = decodeURIComponent(response.message.replace(/\+/gi, ' '));
		}
		function box_clicked(e){
			chin.hide(user_message);
			chin.stop_observing(user_messge, 'click', box_clicked);
		}
		function clicked(e){
			chin.stop(e);
			user_message.style.cursor = 'default';
			if(chin.is_hidden(user_message)){
				var request = new chin.ajax({method: 'get', DONE: [window, on_return]});
				request.send(e.target.href);
				chin.show(user_message);
				chin.observe(user_message, 'click', box_clicked);
			}else{
				chin.stop_observing(user_messge, 'click', box_clicked);
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