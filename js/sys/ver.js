///////////////////////////////////////////////
//               Version
///////////////////////////////////////////////
fastFace.ver = {

	ver: 0,

	reInit: function(ver) {
		this.ver = ver;

		var lS = window.localStorage,
		lsVer = ~~lS.getItem('FF_VER'),
		cookieVer = ~~$.cookie('v');

		if ( this.ver > 0 && ( (lsVer > 0 && lsVer !== this.ver) || (cookieVer > 0 && cookieVer !== this.ver) ) ) {
			fastFace.cache.clearLocalStrorage();
		} else {
			this.reStore();
		}
	},

	reStore: function() {
		window.localStorage.setItem('FF_VER', this.ver);
		$.cookie('v', this.ver, { expires: 30, path: '/' });
	},

	msg: function(serverVer, browserVer) {
		return ''+
		'<H2><CENTER>'+
		'<DIV dir=rtl>'+
		'יש עדכון למערכת<BR>'+
		'הקש על  <B><FONT color=green>F5</FONT></B> /   <B><FONT color=green>לרענן</FONT></B> <IMG src=img/ie_refresh.gif><BR>'+
		'</DIV>'+
		'<HR>'+
		'<DIV dir=ltr>'+
		'New version of Емалон system<BR>'+
		'Please press <B><FONT color=green>F5</FONT></B> or <B><FONT color=green>Refresh</FONT></B> <IMG src=img/ie_refresh.gif><BR>'+
		'</DIV>'+
		'<HR>'+
		'<DIV dir=ltr>'+
		'Новая версия системы Емалон<BR>'+
		'Нажмите <B><FONT color=green>F5</FONT></B> или <B><FONT color=green>Обновить</FONT></B> <IMG src=img/ie_refresh.gif><BR>'+
		'</DIV>'+
		'</H2>'+
		'</CENTER>'+
		'<SMALL>[<B>'+serverVer+'</B>] [<B>'+browserVer+'</B>]</SMALL>'+
		'';
	},

	err: function(serverVer, browserVer) {
		$.cookie('v', null, {path: '/'});
		$.pnotify({type: "error", history: false, delay: 30000, width: '90%', title: "Version error", text: this.msg(serverVer, browserVer)});
	}
};
