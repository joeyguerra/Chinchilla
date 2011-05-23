<h1><?php echo AppResource::$member->name;?></h1>
<?php if(AppResource::$member->photo_url !== null):?>
<img src="<?php echo AppResource::$member->photo_url;?>" />
<?php endif;?>


<?php if(AuthController::$current_user !== null && $owner->id === AuthController::$current_user->id):?>
<form action="<?php AppResource::url_for_member("profile");?>" method="post">
	<input type="hidden" value="edit" name="state" />
	<button type="submit">Edit your profile info</button>
</form>
<?php endif;?>
<article>
<?php echo $page !== null ? $page->body : null;?>
</article>
<?php if(AppResource::owns_content()):?>
<form action="<?php echo AppResource::url_for_user("page");?>" method="post">
	<input type="hidden" value="edit" name="state" />
	<input type="hidden" value="profile" name="name" />
	<button type="submit">Edit your profile page</button>
</form>
<?php endif;?>
