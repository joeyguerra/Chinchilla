<section id="table">
	<form action="<?php echo App::url_for("tables");?>" method="post">
		<fieldset>
			<legend>Add a new table</legend>
			<p>
				<label for="sql">Query</label>
				<textarea name="sql" id="sql"></textarea>
			</p>
			<button type="submit">Execute</button>
		</fieldset>
	</form>
</section>