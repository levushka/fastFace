$.extend(fastFace.grid, {
	
	groupTotalObjFn: function(grid, groupCol) {
		var i, col, groupTotal = [];
		for(i=groupCol; i<grid.groupCols; i++) {
			col = grid.groupCols[i];
			groupTotal.push(grid.columns[col].total || this.totalObj());
		}
		return groupTotal;
	},
	
	groupCheck: function(grid, rowData) {
		var groupCol, col, newGroup = false, html = '';
		
		
		for(groupCol=0; groupCol<grid.groupCols; groupCol++) {
			col = grid.groupCols[groupCol];
			if(!grid.lastRow || !rowData || grid.lastRow[col] !== rowData[col]) {
				newGroup = true;
				break;
			}
		}
		
		grid.groupCol = groupCol;


		if(!newGroup) {
			if(rowData && grid.showTotal) {
				for(col=0; col<grid.columns.length; col++) {
					if(!grid.hideGroups || $.inArray(col, grid.groupCols) === -1) {
						grid.columns[col].colTotalRun(null, col, grid.columns[col].totalObj, rowData);
					}
				}
			}
			grid.lastRow = rowData;
			return '';
		} else {
			grid.groupTotalObj = grid.groupTotalObjFn(grid, groupCol) || this.groupTotalObjFn(grid, groupCol);
		}

		if(grid.lastRow) {
			html += grid.groupTotal(grid, rowData);
		}
		
		if(rowData) {
			html += grid.groupHeader(grid, rowData);
		}
		
		grid.lastRow = rowData;
		
		return html;
	},

	groupHeader: function(grid, rowData) {
		var html = [], col;
		for(var i=0; i<grid.groupCols; i++) {
			col = grid.groupCols[i];
			html.push(grid.columns[col].lbl + ' : ' + grid.columns[col].colView(rowData[col]));
		}
		return '<TR class="ui-widget ui-widget-header ui-corner-all ff-grid-group-head"><TD colspan="'+(grid.columns.length)+'">' + html.join(' - ') + '</TD></TR>';
	},

	groupTotal: function(grid, rowData) {
		var html = '';
		for(var col=0; col<grid.columns.length; col++) {
			if(!grid.hideGroups || $.inArray(col, grid.group) === -1) {
				html += '<TD>'+grid.columns[col].colTotal(null)+'</TD>';
			}
		}
		return '<TR class="ui-widget ui-widget-header ui-corner-all ff-grid-group-total">' + html + '</TR>';
	}

});
