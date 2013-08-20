///////////////////////////////////////////////
//               Grid
///////////////////////////////////////////////

fastFace.grid = {
	
	render: function (grid, $context, rndArr) {

		if(grid.showGroup) {
			grid.lastRow = null;
			grid.groupTotalObj = grid.groupTotalObjFn(grid, 0) || this.groupTotalObjFn(grid, 0);
			grid.groupCheck = grid.groupCheck || this.groupCheck;
			grid.groupHeader = grid.groupHeader || this.groupHeader;
			grid.groupTotal = grid.groupTotal || this.groupTotal;
		}
		
		for(var col=0; col<grid.columns.length; col++) {
			if(grid.showHeader && !grid.columns[col].colHead) {
				grid.columns[col].colHead = this.colHead;
			}
			if(grid.showTotal) {
				grid.columns[col].totalObj = grid.columns[col].total || this.totalObjFn();
				grid.columns[col].colTotalRun = grid.columns[col].colTotalRun || this.colCount;
				grid.columns[col].colTotal = grid.columns[col].colTotal || this.colTotalCount;
			}
			if(!grid.columns[col].colRender) {
				grid.columns[col].colRender = this.colRender;
				grid.columns[col].colData = grid.columns[col].colData || this.colData;
				grid.columns[col].colStyle = grid.columns[col].colStyle || this.colStyle;
				grid.columns[col].colClass = grid.columns[col].colClass || this.colClass;
				grid.columns[col].colView = grid.columns[col].colView || this.colView;
			}
		}

		var html = '<DIV id="'+fastFace.render.uid('grid_')+'" class="ff-grid" align="center"><CENTER><TABLE align="center">';
		
		if(grid.title) {
			html += '<TR class="ui-widget ui-widget-header ui-corner-all ff-grid-title"><TD colspan="'+(grid.columns.length)+'">'+ grid.title +'</TD></TR>';
		}

		if(grid.showHeader) {
			html += grid.headerRender ? grid.headerRender(grid) : this.headerRender(grid);
		}

		if(grid.data && grid.data.length > 0) {
			html += grid.dataRender ? grid.dataRender(grid) : this.dataRender(grid);
		}

		if(grid.showTotal) {
			html += grid.totalRender ? grid.totalRender(grid) : this.totalRender(grid);
		}

		html += '</TABLE></CENTER></DIV>';
		$context.html(html);
		$context.find('.ff-grid-head TD SPAN').tooltip();
		
	}
	
};
