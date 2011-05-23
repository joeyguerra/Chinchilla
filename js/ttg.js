function JSObject(){
	var self = this;
	this.bind = function(fn) {
		return function() {
			var args = new Array();
			if(window.event){
				args.push(window.event);
			}
			if(arguments && arguments.length > 0){
				args.concat(arguments);
			}
			return fn.apply(self, args);
		}
	};
	this.stopPropagation = function(e){
		e.cancelBubble = true;
		e.returnValue = false;
	};
	this.addEventListener = function(elem, name, fn){
		// IE doesn't fire an onload event when a script element loads, it implements onreadystatechange like XMLHTTpRequest.
		// So I'm coding for that scenario here.
		if(name === 'load' && elem.nodeName.toLowerCase() === 'script' && elem.attachEvent){
			elem.onreadystatechange = function(){
				if(this.readyState === 'loaded' || this.readyState === 'complete'){
					fn();
				}
			};
		}
		if (elem.addEventListener){
			elem.addEventListener(name, fn, false);
		}else{
			elem.attachEvent('on' + name, fn);
		}
	};
}
JSObject.capitalize = function(text){
	var words = text.toLowerCase().split('_');
	for(key in words){
		if(words[key].slice){
			words[key] = words[key].slice(0, 1).toUpperCase() + words[key].slice(1, words[key].length);
		}
	}	
	return words.join(' ');
};

JSObject.stringify = function(obj){
	var msg = [];
	for(prop in obj){
		if(obj[prop] !== null && typeof obj[prop] === 'object'){
			msg.push(JSObject.stringify(obj[prop]));
		}else{
			msg.push(prop + '=' + obj[prop]);				
		}
	}
	return msg;
};


function JSView(){
	JSObject.apply(this, arguments);
	this.container = null;
	this.activeView = null;
	this.delegate = null;
	this.addScript = function(id, src, callback){
		if(document.getElementById(id) === null){
			var s = document.createElement('script');
			s.setAttribute('type', 'text/javascript');
			s.setAttribute('src', src);
			s.id = id;
			this.addEventListener(s, 'load', callback);
			document.getElementsByTagName('head')[0].appendChild(s);
		}
	};
	this.setHtml = function(html){
		this.container.innerHTML = html;
	};
	
	this.isVisible = function(){
		visible = false;
		if(this.container){
			visible = (this.container.style.display === 'none' && this.container.style.visibility === 'visible');
		}
		return visible;	
	};
	this.show = function(){
		this.container.style.display = 'block';
		if(this.afterShow){
			this.afterShow();
		}
	};
	this.hide = function(){
		this.container.style.display = 'none';
		if(this.afterHide){
			this.afterHide();
		}
	};
	this.toggle = function(){
		if(this.isVisible()){
			this.show();
		}else{
			this.hide();
		}
	};
	this.open = function(url, options){
		var today = new Date();
		this.activeView = window.open(url, (options.name ? options.name : this.id + '_view_' + Date.UTC(today.getFullYear(), today.getMonth(), today.getDate(), today.getHours(), today.getMinutes(), today.getSeconds(), today.getMilliseconds()))
			, (options.options ? options.options : 'dependent=yes,directories=no,height=600,location=no,menubar=no,resizable=yes,outerHeight=600,outerWidth=600,scrollbars=yes,status=no,titlebar=no,toolbar=no, width=600'));
		
	};
	
	this.viewDidClose = function(e){
		this.activeView = null;
	};
	
}
JSView.IFrame = function(id){
	var iframe = document.getElementById(id);
	if(iframe === null){
		iframe = document.createElement('iframe');
		var body = document.getElementsByTagName('body')[0];
		body.insertBefore(iframe, body.firstChild);
	}
	iframe.doc = null;
	if(iframe.contentDocument){
		iframe.doc = iframe.contentDocument;
	}else if(iframe.contentWindow.document){
		iframe.doc = iframe.contentWindow.document;
	}else if(iframe.document){
		iframe.doc = iframe.document;
	}
	iframe.doc.open();
	iframe.doc.close();
	return iframe;
}
JSView.Widget = function(id, html){
	JSView.apply(this, arguments);
	this.container = document.createElement('div');
	this.closeLink = document.createElement('a');
	this.closeLink.setAttribute('href', 'javascript:void(0);');
	this.closeLink.setAttribute('title', 'Close this widget');
	this.closeLink.innerHTML = '<span style="display: block;width: 100%;height: 100%;">x</span>';
	for(key in styles = {
		position: 'absolute'
		, left: '3px'
		, top: '3px'
		, color: 'rgb(180,180,180)'
		, display: 'block'
		, "overflow":"visible"
		, "border-radius":"5px"
		, "-webkit-border-radius":"5px"
		, width: '20px'
		, height: '13px'
		, "box-shadow":"0 0 3px #fff"
		, "-webkit-box-shadow":"0 0 3px #fff"
		, "-moz-box-shadow":"0 0 3px #fff"
		, textDecoration:"none"
		, zIndex: 10001
		, MozBorderRadius:'5px'
		, MozBoxShadow:'0 0 3px #fff'
		, textAlign:'center'
		, fontSize: '11px'
		, lineHeight: '100%'
	}){
        try{
		    this.closeLink.style[key] = styles[key];
        }catch(e){}
	}
    if(!!window.attachEvent){
        for(key in styles = {background: 'rgb(0,0,0)', filter: 'alpha(opacity=80)'}){
            this.closeLink.style[key] = styles[key];
        }
    }else{
        this.closeLink.style['background'] = 'rgba(0,0,0,.5)';
    }
	this.closeLink.id = '__ttg_close_link';
	var style = {position: 'fixed'
		, top: '1em'
		, right: '1em'
		, width: '300px'
		, color: '#fff'
		, padding: '1em'
		, 'border-radius':'5px 5px'
		, '-webkit-border-radius':'5px 5px'
		, "text-align":"left"
		, zIndex:1000001
		
	};
	for(key in style){
        try{
		    this.container.style[key] = style[key];
        }catch(e){}    
	}
    if(!!window.attachEvent){
        for(key in styles = {background: 'rgb(0,0,0)', filter: 'alpha(opacity=80)'}){
            this.container.style[key] = styles[key];
        }
    }else{
        this.container.style['background'] = 'rgba(0,0,0,.5)';
    }
	this.container.style.MozBorderRadius = '5px'

	this.container.innerHTML = html;
	this.container.appendChild(this.closeLink);
	var body = document.getElementsByTagName('body')[0];	
	body.insertBefore(this.container, body.firstChild);
	
	this.onCloseClicked = function(e){
		this.hide();
		this.stopPropagation(e);
	};
	this.addEventListener(this.closeLink, 'click', this.bind(this.onCloseClicked));
}
JSView.Translator = {};

