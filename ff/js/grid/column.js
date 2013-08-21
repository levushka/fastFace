$.extend(fastFace.grid, {
	
	colRender: function(row, col, colDef, rowData) {
		return '<SPAN'+
			colDef.colData(row, col, colDef, rowData)+
			colDef.colStyle(row, col, colDef, rowData)+
			colDef.colClass(row, col, colDef, rowData)+
			'>'+
			colDef.colView(rowData[col])+
			'</SPAN>';
	},
	
	colData: function(row, col, colDef, rowData) {
		return ' data-row="'+row+'" data-col="'+col+'"';
	},

	colStyle: function(row, col, colDef, rowData) {
		return ' style="width: '+(colDef.width || 40)+'px;"';
	},

	colClass: function(row, col, colDef, rowData) {
		return '';//' class=""';
	},

	colView: function(value) {
		return typeof value === 'string' ? value.replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/'/g, '&#39;').replace(/</g, '&lt;').replace(/>/g, '&gt;') : value;
	}
			
});
