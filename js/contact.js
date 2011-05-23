chin.controller.contact = (function($){
	var self = {};
	var timeout = 5000;
	var start = (new Date()).getTime();
	var table = null;
	var fieldset = null;
	self.init = function(){
		table = $("#contact");
		fieldset = table.find("form fieldset");
		$("button[type=submit]").click(function(e){
			//chin.stop(e);
			chin.default_center.post("submit_was_clicked", e.target, e);
		});
		
		chin.default_center.subscribe("submit_was_clicked", self, null);
		
	};
	
	self.submit_was_clicked = function(publisher, info){
	};
	
	var interval = setInterval(function(){
		if($("#contact").length > 0){
			self.init();
			clearInterval(interval);
		}
		
		if(((new Date()).getTime() - start) >= timeout){
			console.log("timedout");
			clearInterval(interval);
		}
		
	}, 250);
	
	return self;
})(jQuery);