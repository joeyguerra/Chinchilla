
UIView.PostMenu = function(id){
	this.onClick = function(e){
		if(e.target.id === 'address'){
			SDDom.stop(e);
			this.open(e.target, {name: 'address_view'});
		}else if(e.target.id === 'photos_link'){
			
		}else{
			SDDom.stop(e);
		}
	};
	UIView.apply(this, arguments);
};
UIView.TextArea = function(id, options){
	UIView.apply(this, arguments);
	this.onResize = function(e){
		//this.resize(SDDom.getHeight(window) - SDDom.getPosition(this.container).y - 80);
	};
	
	this.resize = function(height){
		SDDom.setStyles({height: height + 'px'}, this.container);
		SDDom.setStyles({height: (height - 30 ) + 'px'}, SDDom.byTag('iframe', this.container));
	};
	this.addImage = function(img){
		this.container.value += img.src + '\n';
	};
	this.removeImage = function(img){
		this.container.value = this.container.value.replace(img.src + '\n', '');
	};
	this.keypress = function(e){
		if((e.metaKey || e.ctrlKey) && String.fromCharCode(e.charCode || e.keyCode).toLowerCase() === 's'){
			SDDom.stop(e);
			if(this.delegate && this.delegate.doSave){
				this.delegate.doSave.apply(this.delegate, [this, e]);
			}
		}
	};
	this.has = function(text){
		return (this.container.value.search(text) !== -1);
	};
	this.eventResize = this.bind(this.onResize);
	SDDom.addEventListener(window, 'resize', this.eventResize);
	SDDom.addEventListener(this.container, 'keypress', this.bind(this.keypress));
};

UIView.Modal.AddressBook = function(id, options){
	UIView.Modal.apply(this, [id, options]);
	this.selectedGroup = null;
	this.selectedPerson = null;
	this.people = [];
	this.groups = [];
	this.didClickHandle = function(e){
		SDDom.setStyles({"width":"400px","height":"300px", "background":"#fff", "marginLeft":"-200px"}, this.container);	
	};
	this.clearPeople = function(){
		SDArray.each(SDDom.findAll('li', this.peopleContainer()), function(li){
			SDDom.remove(li);
		});
	};
	this.addPeople = function(people, selectedPeople){
		var peopleContainer = SDDom.findFirst('ul', this.peopleContainer());
		var first = SDDom.findFirst('input', this.groupContainer());
		var i = 0;
		var person = null;
		for(i = 0; i < people.length; i++){
			person = people[i];
			var li = SDDom.create('li');
			li.setAttribute('rel', person.id);
			li.setAttribute('class', person.is_owner ? 'owner' : '');
			var checkbox = SDDom.create('input');
			var a = SDDom.create('a');
			a.setAttribute('href', 'javascript:void(0);');
			checkbox.type = 'checkbox';
			checkbox.id = 'person_checkbox_' + person.id;
			checkbox.name = 'people';
			checkbox.value = encodeURIComponent(JSON.stringify(person));
			var span = SDDom.create('span');
			span.innerHTML = decodeURIComponent(person.name);
			span.setAttribute('rel', JSON.stringify(person));			
			if(first && first.checked){
				checkbox.checked = false;
				checkbox.disabled = true;
			}else{
				if(SDArray.collect(selectedPeople, function(p){return p.id === person.id;}).length > 0){
					checkbox.checked = true;
				}
			}
			SDDom.append(a, span);
			SDDom.append(li, checkbox);
			SDDom.append(li, a);
			SDDom.append(peopleContainer, li);
		}
	};
	this.groupContainer = function(){
		return SDDom('groups');
	};
	this.peopleContainer = function(){
		return SDDom('people');
	};
	this.disableAllCheckboxes = function(first){
		SDArray.each(SDDom.findAll('input', this.container), function(checkbox){
			if(checkbox !== first){
				checkbox.checked = false;
				checkbox.disabled = true;
			}
		});
	}
	
	this.enableAllCheckboxes = function(first){
		SDArray.each(SDDom.findAll('input', this.container), function(checkbox){
			if(checkbox !== first){
				checkbox.disabled = false;
			}
		});
	}
	
	this.didClickView = function(e){
		if(e.target && e.target.getAttribute){
			var section = SDDom.getParent('section', e.target);
			var nodeName = e.target.nodeName.toLowerCase();
			var target = (nodeName !== 'input' && nodeName !== 'li' ? SDDom.getParent('li', e.target) : e.target);
			var rel = (nodeName === 'input' ? target.value : target.getAttribute('rel'));
			if(nodeName === 'input'){
				if(target === SDDom.findFirst('input', this.div)){
					if(this.delegate.allContactsWasSelected){
						if(target.checked){
							this.delegate.allContactsWasSelected.apply(this.delegate, [target]);
						}else{
							this.delegate.allContactsWasDeselected.apply(this.delegate, [target]);
						}
					}
				}				
			}
			if(section === SDDom.findFirst('section', this.div)){
				if(nodeName !== 'input' && this.delegate && this.delegate.didClickGroups){
					this.delegate.didClickGroups.apply(this.delegate, [e, rel]);
				}

				if(nodeName === 'input' && this.delegate && this.delegate.aGroupWasChecked){
					this.delegate.aGroupWasChecked.apply(this.delegate, [target]);
				}

				if(this.selectedGroup){
					SDDom.removeClass('selected', this.selectedGroup);
				}
				if(target !== null){
					this.selectedGroup = SDDom.getParent('li', target);
				}
				if(this.selectedGroup){
					SDDom.addClass('selected', this.selectedGroup);
				}
			}else if(section === SDDom.findAll('section', this.div)[1]){
				if(this.delegate && this.delegate.didClicPeople){
					this.delegate.didClicPeople.apply(this.delegate, [e, rel]);
				}
				if(this.delegate && this.delegate.aPersonWasChecked){
					this.delegate.aPersonWasChecked.apply(this.delegate, [e, target]);
				}

				if(this.selectedPerson){
					SDDom.removeClass('selected', this.selectedPerson);
				}
				if(this.selectedPerson){
					this.selectedPerson = SDDom.getParent('li', target);
				}
			}
		}		
	};
	
};