JSView.Translator.Status = function(id){
	this.html = '<form id="__ttg_form" action="" target="__ttg_iframe" method="post"><fieldset style="border: none;"><legend id="__ttg_legend">Translator Status</legend><textarea cols="35" rows="3" name="status" id="__ttg_status" style="font-size: 11px;display: block;margin: 0 0 5px 0;"></textarea><p style="clear: both;padding: 0;margin: 0;">Translation</p><textarea id="__ttg_translation" style="font-size: 11px;display: block;margin: 0 0 5px 0;" cols="35" rows="3"></textarea><select id="__ttg_language" name="to"></select></fieldset></form><p id="__ttg_count">0</p><p id="__ttg_translation_count">0</p><div id="__ttg_branding"></div><iframe id="__ttg_iframe" name="__ttg_iframe" style="width: 0px;height:0px;border:0px;"></iframe>';
	JSView.Widget.apply(this, [id, this.html]);
	this.form = document.getElementById('__ttg_form');
	for(key in style = {padding: 0, margin: "10px 0 0 0"}){
		this.form.style[key] = style[key];
	}
	this.data = null;
	this.status = document.getElementById('__ttg_status');
	this.iframe = null;
	this.language = 'unknown';
	this.iframe = new JSView.IFrame('__ttg_iframe');
	this.language_list = document.getElementById('__ttg_language');
	this.translation = document.getElementById('__ttg_translation');
	this.translationCount = document.getElementById('__ttg_translation_count');
	this.characterCount = document.getElementById('__ttg_count');
	for(key in styles = {"text-align":"center", width: '30px', "font-variant":"small-caps", color: '#fff', position: 'absolute', "font-size":"2em", right: '10px', top: '50px', padding: 0, margin: 0}){
		this.characterCount.style[key] = styles[key];
		this.translationCount.style[key] = styles[key];
	}
	for(key in styles = {top: "130px"}){
		this.translationCount.style[key] = styles[key];
	}
	this.branding = document.getElementById('__ttg_branding');
	for(key in style = {position: 'absolute', bottom: '2px', right: '2px', background: '#fff'}){
		this.branding.style[key] = style[key];
	}
	var option = null;
	this.getSelectedLanguage = function(){
		return this.language_list.options[this.language_list.selectedIndex].value;	
	};
	this.setLanguages = function(languages){
		for(l in languages){
			option = document.createElement('option');
			option.value = google.language.Languages[l];
			option.innerHTML = JSObject.capitalize(l);
			option.text = option.innerHTML;
			if(window["default_lan"] === option.value){
				option.selected = 'true';
			}
			this.language_list.appendChild(option);
		}
	};
	this.setTranslation = function(translation){
		this.translation.value = translation;	
	};
	
	this.onLanguageChange = function(e){
		if(this.delegate && this.delegate.languageWasChanged){
			this.delegate.languageWasChanged(e);
		}
	};
	
	this.onSubmit = function(e){
		if(this.delegate && this.delegate.formWasSubmitted){
			this.delegate.formWasSubmitted(e);
		}
	};
	this.onIframeLoad = function(){
		document.getElementById('__ttg_legend').innerHTML = 'Twitter Status: updated';
		setTimeout(function(){
			document.getElementById('__ttg_legend').innerHTML = 'Twitter Status';
		}, 2000);
	};
	this.value = function(){
		return this.status.value;
	};
	
	this.reset = function(){
		this.status.value = '';
		this.translation.value = '';
		this.status.innerHTML = '';
		this.translation.innerHTML = '';
	};
	
	this.onKeyPress = function(e){
		if(this.delegate && this.delegate.keyWasPressed){
			this.delegate.keyWasPressed(e);
		}
	};
	this.afterHide = function(){
		clearTimeout(this.timer);
		this.timer = null;
	};
	this.afterShow = function(){
		clearTimeout(this.timer);
		this.timer = setTimeout(this.bind(this.poll), 3000);
	};
	this.poll = function(){
		this.characterCount.innerHTML = this.value().length;
		this.translationCount.innerHTML = this.translation.value.length;
		clearTimeout(this.timer);
		this.timer = setTimeout(this.bind(this.poll), 3000);
	};
	
	this.timer = setTimeout(this.bind(this.poll), 3000);
	this.addEventListener(this.status, 'keypress', this.bind(this.onKeyPress));
	this.addEventListener(this.language_list, 'change', this.bind(this.onLanguageChange));
	this.addEventListener(this.iframe, 'load', this.bind(this.onIframeLoad));
	this.addEventListener(this.form, 'submit', this.bind(this.onSubmit));
};

