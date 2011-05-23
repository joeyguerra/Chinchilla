<?php class_exists("PostResource") || require("resources/PostResource.php");?>
<?php foreach($posts as $post):?>
	<article>
		<header>
			<h2><?php echo $post->title;?></h2>
			<aside>
				<time><?php echo date("Y-m-d h:i:s", $post->post_date);?></time>
			</aside>
		</header>
		<?php echo PostResource::add_p_tags(Post::get_excerpt($post));?>
		<footer>
			<a href="<?php echo AppResource::url_for_user("post", array("id"=>$post->id));?>">edit</a>
			<?php if($post->id > 0):?>
			<form action="<?php echo AppResource::url_for_user("post");?>" method="post" onsubmit="return confirm('Are you sure you want to delete?');">
				<input type="hidden" value="<?php echo $post->id;?>" name="post[id]" />
				<input type="hidden" value="delete" name="_method" />
				<button type="submit">delete</button>
			</form>
			<?php endif;?>
		</footer>
	</article>
<?php endforeach;?>
