///////////////////////////////////////////////
//               DB Find
///////////////////////////////////////////////
fastFace.db._fnd = function(id_or_url, arg) {
		var def = fastFace.tbl.get(id_or_url),
			tblDef = def.tbl,
			tblId = tblDef.id,
			cols = def.cols,
			keys = def.keys,
			fns = def.fns,
			getDef = fns.get || {},
			fndDef = fns.fnd || {},
			isAdv = fndDef.adv || false,
			gridDisp = (fndDef.grid && fndDef.grid.disp ? fndDef.grid.disp : 'bg'),
			d = fastFace.dict,
			i,
			formArg = [],
			buttons = [],
			dlgId = fastFace.render.uid('fnd_'),
			sql_fn_mult = ['IN', 'NOT IN'],
			sql_fn_set = ['=', '<>', 'IN', 'NOT IN'],
			sql_fn_betw = ['BETWEEN', 'NOT BETWEEN'];

		var searchFn = function() {
			var $dlg = $('#'+dlgId),
			search_txt_val = $.trim($dlg.find('#search_txt').val()),
			getArg = {
				tbl:   tblDef.id,
				WHERE: [],
				LIMIT: [0, ~~$dlg.find('#find_lim').val()]
			};

//      if(search_txt_val.length > 1 || (search_txt_val.length > 0 && ~~search_txt_val > 0)) {
				//if(typeof tblDef.no_search === 'undefined') {
				//  getArg.search=search_txt_val;
				//  getArg.search_full=$dlg.find('#search_full').prop('checked');
				//} else {
					//getArg.WHERE.push({'LIKE': [fndDef.txt_col, $.map(search_txt_val.split(' '), function(n, i) { return '%'+$.trim(n)+'%'; })]});
				//}
//      }

			//var i = 0;
			//if(fndDef.fltr && $.isArray(fndDef.fltr)) { fndDef.fltr = [[]]; }  // Saving last search
//      $dlg.find('[name=fltr_cmd]').each(function() {
//        var $fltCmd = $(this), fn = $fltCmd.val(), fltrCol = $fltCmd.attr('id').substring(9), colObj = cols[fltrCol], isBool = (colObj.type === 'bool'), fltValArr = [], $fltVal = $dlg.find('#fltr_'+fltrCol);
//        if($.inArray(fn, sql_fn_mult) >= 0) {
//          fltValArr = $fltVal.val();
//        } else if($.inArray(fn, sql_fn_betw) >= 0) {
//          $fltVal.each(function() {fltValArr.push((isBool && $(this).is('INPUT[type=checkbox]')) ? $(this).prop('checked') : $(this).val());});
//        } else {
//          fltValArr = (isBool && $fltVal.is('INPUT[type=checkbox]')) ? $fltVal.prop('checked') : $fltVal.val();
//        }
				//fndDef.fltr[0][i++] = [fn, fltrCol, fltValArr]; // Saving last search
//        if( fn !== 'off' && fltValArr !== null && fltValArr.length > 0 ) {
//          var tmp = {};
//          tmp[fn] = [fltrCol,fltValArr];
//          getArg.WHERE.push(tmp);
//        }
//      });

			$('#'+dlgId).dialog('close');

			fastFace.db.get(getArg);
		};

		buttons.push({text: d.val('find'), icons: $.makeObj([fastFace.lang.ico], ['ui-icon-search']), click: searchFn});
		if(def.add) {
			buttons.push({text: d.val('add'), icons: $.makeObj([fastFace.lang.ico], ['ui-icon-document']), click: function() {
				$('#'+dlgId).dialog('close');
				fastFace.db._add(tblId);
			}});
		}
		buttons.push({text: d.val('close'), icons: $.makeObj([fastFace.lang.ico], ['ui-icon-cancel']), click: function() {
			$('#'+dlgId).dialog('close');
		}});

		if(typeof tblDef.no_search === 'undefined') {
			formArg.push(['FIELDSET', {addClass: 'ui-widget ui-widget-content ui-corner-all'}, [['DIV', {}, [
				['LABEL', {html:d.val('find'), addClass: 'lbl_100', attr: {'for':'search_txt'}}, null],
				['INPUT', {attr: {typeee:'text', id:'search_txt', width: 300}, keyup :function(event) {if (event.keyCode === $.ui.keyCode.ENTER) {searchFn();}}}, null]
				//['INPUT', {attr: {type:'checkbox', id:'search_full', value:'false'}}, null]
			]]]]);
		}


		if(typeof fndDef.fltr === 'object') {

			var fltrCmdChangeFn = function(event) {
				var $dlg = $('#'+dlgId), fltCmd = event.target.value, fltrCol = event.target.id.substring(9), colObj = cols[fltrCol], $fltrVal = $dlg.find('#fltr_'+fltrCol);
				switch(colObj.type)
				{
					case 'date':
						$fltrVal.each(function(){$(this).datepicker("destroy");});
						break;
					case 'datetime':
						$fltrVal.each(function(){$(this).datetimepicker("destroy");});
						break;
					case 'time':
						$fltrVal.each(function(){$(this).timepicker("destroy");});
						break;
				}
				if($fltrVal.length === 2 && $.inArray(fltCmd, sql_fn_betw) < 0) {
					$fltrVal.eq(1).remove();
				}
				if( (colObj.fk) && $.inArray(fltCmd, sql_fn_mult) < 0 && $fltrVal.prop('multiple') ) {
					$fltrVal.prop({multiple: false, size: 0});
				}
				if(fltCmd === 'off') {
					if(!$fltrVal.prop('disabled')) {
						$(event.target).css({background: '#F2F2F2', color: '#555'}).parent().css({background: '#F2F2F2', color: '#555'});
						$fltrVal.prop('disabled', true);
					}
				} else {
					$(event.target).css({background: '', color: ''}).parent().css({background: '', color: ''});
					$fltrVal.prop('disabled', false);
					if($.inArray(fltCmd, sql_fn_betw) >= 0) {
						if($fltrVal.length === 1) {
							$fltrVal.clone().insertAfter($fltrVal);
							$fltrVal = $dlg.find('#fltr_'+fltrCol);
						}
					} else if( (colObj.fk) && ($.inArray(fltCmd, sql_fn_mult) >= 0) && !$fltrVal.prop('multiple') ) {
						$fltrVal.prop({multiple: true, size: 6});
					}
					switch(colObj.type)
					{
						case 'date':
							$fltrVal.each(function(){$(this).datepicker();});
							break;
						case 'datetime':
							$fltrVal.each(function(){$(this).datetimepicker({autoSize: false});});
							break;
						case 'time':
							$fltrVal.each(function(){$(this).timepicker();});
							break;
					}
				}
			};

			var fltrArr = [['LEGEND', {attr: {title: d.val('fltr_tip')}, addClass: 'ui-widget ui-widget-header ui-corner-all', collapse2:{}, html: d.val('fltr'), tooltip: {}}, null]];

			for(i=0; i<fndDef.fltr.length; i++) {
				for(var j=0; j<fndDef.fltr[i].length; j++) {
					var fndEl = fndDef.fltr[i][j],
						fn = fndEl[0],
						key = fndEl[1],
						val = fndEl[2];

					if(typeof key === 'string') {
						if(typeof val !== 'object') { val = [val]; }
						if(typeof cols[key] === 'object') {

							var divArr = [],
								colObj = cols[key],
								isBool = (colObj.type === 'bool'),
								isText = (colObj.type === 'text' || colObj.type === 'char'),
								disabled = ( !isAdv || fn !== 'off' ? false : true );

							fn = ( isAdv || fn !== 'off' ) ? fn : ( (isBool) ? '=' : ( isText ? 'LIKE' : ( colObj.fk ? '=' : 'BETWEEN' ) ) );

							divArr.push(['LABEL', {attr: {'for':'fltr_'+key}, html: d.val(colObj.lbl || (colObj.fk ? (colObj.cache || (colObj.arr ? key : colObj.fk.tblId)) : key)), addClass: 'lbl_100'}, null]);

							if( isAdv ) {
								divArr.push(['SELECT', {
									attr: {id:'fltr_cmd_'+key, name:'fltr_cmd', css:(fn==='off'?{background: '#F2F2F2', color: '#555'}:{})},
									html: (isBool ? fastFace.tbl.get('cache_sql_fn_bool').getOpt(!isAdv) : ( colObj.fk ? fastFace.tbl.get('cache_sql_fn_fk').getOpt(!isAdv) : (isText ? fastFace.tbl.get('cache_sql_fn_txt').getOpt(!isAdv) : fastFace.tbl.get('cache_sql_fn_reg').getOpt(!isAdv)) ) ),
									val: [fn],
									change: fltrCmdChangeFn
								}, null]);
							} else {
								divArr.push(['INPUT', {
									attr: {id:'fltr_cmd_'+key, name:'fltr_cmd', type:'hidden'},
									val: fn
								}, null]);
							}

							if(isBool) {

								divArr.push(['SELECT', {attr: {id:'fltr_'+key, name:'fltr_val', disabled: disabled}, html:fastFace.tbl.get('cache_yes_no').getOpt(!isAdv), val:val}, null]);

							} else if ( colObj.fk ) {

								var fk = colObj.fk;

								divArr.push(
									[
										'SELECT',
										{
											attr: $.extend(
												{
													id:'fltr_'+key,
													name:'fltr_val',
													disabled: disabled
												},
												$.inArray(fn, sql_fn_mult) >=0 ?
												{
													multiple: true,
													size: fk.length > 6 ? 6 : fk.length
												} :
												{}
											),
											html: fastFace.tbl.get(fk.tblId).getOpt( !isAdv ),
											val: isAdv ? val : (val || '')
										},
										null
									]
								);

							} else {
								val = val || [null, null];

								var tmpObj = $.extend({attr: {type:'text', id:'fltr_'+key, name:'fltr_val', value:val[0], disabled: disabled}}, (fn==='off'?{}:(colObj.type === 'date'?{datepicker:{}}:colObj.type === 'datetime'?{datetimepicker:{autoSize: false}}:colObj.type === 'time'?{timepicker:{}}:{})));

								divArr.push(['INPUT', tmpObj, null]);

								if( $.inArray(fn, sql_fn_betw) >=0 ) {
									var tmpObj2 = $.extend({}, tmpObj);
									tmpObj2.attr = $.extend({}, tmpObj2.attr);
									tmpObj2.attr.value = val[1];
									divArr.push(['INPUT', tmpObj2, null]);
								}

							}

							fltrArr.push(['DIV', {css: $.extend({'border-bottom': '1px dotted #666666'}, fn==='off'?{background: '#F2F2F2', color: '#555'}:{})}, divArr]);

						} else {

							fastFace.msg.err('Filter key['+key+'] not found');

						}
					}
				}
			}

			formArg.push(['FIELDSET', {addClass: 'ui-widget ui-widget-content ui-corner-all'}, fltrArr]);
		}

		var opt = [], step = fns.get.lim/10;
		for(i=1;i<=10;i++) { opt.push('<OPTION value="'+(i*step)+'">'+(i*step)+'</OPTION>'); }
		formArg.push(['FIELDSET', {addClass: 'ui-widget ui-widget-content ui-corner-all'},[
			['LABEL', {attr: {'for':'find_lim'}, html:d.val('limit'), addClass: 'lbl_100'}, null],
			['SELECT', {attr: {id:'find_lim'}, html:opt.join(''), val:[step*4]},null]
		]]);

		fastFace.render[fndDef.disp ? fndDef.disp : 'dlg'](
			{attr: {id: dlgId}, options: {title: d.val([fndDef.lbl || tblDef.url, fastFace.tbl.small(tblDef.url)]), buttons: buttons}},
			null,
			formArg
		);
};
