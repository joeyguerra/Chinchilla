function chin(){
	return this;
}
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

chin.ajax = function(options){
	var self = chin.apply(this, []);
	this.options = {
		method: 'post'
		, asynchronous: true
		, contentType: 'application/x-www-form-urlencoded'
		, encoding: 'UTF-8'
		, parameters: ''
		, evalJSON: true
		, evalJS: true
	};
	var events = ['UNSENT', 'OPENED', 'HEADERS_RECEIVED', 'LOADING', 'DONE'];
	chin.extend(this.options, options !== null ? options : {});
	if(!request){
		var request = createTransport();
	}
	function createTransport(){
		if(XMLHttpRequest)return new XMLHttpRequest();
		if(ActiveXObject && ActiveXObject('Msxml2.XMLHTTP')) return new ActiveXObject('Msxml2.XMLHTTP');
		if(ActiveXObject && ActiveXObject('Microsoft.XMLHTTP')) return new ActiveXObject('Microsoft.XMLHTTP');
		return null;
	}
	function didStateChange(){
		var state = events[request.readyState];
		if(self.options[state]){
			self.options[state][1].apply(self.options[state][0], [request]);
		}
		if(state === 'DONE'){
			request = null;
		}
	}
	function getHeaders(method, params){
		var header = {"X-Requested-With":"XMLHttpRequest", "Accept":"text/javascript, text/html, application/xml, text/xml, */*"};
		if(method === 'post'){
			header["Content-type"] = 'application/x-www-form-urlencoded; charset=UTF-8';
		}
		return header;
	}
	this.send = function(url){
		if(request == null) return;
		if(this.options.parameters){
			if(this.options.method == 'get'){
				url += (/\?/.test(url) ? '&' : '?') + this.options.parameters;
			}else if(/Konqueror|Safari|KHTML/.test(navigator.userAgent)){
				this.options.parameters += '&_=';
			}
		}
		if(!['get', 'post'].indexOf(this.options.method)){
			this.options.parameters += '&_method=' + this.options.method;
			this.options.method = 'post';
		}
		//TODO: attachEvent doesn't work in IE for the readystatechange event on the request. I'm not sure why but
		// I'm working around it for now. I'd love to fix this.
		request.onreadystatechange = this.bind(didStateChange);
		request.open(this.options.method.toUpperCase(), url, this.options.asynchronous);		
		var headers = getHeaders(this.options.method, this.options.parameters);
		for(name in headers){
			request.setRequestHeader(name, headers[name]);
		}
		request.send(this.options.method === 'post' ? this.options.parameters : null);
	};
	return this;
};
chin.extend(chin.ajax, chin);
chin.stop = function(e){
	if(e.preventDefault){
		e.preventDefault();
		e.stopPropagation();
	}else{
		e.cancelBubble = true;
	}	
	e.returnValue = false;
}
