
chin.view.post = function(){
	chin.view.apply(this, [arguments]);
	this.id = null;
	var self = this;
	this.edit_was_clicked = function(e){
		e.preventDefault();
		e.stopPropagation();
		var view_id = self.get_parent(e.target).id;
		chin.default_center.post('edit_was_clicked', e.target, view_id);
	};
	
	this.cancel_was_clicked = function(e){
		e.preventDefault();
		e.stopPropagation();
		var view_id = self.get_parent(e.target).id;
		chin.default_center.post('cancel_was_clicked', e.target, view_id);
	};
	
	this.info_was_clicked = function(e){
		e.preventDefault();
		e.stopPropagation();
		var view_id = self.get_parent(e.target).id;
		chin.default_center.post('info_was_clicked', e.target, view_id);
	};
	
	this.cancel_info_was_clicked = function(e){
		e.preventDefault();
		e.stopPropagation();
		var view_id = self.get_parent(e.target).id;
		chin.default_center.post('cancel_info_was_clicked', e.target, view_id);
	};
	this.save_info_was_clicked = function(e){
		e.preventDefault();
		e.stopPropagation();
		var parent = $(self.get_parent(e.target));
		var elem = parent.find('.info');
		var post_id = parent.attr('post_id');
		var type = elem.find('.type').val();
		var description = elem.find('.description').val();
		var tags = elem.find('.tags').val();
		var is_published = elem.find('.is_published').attr('checked');
		var post = {id: post_id, type: type, description: description, tags: tags, is_published: is_published};
		chin.default_center.post('save_info_was_clicked', e.target, post);
	};
	

	$('.edit').click(this.edit_was_clicked);
	$('.cancel').click(this.cancel_was_clicked);
	$('.info').click(this.info_was_clicked);
	
	return this;
};
chin.view.post.prototype.populate = function(elem, post){
	var parent = $(this.get_parent(elem));
	parent.find('.title a').html(decodeURIComponent(post.title));
	parent.find('.post_date').html(post.post_date);
	parent.find('.tags').html(post.tags);
	
};
chin.view.post.prototype.get_parent = function(elem){
	return $(elem).parents('.hentry')[0];
};
chin.view.post.prototype.toggle = function(button, id){
	var elem = $('#' + id + ' .body');
	if($(button).html() === 'edit'){
		this.switch_to_edit(button, id);
	}else{
		var textarea = $('#' + id + ' .body textarea');
		var titlefield = $('#' + id + ' .title input');
		chin.default_center.post('save_was_clicked', button, {id: elem.parent('[post_id!=null]').attr('post_id'), body: textarea.val(), title: titlefield.val()});
		this.switch_to_display(button, id);
	}
};
chin.view.post.prototype.stop_title_click = function(e){
	e.preventDefault();
};
chin.view.post.prototype.switch_to_edit = function(button, id){
	var elem = $('#' + id + ' .body');
	var textarea = elem.html('<textarea style="margin: 0;border: 0;width: 100%;height: ' + (elem.height()-7) + 'px;">' + elem.html() + '</textarea>');
	$('#' + id + ' .cancel').show();
	$('#' + id + ' .edit').html('save');
	
	var title = $('#' + id + ' .title a');
	title.click(this.stop_title_click);
	var titlefield = title.html('<input type="text" value="' + title.html() + ' " />');
};

