$.extend(fastFace.render, {

	_getView: function(rndFn, rnd, rndType) {
		if(typeof rndFn === 'string') {
			return eval('(function() { return function(value) ' + rndFn + '; }())');
		}
		
		return this[rnd] || this[rnd+(rndType || '')+'View'] || this[rnd+'View'] || this.skipView;
	},

	skipView: function(value) {
		return value;
	},

	intView: function(value) {
		return value ? number_format(value) : '';
	},

	decimalView: function(value) {
		return value ? number_format(value, 2) : '';
	},

	iconView: function(value) {
		return '<SPAN class="ui-icon ui-icon-'+value+'" style="cursor: pointer;"></SPAN>';
	},

	buttonViewGrid: function(value) {
		return '<button class="ui-button ui-widget ui-state-default ui-corner-all ui-button-icon-only" role="button" aria-disabled="false"><span class="ui-button-icon-primary ui-icon ui-icon-'+value+'"></span><span class="ui-button-text"></span></button>';
	},
	
	colorView: function(value) {
		return value ? '<SPAN style="background-color:'+value+';">'+value+'</SPAN>' : '';
	},

	boolView: function(value) {
		return value ? '<SPAN class="ui-icon ui-icon-check"></SPAN>' : '<SPAN class="ui-state-disabled ui-icon ui-icon-radio-on"></SPAN>';
	},

	boolViewGrid: function(value) {
		return value ? '<SPAN class="ui-icon ui-icon-check"></SPAN>' : '';
	},

	unixtimeView: function(value) {
		return value ? date('Y-m-d H:i:s', value) : '';
	},

	charView: function(value) {
		return value ? value.replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/'/g, '&#39;').replace(/</g, '&lt;').replace(/>/g, '&gt;') : '';
	},

	textView: function(value) {
		return fastFace.render.charView(value);
	},

	preView: function(value) {
		return fastFace.render.charView(value);
	},

	htmlView: function(value) {
		return value;
	},

	htmlViewGrid: function(value) {
		return value ? '<SPAN class="ui-icon ui-icon-document"></SPAN>' : '';
	},
	
	gmapView: function(value) {
		var zoom = 16, enc = encodeURI(value);
		return value ? '<A href="#" onclick="window.open(\'https://maps.google.com/maps?ie=UTF-8&hl=en&z='+zoom+'&q='+enc+'\', \'_blank\'); if(event && event.stopImmediatePropagation) {event.stopImmediatePropagation();} return false;">'+
			'<IMG border=0 width=310 height=240 src="https://maps.googleapis.com/maps/api/staticmap?language='+fastFace.lang.cur+'&center='+enc+'&zoom='+zoom+'&size=310x240&maptype=roadmap&markers=color:red%7Clabel:%7C'+enc+'&sensor=false&key=AIzaSyAFDXCs752mfRbb8R9Lrt-eYN1Ggd_ent8"/>'+
			'</A>' : '';
	},

	addressView: function(value) {
		return value ? value + ' <SPAN class="ui-icon ui-icon-search link" onclick="window.open(\'https://maps.google.com/maps?ie=UTF-8&hl=en&z=16&q='+encodeURI(value)+'\', \'_blank\'); if(event && event.stopImmediatePropagation) {event.stopImmediatePropagation();} return false;"></SPAN>': '';
	},

	urlView: function(value) {
		return value ? value + ' <SPAN class="ui-icon ui-icon-search link" onclick="window.open(\''+value+'\', \'_blank\'); if(event && event.stopImmediatePropagation) {event.stopImmediatePropagation();} return false;"></SPAN>': '';
	},

	setView: function(value) {
		return fastFace.render.enumView(value);
	},

	enumView: function(value, columnDefinition) {
		var fk = null, fnName = null/* columnDefinition.fk, fnName = columnDefinition.type === 'set' ? 'getVals' : 'getVal'*/;
		return (fk && fastFace.tbl.isLoaded(fk.tbl)) ? fastFace.tbl.get(fk.tbl)[fnName](value) : 'ERROR';
	}

});