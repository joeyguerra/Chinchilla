/* jquery plugin to make an elememt positioned on the page.*/
(function($){
	$.fn.make_positioned = function(){
		return this.each(function(){
			var elem = $(this);
			var pos = elem.position();
			elem.data("pos", {position: elem.css("position"), marginLeft: elem.css("margin-left"), marginRight: elem.css("margin-right"), top: pos.top, left: pos.left});
			elem.css({
				position: "absolute"
				, marginLeft: 0
				, marginRight: 0
				, top: pos.top
				, left: pos.left
			});
		});
	};
})(jQuery);

(function($){
	$.fn.make_unpositioned = function(){
		return this.each(function(){
			var elem = $(this);
			var pos = elem.data("pos");
			elem.css({
				position: pos.position
				, marginLeft: pos.marginLeft
				, marginRight: pos.marginRight
				, top: pos.top
				, left: pos.left
			});
		});
	};
})(jQuery);

function chin(){
	return this;
}
chin.default_center = (function(){
	var observers = [];
	var self = {
		post: function(notification, publisher, info){
			var ubounds = observers.length;
			var i = 0;
			for(i; i<ubounds; i++){
				if(!observers[i]) continue;
				if(observers[i].notification != notification) continue;
				if(observers[i].publisher != null && observers[i].publisher != publisher) continue;
				try{
					observers[i].observer[notification].apply(observers[i].observer, [publisher, info]);
				}catch(e){
					console.log(e);
				}
			}
		}
		, subscribe: function(notification, observer, publisher){
			observers.push({"notification": notification, "observer":observer, "publisher":publisher});
		}
		, unsubscribe: function(notification, observer, publisher){
			var i = 0;
			var ubounds = observers.length;
			for(i; i<ubounds; i++){
				if(observers[i].observer == observer && observers[i].notification == notification){
					observers.splice(i, 1);
					break;
				}
			}
		}
	}
	return self;
})();

chin.to_json = function(text){
	var response = null;
	eval('response = ' + text + ';');
	return response;
}
chin.prototype.bind = function(fn, context){
	return function() {
		var args = new Array();
		if(window.event){
			var e = window.event;
			e.target = window.event.srcElement;
			args.push(e);
		}
		if(arguments && arguments.length > 0){
			var i = arguments.length;
			while(arg = arguments[--i]){
				args.push(arg);
			}
		}
		return fn.apply(context ? context : this, args);
	}
}
chin.get_element = function(id){
	return id.nodeName ? id : document.getElementById(id);
}
chin.is_hidden = function(elem){
	return elem.style.display === 'none';
}
chin.show = function(id){
	var elem = chin.get_element(id);
	elem.style.display = 'block';
}
chin.hide = function(id){
	var elem = chin.get_element(id);
	elem.style.display = 'none';
}
chin.toggle = function(id){
	var elem = chin.get_element(id);
	if(chin.is_hidden(elem)){
		chin.show(elem);
	}else{
		chin.hide(elem);
	}
}
chin.extend = function(dest, src){
	for(prop in src){
		dest[prop] = src[prop];
	}
	return dest;
};

chin.observe = function(elem, name, fn){
	if (elem.addEventListener){
		elem.addEventListener(name, fn, false);
	}else{
		elem.attachEvent('on' + name, fn);
	}
	return fn;
};
chin.stop_observing = function(elem, name, fn){
	if(elem.removeEventListener){
		elem.removeEventListener(name, fn, false);
	}else{
		elem.detachEvent('on' + name, fn);
	}	
}

chin.stop = function(e){
	if(e.preventDefault){
		e.preventDefault();
		e.stopPropagation();
	}else{
		e.cancelBubble = true;
	}	
	e.returnValue = false;
}
chin.view = function(){
	return this;
};
chin.controller = function(){	
	return this;
};
