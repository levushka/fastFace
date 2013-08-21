///////////////////////////////////////////////
//               DB Grid
///////////////////////////////////////////////
fastFace.db.grid = {

	options: {autoEdit: false, editable: false, defaultColumnWidth: 100, enableAddRow: false, enableCellNavigation: true, enableColumnReorder: true, forceFitColumns: true, autoHeight: false},
	
	colOpt: {sortable: true, resize: true, minWidth: 30, width: 100, maxWidth: 1000},
	iconOpt: {resize: false, noAdd: true, minWidth: 35, width: 35, maxWidth: 35, colView: fastFace.render.iconView},
	ordOpt: {resize: false, noAdd: true, dir: 'ltr', minWidth: 50, width: 50, maxWidth: 50},
	boolOpt: {resize: false, noAdd: true, minWidth: 35, width: 35, maxWidth: 35},
	textOpt: {noAdd: true, minWidth: 30, width: 300, maxWidth: 2000},
	htmlOpt: {resize: false, noAdd: true, dir: 'ltr', minWidth: 35, width: 35, maxWidth: 35},
	intOpt: {width: 40, maxWidth: 100},
	decimalOpt: {width: 80, maxWidth: 120},
	enumOpt: {width: 100, maxWidth: 500},
	setOpt: {noAdd: true, width: 100, maxWidth: 500},
	timeOpt: {noAdd: true, width: 40, maxWidth: 80},
	dateOpt: {noAdd: true, width: 90, maxWidth: 100},
	datetimeOpt: {noAdd: true, width: 150, maxWidth: 160},
	unixtimeOpt: {noAdd: true, width: 150, maxWidth: 160},

	prepare: function(resultObj, options) {
		var i,
			d = fastFace.dict,
			arg = resultObj.arg || {},
			def = fastFace.tbl.get(arg.tbl),
			tbl = def.tbl,
			cols = def.cols,
			keys = def.keys,
			fns = def.fns,
			get = fns.get,
			sel = arg.SELECT || get.cols;
			
		resultObj.showHeader = true;
		resultObj.showTotal = true;
		resultObj.columns = [];
		
		for(i=0; i<sel.length; i++) {
			var colId = sel[i],
				colDef = $.extend({},
					this.colOpt,
					this[cols[colId].type+'Opt'] || {},
					cols[colId].ord ? this.ordOpt : this[(cols[colId].grid.fmt || cols[colId].fmt)+'Opt'] || {},
					cols[colId],
					cols[colId].grid
				);
			resultObj.columns.push(colDef);
		}
		
		if(fns.del) {
			for(i=resultObj.data.length; i--;) { resultObj.data[i].unshift(0); }
			resultObj.columns.unshift($.extend({id: 'del_btn', lbl: d.val('del'), icon: 'trash'}, this.colOpt, this.iconOpt, {sortable:false, colView: function() {return fastFace.render.iconView('trash');}}));
		}

		if(!arg.skipView) {
			for(i=resultObj.data.length; i--;) { resultObj.data[i].unshift(0); }
			resultObj.columns.unshift($.extend({id: 'form_btn', lbl: d.val(fns.upd ? 'edit' : 'view'), icon: (fns.upd ? 'pencil' : 'zoomin')}, this.colOpt, this.iconOpt, {sortable:false, colView: (function(icon){return function() {return fastFace.render.iconView(icon);};})(fns.upd ? 'pencil' : 'zoomin')}));
		}
		
		return resultObj;
	},
	
	
	
		
	getGrid: function(resultObj, dlgId) {

		try {
			
//      // GRID HIDE
//      if(!empty($col_def['grid']['hide'])) {
//        $keys['grid_hide'][] = $col_name;
//      }

			
//      // GRID
//      if(empty($col_def['grid']['skip']) && empty($col_def['sub']) && empty($col_def['hide'])) {
//        $keys['grid'][] = $col_name;
//      }

			var cls = resultObj.cls, clsDef = fastFace.tbl.get(cls),
				dbDef = clsDef.db || {},
				pk = (dbDef.pk || [''])[0],
				i,
				d = fastFace.dict,
				d_c = fastFace.dict.cache,
				colsDef = dbDef.cols || {},
				getDef = clsDef.get,
				getArg = resultObj.arg,
				sel = getArg.sel || getDef.col,
				addCol = clsDef.add && clsDef.add.col ? clsDef.add.col : sel,
				updCol = clsDef.upd && clsDef.upd.col ? clsDef.upd.col : sel,
				assoc = getArg.assoc || false,
				data = resultObj.data,
				act = dbDef.act || [],
				actColName = act.length ? act[0] : '',
				actColId = assoc ? actColName : $.inArray(actColName, sel),
				notActive = actColId >= 0 && data[actColId] === false,
				pk_arr = dbDef.pk || [],
				pk_col = assoc ? pk_arr : $.map(pk_arr, function(val, key) { return $.inArray(val, sel); }),
				row_id = $.grepArr(pk_col, data),
				columns = [],
				frmLbl = d.val(cls),
				formArg = [],
				formId = fastFace.render.uid('frm_');

			var addButton = function(subCls, subId, fldsetId) {
				return function(event) {
					event.stopImmediatePropagation();
					fastFace.db.add(subCls, {tbl: subCls, row: true, sub: true, data: subId}, function(resultObj) {
						var $fldset = $('#'+fldsetId);
						var formArr = fastFace.db.form.getForm(resultObj, dlgId);
						fastFace.render.show([formArr], $fldset.children().last(), 'insertBefore');
					});
					return false;
				};
			};
			
			var fldsArg = [],
				closed = false,
				lnId = null,
				lnArr = [];
			
			for(i=0; i<sel.length; i++) {
				
				var colName = sel[i],
					colId = assoc ? colName : i,
					isReadOnly = colId === actColId ? false : ( (notActive || $.inArray(colName, dbDef.rk || []) >= 0) ? true : false );
				
				if(!$.isPlainObject(colsDef[colName])) {
					fastFace.msg.err('Filter colName['+colName+'] not found');
					return;
				}
				
				var colDef = $.extend({}, colsDef[colName], colsDef[colName].form || {}, {field: colId}),
					val = data[colId],
					lbl = colDef.lng ?
						d.val(colDef.lbl || colDef.lng.name) + ' ' + d_c['in_'+colDef.lng.lng]
						:
						d.val(colDef.lbl || (colDef.fk ? (colDef.cache || (colDef.arr ? colName : colDef.fk.cls)) : colName));
					
				if(colDef.hide || colDef.skip || (colDef.sub && notActive)) {
					continue;
				}

				if(colDef.sub) {
					closed = false;
					lnId = null;
					lnArr = [];

					if(fldsArg.length) {
						formArg.push(['FIELDSET', {addClass: 'ui-widget ui-widget-content ui-corner-all'}, fldsArg]);
						fldsArg = [];
					}

					var subDef = colDef.sub,
						subCls = subDef.cls,
						subClsDef = fastFace.tbl.get(subCls),
						subArg = [['LEGEND', {addClass: (notActive ? 'ui-state-disabled ' : '') + (colDef.sub_frm ? 'sub-frm ' : '') + 'ui-widget ui-widget-header ui-corner-all', html: d.val(subCls)}, null]],
						subId = $.makeObj($.vals(subDef.sk), $.grepArr(assoc ? $.keys(subDef.sk) : $.placeInArr($.keys(subDef.sk), sel), data)),
						fldsetId = fastFace.render.uid('fldset_');
						
					if(colDef.sub_frm) {
						if(val && val.data && val.data.length > 0) {
							for(var j=0; j<val.data.length; j++) {
								subArg.push(fastFace.db.form.getForm({cls: val.cls, fn: val.fn, err: val.err, arg: val.arg, data: val.data[j]}, dlgId));
							}
						}
						if(subClsDef && subClsDef.add) {
							subArg.push(
								[
									'DIV',
									{
										css: {'border-bottom': '1px dotted #666666', 'display':closed?'none':'block'}
									},
									[['BUTTON', {html: fastFace.dict.cache.add + ' ' + d.val(subCls), button: {icons: $.makeObj([fastFace.lang.ico], ['ui-icon-document'])}, click: addButton(subCls, subId, fldsetId)}, null]]
								]
							);
						}
					} else if(val && val.data) {
						val.sub = {sub_id: subId};
						val.grid = colDef.grid || {};
						if(!val.grid.attr && !val.grid.options) {
							val.grid.options = {autoHeight: true, forceFitColumns: false};
						}
						subArg.push(['grid', val, []]);
					}
					

					formArg.push(['FIELDSET', { attr: {id: fldsetId}, addClass: (colDef.sub_frm ? 'sub-frm ' : '') + 'ui-widget ui-widget-content ui-corner-all', collapse: {closed: notActive || subDef.closed || false}}, subArg]);
					
				} else {
					
					columns[colId] = colDef;

					if(colDef.grp) {
						if(fldsArg.length) {
							formArg.push(['FIELDSET', {addClass: 'ui-widget ui-widget-content ui-corner-all'}, fldsArg]);
						}
						lnId = null;
						lnArr = [];
						closed = colDef.grp.closed || false;
						fldsArg = !colDef.grp.lbl ? [] : [['LEGEND', {addClass: (notActive ? 'ui-state-disabled ' : '') + 'ui-widget ui-widget-header ui-corner-all', collapse2: {closed: colDef.grp.closed || false}, html: d.val(colDef.grp.lbl)}, null]];
					}
					
					if(colDef.ln && lnId !== colDef.ln) {
						lnId = colDef.ln;
						lnArr = [];
					}

					lnArr.push(['LABEL', {attr: $.extend({title: lbl}, (colDef.editor && !isReadOnly) ? {'for': colId} : {}), addClass: 'lbl_100 '+ (notActive && actColName === colName ? ' bold ' : '') + (isReadOnly ? 'ui-state-disabled' : (colDef.editor ? 'for' : '' )), html: lbl, tooltip: {}}, null]);

					if(colName === pk && !fldsArg.length) {
						closed = false;
						lnId = null;
						lnArr = [];
						fldsArg = [];
						frmLbl += ' : ' + lbl + ' ' + val;
						//fldsArg = [['LEGEND', {addClass: 'ui-widget ui-widget-header ui-corner-all', collapse2: {}, html: d.val(cls) + ' : ' + frmLbl}, null]];
					} else {
						
						lnArr.push(
							[
								'SPAN',
								{
									attr: (colDef.editor && !isReadOnly) ? {
										id: cls+'_'+colName,
										'for': colId
									} : {},
									html: colDef.formatter ? colDef.formatter(null,null, val, colDef, data) : val,
									addClass: colDef.dir+(isReadOnly ? ' ui-state-disabled ' : ' for ')+(colDef.fmt || colDef.type)+'Frm'
								},
								null
							]
						);
						
					}
					
					if(lnId) {
						if( i+1 >= sel.length || !colsDef[sel[i+1]].form || colsDef[sel[i+1]].form.ln !== lnId ) {
							lnId = null;
						} else if(lnArr.length) {
							lnArr.push(['LABEL', {addClass: 'lbl_50', html: ''}, null]);
						}
					}
					
					if(!lnId && lnArr.length) {
						fldsArg.push(
							['DIV', {css:{'border-bottom': '1px dotted #666666', 'display':closed?'none':'block'}},
								lnArr
							]
						);
						lnArr = [];
					}
					
				}
			}
			
			if(fldsArg.length) {
				formArg.push(['FIELDSET', {addClass: 'ui-widget ui-widget-content ui-corner-all'}, fldsArg]);
			}
			
			var buttons = [];
//      if(clsDef.add) {
//        buttons.push(['BUTTON', {html: fastFace.dict.cache.copy + ' ' + d.val(cls) + ' : ' + frmLbl, button: {icons: $.makeObj([fastFace.lang.ico], ['ui-icon-copy'])}, click: function(event) {
//          event.stopImmediatePropagation();
//          fastFace.db.cpy(cls, row_id, {row: true, sub: true}, function(resultObj) {
//            var $form = $('#'+formId);
//            var formArr = fastFace.db.form.getForm(resultObj, dlgId);
//            fastFace.render.show([formArr], $form, 'insertAfter');
//          });
//          return false;
//        }}, null]);
//      }

			if(clsDef.del) {
				buttons.push(['BUTTON', {html: fastFace.dict.cache.del+ ' ' + d.val(cls) + ' : ' + frmLbl, button: {icons: $.makeObj([fastFace.lang.ico], ['ui-icon-trash'])}, click: function(event) {
					event.stopImmediatePropagation();
					fastFace.db.del(cls, row_id, function() {
						fastFace.msg.info(sprintf(fastFace.dict.cache.del_ok, fastFace.dict.val([cls, fastFace.tbl.small(cls)]), row_id[0]));
						if(dlgId) {
							$('#'+dlgId).dialog('close');
						} else {
							$('#'+formId).remove();
						}
					});
					return false;
				}}, null]);
			}
			
			if(buttons.length > 0) {
				formArg.push(
					[
						'DIV',
						{
							css: {'border-bottom': '1px dotted #666666', 'display':closed?'none':'block'}
						},
						buttons
					]
				);
			}
			
			formArg.unshift(['LEGEND', {addClass: (notActive ? 'ui-state-disabled ' : '') + 'frm ui-widget ui-widget-header ui-corner-all', html: frmLbl}, null]);
			
			return [
				'FIELDSET',
				{
					attr: {
						id: formId,
						cls: cls
					},
					collapse: {closed: notActive || false},
					addClass: 'frm ui-widget ui-widget-content ui-corner-all',
					set_fn: function($tag, dataObj) {
						$tag.on('click', '.for', function (event) {
							fastFace.db.form.start(event, cls, data, columns, row_id);
							event.stopImmediatePropagation();
						}).on('click', function (event) {
							fastFace.db.form.send(fastFace.db.form.current);
							event.stopImmediatePropagation();
						}).on('keydown', function (event) {
							if(event.which === $.ui.keyCode.ENTER && event.ctrlKey) {
								fastFace.db.form.send(fastFace.db.form.current);
							} else if(event.which === $.ui.keyCode.ESCAPE) {
								fastFace.db.form.end(fastFace.db.form.current);
							}
							event.stopImmediatePropagation();
						});
					}
				},
				formArg
			];

		} catch(err) {fastFace.err.js(err);}
	},
	
	dlg: function(resultObj, onCloseFn) {
		try {
			var d = fastFace.dict, dlgId = fastFace.render.uid('grid_dlg_');
			
			fastFace.render.dlg(
				{
					attr: {id: dlgId},
					options: {
						title: fastFace.tbl.get(resultObj.arg.tbl).tbl.url,
						closeOnEscape: false,
						width: '80%',
						height: $(window).height()-50,
						buttons: [
							{text: d.val('close'), icons: $.makeObj([fastFace.lang.ico], ['ui-icon-cancel']), click: function() { $('#'+dlgId).dialog('close'); }}
						],
						beforeClose: function() {
							fastFace.sync.start();
							fastFace.db.form.send(fastFace.db.form.current);
							if(typeof onCloseFn === 'function') {
								onCloseFn();
							}
							fastFace.sync.end();
						}
					}
				},
				null,
				[fastFace.db.grid.getGrid(resultObj, dlgId)]
			);
			
		} catch(err) {fastFace.err.js(err);}
	},
	
	bg: function(resultObj) {
		fastFace.render.bg(
			{options: {title: fastFace.tbl.get(resultObj.arg.tbl).tbl.url}},
		null,
		[['grid', this.prepare(resultObj), null]]
		);
	}
	
};