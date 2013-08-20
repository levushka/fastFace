///////////////////////////////////////////////
//               GateWay  Messages
///////////////////////////////////////////////

fastFace.msg = {
	
	init: function() {
		$.pnotify.defaults.history = false;
		$.pnotify.defaults.stack = {dir1: 'down', dir2: fastFace.lang.align, firstpos1:55, firstpos2:15, push: 'bottom'};
		
		if (typeof window._alert !== 'undefined') {
			return;
		}
		window._alert = window.alert;
		window.alert = function(message) {
			$.pnotify({title: 'Alert', text: message});
		};
	},
	
	alert: function(title, text) {
		$.pnotify({title: title, text: text});
	},

	info: function(text, title) {
		$.pnotify({ type: 'info', delay: 2000, history: false, nonblock: true, nonblock_opacity: 0.2, title: title || 'System message', text: text });
	},
	
	err: function(text, title) {
		$.pnotify({type: 'error', delay: 5000, history: false, title: title || 'System Error', text: text});
	}
	
};


