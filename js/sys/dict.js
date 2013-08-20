///////////////////////////////////////////////
//               Dictionary
///////////////////////////////////////////////

fastFace.dict = {
	
	cache: {add: '', del: '', upd: '', cpy: '', cancel: '', save: '', saving: '', del_conf: '', cpy_conf: '', not_found: '', not_loaded: '', add_ok: '', upd_ok: '', del_ok: '', get_ok: '', add_err: '', upd_err: '', del_err: '', get_err: '', in_he: '', in_ru: '', in_en: ''},

	data: {},
	
	reInit: function() {
		fastFace.pid.run(
			[
				'ff\\tbl_get::get',
				{
					SELECT:     ['key', fastFace.lang.cur],
					FROM:       ['ff_dict'],
					WHERE:      [{'=':['is_act', {val:1}]}],
					'ORDER BY': ['key']
				}
			],
			function(resultObj) {
				var dict = fastFace.dict, resData = resultObj.data, len = resData.length, tmpData = {}, cache = dict.cache;
				for(var i=0; i<len; i++) {
					tmpData[resData[i][0]] = resData[i][1];
				}
				dict.data = tmpData;
				
				for(var key in cache) {
					if(cache.hasOwnProperty(key)) {
						cache[key] = dict.val(key);
					}
				}
				dict.cache = cache;
			}
		);
	},

	val: function(keys, def) {
		if(typeof keys === 'string') {
			return this.data[keys] || def || keys;
		} else if($.isArray(keys)) {
			for(var key in keys) {
				if(typeof this.data[key] === 'string') {
					return this.data[key];
				}
			}
			return def || keys[0];
		}
	}

};