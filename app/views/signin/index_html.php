<form action="<?php echo App::url_for("signin");?>" method="post">
	<fieldset>
		<legend>Sign in</legend>
		<p>
			<label for="signin">Signin Name</label>
			<input type="text" name="signin" id="signin" />
		</p>
		<p>
			<label for="password">Password</label>
			<input type="password" name="password" id="password" />
		</p>
		<toolbar>
			<button type="submit">Sign in</button>
		</toolbar>
	</fieldset>
</form>