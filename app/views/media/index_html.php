<?php if(AuthController::is_authed() && AuthController::$current_user->id == AppResource::$member->id):?>
<form enctype="multipart/form-data" target="upload_target" method="post" id="media_form" name="media_form" action="<?php echo AppResource::url_for_member('photos');?>">
	<fieldset>
		<legend>Photo Picker</legend>
		<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo Settings::$max_filesize;?>" />
		<section>
			<label for="photo" id="photo_label">Upload a photo</label>
			<input type="file" name="photo" id="photo_upload_field" />
		</section>
		<iframe src="<?php echo AppResource::url_for_member('empty');?>" id="upload_target" name="upload_target" style="width:0;height:0;border:none;"></iframe>
	</fieldset>
</form>
<?php endif;?>
<section id="all_photos">
<ul>
	<?php foreach($media as $post):?>
		<li>
			<a href="<?php echo AppResource::url_for_member('blog/' . $post->url);?>" title="<?php echo $post->title;?>">
				<img src="<?php echo urldecode($post->src);?>" alt="<?php echo $post->title;?>" />
			</a>
		<div class="photo_info">
			<a href="<?php echo AppResource::url_for_member('blog/' . $post->url);?>" rel="bookmark" title="<?php echo urldecode($post->title);?>"><?php echo urldecode($post->title);?></a>
		</div>
		</li>
	<?php endforeach;?>
</ul>
<div style="clear:both"></div>
</section>