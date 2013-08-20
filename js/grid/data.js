$.extend(fastFace.grid, {
	
	dataRender: function(grid) {
		
		grid.rowClass = grid.rowClass || function(row, rowData) {
			return row%2 ? 'ff-grid-row-even' : 'ff-grid-row-odd';
		};
		
		var html = '';
		
		for(var row=0; row<grid.data.length; row++) {
			
			if(grid.showGroup) {
				html += grid.groupCheck(grid, grid.data[row]);
			}
			
			html += '<TR class="ff-grid-row '+grid.rowClass(row, grid.data[row])+'">';
			
			for(var col=0; col<grid.columns.length; col++) {
				if(!grid.hideGroups || $.inArray(col, grid.groupCols) === -1) {
					if(grid.showTotal) {
						grid.columns[col].colTotalRun(row, col, grid.columns[col].totalObj, grid.data[row]);
					}
					html += '<TD>'+grid.columns[col].colRender(row, col, grid.columns[col], grid.data[row])+'</TD>';
				}
			}
			
			html += '</TR>';
			
		}
		
		if(grid.showGroup && grid.showTotal) {
			html += grid.groupTotal(grid, null);
		}
		
		return html;
	}
			
});
