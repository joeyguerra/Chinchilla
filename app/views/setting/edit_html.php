<form action="<?php echo AppResource::url_for_user((int)$setting->id > 0 ? "setting" : "settings");?>" method="post">
	<fieldset>
		<legend><?php echo $legend;?></legend>
		<p>
			<input type="text" name="setting[key]" id="setting[key]" value="<?php echo $setting->key;?>" />
		</p>
		<p>
			<label for="setting[value]">Value</label>
			<input type="text" value="<?php echo $setting->value;?>" name="setting[value]" />
		</p>
<?php if($setting->id > 0):?>
		<input type="hidden" value="<?php echo $setting->id;?>" name="setting[id]" />
		<input type="hidden" value="put" name="_method" />
<?php endif;?>
		<footer>
			<button type="submit"><?php echo $setting->id > 0 ? "Save" : "Add";?></button>
		</footer>
	</fieldset>
</form>
<?php if($setting->id > 0):?>
<form action="<?php echo AppResource::url_for_user("setting");?>" method="post" onsubmit="return confirm('Are you sure you want to delete?');">
	<input type="hidden" value="<?php echo $settin->id;?>" name="settin[id]" />
	<input type="hidden" value="delete" name="_method" />
	<button type="submit">delete</button>
</form>
<?php endif;?>