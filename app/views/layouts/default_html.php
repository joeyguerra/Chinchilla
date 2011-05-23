<?php class_exists("AuthController") || require("controllers/AuthController.php");?><!DOCTYPE html>
<!--
	 ____     __                          __          ___   ___                
	/\  _`\  /\ \      __                /\ \      __/\_ \ /\_ \               
	\ \ \/\_\\ \ \___ /\_\    ___     ___\ \ \___ /\_\//\ \\//\ \      __      
	 \ \ \/_/_\ \  _ `\/\ \ /' _ `\  /'___\ \  _ `\/\ \\ \ \ \ \ \   /'__`\    
	  \ \ \L\ \\ \ \ \ \ \ \/\ \/\ \/\ \__/\ \ \ \ \ \ \\_\ \_\_\ \_/\ \L\.\_  
	   \ \____/ \ \_\ \_\ \_\ \_\ \_\ \____\\ \_\ \_\ \_\\____\\____\ \__/.\_\ 
	    \/___/   \/_/\/_/\/_/\/_/\/_/\/____/ \/_/\/_/\/_//____//____/\/__/\/_/ 
		everything should be this easy!
-->
<html>
	<head>
        <title><?php echo $title;?></title>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
		<meta name="author" content="Joey Guerra" />
		<meta name="keywords" content="<?php echo $keywords;?>" />
		<meta name="description" content="<?php echo $description;?>" />		
		<link rel="icon" type="image/png" href="<?php echo App::url_for("favicon.png");?>" />	
		<link rel="stylesheet" type="text/css" href="<?php echo App::url_for_theme("css/default.css");?>" />
		<link href="http://fonts.googleapis.com/css?family=Cabin" rel="stylesheet" type="text/css" />
		<link href="http://fonts.googleapis.com/css?family=Lobster" rel="stylesheet" type="text/css" />
		<?php echo $css;?>
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.5.1/jquery.min.js"></script>
		<script type="text/javascript" src="<?php echo App::url_for("js/default.js");?>"></script>
		<?php echo $js;?>
    </head>
    <body class="<?php echo $resource_name;?>">
		<header id="header">
			<h1 id="logo"><a href="<?php echo AppResource::url_for_member(null);?>" title="Chinchilla: a RESTful framework"><span>Chinchilla</span></a></h1>
			<aside>a RESTful framework</aside>
			<nav>
				<a href="<?php echo App::url_for("example.html");?>" title="example on how to use the Chinchilla framework to return HTML">example.html</a>
				<a href="<?php echo App::url_for("example.xml");?>" title="example on how to use the Chinchilla framework to return XML">example.xml</a>
				<a href="<?php echo App::url_for("example.json");?>" title="example on how to use the Chinchilla framework to return JSON">example.json</a>
				<a href="<?php echo App::url_for("example.json");?>" class="ajax" title="example on how to use the Chinchilla framework to return JSON for an AJAX request">ajax example.json</a>
				<a href="<?php echo AppResource::url_for_user("blog");?>" id="blog" title="<?php echo AppResource::$member->is_owner ? "Chinchillalite blog" : AppResource::$member->name;?>'s blog">blog</a>
				<a href="<?php echo App::url_for("members");?>" title="Network directory">members</a>
			</nav>
		</header>
		<section>
			<div id="user_message"<?php echo (App::get_user_message() === null ?  ' style="display:none;"' : null);?>><?php echo App::get_user_message();?></div>
			<?php echo $output;?>
		</section>
		<footer id="footer">
<?php if(AuthController::is_authed()):?>
			<nav>
				<a href="<?php echo AppResource::url_for_user("post");?>">add a post</a>
				<a href="<?php echo AppResource::url_for_user("posts");?>">posts</a>
				<a href="<?php echo AppResource::url_for_user("addressbook");?>" title="Addressbook">addressbook</a>
				<a href="<?php echo AppResource::url_for_user("files");?>" title="Upload files">upload files</a>
				<a href="<?php echo AppResource::url_for_user("photos");?>" title="Photos">photos</a>
				<a href="<?php echo App::url_for("signout");?>">sign out</a> <?php echo AuthController::$current_user->name;?>
			</nav>
<?php endif;?>
			<p><small>Chinchilla: <?php echo round(memory_get_peak_usage(true) / 1024 / 1024, 2);?> megabytes of memory</small></p>
		</footer>
    </body>
	<script>
		chin.controller.example = function(){
			var self = this;
			chin.controller.apply(self, []);
			var example_container = $("<div />").text("close").attr("id", "example_container");
			var close_button = $("<a />").attr("href", "javascript:void(0);").text("close");
			var container = $("<div />").addClass("dark").css({display: "none", "-webkit-box-shadow": "0 0 7px rgba(0,0,0,.8)", position: "fixed", top: "50%", left: "50%", "margin-left":"-25%",padding: "10px", "border-radius":"10px", "margin-top":"-25%", width: "50%", height: "50%", background: "#000000", color: "#ffffff", overflow: "auto"}).append(close_button).append(example_container).appendTo("body");
			self.handle_ajax_link = function(e){
				e.preventDefault();
				e.stopPropagation();
				console.log([e.pageX, e.pageY]);
				$.get(e.target.href, function(data){
					example_container.html(decodeURIComponent(data.message).replace(/\+/gi, " "));
					container.show();
				});
			};
			self.close_was_clicked = function(e){
				container.hide();
			};
			close_button.click(self.close_was_clicked);
			return self;
		};

		$(function(){
			var example_controller = new chin.controller.example();
			$("a.ajax").click(example_controller.handle_ajax_link);
		});
	</script>
</html>