<h2>Are you trying to use OpenID for <?php echo str_replace("http://", '', $request->realm);?>? If so, sign in below and you'll be on your way.</h2>
<form action="<?php echo App::url_for('openid');?>" method="post" id="openid_login_form">
	<fieldset>
		<legend>Login</legend>
		<p>I need the <label for="email">email address</label><input type="text" value="" id="email" name="email" /> that you used for this site and your <label for="password">password</label><input type="password" value="" id="password" name="password" /> in order to <input type="submit" value="verify" /> your account.
		</p>
	</fieldset>
	<input name="openid.mode" value="{$request->mode}" type="hidden" />
	<input name="openid.return_to" value="{$request->return_to}" type="hidden" />
	<input name="openid.identity" value="{$request->identity}" type="hidden" />
	<input name="openid.assoc_handle" type="hidden" value="{$request->assoc_handle}" />
	<input type="hidden" name="openid.realm" value="{$request->realm}" />
</form>
