<form name="check_authentication" action="<?php echo App::url_for('openid');?>" method="post">
	<fieldset>
		<legend>OpenID Authentication Request</legend>
		<?php echo $request->realm;?> has requested authentication of your identity. Do you wish to confirm and go back to <?php echo $request->realm;?>?
		<button type="submit" name="yes"><span>Yes</span></button>
		<button type="submit" name="no"><span>No</span></button>
	</fieldset>
</form>