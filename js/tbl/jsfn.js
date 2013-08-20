///////////////////////////////////////////////
//               DB load tbl def
///////////////////////////////////////////////
fastFace.tbl.loadClsFn = function(tbl, tblDef, subClsLoaded) {
	
	try {

		if(!tblDef.data && !tblDef.js_def) {
			return;
		}

		if(!tblDef.js_def) {
			tblDef.js_def = {};
		}
		
		var js_def = tblDef.js_def,
			dbDef = tblDef.db || {},
			colsDef = dbDef.cols || {},
			sel = (js_def.get || {}).sel || (tblDef.get || {}).col || dbDef.cols || [],
			
			isTable = js_def.mod ? true : false,
			mod = js_def.mod || 7,

			ttl_val = js_def.edt_val || 'this.data[id][1]',
			edt_val = js_def.edt_val || 'this.data[id][1]',
			fmt_val = js_def.fmt_val || edt_val,
			grp_val = js_def.grp_val || 'this.data[id][3]',

			grp_col_name = js_def.grp_col || null,
			fltr_col_name = js_def.fltr_col || null,
			fltr_col_id = 0;

		if(grp_col_name && colsDef[grp_col_name]) {
			var grp_col_id = $.inArray(grp_col_name, sel);
			if(colsDef[grp_col_name].fk) {
				grp_val = 'fastFace.tbl.get("'+colsDef[grp_col_name].fk.tbl_url+'").getVal(this.data[id]['+grp_col_id+'])';
			} else {
				grp_val = 'this.data[id]['+grp_col_id+']';
			}
			tblDef.data_grp = true;
		}

		if(fltr_col_name && colsDef[fltr_col_name]) {
			fltr_col_id = $.inArray(fltr_col_name, sel);
		}

		var tbl_fn = $.extend(
			{
				getTtlByID: 'function(id) { return '+ttl_val+'; }',
				
				getTtlByData: function(ttl, sel, data) {
				},

				getLblByID: 'function(id) { return '+edt_val+'; }',
				
				getLblWithGrpByID: 'function(id) { return '+fmt_val+'; }',

				getGrpLblByID: 'function(id) { return '+grp_val+'; }',

				getVal: function(val) {
					var dataLen = this.data.length;
					for(var i=0; i<dataLen; i++) {
						if(val === this.data[i][0]) {
							return this.getLblWithGrpByID(i);
						}
					}
					if(fastFace.err.isDebug) {
						console.error('getVal', tbl, '('+typeof(val)+')['+val+']', dataLen ? '('+typeof(this.data[0][0])+')['+this.data[0][0]+']' : null, this);
					}
					return val;
				},
				
				getVals: function(vals) {
					var i, res = [],
						ids = $.isArray(vals) ? vals : (vals || '').split(','),
						idsLen = ids.length,
						dataLen = this.data.length;
					
					if(dataLen && idsLen && typeof(ids[0]) !== typeof(this.data[0][0])) {
						var type = typeof(this.data[0][0]), isNumb = type === 'number', isBool = type === 'boolean';
						for(i=0; i<idsLen; i++) {
							ids[i] = isNumb ? ~~ids[i] : (isBool ? $.boolV(ids[i]) : ids[i]);
						}
					}
					
					for(i=0; i<dataLen; i++) {
						if(isTable && i % mod === 0 && res.length > 0) {
							res.push('</TR><TR>');
						}
						if($.inArray(this.data[i][0], ids) >= 0) {
							res.push(!isTable ? this.getLblByID(i) : '<TD style="width:20px;"><SPAN class="ui-icon ui-icon-check"></SPAN></TD><TD>'+this.getLblByID(i)+'</TD><TD>&nbsp;</TD>');
						} else if(isTable) {
							res.push('<TD style="width:20px;"><SPAN class="ui-icon ui-icon-radio-on ui-state-disabled">&nbsp;</SPAN></TD><TD class="ui-state-disabled">'+this.getLblByID(i)+'</TD><TD>&nbsp;</TD>');
						}
					}

					if(ids.length > 0 && !res.length) {
						if(fastFace.err.isDebug) {
							console.error('getVals', tbl, ids, dataLen ? '('+typeof(this.data[0][0])+')['+this.data[0][0]+']' : null, this);
						}
						return ids.join(',');
					}

					return (!isTable ? '' : '<TABLE border=0><TR>') + res.join(isTable ? '' : ', ') + (!isTable ? '' : '</TR></TABLE>');
				},
				
				getOpt: function(isAll) {
					var curGrp = null,
						res = (isAll || false) ? ['<OPTION value="">'+fastFace.dict.val('all')+'</OPTION>'] : [],
						dataLen = this.data.length,
						isGrp = this.data_grp || (this.data.length > 0 && this.data[0].length === 4);
					
					for(var i=0; i<dataLen; i++) {
						var opt = this.data[i];
						if(isGrp) {
							if(curGrp !== opt[2]) {
								if(curGrp !== null) { res.push('</OPTGROUP>'); }
								curGrp = opt[2];
								res.push('<OPTGROUP LABEL="'+this.getGrpLblByID(i)+'">');
							}
						}
						res.push('<OPTION value="'+opt[0]+'">'+this.getLblByID(i)+'</OPTION>');
					}
					if(curGrp !== null) {
						res.push('</OPTGROUP>');
					}
					return res.join('');
				}
			},
			js_def.fn || {}
		);

		for(var i in tbl_fn ) {
			if(tbl_fn.hasOwnProperty(i)) {
				if(typeof tbl_fn[i] === 'string') {
					tblDef[i] = eval('('+tbl_fn[i]+')');
				} else {
					tblDef[i] = tbl_fn[i];
				}
			}
		}

//    if(subClsLoaded && js_def.sort && $.isArray(tblDef.data)) {
//      eval('js_def.sortFn = '+js_def.sort);
//      tblDef.data.sort(js_def.sortFn);
//    }

			
	} catch(err) {fastFace.err.js(err);}

};
