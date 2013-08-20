///////////////////////////////////////////////
//               Renderer Background panel
///////////////////////////////////////////////

fastFace.render.bg = function (dataObj, $context, rndArr) {
	var $bg_header = null, $bg_body = null, $bg_footer = null;
	var $bg = fastFace.gui.$bg
	.off('bg_resize')
	.empty()
	.on('bg_resize', function(event, ui) {
		if($bg_body) {
			var $children = $bg_body
			.width($bg.width()-2)
			.height($bg.height()-(($bg_header?$bg_header.outerHeight(true):0)+($bg_footer?$bg_footer.outerHeight(true):0)))
			.children();
			if($children.length) {
				$children.each(function(index, domEle) {$(domEle).triggerHandler('resizeStart');});
				$children.each(function(index, domEle) {$(domEle).triggerHandler('resizeStop');});
			}
		}
		return false;
	});

	if(dataObj.options && dataObj.options.title) {$bg_header = $('<DIV id="bg_header" class="ui-widget ui-widget-header ui-corner-all">'+dataObj.options.title+'</DIV>').appendTo($bg);}
	$bg_body = $('<DIV id="bg_body" />').appendTo($bg);
	if(dataObj.options && (dataObj.options.buttons && !$.isEmptyObject(dataObj.options.buttons)) ) {
		$bg_footer = $('<DIV id="e_bg_buttons" style="float: '+fastFace.lang.alignM+';" />').appendTo($bg);
		var btn = dataObj.options.buttons;
		for(var lbl in btn) {
			if(btn.hasOwnProperty(lbl)) { $('<BUTTON>'+lbl+'</BUTTON>').button({label: lbl}).click(btn[lbl]).appendTo($bg_footer); }
		}
	}
	$bg_body.width($bg.width()).height($bg.height()-(($bg_header?$bg_header.outerHeight(true):0)+($bg_footer?$bg_footer.outerHeight(true):0)));

	this.show(rndArr, $bg_body);

	$bg_body.children().each(function(index, domEle) {$(domEle).triggerHandler('resizeStart');});
	$bg_body.children().each(function(index, domEle) {$(domEle).triggerHandler('resizeStop');});
};