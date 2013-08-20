///////////////////////////////////////////////
//               GRID
///////////////////////////////////////////////

fastFace.grid = {
	options: {autoEdit: false, editable: false, defaultColumnWidth: 100, enableAddRow: false, enableCellNavigation: true, enableColumnReorder: true, forceFitColumns: true, autoHeight: false},
	colOpt: {sortable: true, resizable: true, minWidth: 30, width: 100, maxWidth: 1000},
	iconOpt: {resizable: false, cannotTriggerInsert: true, minWidth: 35, width: 35, maxWidth: 35},
	ordOpt: {resizable: false, cannotTriggerInsert: true, cssClass: 'ltr', minWidth: 50, width: 50, maxWidth: 50},
	boolOpt: {resizable: false, cannotTriggerInsert: true, cssClass: 'ltr', minWidth: 35, width: 35, maxWidth: 35},
	textOpt: {cannotTriggerInsert: true, minWidth: 30, width: 300, maxWidth: 2000, formatter: fastFace.fmt.textFmt},
	htmlOpt: {resizable: false, cannotTriggerInsert: true, cssClass: 'ltr', minWidth: 35, width: 35, maxWidth: 35},
	intOpt: {cssClass: 'ltr', width: 40, maxWidth: 100},
	decimalOpt: {cssClass: 'ltr', width: 80, maxWidth: 120},
	enumOpt: {width: 100, maxWidth: 500, formatter: fastFace.fmt.enumFmt},
	setOpt: {cannotTriggerInsert: true, width: 100, maxWidth: 500, formatter: fastFace.fmt.setFmt},
	timeOpt: {cannotTriggerInsert: true, cssClass: 'ltr', width: 40, maxWidth: 80},
	dateOpt: {cannotTriggerInsert: true, cssClass: 'ltr', width: 90, maxWidth: 100},
	datetimeOpt: {cannotTriggerInsert: true, cssClass: 'ltr', width: 150, maxWidth: 160},
	unixtimeOpt: {cannotTriggerInsert: true, cssClass: 'ltr', width: 150, maxWidth: 160},

	getFltrId: function( pk_arr, pk_col, item ) {
		var fltr = [];
		$.each(pk_arr, function( key, val) { fltr.push([ '=', val, item[ pk_col[ key ] ] ]); });
		return [fltr];
	},

	render: function (resultObj, $context, rndArr) {
		var i,
		d = fastFace.dict,
		d_c = fastFace.dict.cache,

		data = resultObj.data || [],

		arg = resultObj.arg || {},
		sub = resultObj.sub || null,
		def = fastFace.tbl.get(arg.tbl),
		tbl = def.tbl,
		cols = def.cols,
		keys = def.keys,
		fns = def.fns,
		get = fns.get,
		gridDef = $.extend({}, fns.grid || {}, resultObj.grid || {}),

		sel = get.cols,
		addRow = fns.add && (!fns.add.grid || (fns.add.grid && !fns.add.grid.skip) ),
		addCol = ( addRow && fns.add.cols ) ? fns.add.cols : sel,
		updCol = ( fns.upd && fns.upd.cols ) ? fns.upd.cols : sel,
		assoc = arg.assoc || false,
		pk_arr = keys.pk || [],
		pk_col = assoc ? pk_arr : $.map(pk_arr, function(val, key) { return $.inArray(val, sel); }),

		field = '',
		columns = [],
		visibleColumns = [],
		options = $.extend({}, this.options, gridDef.options || {}, {editable:!!fns.upd, enableAddRow: addRow}),
		attr = gridDef.attr || {},
		skipView = options.skipView || false,
		autoHeight = options.autoHeight || false,
		edit = {},
		grid,
		addNewRowOk,
		addNewRowErr,
		$grid;

		//      $val['fmt'] = array('fmt'=>'Icon', 'icon'=>'img/icon_txt.gif');
		//      if(!isset($val['fmt'])) { $val['fmt'] = array('fmt'=>'Icon', 'icon'=>'img/icon_html.gif'); }
		/**********************
		*
		*   Define collumns
		*
		***********************/
		for(i=0; i<sel.length; i++) {
			var colId = sel[i], colDef = $.extend({}, cols[colId], cols[colId].grid || {}), colName = colDef.name,
			tmpObj = $.extend(
				{
					cssClass: colDef.dir
				},
				this.colOpt,
				this[(colDef.ord ? 'ord' : (colDef.fmt ? colDef.fmt : colDef.type))+'Opt'] || {},
				colDef,
				colDef.grid || {},
				{
					id: colName,
					field: (assoc) ? colName : ~~i,
					name: colDef.lng ?
						d.val(colDef.lbl || colDef.lng.name) + ' ' + d_c['in_'+colDef.lng.lng]
						:
						d.val(colDef.lbl || (colDef.fk ? (colDef.cache || (colDef.arr ? colName : colDef.fk.tbl)) : colName))
				}
			);
			
			columns.push(tmpObj);

			var nameLen = colName.length,
				isRuCol = colName === 'ru' || colName === 'rus' || colName.substring(nameLen-3) === '_ru' || colName.substring(nameLen-4) === '_rus',
				isHeCol = colName === 'he' || colName === 'heb' || colName.substring(nameLen-3) === '_he' || colName.substring(nameLen-4) === '_heb',
				isEnCol = colName === 'en' || colName === 'eng' || colName.substring(nameLen-3) === '_en' || colName.substring(nameLen-4) === '_eng';

			if($.inArray(colName, keys.pri || []) < 0 &&
			((colDef.grid && colDef.grid.show) || (
			(fastFace.lang.cur === 'he' && !isRuCol && !isEnCol) ||
			(fastFace.lang.cur === 'ru' && !isHeCol && !isEnCol) ||
			(fastFace.lang.cur === 'en' && !isRuCol && !isHeCol)
			))) {
				visibleColumns[ fastFace.lang.rtl ? 'unshift' : 'push' ](tmpObj);
			}
		}

		if(fns.del) {
			field = (assoc)?'del_btn':columns.length;
			for(i=data.length; i--;) { data[i][field] = null; }
			columns.unshift($.extend({id:'del_btn', field:field, name:d.val('del'), formatter: fastFace.fmt.iconGrd, cssClass:'-trash del_btn', sortable:false}, this.colOpt, this.iconOpt));
			if(fns.del.grid && fns.del.grid.show) { visibleColumns[fastFace.lang.rtl ? 'unshift' : 'push'](columns[0]); }
		}

		if(addRow) {
			field = (assoc)?'cpy_btn':columns.length;
			for(i=data.length; i--;) { data[i][field] = null; }
			columns.unshift($.extend({}, {id:'cpy_btn', field:field, name:d.val('copy'), formatter: fastFace.fmt.iconGrd, cssClass:'-copy cpy_btn'}, this.colOpt, this.iconOpt));
			if(fns.add.grid && fns.add.grid.show) { visibleColumns.push(columns[0]); }
		}

		if(!skipView) {
			field = (assoc)?'get_btn':columns.length;
			for(i=data.length; i--;) { data[i][field] = null; }
			columns.unshift($.extend({id:'frm_btn', field:field, name:d.val(options.editable?'edit':'view'), formatter: fastFace.fmt.iconGrd, cssClass:(options.editable?'-pencil':'-zoomin')+' get_btn', sortable:false}, this.colOpt, this.iconOpt));
			visibleColumns[fastFace.lang.rtl ? 'push' : 'unshift'](columns[0]);
		}
		
		$grid = $('<DIV />', $.extend({id: fastFace.render.uid('grid_'), 'class': 'ltr'}, attr))
		.appendTo($context)
		.on({
			'resizeStart': function() {
				$grid.hide();
			},
			'resizeStop': function() {
				$grid.width(attr.width || $context.width());
				if(!autoHeight) {
					$grid.height(attr.height || $context.height());
				}
				$grid.show();
				//grid.resizeCanvas();
				//grid.autosizeColumns();
			},
			'destroyed': function() {
				if(grid !== null) {
					if(data !== null) { data.splice(0,data.length); }
					grid.destroy();
					grid = null;
				}
				if($grid !== null) {
					$grid.off('IMG.form_btn');
					$grid.off('IMG.cpy_btn');
					$grid = null;
				}
			}
		})
		.on('click', 'SPAN.get_btn', function (event) {
			var row = ~~($(event.target).attr('row')), row_id = $.grepArr(pk_col, data[row]);
			fastFace.db.get({
				tbl:   tbl.id,
				sub: true,
				WHERE: [{'=':[keys.pk[0], {val:$.grepArr(pk_col, data[row])[0]}]}]
			});
		})
		.on('click', 'SPAN.del_btn', function (event) {
			var row = ~~($(event.target).attr('row')), row_id = $.grepArr(pk_col, data[row]);
			fastFace.db.del(tbl.id, row_id, function() {
				fastFace.msg.info(sprintf(fastFace.dict.cache.del_ok, fastFace.dict.val([tbl.url, fastFace.tbl.small(tbl.url)]), row_id[0]));
				data.splice(row, 1);
				grid.invalidate();
			});
			return false;
		})
		.on('click', 'SPAN.cpy_btn', function (event) {
			fastFace.db.cpy(tbl.id, $.grepArr(pk_col, data[~~($(event.target).attr('row'))]), {row: true, assoc: assoc, sel: sel}, addNewRowOk, addNewRowErr);
			return false;
		})
		.width(attr.width || $context.width());
		
		if(!autoHeight) {
			$grid.height(attr.height || $context.height());
		}

		grid = new Slick.Grid($grid, data, visibleColumns, options);
		grid.setSelectionModel(new Slick.RowSelectionModel());
		var columnpicker = new Slick.Controls.ColumnPicker(columns, grid, options);
		//ajaxpager = new Slick.Controls.AjaxPager(columns, grid, options);
		
		$grid.find('.slick-header-columns').addClass(fastFace.lang.dir);

		grid.onSort.subscribe(function (e, args) {
			var col = args.sortCol.field, dir = args.sortAsc ? 1 : -1;
			data.sort(function(l, r) {
				return l[col] === r[col] ? 0 : (l[col] < r[col] ? -1 * dir : 1 * dir);
			});
			grid.invalidate();
		});
		grid.onBeforeEditCell.subscribe(function (e, args) {
			edit.id = args.column ? args.column.id : null;
			edit.field = args.column ? args.column.field : null;
			edit.row = args.row || null;
			edit.item = args.item ? $.extend({}, args.item) : null;
		});
		grid.onCellChange.subscribe(function (e, args) {
			var i, curCols = args.grid.getColumns(), curCol = curCols[args.cell], col = curCol.field, row_id = $.grepArr(pk_col, args.item), id = row_id[0], val = args.item[col];
			fastFace.db.upd(
				{ tbl:tbl.id, row_id:row_id, data:$.makeObj([curCol.id], [val]) },
				function(resultObj) {
					if(resultObj.rows === 1) {
						if(curCol.ord) {
							for(i=0;i<data.length;i++) {
								if(data[i][col] >= val && data[i][0] !== id) {
									data[i][col]++;
								}
							}
							data.sort(function(l, r) {
								return l[col] === r[col] ? 0 : (l[col] < r[col] ? -1 : 1);
							});
							for(i=0;i<data.length;i++) {
								data[i][col] = i+1;
							}
							grid.invalidate();
						}
					} else {
						fastFace.msg.err(d_c.upd_err);
						if(edit.row && edit.item) {
							data[edit.row][col] = edit.item[col];
							grid.invalidate();
						}
					}
					edit.item = null;
				},
				function() {
					fastFace.msg.err(d_c.upd_err);
					if(edit.row && edit.item) {
						data[edit.row][edit.field] = edit.item[edit.field];
						grid.invalidate();
					}
					edit.item = null;
				}
			);
		});

		addNewRowOk = function(resultObj) {
			if(fns.del) { resultObj.data.push(null); }
			if(addRow) { resultObj.data.push(null); }
			if(!skipView) { resultObj.data.push(null); }
			data.push(resultObj.data);
			grid.invalidate();
		};
		
		addNewRowErr = function() {
			fastFace.msg.err(sprintf(d_c.add_err, fastFace.dict.val([tbl.url, fastFace.tbl.small(tbl.url)])));
			grid.invalidate();
		};

		grid.onAddNewRow.subscribe(function (e, args) {
			edit.item = null;
			if(args.item[ args.column.field ]) {
				var newObj = $.makeObj( [ args.column.id ], [ args.item[ args.column.field ] ] );
				if ( sub ) { $.extend( newObj, sub.sub_id ); }
				fastFace.db.add(tbl.id, { tbl: tbl.id, row: true, assoc: assoc, sel: sel, data: newObj }, addNewRowOk, addNewRowErr);
			}
		});
	}

};
