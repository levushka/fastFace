///////////////////////////////////////////////
//               Menu
///////////////////////////////////////////////

fastFace.menu = {
	
	data: [],
	
	reInit: function(lang, initFn) {
		var menuList = [];
		for(var i=0;i<fastFace.login.data.user.menu.length;i++) {
			menuList.push({val: fastFace.login.data.user.menu[i]});
		}
		fastFace.pid.run(
			[
				'ff\\tbl_get::get',
				{
					SELECT:     ['g.id', 'lg.'+fastFace.lang.cur, 'lm.'+fastFace.lang.cur, 'm.url'],
					FROM:       [
						{
							JOIN: 'ff_menu_grp',
							AS:   'g'
						},
						{
							JOIN:  'ff_menu',
							AS:    'm',
							WHERE: [{'=':['g.id','m.ff_menu_grp']}, {'=':['m.is_act',true]}]
						},
						{
							JOIN:  'ff_lang_char',
							AS:    'lg',
							WHERE: [{'=':['g.name','lg.id']}]
						},
						{
							JOIN:  'ff_lang_char',
							AS:    'lm',
							WHERE: [{'=':['m.name','lm.id']}]
						}
					],
					WHERE:      [{'=':['g.is_act',true]}, {'IN':['m.id',menuList]}],
					'ORDER BY': ['g.ord', 'm.ord']
				}
			],
			function(resultObj) {
				fastFace.menu.data = resultObj.data;
			}
		);
	},
	
	create: function($cont, isLine) {
		var tmp = [], grpId = 0;

		tmp.push(isLine ? '<UL class="menu">' : '<DIV>&nbsp;<SPAN class="ui-icon ui-icon-triangle-1-s"></SPAN></DIV>'+fastFace.dict.val('menu')+'<UL class="ui-state-default">');
		$.each(this.data, function(key, menuItem) {
			if(grpId !== menuItem[0]) {
				if(grpId !== 0 && isLine) {
					tmp.push('</UL></LI>');
				}
				tmp.push(isLine ? '<LI class="menu ui-state-default"><DIV>&nbsp;<SPAN class="ui-icon ui-icon-triangle-1-s"></SPAN></DIV>'+menuItem[1]+'<UL class="ui-state-default">' : '<LI class="spacer ui-state-default"></LI>');
				grpId = menuItem[0];
			}
			tmp.push(
				menuItem[3] ?
				'<LI class="ui-state-default" onclick="'+menuItem[3].replace(/"/g, '&quot;')+';return false;">'+menuItem[2]+'</LI>' :
				'<LI class="spacer ui-state-default"></LI>'
			);
		});
		tmp.push(isLine ? '</UL></LI></UL>' : '</UL>');
		$cont.html(tmp.join('')).show();
	}

};