function UIController(view){
	JSObject.apply(this, [view]);
	this.view = view;
}

UIController.Twitter = function(view){
	UIController.apply(this, [view]);
	var option = null;
	this.view.delegate = this;
	this.start = null;
	this.timer = null;
	this.keyWasPressed = function(e){
		this.start = new Date();
	};
	this.formWasSubmitted = function(e){
		if(this.view.data.length > 0){
			e.target.action = 'http://api.twitter.com/1/statuses/update.xml?status=' + encodeURIComponent(this.view.data);
			this.view.reset();
		}	
	};
	this.languageWasChanged = function(e){
		this.translate(this.view.value());
	};
	this.poll = function(){
        /*if(!google.language){
            google.load("language", "1", {callback: this.bind(this.googleInit)});
        }*/
		if(this.start !== null){
			var diff =  ((new Date()) - this.start)/1000;
			if(diff > .5 && this.view.value().length > 1){
				this.translate(this.view.value());
				this.start = null;
			}
		}
		clearTimeout(this.timer);
		this.timer = setTimeout(this.bind(this.poll), 3000);
	};
	this.stopTimer = function(){
		clearTimeout(this.timer);		
	};
	this.onTranslationResponse = function(result) {
		console.log(this);
		
		if(result){
			if (!result.error) {
				this.view.setTranslation(result.translation);
			}else{
				alert(JSObject.stringify(result));
			}
		}else{
			alert('There was a bad error. I have no idea what to do now.');
		}
	};
	this.translate = function(text){
		// Had to reference self as a private variable within this function so I could reference this object within the 
		// translation call back because it wasn't working.
		var self = this;
		google.language.translate(text, '', this.view.getSelectedLanguage(), function(result){
			self.view.setTranslation(result.translation);
		});
	};
	
	this.onScriptLoad = function(){
		google.load("language", "1", {callback: this.bind(this.googleInit)});
	};

	this.googleInit = function(){
		this.view.setLanguages(google.language.Languages);
		google.language.getBranding(this.view.branding, null);   
	};
	if(!window['google'] || !window['google']['language']){
		this.view.addScript('google_jsapi', 'http://www.google.com/jsapi', this.bind(this.onScriptLoad));		
	}else{
        this.googleInit();
    }
	
	this.timer = setTimeout(this.bind(this.poll), 3000);

};
var __ttg_widget = new JSView.Translator.Status(null);
var __ttg_controller = new UIController.Twitter(__ttg_widget);