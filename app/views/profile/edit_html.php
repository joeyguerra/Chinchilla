<style type="text/css">
	#files{display: none;}
</style>
<article>
	<h2>This is you</h2>
	<img id="profile_photo" src="<?php echo $owner->photo_url;?>" />
	<form id="file_upload" action="<?php echo AppResource::url_for_user("files.json");?>" method="post" enctype="multipart/form-data">
		<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo Settings::$max_filesize;?>" />
	    <input type="file" name="files[]" multiple />
	    <button>Upload</button>
	    <div>Pick a photo</div>
	</form>
	<table id="files"></table>

	<form action="<?php echo AppResource::url_for_member("profile");?>" method="post">
		<fieldset>
			<legend>Edit your profile</legend>
			<input type="hidden" name="owner[photo_url]" value="<?php echo $owner->photo_url;?>" id="photo_url" />
			<input type="hidden" name="owner[id]" value="<?php echo $owner->id;?>" id="owner[id]" />
			<p>
				<label for="owner[name]">Your parents call you</label>
				<input type="text" value="<?php echo $owner->name;?>" name="owner[name]" id="owner[name]" />
			</p>
			<p>
				<label for="owner[email]">Folks can email you at</label>
				<input type="email" value="<?php echo $owner->email;?>" name="owner[email]" id="owner[email]" />
			</p>
			<p>
				<label for="owner[signin]">You signin to the site as</label>
				<input type="text" value="<?php echo $owner->signin;?>" name="owner[signin]" id="owner[signin]" />
			</p>
			<p>
				<label for="owner[display_name]">You want people to know you as</label>
				<input type="text" name="owner[display_name]" id="owner[display_name]" value="<?php echo $owner->display_name;?>" />
			</p>
			<p>
				<label for="owner[in_directory]">Do you want to be publicly listed in the directory?</label>
				<input type="radio" value="true" name="owner[in_directory]" id="yes_in_directory"<?php echo $owner->in_directory ? " checked" : null;?> />
				<label for="yes_in_directory">Yes</label>
				<input type="radio" value="false" name="owner[in_directory]" id="no_in_directory"<?php echo !$owner->in_directory ? " checked" : null;?> />
				<label for="no_in_directory">No</label>
			</p>
		</fieldset>
		<input type="hidden" name="_method" value="put" />
		<button type="submit">Save</button>
	</form>
</article>