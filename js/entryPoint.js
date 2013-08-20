/*!
*    main JS file
*/

///////////////////////////////////////////////
//               EMALON
///////////////////////////////////////////////

var fastFace = {

	token: null,
	urlAPI: null,
	
	htmlEditor: null,
	htmlEditorSimple: null,
	htmlEditorFull: null,
	
	///////////////////////////////////////////////
	//               INIT
	///////////////////////////////////////////////
	init: function(options, initFn) {
		this.sync.start();
		
		this.token = options.ff_token;
		this.userId = options.ff_user_id;
		this.urlAPI = options.ff_url_api;
		this.cache.isLS = options.is_js_storage;
		this.tbl.init(options.tbl_options);
		this.msg.init();
		this.err.init(options.is_debug, options.d_code);
		this.login.init();
		this.gui.init();

		this.ver.reInit(options.ff_ver);
		this.cache.reInit(options.ff_ver_cache);

		this.htmlEditor = new nicEditor({iconsPath : '/js_lib/nicEdit/nicEditorIcons.gif', buttonList : ['fontSize','fontFamily','bold','italic','underline','strikeThrough','left','center','right','forecolor','bgcolor','link','unlink','image','upload','xhtml']});
		this.htmlEditorSimple = new nicEditor({iconsPath : '/js_lib/nicEdit/nicEditorIcons.gif', buttonList : ['fontSize','bold','italic','underline','xhtml']});
		this.htmlEditorFull = new nicEditor({iconsPath : '/js_lib/nicEdit/nicEditorIcons.gif', fullPanel : true});
		
		this.lang.cur = null;
		this.lang.changeLang(options.lang);
		
		this.sync.end('ff_init', null, initFn);
		return true;
	},

	clean: function() {
		this.cache.clearCache();
		this.timer.clean();
		this.gui.clean();
	},

	///////////////////////////////////////////////
	//               RE INIT for new user
	///////////////////////////////////////////////
	reInit: function(options) {
		this.sync.start();
		
		this.token = options.ff_token;
		this.userId = options.ff_user_id;
		this.clean();

		this.lang.changeLang(options.lang);
		this.menu.reInit();
		
		this.sync.end('ff_reinit', null, function() {
			//fastFace.timer.reInit();
			fastFace.gui.reInit();
			fastFace.gui.contentUrl('/admin/welcome.php');
			//fastFace.timer.add('sync', 300000, 'timer_check', {req: ['ff\\ff_timer::check']});
			//fastFace.counter.reInit();
		}, function() {
				fastFace.msg.err('Cannot reInit system.');
		});
	},

	checkPerm: function(cls, fnArr) {
		if(this.login.data.user['super']) {
			return true;
		}

		var clsPerm = this.login.data.perm[cls] || null;

		if(clsPerm === null || clsPerm.length === 0) {
			return false;
		}

		if(clsPerm.any_cmd) {
			return true;
		}

		for( var i = fnArr.length; i--; ) {
			if($.inArray(fnArr[i], clsPerm) >= 0) {
				return true;
			}
		}

		return false;
	}



};
