<form action="<?php echo AppResource::url_for_member((int)$post->id > 0 ? "post" : "posts");?>" method="post">
	<fieldset>
		<legend><?php echo $legend;?></legend>
		<p>
			<input type="text" name="post[title]" id="post[title]" value="<?php echo $post->title;?>" />
		</p>
		<article>
			<textarea name="post[body]"><?php echo $post->body;?></textarea>
		</article>
		<p>
			<label for="post[status]">Status</label>
			<input type="text" value="<?php echo $post->status;?>" name="post[status]" id="status" />
		</p>
<?php if($post->id > 0):?>
		<input type="hidden" value="<?php echo $post->id;?>" name="post[id]" />
		<input type="hidden" value="put" name="_method" />
<?php endif;?>
		<footer>
			<button type="submit"><?php echo $post->id > 0 ? "Save" : "Add";?></button>
		</footer>
	</fieldset>
</form>
<form action="<?php echo AppResource::url_for_member("post");?>" method="post" onsubmit="return confirm('Are you sure you want to delete?');">
	<input type="hidden" value="<?php echo $post->id;?>" name="post[id]" />
	<input type="hidden" value="delete" name="_method" />
	<button type="submit">delete</button>
</form>