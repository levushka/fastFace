///////////////////////////////////////////////
//               TIMER
///////////////////////////////////////////////

fastFace.timer = {

	ref: null,

	data: {},

	reInit: function() {
		this.clean();
		if(this.ref === null) {
			this.ref = setInterval(fastFace.timer.run,5000);
		}
	},

	clean: function() {
		if(this.ref) {
			clearInterval(this.ref);
			this.ref = null;
		}
		this.data = {};
	},

	add: function(type, delay, key, timer) {
		if(typeof this.data[type+'_'+delay] === 'undefined') {
			this.data[type+'_'+delay] = {type:type, delay:delay, lastTime:0, timers:{}};
		}
		this.data[type+'_'+delay].timers[key] = timer;
	},

	del: function(type, delay, key) {
		if(typeof this.data[type+'_'+delay] === 'undefined') {
			return;
		}
		if(typeof key === 'undefined') {
			delete this.data[type+'_'+delay];
		} else {
			delete this.data[type+'_'+delay].timers[key];
		}
	},

	run: function() {
		try {
			var self = fastFace.timer, curTime = (new Date()).getTime();
			fastFace.sync.start();
			for(var i in self.data) {
				if(self.data.hasOwnProperty(i) && (curTime - self.data[i].lastTime) > self.data[i].delay) {
					self.data[i].lastTime = curTime;
					self['_'+self.data[i].type].call(self, self.data[i].timers);
				}
			}
			fastFace.sync.end();
		} catch(err) {
			fastFace.err.js(err);
		}
	},

	_pid: function(timers) {
		$.each(timers, function(key, timer) {
			fastFace.pid.run(timer.req, timer.success, timer.fail);
		});
	},

	_sync: function(timers) {
		$.each(timers, function(key, timer) {
			fastFace.sync.run(timer.req, timer.success, timer.fail, timer.before, timer.complete);
		});
	},

	_fn: function(timers) {
		$.each(timers, function(key, timer) {
			timer.fn.call(timer.obj || this, timer.arg || null);
		});
	}

};
