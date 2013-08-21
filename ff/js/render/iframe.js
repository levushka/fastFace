///////////////////////////////////////////////
//               Renderer IFrame panel
///////////////////////////////////////////////

fastFace.render.ifrm = function (dataObj, $context, rndArr) {
	this.show(rndArr, this.tag($.extend({
		tag: 'IFRAME',
		width: $context.width() - 50,
		height: $context.height() - 50,
		bind: {
			'resizeStart': function() { $(this).hide(); },
			'resizeStop': function() { $(this).width((dataObj.width || $context.width()) - 0).height((dataObj.height || $context.height()) - 5).show(); }
		}
	}, dataObj), $context, rndArr));
};
