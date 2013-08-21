///////////////////////////////////////////////
//               cache
///////////////////////////////////////////////
fastFace.cache = {

	ver: 0,
	isLS: false,
	data: {},

	reInit: function(ver) {
		this.ver = ver;

		var lS = window.localStorage,
		lsVer = this.get('CACHE_VER', 0, true),
		cookieVer = ~~$.cookie('c');

		if ( this.ver > 0 && ( (lsVer > 0 && lsVer !== this.ver) || (cookieVer > 0 && cookieVer !== this.ver) ) ) {
			this.clearLocalStrorage();
			this.clearCache();
		} else {
			try {
				if($.sizeOf(lS) > 1000000) {
					this.clearLocalStrorage();
				}
			} catch(err) {
				this.clearLocalStrorage();
			}
			this.reStore();
		}

	},

	reStore: function() {
		this.set('CACHE_VER', this.ver, true);
		$.cookie('c', this.ver, { expires: 30, path: '/' });
	},


	clearLocalStrorage: function() {
		window.localStorage.clear();
		this.reStore();
		fastFace.ver.reStore();
		fastFace.login.reStore();
	},

	clearCache: function() {
		return;
//    fastFace.tbl.data = {};
//    fastFace.tbl.toLoad = {};
	},

	reloadCache: function(ver) {
		return;
//    var loadedCls = $.filterArrVal(/^arr_|^cache_/, $.keys(fastFace.tbl.data), false);
//    this.ver = ver;
//    this.clearLocalStrorage();
//    this.clearCache();
//    if(loadedCls.length > 0) {
//      fastFace.sync.start();
//      $.each(loadedCls, function(key, val) { fastFace.tbl.run(val); });
//      fastFace.sync.end();
//    }
	},




	
	getKeys: function(prefix) {
		var res = [];
		if(prefix) {
			var testRexExp = new RegExp('^'+prefix);
			Object.keys(this.data)
				.forEach(function(key) {
					if(testRexExp.test(key)) {
						res.push(key);
					}
				});
		} else {
			Object.keys(this.data)
				.forEach(function(key) {
					res.push(key);
				});
		}
	},

	get: function(key, def, isGlobal, fromLS) {
		if(this.data[key]) {
			return this.data[key];
		} else if(this.isLS || fromLS) {
			var tmp = window.localStorage.getItem('ff/' + (isGlobal ? '' : fastFace.login.userId+'/'+fastFace.lang.cur+'/') + key);
			if(tmp) {
				this.data[key] = $.parseJSON(tmp);
				return this.data[key];
			} else {
				return def || null;
			}
		} else {
			return def || null;
		}
	},

	set: function(key, data, isGlobal, toLS) {
		this.data[key] = data;
		if(this.isLS || toLS) {
			window.localStorage.setItem('ff/' + (isGlobal ? '' : fastFace.login.userId+'/'+fastFace.lang.cur+'/') + key, JSON.stringify(data));
		}
	},

	del: function(key, isGlobal, fromLS) {
		delete this.data[key];
		if(this.isLS || fromLS) {
			window.localStorage.removeItem('ff/' + (isGlobal ? '' : fastFace.login.userId+'/'+fastFace.lang.cur+'/') + key);
		}
	},

	delAll: function(prefix, isGlobal, fromLS) {
		var testRexExp;

		if(prefix) {
			testRexExp = new RegExp('^'+prefix);
			Object.keys(this.data)
				.forEach(function(key) {
					if(testRexExp.test(key)) {
						delete this.data[key];
					}
				});
		} else {
			this.data = {};
		}

		if(this.isLS || fromLS) {
			testRexExp = new RegExp('^ff\/'+ (isGlobal ? '' : fastFace.login.userId+'\/'+fastFace.lang.cur+'\/') + (prefix || ''));
			Object.keys(localStorage)
				.forEach(function(key) {
					if(testRexExp.test(key)) {
						localStorage.removeItem(key);
					}
				});
		}
		
	}

};
