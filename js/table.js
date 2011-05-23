chin.controller.table = (function($){
	var self = {};
	var timeout = 5000;
	var start = (new Date()).getTime();
	var table = null;
	var fieldset = null;
	self.init = function(){
		table = $("#table");
		fieldset = table.find("form fieldset");
		console.log(fieldset);
		$("button.add").click(function(e){
			chin.stop(e);
			chin.default_center.post("add_column_was_clicked", e.target, e);
		});
		
		chin.default_center.subscribe("add_column_was_clicked", self, null);
		
	};
	
	self.add_column_was_clicked = function(publisher, info){
		var list = $("<dl />").insertAfter(fieldset.find("dl").last());
		var column = $("<dd />").appendTo(list);
		var columns = $("input[name^=columns]");
		var i = columns.length / 4;
		$("<label />").text("Name").appendTo(column);
		$("<input />").attr({type: "text", name: "columns[" + i + "][name]"}).appendTo(column);
		
		var data_type = $("<dd />");
		$("<label />").text("Data type").appendTo(data_type);
		$("<input />").attr({type:"text", name:"columns[" + i + "][type]"}).appendTo(data_type);
		list.append(data_type);
		
		var nullable = $("<dd />");
		$("<label />").text("Nullable").appendTo(nullable);
		$("<input />").attr({type:"checkbox", name:"columns[" + i + "][notnull]"}).appendTo(nullable);
		list.append(nullable);
		
		var default_column = $("<dd />");
		$("<label />").text("Default Value").appendTo(default_column);
		$("<input />").attr({type: "text", name:"columns[" + i + "][dflt_value]"}).appendTo(default_column);
		list.append(default_column);
	};
	
	var interval = setInterval(function(){
		if($("#table").length > 0){
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