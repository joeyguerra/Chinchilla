<ul>
<?php foreach($settings as $setting):?>
	<li>
		<p><?php echo "$setting->key = $setting->value";?></p>
		<form method="post" action="<?php echo AppResource::url_for_user("setting", array("key"=>$setting->key));?>">
			<input type="hidden" value="edit" name="state" />
			<button type="submit">edit</button>
		</form>
	</li>
<?php endforeach;?>
</ul>