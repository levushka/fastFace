///////////////////////////////////////////////
//               DB Edit
///////////////////////////////////////////////
fastFace.db.edit = {
	
	current: null,
	
	start: function (event, tbl_id, data, columns, row_id) {
		
		var colId = $(event.currentTarget).attr('for'), cur = fastFace.db.edit.current || {tbl_id: null, row_id: [0], colId: null};

		if(cur.tbl_id && (cur.tbl_id !== tbl_id || cur.row_id[0] !== row_id[0] || cur.colId !== colId)) {
			fastFace.db.edit.send(cur);
		}

		if(typeof colId === 'undefined') {
			return;
		}
		
		var c = {
			inProcess: false,
			$cntr: $(event.delegateTarget).find('#'+tbl_id+'_'+colId)
		};
		
		if(c.$cntr.length === 1) {
			if(c.$cntr.data('row') !== cur.row || c.$cntr.data('cell') !== cur.cell) {
				fastFace.db.edit.send(cur);
			} else if(c.$cntr.data('editing')) {
				c.$cntr = null;
				c = null;
				return;
			}
			c.tbl_id = tbl_id;
			c.data = data;
			c.row_id = row_id;
			c.row = c.$cntr.attr('row');
			c.cell = c.$cntr.attr('cell');
			c.colDef = columns[colId];
			c.colId = colId;
			c.val = data[c.row][c.cell];
			c.height = c.$cntr.height();
			c.width = c.$cntr.width();
			c.$cntr.data('editing', true).html('');
			c.editor = new c.colDef.editor(
				{
					column: c.colDef,
					container: c.$cntr.get(0),
					commitChanges: function() { fastFace.db.edit.send(c); },
					cancelChanges: function() { fastFace.db.edit.end(c); },
					position: c.$cntr.position()
				}
			);
			c.editor.loadValue(c.data[c.row]);
			if(c.editor.show) {
				c.editor.show();
			}
			fastFace.db.edit.current = c;
		} else {
			c.$cntr = null;
			c = null;
		}
	},

	end: function(c) {
		if(c && c.tbl_id && !c.inProcess) {
			if(c.editor) {
				c.editor.destroy();
				c.editor = null;
			}
			if(c.$cntr) {
				c.$cntr.data('editing', false).html(c.colDef.formatter ? c.colDef.formatter(null, null, c.val, c.colDef, null) : c.val);
				c.$cntr = c.tbl_id = c.data = c.row_id = c.colDef = c.colId = c.val = null;
			}
			c = null;
		}
	},
	
	send: function(c) {
		if(c && c.tbl_id && !c.inProcess) {
			if(c.editor && c.editor.isValueChanged()) {
				var newVal = c.editor.serializeValue();
				c.editor.destroy();
				c.editor = null;
				if(c.$cntr) {
					c.inProcess = true;
					c.$cntr.html('<DIV style="display: inline-block; padding: 0px 5px 0px 5px; width:'+c.width+'px; height:'+c.height+'px; color: #006600; background-color:#CCFFFF; direction:'+fastFace.lang.dir+'; text-align:'+fastFace.lang.align+'; white-space:nowrap;">'+fastFace.dict.cache.saving+'</DIV>');
					fastFace.db.upd(
						{ tbl:c.tbl_id, row_id:c.row_id, data: $.makeObj([c.colDef.id], [newVal]) },
						function(resultObj) {
							c.inProcess = false;
							if(resultObj.rows === 1) {
								if(c.data) {
									c.data[c.row][c.cell] = newVal;
								}
								c.val = newVal;
								fastFace.db.edit.end(c);
							} else {
								fastFace.msg.err(fastFace.dict.cache.upd_err);
								fastFace.db.edit.end(c);
							}
						},
						function() {
							c.inProcess = false;
							fastFace.msg.err(fastFace.dict.cache.upd_err);
							fastFace.db.edit.end(c);
						}
					);
				}
			} else {
				fastFace.db.edit.end(c);
			}
		}
	}
			
};