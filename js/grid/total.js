$.extend(fastFace.grid, {
	
	totalObjFn: function() {
		return {
			sum: 0,
			count: 0,
			hashCount: {}
		};
	},
	
	totalRender: function(grid) {
		var html = '';
		for(var col=0; col<grid.columns.length; col++) {
			if(!grid.hideGroups || $.inArray(col, grid.groupCols) === -1) {
				html += '<TD>'+grid.columns[col].colTotal(grid.columns[col].totalObj)+'</TD>';
			}
		}
		return '<TR class="ui-widget ui-widget-header ui-corner-all ff-grid-total">' + html + '</TR>';
	},

	colSum: function(row, col, totalObj, rowData) {
		if(typeof rowData[col] === 'number') {
			totalObj.sum += rowData[col];
		}
	},
	
	colCount: function(row, col, totalObj, rowData) {
		if(rowData[col]) {
			totalObj.count++;
		}
	},
	
	colHashCount: function(row, col, totalObj, rowData) {
		if(!totalObj.hashCount[rowData[col]]) {
			totalObj.hashCount[rowData[col]] = 0;
		}
		totalObj.hashCount[rowData[col]]++;
	},
	
	colTotalCount: function(totalObj) {
		return totalObj.count;
	},
	
	colTotalHashCount: function(totalObj) {
		return totalObj.count;
	},
	
	colTotalSum: function(totalObj) {
		return totalObj.sum;
	},
	
	colTotalAvg: function(totalObj) {
		return totalObj.sum/totalObj.count;
	}
			
});