chin.view.post.prototype.switch_to_display = function(button, id){
	var elem = $('#' + id + ' .body');
	var textarea = $('#' + id + ' .body textarea');
	var html = textarea.val();
	$('#' + id + ' .cancel').hide();
	$('#' + id + ' .edit').html('edit');
	textarea.remove();
	elem.html(html);
	
	var title = $('#' + id + ' .title a');
	title.unbind('click', this.stop_title_click);
	var textfield = $('#' + id + ' .title a input');
	var title_html = textfield.val();
	textfield.remove();
	title.html(title_html);
};
chin.view.post.prototype.toggle_info = function(button, id){
	var info_view = $('#info_' + id);
	//var parent = $(this.get_parent(button));
	if(info_view.length == 0){
		info_view = this.create_info_view(info_view, id);
		//parent.data('height_' + id, parent.height());
	}
	if(info_view.is(':visible')){
		info_view.slideUp(150);
		//parent.height(parent.data('height_' + id));
	}else{
		var key = '#' + id + ' .meta';
		var container = $(key);
		var post = {id: $('#' + id).attr('post_id'), description: container.find('.description').html()
			, type: container.find('.type').html(), is_home_page: container.find('.is_home_page').html().length > 0 ? true : false
			, is_published: container.find('.is_published').html() === 'public' ? true : false, post_date: container.find('.post_date').html()
			, tags: container.find('.tags').html()};
		this.populate_info_view(info_view, post);
		//parent.height(info_view.height());
		info_view.slideDown(150);
	}
};
chin.view.post.prototype.populate_info_view = function(info_view, post){
	var elem = null;
	for(p in post){
		elem = info_view.find('.' + p);
		if(elem.length == 0) continue;
		if(elem[0].type === 'checkbox') elem.attr('checked', post[p]);
		elem.val(post[p]);
	}
	this.get_parent(info_view).className = 'hentry ' + post.type;
};
chin.view.post.prototype.create_info_view = function(info_view, id){
	var key = '#' + id + ' .meta';
	var container = $(key);
	info_view = $('<div class="info"></div>').attr('id', 'info_' + id);
	container.after(info_view);
	info_view.css({
		position: 'absolute'
		, top: '2px'
		, left: '50%'
		, width: '380px'
		, marginLeft: '-190px'
		, marginTop: 0
		, backgroundColor: 'white'
		, zIndex: 1000
	});
	
	info_view.append('<fieldset><p><label for="' + info_view.attr('id') + '_type">Type</label><select class="type" id="' + info_view.attr('id') + '_post_type" name="type"><option value="post">Post</option><option value="photo">Photo</option><option value="link">Link</option><option value="video">Video</option><option value="status">Tweet</option></select></p><p><label for="">Excerpt</label><textarea class="description" name="description"></textarea></p><p><label for="">Tags</label><input type="text" class="tags" name="tags" /></p><p><label for="">Make public</label><input type="checkbox" class="is_published" name="is_published" /></p></fieldset><toolbar><button type="submit" class="save">Save</button><button type="cancel" class="cancel">Cancel</button></toolbar>');
	info_view.hide();
	info_view.find('.cancel').click(this.cancel_info_was_clicked);
	info_view.find('.save').click(this.save_info_was_clicked);
	return info_view;
};

chin.controller.post = function(view){
	chin.controller.apply(this, [arguments]);
	this.view = view;
	return this;
}
chin.controller.post.prototype.edit_was_clicked = function(publisher, info){
	this.view.toggle(publisher, info);
};
chin.controller.post.prototype.cancel_was_clicked = function(publisher, info){
	this.view.switch_to_display(publisher, info);
}
chin.controller.post.prototype.info_was_clicked = function(publisher, info){
	this.view.toggle_info(publisher, info);
}
chin.controller.post.prototype.cancel_info_was_clicked = function(publisher, info){
	this.view.toggle_info(publisher, info);
};
chin.controller.post.prototype.save_info_was_clicked = function(publisher, info){
	this.save_was_clicked(publisher, info);
	this.view.toggle_info(publisher, $(this.view.get_parent(publisher)).attr('id'));
};
chin.controller.post.prototype.save_was_clicked = function(publisher, info){
	var url = 'post.json';
	var self = this;
	var info_view = $(publisher).parents('.info');
	info._method = 'put';
	$.ajax({type: 'POST', url: url, data: info
		, error: function(request, type, e){
			console.log(e);
		}
		, success: function(data, status, request){
			if(info_view.length > 0){
				this.view.populate_info_view(info_view, data);
			}
			this.view.populate(publisher, data);
	}, dataType: 'json', context: this});
}
$(document).ready(function(){
	
	var view_controller = new chin.controller.post(new chin.view.post());
	chin.default_center.subscribe('edit_was_clicked', view_controller, null);
	chin.default_center.subscribe('cancel_was_clicked', view_controller, null);
	chin.default_center.subscribe('cancel_info_was_clicked', view_controller, null);
	chin.default_center.subscribe('save_info_was_clicked', view_controller, null);	
	chin.default_center.subscribe('save_was_clicked', view_controller, null);
	chin.default_center.subscribe('info_was_clicked', view_controller, null);	
});
