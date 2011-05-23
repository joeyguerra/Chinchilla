function NotificationCenter(win){
	this.window = win;
	this.observers = new Array();
}

NotificationCenter.prototype.postNotificationCheck = function(e){return true;}

NotificationCenter.prototype.addObserver = function(anObserver, aSelector, notificationName, anObject){
	var aNotificationObserver = new NotificationObserver(anObserver, aSelector, notificationName, anObject);
	this.observers[this.observers.length] = aNotificationObserver;
}

NotificationCenter.prototype.removeObserver = function(aNotificationObserver){
	var length = this.observers.length;
	for(var i = length; i > 0; i--){
		if(this.observers[i].observer == aNotificationObserver.observer 
			&& this.observers[i].notificationName == aNotificationObserver.notificationName)
			this.observers.splice(i, 1);
	}
}

NotificationCenter.prototype.removeObserverForAll = function(anObserver){
	var length = this.observers.length - 1;
	do{
		if(this.observers[length].observer == anObserver){
			this.observers.splice(length, 1);
			
			// Decrement the counter after removing the observer from the array
			// so we keep it in synch with the number of items in the array. Or else,
			// the current number of items in the array won't match the counter.
			length--;
		}
		length--;
	}while(length > 0)
}

NotificationCenter.prototype.postNotification = function(notification){
	var length = this.observers.length;
	var observersToRemove = new Array();
	if(this.postNotificationCheck(notification)){
		for(var i = 0; i < length; i++){
			// If the observer is listening for a particular publisher, then check that also.
			// If not, then just check that the notification name is what the observer is 
			// listening for.
			if(this.observers[i].publisher){
				if(this.observers[i].notificationName == notification.name
					&& this.observers[i].publisher == notification.publisher){
						this.observers[i].selector.apply(this.observers[i].obj, new Array(notification.obj, notification.info));
					}
			}else{				
				if(this.observers[i].notificationName == notification.name){
				
					// Catch observers that don't exist and store them for removal 
					// after this block of code is done.
					try{
						this.observers[i].selector.apply(this.observers[i].obj, new Array(notification.obj, notification.info));
					}catch(e){
						observersToRemove[observersToRemove.length] = this.observers[i];					
					}
				}
			}
		}
		
		// Remove observers that no longer exist.
		if(observersToRemove.length > 0){
			for(i = 0; i < observersToRemove.length; i++){
				this.removeObserverForAll(observersToRemove[i]);
			}
		}
	}		
}

NotificationCenter.prototype.postNotificationName = function(notificationName, obj, info){
	var notification = new Notification(notificationName, obj, info);
	this.postNotification(notification);
}

function Notification(name, obj, info){
	this.name = name;
	this.obj = obj;
	this.info = info;
}

function NotificationObserver(anObserver, aSelector, notificationName, anObject){
	this.observer = anObserver;
	this.selector = aSelector;
	this.notificationName = notificationName;
	this.publisher = anObject;
}

var defaultCenter = new NotificationCenter(window);