UIController.AddressBook = function(view){
	UIController.apply(this, arguments);
	this.view = view;
	this.view.delegate = this;
	
	this.allContactsWasDeselected = function(elem){
		this.view.enableAllCheckboxes(elem);
		this.view.groups = [];
		this.view.people = [];
	};
	this.didClickCancel = function(e){
		this.view.groups = [];
		this.view.people = [];
		if(this.delegate && this.delegate.didClickCancel){
			this.delegate.didClickCancel.apply(this.delegate, [e]);
		}
	};
	this.allContactsWasSelected = function(elem){
		var i = this.view.groups.length;
		var j = this.view.people.length;
		if(this.delegate && this.delegate.didRemoveGroup){
			while(group = this.view.groups[i--]){
				this.delegate.didRemoveGroup.apply(this.delegate, [group]);
			}
		}
		if(this.delegate && this.delegate.didRemovePerson){
			while(person = this.view.people[j--]){
				this.delegate.didRemovePerson.apply(this.delegate, [person]);
			}
		}		
		
		this.view.disableAllCheckboxes(elem);
		this.view.groups = [];
		this.view.people = [];
		this.view.groups.push(elem.getAttribute('value'));
	};
	this.onAddressbookAjaxDONE = function(request){		
		this.view.setHtml(request.responseText);		
		this.view.selectedGroup = SDDom.findFirst('li.selected');
		var i = 0;
		SDDom.remove(SDDom.findFirst('.owner'));
		// Remove the friend request group.
		SDArray.each(SDDom.findAll('li', this.view.groupContainer()), function(li){
			if(li.getAttribute('rel') === 'Friend Requests'){
				SDDom.remove(li);
			}
		});
		
		for(i = 0; i < this.view.groups.length; i++){
			checkbox = SDDom('group_checkbox_' + this.view.groups[i]);
			if(checkbox){
				checkbox.checked = true;
			}
		}
		
		for(i = 0; i < this.view.people.length; i++){
			checkbox = SDDom('person_checkbox_' + this.view.people[i].id);
			if(checkbox){
				checkbox.checked = true;
			}
		}
	};
	this.didClickHandle = function(e){
		var url = e.target.href.replace('.html', '') + '.phtml';
		var ajax = new SDAjax({method: 'get', DONE: [this, this.onAddressbookAjaxDONE]});
		ajax.send(url);
	};
	this.onGroupAjaxDONE = function(request){
		var response = JSON.parse(request.responseText);
		this.view.clearPeople();
		if(response.people && response.people.length > 0){
			this.view.addPeople(response.people, this.view.people);					
		}
		SDDom.remove(SDDom.findFirst('li.owner'));
	}
	this.didClickGroups = function(e, text){
		var url = 'people/' + text + '.json';
		var ajax = new SDAjax({method: 'get', DONE: [this, this.onGroupAjaxDONE]});
		ajax.send(url);
	};
	
	this.aGroupWasChecked = function(elem){
		var group = elem.getAttribute('value');
		if(elem.checked){
			if(!SDArray.contains(group, this.view.groups)){
				this.view.groups.push(group);
			}
			if(this.delegate && this.delegate.didAddGroup){
				this.delegate.didAddGroup.apply(this.delegate, [group]);
			}
		}else{
			
			if(this.delegate && this.delegate.didRemoveGroup){
				this.delegate.didRemoveGroup.apply(this.delegate, [group]);
			}
			SDArray.remove(group, this.view.groups);			
		}
	};
	
	this.aPersonWasChecked = function(e, elem){
		if(elem.nodeName.toLowerCase() === 'input'){
			var value = decodeURIComponent(elem.value).replace(/\+/g, '');
			var person = JSON.parse(value);
			if(elem.checked){
				if(SDArray.collect(this.view.people, function(p){return p.id === person.id;}).length === 0){
					this.view.people.push(person);
				}
				if(this.delegate && this.delegate.didAddPerson){
					this.delegate.didAddPerson.apply(this.delegate, [person]);
				}
			}else{
				this.view.people = SDArray.collect(this.view.people, function(p){return p.id !== person.id;});
				if(this.delegate && this.delegate.didRemovePerson){
					this.delegate.didRemovePerson.apply(this.delegate, [person]);
				}
			}
		}else{
			SDDom.stop(e);
		}
	};
	
};

