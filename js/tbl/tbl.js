///////////////////////////////////////////////
//               TBL
///////////////////////////////////////////////
fastFace.tbl = {

	db_names: {},
	key_names: [],
	
	data:   {},
	id2url:   {},
	url2id:   {},
	toLoad: {},

	init: function(options) {
		this.db_names = options.db_names;
		this.key_names = options.key_names;
	},
	
	db_name: function(alias) {
		return this.db_names[alias] || alias;
	},

	small: function(url) {
		var tmp = url.split('.');
		return tmp[1] || tmp[0];
	},


	isLoaded: function(id) {
		return id && !!this.id2url[id];
	},
	
	getId: function(id_or_url) {
		if(typeof id_or_url === 'number' || $.isNumeric(id_or_url)) {
			return ~~id_or_url;
		} else if(typeof id_or_url === 'string') {
			var tmpArr = id_or_url.split('.');
			if(tmpArr.length === 1) {
				id_or_url = this.db_name('ff') + '.' + tmpArr[0];
			} else {
				id_or_url = this.db_name(tmpArr[0]) + '.' + tmpArr[1];
			}
			if(this.url2id[id_or_url]) {
				return this.url2id[id_or_url];
			}
		}
		return null;
	},
	
	get: function(id_or_url) {
		var id = this.getId(id_or_url);
		if(this.isLoaded(id) && this.data[id]) {
			return this.data[id];
		} else {
			throw new Error( 'Tbl '+id_or_url+' not loaded' );
		}
	},

	set: function(def) {
		if($.isPlainObject(def) && def.tbl) {
			this.data[def.tbl.id] = def;
			this.id2url[def.tbl.id] = def.tbl.url;
			this.url2id[def.tbl.url] = def.tbl.id;
		} else {
			throw new Error( 'Wrong def for tbl' );
		}
	}

};
