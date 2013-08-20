$.extend(fastFace.grid, {
	
	headerRender: function(grid) {
		var html = '';
		for(var col=0; col<grid.columns.length; col++) {
			if(!grid.hideGroups || $.inArray(col, grid.groupCols) === -1) {
				html += '<TD>'+grid.columns[col].colHead(col, grid.columns[col])+'</TD>';
			}
		}
		return '<TR class="ui-widget ui-widget-header ui-corner-all ff-grid-head">' + html + '</TR>';
	},
	
	colHead: function(col, colDef) {
		return '<SPAN data-col="'+col+'" title="'+colDef.lbl+'" style="width: '+(colDef.width || 40)+'px;">'+(colDef.icon ? '<span class="ui-icon ui-icon-'+colDef.icon+'" style="display:inline-block"></span>' : colDef.lbl)+'</SPAN>';
	}
			
});