UIController.Post = function(options){
	UIController.apply(this, arguments);
	this.send_to_list = SDDom('send_to_list');
	this.list_of_people = SDDom.findFirst('ul', this.send_to_list);
	this.add_a_photo_link = SDDom('add-a-photo-link');
	this.form = SDDom('post_form');
	var textarea = new UIView.TextArea('body', {delegate: this});
	this.photo_viewer = new UIView.PhotoViewer('photo_viewer', {delegate: this, title: 'Photo Picker'});
	this.photo_viewer.toggle();
	if(!this.list_of_people){
		this.list_of_people = SDDom.append(this.send_to_list, SDDom.create('ul'));
	}
	function addImageToPost(img){
		SDDom.toggleClass('selected', img.parentNode);
		if(!textarea.has(img.src)){
			textarea.addImage(img);
		}else{
			textarea.removeImage(img);
		}
	}
	this.imageWasClicked = function(e){
		addImageToPost(e.target);
	};
	this.addPhotoWasClicked = function(e){
		SDDom.stop(e);
		SDDom('body').focus();
		this.photo_viewer.refresh(e.target.href);
		this.photo_viewer.toggle();
		SDDom.setStyles({top: '0px', left: (SDDom.getWidth(document.body) - SDDom.getWidth(this.photo_viewer.container)/2) + 'px'}, this.photo_viewer.container);
		console.log([SDDom.getWidth(document.body), SDDom.getWidth(this.photo_viewer.container)]);
	};
	this.doSave = function(e){
		this.form.submit();
	};
	this.didAddGroup = function(group){
		var li = SDDom.create('li');
		li.innerHTML = '<span>' + decodeURIComponent(group).replace('+', ' ') + '</span>';
		var input = SDDom.create('input');
		input.type = 'hidden';
		input.name = 'groups[]';
		input.id = 'groups_' + this.getUniqueId();
		input.value = group;
		SDDom.append(li, input);
		SDDom.append(this.list_of_people, li);
	};
	this.didRemoveGroup = function(group){
		var fields = SDDom.findAll('input[type=hidden]', SDDom('send_to_list'));
		var i = fields.length;
		while(field = fields.item(--i)){
			if(field.value === group){
				break;
			}
		}
		
		if(field){
			SDDom.remove(SDDom.getParent('li', field));
		}
	};
	this.didAddPerson = function(person){
		var li = li = SDDom.create('li');
		li.id = 'li_person_' + person.id;
		li.innerHTML = '<span>' + decodeURIComponent(person.name).replace('+', ' ') + '</span>';
		var input = SDDom.create('input');
		input.type = 'hidden';
		input.name = 'people[]';
		input.id = 'person_checkbox_' + person.id;
		input.value = person.id;
		SDDom.append(li, input);
		SDDom.append(this.list_of_people, li);
	};
	this.didRemovePerson = function(person){
		SDDom.remove(SDDom('li_person_' + person.id));
	};
	this.didClickCancel = function(e){
		var lis = SDDom.findAll('ul li', this.list_of_people);
		var i = lis.length;
		while(i > 0){
			SDDom.remove(lis.item(--i));
		}
	};
	this.didClickReblog = function(e){
		SDDom('is_published').checked = true;
	};
	
	this.didChangePhoto = function(e){
		if(SDDom('photo_names[' + this.value + ']')){
			alert("you've already added that photo.");
			SDDom.stop(e);
		}else{
			$('media_form').submit();
		}	
	};
		
	if(this.add_a_photo_link){
		SDDom.addEventListener(this.add_a_photo_link, 'click', this.bind(this.addPhotoWasClicked));
	}
	/*SDArray.each([SDDom('title'), SDDom('body')], function(elem, i){
			if(elem.value.length == 0){
				SDDom.show(SDDom.findFirst('label[for="' + elem.id + '"]'));			
			}else{
				SDDom.hide(SDDom.findFirst('label[for="' + elem.id + '"]'));			
			}
		
			SDDom.addEventListener(elem, 'focus', function(e){
				if(e.target.value.length == 0){
					SDDom.hide(SDDom.findFirst('label[for="' + elem.id + '"]'));			
				}
			});

			SDDom.addEventListener(elem, 'blur', function(e){
				if(e.target.value.length == 0){
					SDDom.show(SDDom.findFirst('label[for="' + elem.id + '"]'));
				}
			});
		}
	);*/
	var reblog = SDDom('reblog');
	if(reblog){
		SDDom.addEventListener(reblog, 'click', this.bind(this.didClickReblog));
	}
}
var editor = null;
var postController;

SDDom.addEventListener(window, 'load', function(e){
	var postMenu = new UIView.PostMenu('post_menu');
	var addressBookController = new UIController.AddressBook(new UIView.Modal.AddressBook('addressbook_modal', {handle: 'address'}));
	postController = new UIController.Post(null);
	addressBookController.delegate = postController;
});