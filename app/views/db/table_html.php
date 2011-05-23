
<section id="table">
	<form action="<?php echo App::url_for("table");?>" method="post">
		<fieldset>
			<legend><?php echo $table_name === null ? "Add a table" : $table_name;?><button class="add">Add a column</button></legend>
<?php foreach($columns as $key=>$column):?>
			<dl>
				<dd>
					<label for="columns_<?php echo $column->name;?>">Name</label>
					<input type="text" name="columns[<?php echo $key;?>][name]" id="columns_name_<?php echo $key;?>" value="<?php echo $column->name;?>" />
				</dd>
				<dd>
					<label for="columns_type_<?php echo $key;?>">Data type</label>
					<input type="text" name="columns[<?php echo $key;?>][type]" id="columns_type_<?php echo $key;?>" value="<?php echo $column->type;?>" />
				</dd>
				<dd>
					<label for="columns_notnull_<?php echo $key;?>">Not NULL</label>
					<input type="checkbox" name="columns[<?php echo $key;?>][notnull]" id="columns_notnull_<?php echo $key;?>" value="true"<?php echo $column->notnull ? null : " checked";?> />
				</dd>
				<dd>
					<label for="columns_dflt_value_<?php echo $key;?>">Default Value</label>
					<input type="text" name="columns[<?php echo $key;?>][dflt_value]" id="columns_dflt_value_<?php echo $key;?>" value="<?php echo $column->dflt_value;?>" />
				</dd>
			</dl>
<?php endforeach;?>
<?php if($table_name !== null):?>
			<input type="hidden" name="_method" value="put" />
<?php endif;?>
			<input type="hidden" name="table_name" value="<?php echo $table_name;?>" />
			<button type="submit">Save</button>
		</fieldset>
	</form>
<?php if($table_name !== null):?>
	<form action="<?php echo App::url_for("table");?>" method="post" onsubmit="return confirm('You sure you want to delete this table?');">
		<input type="hidden" name="_method" value="delete" />
		<input type="hidden" name="table_name" value="<?php echo $table_name;?>" />
		<button type="submit">Delete</button>
	</form>
<?php endif;?>
<?php
	if($results !== null){
		$class = new ReflectionClass($results[0]);
		$properties = $class->getProperties();
		$fields = array();
		foreach($properties as $property){
			if($property->isPublic()) $fields[] = $property;
		}
	}
?>

<?php if($results !== null):?>
	<table>
		<tr>
		<?php foreach($fields as $field):?>
			<th><?php echo $field->getName();?></th>
		<?php endforeach;?>
			<th>delete</th>
		</tr>
		<?php foreach($results as $obj):?>
		<tr>
			<?php foreach($fields as $field):?>
			<td><?php echo $obj->{$field->getName()};?></td>	
			<?php endforeach;?>
			<td>
			<form action="<?php echo App::url_for("table/$table_name");?>" method="post">
				<input type="hidden" name="_method" value="delete" />
				<input type="hidden" name="id" value="<?php echo $obj->id;?>" />
				<button type="submit">del</button>
			</form>
			</td>
		</tr>
		<?php endforeach;?>
	</table>
<?php endif;?>
</section>