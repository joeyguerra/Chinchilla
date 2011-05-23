(function($){
	var root = $("script[src*=default]").first().attr("src").replace("js/default.js", "");
	$("<script />").attr({src:"http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.10/jquery-ui.min.js"}).appendTo("head");
	$("<script />").attr({src: root + "plugins/jquery-file-upload/jquery.fileupload.js"}).appendTo("head");
	$("<script />").attr({src: root + "plugins/jquery-file-upload/jquery.fileupload-ui.js"}).appendTo("head");
	$("<link />").attr({rel: "stylesheet", href:"http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.10/themes/base/jquery-ui.css"}).appendTo("head");
	$("<link />").attr({rel: "stylesheet", href:root + "plugins/jquery-file-upload/jquery.fileupload-ui.css"}).appendTo("head");
	
})(jQuery);

$(function () {
	chin.default_center.subscribe("setting_profile_photo", {
		setting_profile_photo: function(publisher, info){
			$("#photo_url").val(info[0].thumbnail_src);
			$("#profile_photo").attr("src", info[0].thumbnail_src);
		}
	}, null);
    $('#file_upload').fileUploadUI({
        uploadTable: $('#files')
		, downloadTable: $('#files')
		, buildUploadRow: function (files, index) {
            return $('<tr><td>' + files[index].name + '<\/td>' +
                    '<td class="file_upload_progress"><div><\/div><\/td>' +
                    '<td class="file_upload_cancel">' +
                    '<button class="ui-state-default ui-corner-all" title="Cancel">' +
                    '<span class="ui-icon ui-icon-cancel">Cancel<\/span>' +
                    '<\/button><\/td><\/tr>');
        }
		, buildDownloadRow: function (file) {
			chin.default_center.post("setting_profile_photo", this, file);
			return $('<tr><td><img src="' + file[0].thumbnail_src + '" />' + file[0].name + '<\/td><\/tr>');
        }
    });
});
