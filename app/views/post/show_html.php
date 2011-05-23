<?php class_exists("PostResource") || require("resources/PostResource.php");?>
<?php if($post->id == 0):?>
	<h1>Not found</h1>
<?php endif;?>
<article>
	<header>
		<h1><?php echo $post->title;?></h1>
	</header>
	<?php echo PostResource::add_p_tags($post->body);?>
	<footer></footer>
</article>