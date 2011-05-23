<aside>
	<ul>
	<?php foreach($contacts as $contact):?>
		<li>
			<a href="http://<?php echo $contact->url;?>" data-id="<?php echo $contact->id;?>" title="<?php echo $contact->name;?>">
				<?php echo $contact->name;?>
			</a>
		</li>
	<?php endforeach;?>
	</ul>
</aside>
	
<article>
	<form action="<?php echo AppResource::url_for_user("messages");?>" method="post">
		<textarea name="message"></textarea>
		<?php foreach($contact_ids as $id):?>
		<input type="hidden" name="contact_ids[]" value="<?php echo $id;?>" />
		<?php endforeach;?>
		<button type="submit">Send</button>
	</form>
</article>
<dl>
<?php foreach($messages as $message):?>
	<dd>
		from: <?php echo $message->email;?><br />
		<?php echo $message->body;?>
	</dd>
<?php endforeach;?>
</dl>