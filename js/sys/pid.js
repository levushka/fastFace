///////////////////////////////////////////////
//               PID
///////////////////////////////////////////////
fastFace.pid = {
	queue: {},

	done: function(pid, data) {
		if(typeof this.queue[pid] === 'object' && this.queue[pid] !== null) {
			if(typeof this.queue[pid].success === 'function') {
				this.queue[pid].success(data);
			}
			this.queue[pid] = null;
			delete this.queue[pid];
		} else {
			if(fastFace.err.isDebug) {
				throw new Error('Wrong pid ['+pid+']');
			}
		}
	},

	check: function(pid) {
		if(typeof this.queue[pid] === 'object' && this.queue[pid] !== null) {
			if(typeof this.queue[pid].fail === 'function') {
				this.queue[pid].fail();
			} else if(fastFace.err.isDebug) {
				throw new Error('Pid not finished ['+pid+']');
			}
			this.queue[pid] = null;
			delete this.queue[pid];
		}
	},

	run: function(req, success, fail) {
		if(req.length < 1) {
			throw new Error('Wrong amount of arguments');
		} else if(req.length === 1) {
			req.push({});
		}
		
		var pid = req[1].pid = (req[1].pid || fastFace.render.uid('pid_'));
		
		this.queue[pid] = {success: success, fail: fail};
		
		fastFace.sync.run(req, null, null, null, function() {
			fastFace.pid.check(pid);
		});
	}
};
