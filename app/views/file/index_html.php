<link rel="stylesheet" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.10/themes/base/jquery-ui.css" />
<link rel="stylesheet" href="<?php echo App::url_for("plugins/jquery-file-upload/jquery.fileupload-ui.css");?>" />
<h1>Upload files</h1>
<?php
	
?>
<form id="file_upload" action="<?php echo AppResource::url_for_user("files.json");?>" method="post" enctype="multipart/form-data">
	<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo Settings::$max_filesize;?>" />
    <input type="file" name="files[]" multiple />
    <button>Upload</button>
    <div>Upload files</div>
</form>
<table id="files"></table>
<script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.10/jquery-ui.min.js"></script>
<script src="<?php echo App::url_for("plugins/jquery-file-upload/jquery.fileupload.js");?>"></script>
<script src="<?php echo App::url_for("plugins/jquery-file-upload/jquery.fileupload-ui.js");?>"></script>

<script>
$(function () {
    $('#file_upload').fileUploadUI({
        uploadTable: $('#files'),
        downloadTable: $('#files'),
        buildUploadRow: function (files, index) {
            return $('<tr><td>' + files[index].name + '<\/td>' +
                    '<td class="file_upload_progress"><div><\/div><\/td>' +
                    '<td class="file_upload_cancel">' +
                    '<button class="ui-state-default ui-corner-all" title="Cancel">' +
                    '<span class="ui-icon ui-icon-cancel">Cancel<\/span>' +
                    '<\/button><\/td><\/tr>');
        },
        buildDownloadRow: function (file) {
            return $('<tr><td><img src="' + file[0].thumbnail_src + '" />' + file[0].name + '<\/td><\/tr>');
        }
    });
});
</script>