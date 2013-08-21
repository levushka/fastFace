///////////////////////////////////////////////
//               ERROR
///////////////////////////////////////////////
fastFace.err = {
	
	cur:0,
	isDebug: true,
	d: 0,
	data:[],
	$DLG: null,
	$BTN: null,

	init: function(isDebug, dCode) {
		this.isDebug=isDebug;
		this.d=dCode;
		//window.onerror = this.js_event;
		$(document).ajaxError(this.ajax);
//    this.load_names(fastFace, "FF", true);
//    if(typeof $ !== 'undefined' && $ !== null) {
//      fastFace.err.load_names($, "$", false);
//      if(typeof $.fn !== 'undefined' && $.fn !== null) fastFace.err.load_names($.fn, "$.fn", false);
//      if(typeof $.ui !== 'undefined' && $.ui !== null) fastFace.err.load_names($.ui, "$.ui", false);
//      if(typeof $.effects !== 'undefined' && $.effects !== null) fastFace.err.load_names($.effects, "$.effects", false);
//      if(typeof $.datepicker !== 'undefined' && $.datepicker !== null) fastFace.err.load_names($.datepicker, "$.datepicker", false);
//      if(typeof $.easing !== 'undefined' && $.easing !== null) fastFace.err.load_names($.easing, "$.easing", false);
//    }
//    if(typeof phpjs !== 'undefined' && phpjs !== null) fastFace.err.load_names(phpjs, "phpjs", false);
//    if(typeof Slick !== 'undefined' && Slick !== null) {
//      fastFace.err.load_names(Slick, "Slick", false);
//      if(typeof Slick.Grid !== 'undefined' && Slick.Grid !== null) fastFace.err.load_names(Slick.Grid, "Slick.Grid", false);
//    }
	},

	init_dialog: function() {
		if($('#e_err_dlg').length === 0) {
			$('BODY').append(''+
				'<DIV id="e_err_dlg" title="Error log" class="ltr ui-helper-hidden">'+
					'<DIV>'+
						'<SPAN class="ui-button ui-button-text-only ui-widget ui-state-default ui-corner-all"><SPAN id="e_err_total" class="ui-button-text">Total errors: 0</SPAN></SPAN>'+
						'<BUTTON icon="seek-start" onclick="fastFace.err.show(0);">first</BUTTON>'+
						'<BUTTON icon="seek-prev" onclick="fastFace.err.show(fastFace.err.cur-1);">prev</BUTTON>'+
						'<BUTTON icon="seek-next" onclick="fastFace.err.show(fastFace.err.cur+1);">next</BUTTON>'+
						'<BUTTON icon="seek-end" onclick="fastFace.err.show(fastFace.err.data.length-1);">last</BUTTON>'+
					'</DIV>'+
					'<TEXTAREA id="e_err_txt" cols=100 rows=10 dir="ltr"></TEXTAREA>'+
				'</DIV>'+
			'');
		}
		this.$DLG = $('BODY').find('#e_err_dlg');
		this.$DLG.find('button').each2(function(i, jq){jq.button({text: false, icons: {primary: 'ui-icon-'+jq.attr("icon")}});});
		this.$DLG.dialog({height:'auto', width:'auto', draggable:true, resizable:true, modal:false, autoOpen:false, resizeStop: function(event, ui) { }});
	},
	
	open: function() {
		try {
			if(!this.$BTN && fastFace.gui.$menu !== null) {
				this.$BTN = fastFace.gui.$menu.find('#e_n_err').show().find("SPAN:eq(1)");
			}
			if(this.$BTN) {
				this.$BTN.html(this.data.length);
			}
			if(this.isDebug) {
				if(this.$DLG === null) {this.init_dialog();}
				this.$DLG.find('#e_err_total').html('Total errors: '+this.data.length);
				this.$DLG.dialog('open');
				this.show(this.data.length - 1);
			} else {
				try {
					var errObj = this.obj2obj(this.data[this.data.length - 1]);
					fastFace.msg.err(errObj.from + ' error',
						(errObj.type ? '<b>Type:</b> '+errObj.type+'<BR>' : '') +
						(errObj.name ? '<b>Name:</b> '+errObj.name+'<BR>' : '') +
						(errObj.message ? '<b>Msg:</b> '+errObj.message+'<BR>' : '') +
						'');
				} catch(err1) {
					fastFace.msg.err(err1.message);
				}
			}
		} catch(err) {
			fastFace.msg.err(err.message);
		}
	},
	
	obj2txt: function(errObj, tabs) {
		var str = '', isArr = false, i, j=0;
		tabs = tabs || 1;
		try {
			isArr = $.isArray(errObj);
			
			var errObjType = typeof errObj;
			
			if(errObj instanceof Error) { // TypeError SyntaxError
				return '{\n'+errObj.stack+'\n}';
			}
			
			if(errObj === null || errObjType !== 'object') {
				return ''+( (errObjType === 'string' || errObjType === 'number' || errObjType === 'boolean' || errObj === null) ? '' : '('+errObjType+')' )+( errObjType === 'string' ? errObj.replace(/\\r\\n/gmi,'\n').replace(/\\n/gmi,'\n') : errObj);
			}

			if($.isEmptyObject(errObj)) {
				return '{ '+errObj+' }';
			}
			
			str = isArr ? '[' : '{\n';
			for(i in errObj) {
				if(errObj.hasOwnProperty(i)) {
					j++;
					try {
						str += (isArr) ? (this.obj2txt(errObj[i], tabs + 1)+', ') : ( '' + str_repeat('  ', tabs) + i + ':\t'+this.obj2txt(errObj[i], tabs + 1)+'\n' );
					} catch(err2) {
						str += '\n\nCatched Err in [' + i + ']: ' + err2.message+'\n';
					}
				}
			}
			if(isArr && i > 0) {
				str = str.substring(0, str.length-2);
			}
		} catch(err1) {
			str += '\n\nCatched Err: ' + err1.message+'\n';
		}
		return str + ( isArr ? ']' : ( (j ? '' : str_repeat('  ', tabs) + errObj + '\n') + str_repeat('  ', tabs-1) + '}' ) )+':'+j;
	},
	
	obj2obj: function(errObj) {
		var isArr = $.isArray(errObj), isObj = $.isPlainObject(errObj), ret = isArr ? [] : {};
		try {
			if(!isArr && !isObj) {
				return errObj;
			}
			
			for(var i in errObj) {
				if(errObj.hasOwnProperty(i)) {
					try {
						if($.isPlainObject(errObj[i]) || $.isArray(errObj[i])) {
							ret[i] = this.obj2obj(errObj[i]);
						} else {
							ret[i] = errObj[i];
						}
					} catch(err2) {
						if(isArr) {
							ret.push(err2.message);
						} else {
							ret['catched_err_'+i] = err2.message;
						}
					}
				}
			}
		} catch(err1) {
			if(isArr) {
				ret.push(err1.message);
			} else {
				ret.catched_err = err1.message;
			}
		}
		return ret;
	},
	
	show: function(err_id) {
		var str = 'No errors!';
		this.cur = (err_id < 0 || this.data.length === 0)?0:(err_id >= this.data.length)?this.data.length-1:err_id;
		if(this.data.length > 0) {
			str = 'Error: '+(this.cur+1)+'/'+this.data.length+'\n\n';
			str += this.obj2txt(this.data[this.cur]);
		}
		this.$DLG.find('#e_err_txt').val(str);
	},
	
	add: function(err) {
		this.data.push(err);
		this.open();
	},
	
	php: function(err) {
		console.error('Err.php:\n', err);
		this.add(err);
	},
	
	js: function (err) {
		console.error('Err.js:\ne:', err, '\nobj2txt:', this.obj2txt(err));
		this.add(this.stack_trace(err, {from:'js'}));
	},
	
	js_event: function(msg, url, line) {
		console.error("Err.js_event: "+(new Date())+"\nMsg: "+msg+"\nLine: "+line+"\nUrl: "+url+"");
		fastFace.err.add(fastFace.err.stack_trace(null, {from:'js_event', message: msg, url: url, line: line}));
	},
	
	ajax: function(event, request, settings, ex) {
		console.error('Err.ajax:: \nevent:', event, '\nrequest:', request, '\nsettings:', settings, '\nex:', ex, '\nobj2txt:', fastFace.err.obj2txt(ex));
		fastFace.err.add(fastFace.err.stack_trace(ex, {from:'ajax', responseText: request.responseText, url: settings.url, ex:ex}));
	},

	custom: function(msg) {
		console.error("Err.custom: "+(new Date())+"\nMsg: "+msg);
		fastFace.err.add(fastFace.err.stack_trace(null, {from:'custom', message: msg}));
	},
	
	load_names: function (obj, name, recursive) {
		try {
			for (var x in obj) {
				if(typeof obj[x] === 'function') {
					if(obj[x].prototype === null) {
						obj[x].prototype = {};
					}
					if(typeof obj[x].prototype === 'object') {
						obj[x].prototype.ff_name = name+'.'+x;
					}
				} else if(recursive && typeof obj[x] === 'object') {
					this.load_names(obj[x], name+'.'+x);
				}
			}
		} catch(err) {
			return;
		}
	},
	
	trace_log: function () {
		var not_function;
		try {
			not_function();
		} catch(err) {
			console.error(err.stack, "\n", err);
		}
	},
	
	throw_err: function () {
		var not_function;
		not_function();
	},
	
	stack_trace: function(err, prop) {
		var ex = err || (function() {
			var not_function;
			try {
				not_function();
			} catch(err1) {
				return err1;
			}
		}()) || {};
		ex.time = date('Y-m-d H:i:s');
		if(!ex.trace && (ex.stack || ex.stacktrace)) {
			ex.trace = ex.stack || ex.stacktrace;
		}
		$.extend(ex, prop, {ver: fastFace.ver.ver});
		if (!ex.trace && !ex.stack && !ex.stacktrace) {
			var ANON = '{anonymous}', fn, args, curr = arguments.callee;
			ex.trace = '';

			for(var i=0; curr && i < 15; i++) {
				fn = (curr && curr.prototype && curr.prototype.ff_name) || ANON;
				//fn = (''+curr.toString()).replace(/\ \ |\t|\r|\n/gmi, '');
				args = Array.prototype.slice.call(curr['arguments']);
				ex.trace += '  [' + fn + '](' + this.obj2txt(args) + ')\n';
				curr = curr.caller;
			}
		}
		return ex;
	}
	
};