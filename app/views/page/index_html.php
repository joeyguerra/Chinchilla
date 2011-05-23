<?php class_exists("AuthController") || require("controllers/AuthController.php");?>
<?php echo $page->body;?>
<?php if(AppResource::owns_content()):?>
<form action="<?php echo AppResource::url_for_user("page");?>" method="post">
	<input type="hidden" value="edit" name="state" />
	<input type="hidden" value="<?php echo $page->name;?>" name="name" />
	<button type="submit">Edit</button>
</form>
<?php endif;?>
