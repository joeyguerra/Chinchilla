<form action="<?php echo AppResource::url_for_user((int)$post->id > 0 ? "page" : "pages");?>" method="post">
	<fieldset>
		<legend><?php echo $legend;?></legend>
		<p>
			<label for="post[title]">Title</label>
			<input type="text" name="post[title]" id="post[title]" value="<?php echo $post->title;?>" />
		</p>
		<p>
			<label for="post[name]">Page Name</label>
			<input type="text" name="post[name]" id="post[name]" value="<?php echo $post->name;?>" />
		</p>
		<article>
			<textarea name="post[body]"><?php echo $post->body;?></textarea>
		</article>
		<p>
			<label for="post[status]">Status</label>
			<select id="post[status]" name="post[status]">
			<?php foreach(array("Public"=>"public", "Pending"=>"pending", "Draft"=>"draft") as $key=>$value):?>
				<option value="<?php echo $value;?>"<?php echo ($post->status === $value ? " selected" : null);?>><?php echo $key;?></option>
			<?php endforeach;?>
			</select>
		</p>
		<p>
			<label for="post[post_date]">Post Date</label>
			<input type="text" value="<?php echo date("Y-m-d g:i:s a", $post->post_date);?>" name="post[post_date]" />
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
<?php if($post->id > 0):?>
<form action="<?php echo AppResource::url_for_user("page");?>" method="post" onsubmit="return confirm('Are you sure you want to delete?');">
	<input type="hidden" value="<?php echo $post->id;?>" name="post[id]" />
	<input type="hidden" value="delete" name="_method" />
	<button type="submit">delete</button>
</form>
<?php endif;?>