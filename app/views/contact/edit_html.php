<form id="contact" action="<?php echo AppResource::url_for_member((int)$contact->id > 0 ? "contact" : "contacts");?>" method="post">
	<fieldset>
		<legend><?php echo $legend;?></legend>
		<p>
			<label for="contact[name]">Name</label>
			<input type="text" name="contact[name]" id="contact[name]" value="<?php echo $contact->name;?>" autocomplete="off" />
		</p>

		<p>
			<label for="contact[email]">Email</label>
			<input type="text" name="contact[email]" id="contact[email]" value="<?php echo $contact->email;?>" autocomplete="off" />
		</p>
	
		<p>
			<label for="contact[url]">Website</label>
			<input type="text" name="contact[url]" id="contact[url]" value="<?php echo $contact->url;?>" autocomplete="off" />
		</p>

<?php if($contact->json !== null):?>
	<?php $fields = json_deserialize($contact->json);?>
<?php endif;?>

<?php if($contact->id > 0):?>
		<input type="hidden" value="<?php echo $contact->id;?>" name="contact[id]" />
		<input type="hidden" value="put" name="_method" />
<?php endif;?>
		<footer>
			<button type="submit"><?php echo $contact->id > 0 ? "Save" : "Add";?></button>
		</footer>
	</fieldset>
</form>
<form action="<?php echo App::url_for("contact");?>" method="post" onsubmit="return confirm('Are you sure you want to delete?');">
	<input type="hidden" value="<?php echo $contact->id;?>" name="contact[id]" />
	<input type="hidden" value="delete" name="_method" />
	<button type="submit">Delete</button>
</form>