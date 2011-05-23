<ul>
<?php foreach($contacts as $key=>$contact):?>
	<li>
		<a href="">
			<span>
				<?php echo $contact->name;?>
			</span>
		</a>
	</li>
<?php endforeach;?>
</ul>
<ul>
<?php foreach($messages as $key=>$message):?>
	<li>
		<?php echo $message->message;?>
		<small>from <?php echo $message->sender;?></small>
	</li>
<?php endforeach;?>
</ul>