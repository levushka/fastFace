///////////////////////////////////////////////
//               Renderer
///////////////////////////////////////////////

fastFace.render = {
	
	_uidCount: 0,
	
	uid: function (prefix) {
		return (prefix || 'uid_') + (++this._uidCount);
	},

	show: function (rndArr, $context, contFn) {
		//try {
			if(!rndArr || typeof rndArr !== 'object' || rndArr === null) {
				return;
			}
			
			contFn = contFn || 'appendTo';
			for(var i=0; i<rndArr.length; i++) {
				var rndObj = rndArr[i];
				if(typeof rndObj === 'object' && rndObj !== null && rndObj.length === 3) {
					var fn = rndObj[0], dataObj = rndObj[1];
					if(typeof fn === 'string') {
						if(typeof this[fn] !== 'function') {
							if(typeof dataObj !== 'object' || dataObj === null) { dataObj = {}; }
							dataObj.tag = fn;
							fn = 'tag';
						}
						this[fn](dataObj, $context, rndObj[2], contFn);
					} else if(typeof fn === 'function') {
						fn(dataObj, $context, rndObj[2], contFn);
					}
				}
			}
		//} catch(err) {fastFace.err.js(err);}
	},

	tag: function (dataObj, $context, rndArr, contFn) {
		if(!dataObj) { dataObj = {}; }
		if(!dataObj.attr) { dataObj.attr = {}; }
		if(!dataObj.attr.id) { dataObj.attr.id = this.uid(dataObj.tag+'_'); }
		contFn = contFn || 'appendTo';

		var $tag =  $('<'+dataObj.tag+' />', dataObj.attr);
		dataObj.attr = null;
		delete dataObj.attr;

		if(dataObj.tag === 'INPUT') { // && (dataObj.attr.type === 'text' || dataObj.attr.type === 'password')
			$tag
			.addClass( 'ui-helper-reset ui-state-default ui-corner-all' )
			.on({
				mouseenter: function() { $( this ).addClass( 'ui-state-hover' ); }, mouseleave: function() { $( this ).removeClass( 'ui-state-hover' ); },
				focus: function() { $( this ).addClass( 'ui-state-focus' ); }, blur: function() { $( this ).removeClass( 'ui-state-focus' ); }
			});
		}
		
		$tag[contFn]($context);

		if(dataObj.tag === 'LABEL') {
			$tag.attr('title', $tag.html());
		}

		if($.inArray(dataObj.tag, ['FORM', 'FIELDSET']) >= 0) {
			$tag.on({
				resizeStart: function(event, ui) { $tag.children().each(function(index, domEle) {$(domEle).triggerHandler('resizeStart');}); },
				resizeStop: function(event, ui) { $tag.children().each(function(index, domEle) {$(domEle).triggerHandler('resizeStop');}); }
			});
		}

		this.show(rndArr, $tag);

		for(var fn in dataObj) {
			if(dataObj.hasOwnProperty(fn)) {
				if(typeof fn === 'string') {
					if(dataObj[fn] !== null) {
						if(typeof $tag[fn] === 'function') {
							$tag[fn](dataObj[fn]);
						} else if(typeof dataObj[fn] === 'function') {
							dataObj[fn]($tag, dataObj);
						}
					}
				} else if(typeof fn === 'function') {
					fn($tag, dataObj[fn]);
				}
			}
		}
	}

};
