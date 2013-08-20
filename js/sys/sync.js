///////////////////////////////////////////////
//               Sync call
///////////////////////////////////////////////
fastFace.sync = {
	_count: 0,
	
	_before: {
		req: {},
		fn: {}
	},

	_body: {
		req: [],
		before: [],
		success: [],
		fail: [],
		complete: []
	},

	_after: {
		req: {},
		success: {},
		fail: {},
		complete: {}
	},

	add: function(req, success, fail, before, complete) {
		if(req && typeof req === 'object' && req !== null) {
			if(req[0] && typeof req[0] === 'object' && req[0] !== null) {
				$.merge(this._body.req, req);
			} else {
				this._body.req.push(req);
			}
		}
		if(before && typeof before === 'function') {
			this._body.before.push(before);
		}
		if(success && typeof success === 'function') {
			this._body.success.push(success);
		}
		if(fail && typeof fail === 'function') {
			this._body.fail.push(fail);
		}
		if(complete && typeof complete === 'function') {
			this._body.complete.push(complete);
		}
	},

	run: function(req, success, fail, before, complete) {
		this.add(req, success, fail, before, complete);
		this.check();
	},

	start: function(key, req, fn) {
		this._count++;
		this.before(key, req, fn);
	},

	end: function(key, req, success, fail, fn) {
		this.after(key, req, success, fail, fn);
		this._count--;
		this.check();
	},

	before: function(key, req, fn) {
		key = key || fastFace.render.uid();
		if(req && typeof req === 'object' && req !== null) {
			this._before.req[key] = (req[0] && typeof req[0] === 'object' && req[0] !== null) ? req : [req];
		}
		if(fn && typeof fn === 'function') {
			this._before.fn[key] = fn;
		}
	},

	after: function(key, req, success, fail, complete) {
		key = key || fastFace.render.uid();
		if(req && typeof req === 'object' && req !== null) {
			this._after.req[key] = (req[0] && typeof req[0] === 'object' && req[0] !== null) ? req : [req];
		}
		if(success && typeof success === 'function') {
			this._after.success[key] = success;
		}
		if(fail && typeof fail === 'function') {
			this._after.fail[key] = fail;
		}
		if(complete && typeof complete === 'function') {
			this._after.complete[key] = complete;
		}
	},

	wrap: function(key, reqBefore, fnBefore, reqAfter, success, fail, complete) {
		this.before(key, reqBefore, fnBefore);
		this.after(key, reqAfter, success, fail, complete);
	},

	check: function() {
		if(this._count === 0) {
			
			$.each(this._before.fn, function(key, fn) { fn(); });
			this._before.fn = {};

			$.each(this._body.before, function(key, fn) { fn(); });
			this._body.before = {};
			
			var finalReq = $.merge($.mergeArr($.vals(this._before.req)), $.merge(this._body.req, $.mergeArr($.vals(this._after.req))));
			
			this._before.req = {};
			this._body.req = [];
			this._after.req = {};
				
			if(finalReq.length > 0) {

				var reqStr = JSON.stringify(finalReq),
					reqStrLen = escape(reqStr).length;
				
				finalReq = null;
				
				$.ajax(
					fastFace.urlAPI+'?'+jQuery.param({
						v: fastFace.ver.ver,
						c: fastFace.cache.ver,
						l: fastFace.lang.cur,
						d: fastFace.err.d,
						t: fastFace.token,
						o: 'js',
						i: 'json'
					}),
					{
						type: reqStrLen < 2000 ? 'get' : 'post',
						dataType: 'script',
						data: {cmd: reqStr},
						
						success: (function(success, after) {
							return function(data, textStatus, jqXHR) {
								$.each(success, function(key, fn) { fn(data, textStatus, jqXHR); });
								$.each(after, function(key, fn) { fn(data, textStatus, jqXHR); });
							};
						}(this._body.success, this._after.success)),
						
						error: (function(fail, after) {
							return function(jqXHR, textStatus, errorThrown) {
								$.each(fail, function(key, fn) { fn(jqXHR, textStatus, errorThrown); });
								$.each(after, function(key, fn) { fn(jqXHR, textStatus, errorThrown); });
							};
						}(this._body.fail, this._after.fail)),
						
						complete: (function(complete, after) {
							return function(jqXHR, textStatus) {
								$.each(complete, function(key, fn) { fn(jqXHR, textStatus); });
								$.each(after, function(key, fn) { fn(jqXHR, textStatus); });
							};
						}(this._body.complete, this._after.complete))
					}
				);
				
				reqStr = null;

				this._body.success = [];
				this._body.fail = [];
				this._body.complete = [];
				this._after.success = {};
				this._after.fail = {};
				this._after.complete = {};

			} else {
				
				$.each(this._body.success, function(key, fn) { fn(); });
				this._body.success = [];
				this._body.fail = [];
				$.each(this._body.complete, function(key, fn) { fn(); });
				this._body.complete = [];
				
				$.each(this._after.success, function(key, fn) { fn(); });
				this._after.success = {};
				this._after.fail = {};
				$.each(this._after.complete, function(key, fn) { fn(); });
				this._after.complete = {};
				
			}
			
		}
	}
	
};
