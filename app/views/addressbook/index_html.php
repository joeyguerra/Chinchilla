<ul>
	<?php foreach($tags as $tag):?>
		<li><a href="<?php echo AppResource::url_for_member("addressbook/{$tag->name}");?>"><?php echo $tag->name;?></a></li>
	<?php endforeach;?>
</ul>

<form action="<?php echo AppResource::url_for_user("messages");?>" method="post">
	<textarea name="message"></textarea>
	<?php foreach($contacts as $contact):?>
	<input type="hidden" name="contact_ids[]" value="<?php echo $contact->id;?>" />
	<?php endforeach;?>
	<button type="submit">Send</button>
</form>

<section id="contacts">
	<dl>
		<dt>Contacts</dt>
		<?php foreach($contacts as $contact):?>
			<dd>
				<a href="http://<?php echo $contact->url;?>" title="<?php echo $contact->name;?>"><?php echo $contact->name;?></a>				
				<a href="<?php echo AppResource::url_for_user("contact?id={$contact->id}");?>">edit</a>
				<a href="<?php echo AppResource::url_for_user("message", array("contact_ids[]"=>$contact->id));?>" title="Send <?php echo $contact->name;?> a message">new message</a>
			</dd>
		<?php endforeach;?>
	</dl>
	<form action="<?php echo AppResource::url_for_user("contact");?>" method="get">
		<button type="submit">Add a contact</button>
	</form>

</section>
