///////////////////////////////////////////////
//               LOGIN
///////////////////////////////////////////////
fastFace.login = {

	userId: 0,
	lastLogin: null,
	savedLogins: null,
	data: {user:{id:0, type:0, grp:0, perm:0, sess:0, phpsess: '', token: '', name: '', lang: 'he'}, perm:{}},

	init: function() {
		this.lastLogin = fastFace.cache.get('login/last', '', true, true);
		this.savedLogins = fastFace.cache.get('login/saved', null, true, true);

		if(!$.isPlainObject(this.savedLogins)) {
			this.savedLogins = null;
		}
		this.reStore();
	},

	reStore: function() {
		if(this.lastLogin) {
			fastFace.cache.set('login/last', this.lastLogin, true, true);
		} else {
			fastFace.cache.del('login/last', true, true);
		}
		if(this.savedLogins && !$.isEmptyObject(this.savedLogins)) {
			fastFace.cache.set('login/saved', this.savedLogins, true, true);
		} else {
			fastFace.cache.del('login/saved', true, true);
		}
	},

	autoLogin: function() {
		if(this.lastLogin && this.savedLogins && $.keys(this.savedLogins).length === 1 && this.savedLogins[this.lastLogin]) {
			fastFace.sync.start();
			fastFace.sync.add(['ff\\login::access', {login:this.lastLogin, password:null, login_code:this.savedLogins[this.lastLogin].code, store_login:true}]);
			fastFace.sync.end();
		} else {
			fastFace.login.show(null, null);
		}
	},

	addAutoLogin: function(login, data) {
		if(!this.savedLogins) {
			this.savedLogins = {};
		}
		this.savedLogins[login] = data;
		this.lastLogin = login;
		this.reStore();
	},

	logout: function() {
		fastFace.sync.run(['ff\\login::logout'], function() {
			fastFace.clean();
			fastFace.login.show(null, null);
		});
	},

	login_under_user: function() {
		var d = fastFace.dict,
			dlgId = fastFace.render.uid('login_under_user_dlg_'),
			all_users = fastFace.checkPerm('user', ['agents']);

		if(!all_users) {
			fastFace.msg.err('No permissions for [login_under_user]');
		}

		var enterFn = function() {
			var $dlg = $('#'+dlgId),
				user_id = $dlg.find('SELECT[name=user_id]').val();

			$dlg.dialog('close');

			fastFace.sync.run(['ff\\login::login_under_user', {user_id:user_id, stored_logins: window.localStorage.getItem('SAVED_LOGINS')}]);
		};

		fastFace.render.dlg(
			{attr: {id: dlgId}, options: {fullText: null, printText: null, title: d.val('login_under_user'), buttons: $.makeObj([d.val('login_under_user')], [enterFn])}},
			null,
			[[
				'FIELDSET', {addClass: 'ui-widget ui-widget-content ui-corner-all'},
				[
					['DIV', {}, [
						['LABEL', {html:d.val('user'), addClass: 'lbl_200', attr: {'for': 'user_id'}}, null],
						['SELECT', {attr: {id: 'user_id', name: 'user_id', 'class':( fastFace.lang.rtl ? 'chzn-rtl' : '' )}, html:fastFace.tbl.get('ff.ff_user').getOpt(true), val:fastFace.login.data.user.id}, null]
					]]
				]
			]]
		);
	},

	change_password: function() {
		var d = fastFace.dict,
			dlgId = fastFace.render.uid('change_password_dlg_'),
			all_users = fastFace.checkPerm('user', ['agents']);

		var enterFn = function() {
			var $dlg = $('#'+dlgId),
				user_id = $dlg.find('SELECT[name=user_id]').val(),
				old_password = $.trim($dlg.find('INPUT[name=old_password]').val()),
				new_password = $.trim($dlg.find('INPUT[name=new_password]').val());

			$dlg.dialog('close');

			fastFace.sync.run(['ff\\login::change_password', {user_id:user_id, old_password:old_password, new_password:new_password}]);
		};

		fastFace.render.dlg(
			{attr: {id: dlgId}, options: {fullText: null, printText: null, title: d.val('change_password'), buttons: $.makeObj([d.val('change_password')], [enterFn])}},
			null,
			[[
				'FIELDSET', {addClass: 'ui-widget ui-widget-content ui-corner-all'},
				[
					all_users ? ['DIV', {}, [
						['LABEL', {html:d.val('user'), addClass: 'lbl_200', attr: {'for': 'user_id'}}, null],
						['SELECT', {attr: {id: 'user_id', name: 'user_id', 'class':( fastFace.lang.rtl ? 'chzn-rtl' : '' )}, html:fastFace.tbl.get('ff.ff_user').getOpt(true), val:fastFace.login.data.user.id}, null]
					]] : null,
					all_users ? null : ['DIV', {}, [
						['LABEL', {html:d.val('old_password'), addClass: 'lbl_200', attr: {'for': 'old_password'}}, null],
						['INPUT', {attr: {type: 'text', id: 'old_password', name: 'old_password'}}, null]
					]],
					['DIV', {}, [
						['LABEL', {html:d.val('new_password'), addClass: 'lbl_200', attr: {'for': 'new_password'}}, null],
						['INPUT', {attr: {type: 'text', id: 'new_password' , name: 'new_password'}, keyup: function(event) {if (event.keyCode === $.ui.keyCode.ENTER) {enterFn();}}}, null]
					]]
				]
			]]
		);
	},

	show: function(err_login, err_msg) {
		var d = fastFace.dict,
			dlgId = fastFace.render.uid('login_dlg_'),
			savedLoginsOpt = null;

		this.data = {user:{id:0, type:0, grp:0, perm:0, sess:0, phpsess: '', token: '', name: '', lang: 'he'}, perm:{}};

		if(this.savedLogins) {
			try {
				if(err_login && this.savedLogins[err_login]) {
					delete this.savedLogins[err_login];
				}
				this.reStore();

				var savedLoginsArr = $.map(this.savedLogins, function( obj, key ) { return [[key, key + ' / ' + obj.name]]; });
				savedLoginsArr.unshift( ['login_manual', d.val('login_manual')] );
				fastFace.tbl.load('user_savedLogins', {data: {data: savedLoginsArr}});
				savedLoginsOpt = fastFace.tbl.get('user_savedLogins').getOpt(false);
			} catch(err) {
				this.savedLogins = null;
			}
		}

		var enterFn = function() {
			var $dlg = $('#'+dlgId),
				password = $.trim($dlg.find('INPUT[name=password]').val()),
				login = $.trim($dlg.find('INPUT[name=login]').val()) || $.trim($dlg.find('SELECT[name=login_auto]').val()),
				login_code = (fastFace.login.savedLogins && fastFace.login.savedLogins[login]) ? fastFace.login.savedLogins[login].code : '',
				store_login = $dlg.find('INPUT[name=store_login]').prop('checked'),
				lang = $dlg.find('INPUT[name=lang]').filter(':checked').val();

			$dlg.dialog('close');

			fastFace.sync.start();
			fastFace.lang.changeLang(lang);

			fastFace.login.lastLogin = login;
			fastFace.login.reStore();

			fastFace.sync.add(['ff\\login::access', {login:login, password:password, login_code:login_code, store_login:store_login}]);
			fastFace.sync.end();
		};

		var changeLoginAutoFn = function(event) {
			var $dlg = $('#'+dlgId), $legend = $dlg.find('#login_manual'), selVal = event.target.value;
			$dlg.find('INPUT[name=login]').val('');
			$dlg.find('INPUT[name=password]').val('');
		};

		fastFace.render.dlg(
			{attr: {id: dlgId}, options: {modal: true, fullText: null, printText: null, closeOnEscape: false, title: d.val('login_dlg'), buttons: $.makeObj([d.val('enter')], [enterFn]), open: function(event, ui) {$(this).parent().children().children('.ui-dialog-titlebar-close').hide();}}},
			null,
			[
				savedLoginsOpt ? ['FIELDSET', {addClass: 'ui-widget ui-widget-content ui-corner-all'}, [
					['LEGEND', {addClass: 'ui-widget ui-widget-header ui-corner-all', html: d.val('login_auto')}, null],
					['DIV', {}, [
						['LABEL', {html: d.val('login_auto'), addClass: 'lbl_200', attr: {'for': 'login_auto'}}, null],
						['SELECT', {attr: {id: 'login_auto', name: 'login_auto'}, html: savedLoginsOpt, val: this.lastLogin ? this.lastLogin : '', change: changeLoginAutoFn}, null]
					]]
				]] : null,
				['FIELDSET', {addClass: 'ui-widget ui-widget-content ui-corner-all'}, [
					['LEGEND', {addClass: 'ui-widget ui-widget-header ui-corner-all', attr: {id: 'login_manual'}, html: d.val('login_manual')}, null],
					err_msg?['DIV', {html: err_msg, addClass: 'ui-state-error ui-state-error-text'}, null]:null,
					['DIV', {}, [
						['LABEL', {html:d.val('login'), attr: {'for': 'login', 'class': 'lbl_100'}}, null],
						['INPUT', {attr: {type: 'text', id: 'login', 'class': 'ltr', name: 'login', value: this.lastLogin ? this.lastLogin : ''}}, null]
					]],
					['DIV', {}, [
						['LABEL', {html:d.val('password'), attr: {'for': 'password', 'class': 'lbl_100'}}, null],
						['INPUT', {attr: {type: 'password', id: 'password', 'class': 'ltr', name: 'password'}, keyup: function(event) {if (event.keyCode === $.ui.keyCode.ENTER) {enterFn();}}}, null]
					]],
					['DIV', {}, [
						['LABEL', {attr: {title: d.val('login_store_tip'), 'class': 'lbl_150'}, html: d.val('login_store'), tooltip: {}}, null],
						['INPUT', {attr: {type: 'checkbox', name: 'store_login', value: 'true', title: d.val('login_store_tip')}, tooltip: {}}, null]
					]]
				]],
				['FIELDSET', {addClass: 'ui-widget ui-widget-content ui-corner-all'}, [
					['LEGEND', {addClass: 'ui-widget ui-widget-header ui-corner-all', html: d.val('lang')}, null],
					['DIV', {}, [
						['LABEL', {html: d.val('lang'), addClass: 'lbl_100'}, null],
						['INPUT', {attr: {type: 'radio', id: 'lang_ru', name: 'lang', value: 'ru'}, val:[fastFace.lang.cur]}, null],
						['LABEL', {html: d.val('ru'), attr: {'for': 'lang_ru'}}, null],
						['INPUT', {attr: {type: 'radio', id: 'lang_he', name: 'lang', value: 'he'}, val:[fastFace.lang.cur]}, null],
						['LABEL', {html: d.val('he'), attr: {'for': 'lang_he'}}, null]
					]]
				]]
			]
		);
	}
		
};
