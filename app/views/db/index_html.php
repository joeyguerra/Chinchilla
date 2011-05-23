<dl>
<?php foreach($tables as $table):?>
	<dd>
		<a href="<?php echo App::url_for("table/{$table->name}");?>"><?php echo $table->name;?></a>
		<form action="<?php echo App::url_for("table");?>" method="post" class="delete" onsubmit="return confirm('You sure you want to delete this table?');">
			<input type="hidden" name="_method" value="delete" />
			<input type="hidden" name="table_name" value="<?php echo $table->name;?>" />
			<button type="submit">Delete</button>
		</form>
		
	</dd>
<?php endforeach;?>
</dl>
<form action="<?php echo App::url_for("table");?>" method="get">
	<fieldset>
		<button type="submit">Add</button>
	</fieldset>
</form>
