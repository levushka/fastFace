///////////////////////////////////////////////
//               DB Form
///////////////////////////////////////////////
fastFace.db.form = {
	
	title: function(resultObj) {
		try {
			return fastFace.tbl.get(resultObj.arg.tbl).tbl.url;
		} catch(err) {
			fastFace.err.js(err);
			return 'ERROR getting title';
		}
	},

	line: function() {
		
	},

	lbl: function() {
		
	},

	val: function() {
		
	},
		
	getForm: function(resultObj, dlgId) {

		try {
			
			var data = resultObj.data,
				arg = resultObj.arg,
				
				def = fastFace.tbl.get(arg.tbl),
				tbl = def.tbl,
				cols = def.cols,
				keys = def.keys,
				fns = def.fns,
				get = fns.get,
				
				i,
				d = fastFace.dict,
				d_c = fastFace.dict.cache,
				getDef = def.get,
				sel = arg.SELECT,
				addCol = fns.add && fns.add.cols ? fns.add.cols : [],
				updCol = fns.upd && fns.upd.cols ? fns.upd.cols : [],
				
				assoc = arg.assoc || false,
				act = keys.act || [],
				actColName = act.length ? act[0] : '',
				actColId = assoc ? actColName : $.inArray(actColName, sel),
				notActive = actColId >= 0 && data[0][actColId] === false,
				pk_arr = keys.pk || [],
				pk_col = assoc ? pk_arr : $.map(pk_arr, function(val, key) { return $.inArray(val, sel); }),

				row_id = $.grepArr(pk_col, data[0]),
				columns = [],
				frmLbl = 'title',
				formArg = [],
				formId = fastFace.render.uid('form_');

//      var addButton = function(subCls, subId, fldsetId) {
//        return function(event) {
//          event.stopImmediatePropagation();
//          fastFace.db.add(subCls, {tbl: subCls, row: true, sub: true, data: subId}, function(resultObj) {
//            var $fldset = $('#'+fldsetId);
//            var formArr = fastFace.db.form.getForm(resultObj, dlgId);
//            fastFace.render.show([formArr], $fldset.children().last(), 'insertBefore');
//          });
//          return false;
//        };
//      };
			
			var fldsArg = [],
				closed = false,
				lnId = null,
				lnArr = [];
			
			for(i=0; i<sel.length; i++) {
				
				var colId = sel[i],
					colName = cols[colId].name,
					isReadOnly = colId === actColId ? false : ( (notActive || $.inArray(colId, keys.ro || []) >= 0) ? true : false );
				
				var colDef = $.extend({field: assoc ? colName : i}, cols[colId], cols[colId].form || {}),
					val = data[0][assoc ? colName : i],
					lbl = colDef.lng ?
						d.val(colDef.lbl || colDef.lng.name) + ' ' + d_c['in_'+colDef.lng.lng]
						:
						d.val(colDef.lbl || colName);
					
				if(colDef.hide || colDef.skip || (colDef.sub && notActive)) {
					continue;
				}

				if(false && colDef.sub) {
					return;
//          closed = false;
//          lnId = null;
//          lnArr = [];

//          if(fldsArg.length) {
//            formArg.push(['FIELDSET', {addClass: 'ui-widget ui-widget-content ui-corner-all'}, fldsArg]);
//            fldsArg = [];
//          }

//          var subDef = colDef.sub,
//            subCls = subDef.cls,
//            subClsDef = fastFace.tbl.get(subCls),
//            subArg = [['LEGEND', {addClass: (notActive ? 'ui-state-disabled ' : '') + (colDef.sub_frm ? 'sub-frm ' : '') + 'ui-widget ui-widget-header ui-corner-all', html: d.val(subCls)}, null]],
//            subId = $.makeObj($.vals(subDef.sk), $.grepArr(assoc ? $.keys(subDef.sk) : $.placeInArr($.keys(subDef.sk), sel), data)),
//            fldsetId = fastFace.render.uid('fldset_');
//
//          if(colDef.sub_frm) {
//            if(val && val.data && val.data.length > 0) {
//              for(var j=0; j<val.data.length; j++) {
//                subArg.push(fastFace.db.form.getForm({cls: val.cls, fn: val.fn, err: val.err, arg: val.arg, data: val.data[j]}, dlgId));
//              }
//            }
//            if(false && subClsDef && subClsDef.add) {
//              subArg.push(
//                [
//                  'DIV',
//                  {
//                    css: {'border-bottom': '1px dotted #666666', 'display':closed?'none':'block'}
//                  },
//                  [['BUTTON', {html: fastFace.dict.cache.add + ' ' + d.val(subCls), button: {icons: $.makeObj([fastFace.lang.ico], ['ui-icon-document'])}, click: addButton(subCls, subId, fldsetId)}, null]]
//                ]
//              );
//            }
//          } else if(val && val.data) {
//            val.sub = {sub_id: subId};
//            val.grid = colDef.grid || {};
//            if(!val.grid.attr && !val.grid.options) {
//              val.grid.options = {autoHeight: true, forceFitColumns: false};
//            }
//            subArg.push(['grid', val, []]);
//          }
//

//          formArg.push(['FIELDSET', { attr: {id: fldsetId}, addClass: (colDef.sub_frm ? 'sub-frm ' : '') + 'ui-widget ui-widget-content ui-corner-all', collapse: {closed: notActive || subDef.closed || false}}, subArg]);
					
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

					if($.inArray(colId, keys.pk) >= 0 && !fldsArg.length) {
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
										'for': colId,
										id: tbl.id+'_'+colId,
										row: 0,
										cell: i
									} : {},
									html: colDef.formatter ? colDef.formatter(null, null, val, colDef) : val,
									addClass: colDef.dir+(isReadOnly ? ' ui-state-disabled ' : ' for ')+(colDef.form.fmt || colDef.fmt || colDef.type)+'Frm'
								},
								null
							]
						);
						
					}
					
					if(lnId) {
						if( i+1 >= sel.length || !cols[sel[i+1]].form || cols[sel[i+1]].form.ln !== lnId ) {
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
//      if(def.add) {
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

//      if(def.del) {
//        buttons.push(['BUTTON', {html: fastFace.dict.cache.del+ ' ' + d.val(cls) + ' : ' + frmLbl, button: {icons: $.makeObj([fastFace.lang.ico], ['ui-icon-trash'])}, click: function(event) {
//          event.stopImmediatePropagation();
//          fastFace.db.del(cls, row_id, function() {
//            fastFace.msg.info(sprintf(fastFace.dict.cache.del_ok, fastFace.dict.val([cls, fastFace.tbl.small(cls)]), row_id[0]));
//            if(dlgId) {
//              $('#'+dlgId).dialog('close');
//            } else {
//              $('#'+formId).remove();
//            }
//          });
//          return false;
//        }}, null]);
//      }
			
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
						tbl_id: tbl.id
					},
					collapse: {closed: notActive || false},
					addClass: 'frm ui-widget ui-widget-content ui-corner-all',
					set_fn: function($tag, dataObj) {
						$tag.on('click', '.for', function (event) {
							fastFace.db.edit.start(event, tbl.id, data, columns, row_id);
							event.stopImmediatePropagation();
						}).on('click', function (event) {
							fastFace.db.edit.send(fastFace.db.edit.current);
							event.stopImmediatePropagation();
						}).on('keydown', function (event) {
							if(event.which === $.ui.keyCode.ENTER && event.ctrlKey) {
								fastFace.db.edit.send(fastFace.db.edit.current);
							} else if(event.which === $.ui.keyCode.ESCAPE) {
								fastFace.db.edit.end(fastFace.db.edit.current);
							}
							event.stopImmediatePropagation();
						});
					}
				},
				formArg
			];

		} catch(err) {
			fastFace.err.js(err);
		}
	},
	
	dlg: function(resultObj, onCloseFn) {
		try {
			var d = fastFace.dict, dlgId = fastFace.render.uid('form_dlg_');
			
//  {text: fastFace.dict.cache.add + ' ' + d.val(cls), icons: $.makeObj([fastFace.lang.ico], ['ui-icon-cancel']), click: function() { fastFace.db._add(cls); $('#'+dlgId).dialog('close'); }}
			fastFace.render.dlg(
				{
					attr: {id: dlgId},
					options: {
						title: this.title(resultObj),
						closeOnEscape: false,
						width: '80%',
						height: $(window).height()-50,
						buttons: [
							{text: d.val('close'), icons: $.makeObj([fastFace.lang.ico], ['ui-icon-cancel']), click: function() { $('#'+dlgId).dialog('close'); }}
						],
						beforeClose: function() {
							fastFace.sync.start();
							fastFace.db.edit.send(fastFace.db.edit.current);
							if(typeof onCloseFn === 'function') {
								onCloseFn();
							}
							fastFace.sync.end();
						}
					}
				},
				null,
				[fastFace.db.form.getForm(resultObj, dlgId)]
			);
			
		} catch(err) {fastFace.err.js(err);}
	},
	
	bg: function(resultObj) {
		fastFace.render.bg(
			{options: {title: this.title(resultObj)}},
		null,
		[this.getForm(resultObj)]
		);
	}
	
};