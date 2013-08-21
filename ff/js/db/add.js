///////////////////////////////////////////////
//               DB Add
///////////////////////////////////////////////
fastFace.db._add = function(cls) {
	try {

		if(typeof fastFace.tbl.get(cls).add !== 'object') {
			throw new Error('Function [<b>add</b>] not exists for class [<b>'+cls+'</b>].');
		}

		var i,
			tmpObj = null,
			formArg = [],
			d = fastFace.dict,
			d_c = fastFace.dict.cache,
			clsDef = fastFace.tbl.get(cls),
			dbDef = clsDef.db || {},
			colsDef = dbDef.cols || {},
			addDef = clsDef.add || {},
			dfltVal = addDef.dflt || {},
			getDef = clsDef.get || {},
			sel = addDef.col || getDef.col || [],
			buttons = {},
			dlgId = fastFace.render.uid('fnd_dlg_');

		var closeFn = function() { $('#'+dlgId).dialog('close'); };

		var saveFn = function() {
			try {
				var $dlg = $('#'+dlgId), newItem = {};

				$dlg.find('[name=add_fld]').each(function() {
					newItem[$(this).attr('id')] = $(this).val();
				});

				fastFace.db.add(cls, {tbl: cls, row: true, assoc: true, data: newItem}, function(resultObj) {
					closeFn();
					fastFace.db.form.dlg(cls, resultObj);
				});
			} catch(err) {fastFace.err.js(err);}
		};

		buttons[d.val('save')] = saveFn;
		buttons[d.val('close')] = closeFn;

		
		var fldsArg = [], closed = false;
		for(i=0; i<sel.length; i++) {
			var colName = sel[i], startEdit = null, click = null;
				if($.isPlainObject(colsDef[colName])) {
					var fldData = null,
						colDef = $.extend({}, colsDef[colName], {field: colName}),
						lbl = colDef.lng ?
							d.val(colDef.lbl || colDef.lng.name) + ' ' + d_c['in_'+colDef.lng.lng]
							:
							d.val(colDef.lbl || (colDef.fk ? (colDef.cache || (colDef.arr ? colName : colDef.fk.cls)) : colName)),
						val = dfltVal[colName] || null,
						disabled = val ? true : false ;

					if( colDef.form.skip || colDef.hide ||
							$.inArray(colName, dbDef.rk || []) >= 0 ||
							colDef.type === 'html'
					) { continue; }

					if(colDef.form.grp) {
						if(fldsArg.length) { formArg.push(['FIELDSET', {addClass: 'ui-widget ui-widget-content ui-corner-all'}, fldsArg]); }
						closed = colDef.form.grp.closed || false;
						fldsArg = [['LEGEND', {addClass: 'ui-widget ui-widget-header ui-corner-all', collapse2: {closed: colDef.form.grp.closed || false}, html: d.val(colDef.form.grp.lbl)}, null]];
					}

					if(colDef.type === 'bool') {

						fldData = ['SELECT', {attr: {id: colName, name: 'add_fld', disabled:disabled}, html: fastFace.tbl.get('cache_yes_no').getOpt(false), val: val}, null];

					} else if ( colDef.fk ) {

						var fk = colDef.fk || {};

						fldData = ['SELECT', {attr: $.extend({id:colName, name:'add_fld', disabled:disabled}, colDef.type === 'set' ? {multiple:true, size:18} : {} ),
							html: fastFace.tbl.get(fk.cls).getOpt(false), val:val}, null];

					} else if ( colDef.type === 'text' ) {

						fldData = ['TEXTAREA', {addClass: 'text_frm '+colDef.dir, css: {width: '60%'}, attr: {type:'text', id:colName, name:'add_fld', disabled:disabled}, val:val}, null];

					} else {

						tmpObj = $.extend({addClass: 'frm_cell '+colDef.dir, attr: {type:'text', id:colName, name:'add_fld', disabled:disabled}, val:val}, ((colDef.type === 'date'?{datepicker:{}}:colDef.type === 'datetime'?{datetimepicker:{autoSize: false}}:colDef.type === 'time'?{timepicker:{}}:{})));
						fldData = ['INPUT', tmpObj, null];

					}

					fldsArg.push(
						['DIV', {css:{'border-bottom': '1px dotted #666666', 'display':closed?'none':'block'}}, [
							['LABEL', {addClass: 'lbl_200', html: lbl, attr: {'for':'add_'+colName}}, null],
							fldData
							//['BDO', {html:colName, addClass: 'frm_cell '+colDef.dir}, null]
						]]
					);

				} else {
					fastFace.msg.err('Filter colName['+colName+'] not found');
				}
		}
		formArg.push(['FIELDSET', {addClass: 'ui-widget ui-widget-content ui-corner-all'}, fldsArg]);

		fastFace.render.dlg(
			{attr: {id: dlgId}, options: {title: d.val(addDef.lbl || cls), width: '80%', buttons: buttons}},
			null,
			formArg
		);

	} catch(err) {fastFace.err.js(err);}
};
