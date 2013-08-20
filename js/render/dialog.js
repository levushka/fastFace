///////////////////////////////////////////////
//               Renderer Dialog panel
///////////////////////////////////////////////

fastFace.render.dlg = function (dataObj, $context, rndArr) {
	var $dlg = $('<DIV />', $.extend({id: this.uid('dlg_'), 'class': fastFace.lang.dir}, dataObj.attr)).appendTo($('BODY'));

	this.show(rndArr, $dlg);

	$dlg.dialog($.extend(
		{
			width: (fastFace.gui.ieVer ? 800 : 'auto'),
			height: $dlg.height() > ($(window).height()-50) ? $(window).height()-50 : 'auto',
			minWidth: 300,
			minHeight: 200,
			maxHeight: $(window).height()-50,
			resizable: true,
			modal: false,
			closeOnEscape: true,
			printText: 'Print',
			fullText: 'Full screen',
//      open: function(event, ui) {$(this).parent().children().children('.ui-dialog-titlebar-close').hide();},
			resizeStart: function(event, ui) {
				$dlg.children().each(function(index, domEle) {
					$(domEle).triggerHandler('resizeStart');
				});
			},
			resizeStop: function(event, ui) {
				$dlg.children().each(function(index, domEle) {
					$(domEle).triggerHandler('resizeStop');
				});
			}
		},
		dataObj.options,
		{
			close: function(event, ui) {
				if(typeof dataObj.options.close === 'function') {
					dataObj.options.close();
				}
				if($dlg) {
					$dlg.dialog('destroy').remove();
				}
			}
		}
	));

	$dlg.scrollTop(0);
	
	$dlg.children().each(function(index, domEle) {
		$(domEle).triggerHandler('resizeStart');
	});
	$dlg.children().each(function(index, domEle) {
		$(domEle).triggerHandler('resizeStop');
	});
};
