///////////////////////////////////////////////
//               DB load tbl def
///////////////////////////////////////////////
fastFace.tbl.load = function(id, def) {
	
	try {
		var i,
		d = fastFace.dict,
		d_c = fastFace.dict.cache,
		txt_col = [],
		tbl = def.tbl,
		cols = def.cols,
		keys = {col2id:{}, id2col:{}},
		fns = def.fns,
		addCol = (fns.add && fns.add.cols) ? fns.add.cols : [],
		updCol = (fns.upd && fns.upd.cols) ? fns.upd.cols : [],
		rtlNames = ['he', '_he', 'heb', '_heb'],
		ltrNames = ['ru', '_ru', 'rus', '_rus', 'en', '_en', 'eng', '_eng'];

		fns.get = fns.get || {};

		$.each(fastFace.tbl.key_names, function( key, val ) {
			keys[val] = [];
		});
		
		$.each(cols, function( colId, colDef ) {
			colId = ~~colId;
			var colName = colDef.name,
				len = colName.length,
				subNames = [colName, colName.substring(len-3), colName.substring(len-3)];
				
			colDef.dir = (colDef.dir || (
				(colDef.fk || colDef.arr || colDef.sk || colDef.ik) ? fastFace.lang.dir : (
					$.inArray(colDef.type, ['bool', 'int', 'decimal', 'time', 'date', 'datetime', 'unixtime']) >= 0 ? 'ltr' : (
						$.oneOfInArr(subNames, rtlNames) ? 'rtl' : (
							$.oneOfInArr(subNames, ltrNames) ? 'ltr' : fastFace.lang.dir
						)
					)
				)
			));

			colDef.lbl = ((colDef.lng ?
				d.val(colDef.lbl || colDef.lng.name) + ' ' + d_c['in_'+colDef.lng.lng]
				:
				d.val(colDef.lbl || (colDef.fk ? (colDef.cache || (colDef.arr ? colName : colDef.fk.cls)) : colName))
				)|| '').replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/'/g, '&#39;').replace(/</g, '&lt;').replace(/>/g, '&gt;');

			if(colDef.pk) {
				colDef.icon = 'key';
			}
				
			colDef.form = colDef.form || {};
			colDef.grid = colDef.grid || {};

			colDef.form.formatter = fastFace.render._getView(colDef.form.fmtFn || colDef.fmtFn || null, colDef.form.fmt || colDef.fmt || colDef.type);
			colDef.form.editor = null;
			colDef.grid.colView = fastFace.render._getView(colDef.grid.fmtFn || colDef.fmtFn || null, colDef.grid.fmt || colDef.fmt || colDef.type, 'Grid');
			colDef.grid.colRenderEdit = null;

			if($.inArray(colName, fns.get.cols || [colName]) >= 0 && (colDef.type === 'char' || colDef.type === 'text')) {
				txt_col.push(colName);
			}

			keys.col2id[colName] = colId;
			keys.id2col[colId] = colName;
			$.each(fastFace.tbl.key_names, function( key_id, key_name ) {
				if(typeof colDef[key_name] !== 'undefined') {
					keys[key_name].push(colId);
				}
			});
		});
		

		keys.ro = $.unique($.merge(keys.pk, keys.sk, keys.ro));
		def.keys = keys;
		
		$.each(cols, function( colId, colDef ) {
			colId = ~~colId;
			if($.inArray(colId, keys.ro || []) === -1 && $.inArray(colId, addCol) >=0 && $.inArray(colId, updCol) >=0) {
				colDef.form.editor = fastFace.render._getEdt(colDef.form.edtFn || colDef.edtFn || null, colDef.form.edt || colDef.edt || colDef.form.fmt || colDef.fmt || colDef.type);
				colDef.grid.colRenderEdit = fastFace.render._getEdt(colDef.grid.edtFn || colDef.edtFn || null, colDef.grid.edt || colDef.edt || colDef.grid.fmt || colDef.fmt || colDef.type, 'Grid');
			}
		});
		
		fns.get.lim = fns.get.lim || 2000;
		fns.fnd = fns.fnd || {};
		fns.fnd.txt_col = fns.fnd.txt_col || txt_col;

		//fastFace.tbl.loadClsFn(id, def, subCls.length === 0);

		fastFace.tbl.set(def);

	} catch(err) {fastFace.err.js(err);}

};
