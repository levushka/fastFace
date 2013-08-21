///////////////////////////////////////////////
//               GUI
///////////////////////////////////////////////
fastFace.gui = {
	isIE: false,
	ieVer: null,
	$ajaxWait: null,
	$header: null,
	$bg: null,
	data: {header_height:22},

	init: function() {
		var appVer = navigator.appVersion;
		if(navigator.appName === 'Microsoft Internet Explorer') {
			this.isIE = true;
			this.ieVer = null;
			if(appVer.indexOf('MSIE 7.0') > 0 && appVer.indexOf('MSIE 8.0') < 0) {
				this.ieVer = 'ie7';
			} else if(appVer.indexOf('MSIE 6.0') > 0 && appVer.indexOf('MSIE 8.0') < 0) {
				this.ieVer = 'ie6';
			}
		}

		$('BODY').append('<DIV id="e_ajax_wait" class="ui-corner-bottom" style="display:block; color:black; background-color:#FFC129; font-weight:bold; padding:5px 30px 5px 30px; white-space:nowrap; position:fixed; z-index:10001; top:0px; left:0px;">Starting system...</DIV>');

		this.$ajaxWait = $('BODY').find('#e_ajax_wait');
		$(document).on({
			ajaxStart: function() { fastFace.gui.$ajaxWait.show(); },
			ajaxStop: function() { fastFace.gui.$ajaxWait.hide(); }
		});

		$('BODY').find('#e_gui').iff(this.ieVer).addClass(this.ieVer).end().html('<DIV id="e_menu" class="ui-widget-content"></DIV><DIV id="e_bg" class="printcont"></DIV>');

		this.$header = $('BODY').find('#e_menu');
		this.$bg = $('BODY').find('#e_bg');

		$(window).resize( fastFace.gui.resize );
	},

	reInit: function() {
		$.datepicker.setDefaults( $.datepicker.regional[ fastFace.lang.cur ] );
		$.datepicker.setDefaults({
			autoSize: true,
			changeMonth: true,
			changeYear: true,
			dateFormat: 'yy-mm-dd',
			firstDay: 0,
			//gotoCurrent: true,
			showButtonPanel: true,
			showOn: "both",
			buttonImage: "img/calendar.gif",
			buttonImageOnly: true
		});
		$.timepicker.setDefaults( $.timepicker.regional[ fastFace.lang.cur ] );

		if(this.$ajaxWait) {this.$ajaxWait.removeClass('ltr rtl').addClass(fastFace.lang.dir).html(fastFace.dict.val('ajax_wait', 'Loading data.....'));}
		this.header();
	},

	resize: function() {
		try {
			var gui = fastFace.gui;
			if(gui.$ajaxWait) {gui.$ajaxWait.css('left', ($(window).width()/2)+'px');}
			if(gui.$header) {gui.$header.height(fastFace.gui.data.header_height);}
			if(gui.$bg) {
				gui.$bg.height($(window).height()-fastFace.gui.data.header_height);
				gui.$bg.triggerHandler('bg_resize');
			}
		} catch(err) {fastFace.err.js(err);}
	},

	clean: function() {
		if(typeof this.$header !== 'undefined' && this.$header !== null) { this.$header.empty(); }
		if(typeof this.$bg !== 'undefined' && this.$bg !== null) { this.$bg.off('bg_resize').empty(); }
	},

	print: function() {
		var $bg_header = this.$bg.find('#bg_header'),
			$bg_body = this.$bg.find('#bg_body'),
			$bg_frame = $bg_body.find('IFRAME');
			
		if($bg_frame.length > 0 && $bg_frame.parent().attr('id') === 'bg_body') {
			try {
				var ifWin = $bg_frame[0].contentWindow || $bg_frame[0];
				ifWin.focus();
				ifWin['print'+(typeof ifWin.printPage === 'function'?'Page':'')]();
			} catch(err) {
				alert('Cannot print content of IFRAME.');
			}
		} else {
			var win = window.open();
			win.document.write(
				'<HTML><HEAD><TITLE>'+
				($bg_header.length === 1 ? $bg_header.html() : '')+
				'</TITLE><STYLE type="text/css">'+
				$.map(document.styleSheets, function(v, k) {return $.map(v.cssRules, function(v1, k1) { return v1.cssText;});}).join('\n')+
				'</STYLE></HEAD><BODY class="'+$bg_body.attr('class')+'">'+
				$bg_body.html()+
				'</BODY></HTML>');
			win.focus();
			win.print();
		}
	},

	updateLogoInfo: function() {
		if(this.$header) {
			var u = fastFace.login.data.user;
			this.$header.find('#e_logo').show().attr({title:''}).tooltip({tooltipClass: 'ltr', content: ''+
				'User name: '+u.name.replace(/"/g, '&quot;')+
				( fastFace.err.isDebug ? ( ''+
					'<hr>User id: '+u.id+
					'<br>User type: '+u.type+
					'<br>User grp: '+u.grp+
					'<br>User perm: '+u.role+
					'<br>Sesssion id: '+u.sess+
					'<br>PHPSESSID: '+$.cookie('PHPSESSID')+
					'<br>Token: '+fastFace.token+
					''
				) : ''
				) +
				'<hr>Client ver: '+fastFace.ver.ver+
				'<br>jQuery ver: '+$.fn.jquery+
				'<br>jQuery UI ver: '+$.ui.version+
				'<hr>Client cache at: '+date('Y-m-d H:i:s', fastFace.cache.ver)+
				'<br>Client cache user size: '+$.bytesToSize((function() { var bytes = 0, ls = window.localStorage, pref = fastFace.lang.cur+'_'+u.id+'_'; for(var i in ls) { if(typeof ls[i] === 'string' && i.indexOf(pref) === 0) { bytes+=ls[i].length; } } return bytes;}()), 0)+
				'<br>Client cache total size: '+$.bytesToSize($.sizeOf(window.localStorage), 0)+
				'<hr>Login at: '+u.login_on+
				''});
		}
	},

	header: function() {
		var secondLine = (fastFace.menu.data.length >= 20) ? true : false;

		var tmp = [
			'<DIV style="padding-'+fastFace.lang.align+': 200px; height:22px; '+( secondLine ? 'overflow:  hidden;' : '' )+'"><UL class="menu">',
			'<LI id="e_logo" onclick="fastFace.gui.contentUrl(\'admin/welcome.php\');return false;"><IMG src="img/logo_small.gif" style="cursor:pointer; margin:0 0 -3px 0; padding-'+fastFace.lang.alignM+': 50px;"/></LI>',
			'<LI class="btn ui-state-default" onclick="fastFace.gui.contentUrl(\'admin/welcome.php\');return false;">&nbsp;<SPAN class="ui-icon ui-icon-home"></SPAN> Home</LI>',
			secondLine ? '' : '<LI id="menu_btn" class="menu ui-state-default"></LI>',
			'<LI><FORM id="form_find_orders"><INPUT id="find_main" name="find_main" type=text size=9 title="Order ID" style="height:14px;" dir=ltr></FORM></LI>',
			'<LI id="e_n_err" class="btn ui-state-error" onclick="fastFace.err.open();return false;"><SPAN class="ui-icon ui-icon-alert"></SPAN> <SPAN>0</SPAN></LI>',
			'<LI class="btn ui-state-default" title="'+(fastFace.dict.val('change_lng')+fastFace.dict.val(fastFace.lang.cur === 'ru'?'he':'ru'))+'" onclick="top.location.href=\'admin.php?l='+(fastFace.lang.cur === 'ru'?'he':'ru')+'\';">&nbsp;<SPAN class="ui-icon ui-icon-refresh"></SPAN>'+(fastFace.lang.cur === 'ru'?'he':'ru')+'</LI>',
			'<LI class="btn ui-state-default" onclick="fastFace.gui.print();return false;">&nbsp;<SPAN class="ui-icon ui-icon-print"></SPAN>&nbsp;</LI>',
			'<LI><B style="font:bold 12px Arial; vertical-align: middle; padding-'+fastFace.lang.align+': 50px;">info@emalon.co.il &nbsp; 972-3-5260505</B></LI>',
			'</UL></DIV>',
			secondLine ? '<DIV id="menu_line" style="padding-'+fastFace.lang.align+': 100px; height:22px; "></DIV>' : ''
		];

		this.data.header_height = secondLine ? 44 : this.data.header_height;
		this.$header.empty().removeClass("ltr rtl").addClass(fastFace.lang.dir).html(tmp.join(''));
		this.$header.find("LI[title]").tooltip();
		this.$header.find("LI[id]").hide();

		fastFace.menu.create(this.$header.find("#menu_"+(secondLine ? 'line' : 'btn')), secondLine);

		var find_main = this.$header.find("#find_main");

		var getOrdersAutocomplete = function () {
			var savedOrdersAutocompleteStr = window.localStorage.getItem('SAVED_ORDERS_AUTOCOMPLETE');

			if(savedOrdersAutocompleteStr) {
				try {
					var result = $.parseJSON(savedOrdersAutocompleteStr);
					if(!$.isArray(result)) {
						return [];
					}
					return result;
				} catch(err) {
					return [];
				}
			}

			return [];
		};

		var submitOrdersSearch = function (event, ui) {
			var newOrderId = find_main.val(), savedOrdersAutocomplete = getOrdersAutocomplete();

			if(newOrderId && newOrderId !== null && newOrderId !== '' && newOrderId !== 'Order ID') {
				savedOrdersAutocomplete.push(newOrderId);
				savedOrdersAutocomplete = $.unique(savedOrdersAutocomplete);
				window.localStorage.setItem('SAVED_ORDERS_AUTOCOMPLETE', JSON.stringify(savedOrdersAutocomplete));

				find_main.val('').trigger('blur').autocomplete("option", "source", savedOrdersAutocomplete);

				fastFace.gui.contentUrl('admin/orders.php?pagecode=list&addon1=view&find_main='+newOrderId);
			}

			return false;
		};

		find_main.css({height: "14px", margin: "0 1px 0 1px", "vertical-align": "top"}).addClass("ui-corner-all")
		.labelify()
		.autocomplete({source:getOrdersAutocomplete(), select: function(event, ui) { find_main.val(ui.item.value); submitOrdersSearch(); return false; }});

		this.$header.find("#form_find_orders").submit(submitOrdersSearch);

		if(fastFace.err.data.length) {this.$header.find('#e_n_err').show().find("SPAN:eq(2)").html(fastFace.err.data.length);}
		if(this.ieVer === 'ie6') {this.$header.find("#menu li.menu").hover(function () {$(this).children("ul").show();},function(){$(this).children("ul").hide();});}
		this.updateLogoInfo();
		this.resize();
	},

	contentUrl: function(src) {
		try {
			if(typeof src === 'undefined' || src === '') {
				fastFace.msg.err('URL not defined!');
			} else {
				fastFace.render.bg(
					{},
					null,
					[[
						'ifrm',
						{attr: {
							src: src + (
								src.indexOf('http://') === 0 ?
								''
								:
								(src.indexOf('?')>=0?'&':'?') + 'l=' + fastFace.lang.cur + '&v=' + fastFace.ver.ver + '&d=' + fastFace.err.d + '&t=' + fastFace.token
							),
							'class': fastFace.lang.dir,
							scrolling: 'auto',
							frameborder: 0
						}},
						null
					]]
				);
			}
			return false;
		} catch(err) {fastFace.err.js(err);}
	}

};