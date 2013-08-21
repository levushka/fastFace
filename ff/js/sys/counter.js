///////////////////////////////////////////////
//               COUNTER
///////////////////////////////////////////////

fastFace.counter = {

	data: {},
	
	reInit: function() {
		fastFace.pid.run(
			[
				'ff\\tbl_get::get',
				{
					SELECT: ['key', 'count', 'jq_class', 'icon_url', 'hint_'+fastFace.lang.cur, 'lbl_'+fastFace.lang.cur, 'url'],
					FROM:   ['ff_counter'],
					WHERE:  [{'=':['is_act',true]}],
					assoc:  true
				}
			], function(resultObj) {
			
				var $btn, $header = fastFace.gui.$header, $e_n_err = fastFace.gui.$header.find('#e_n_err');

				$.each(resultObj.data, function(key, val) {
					fastFace.counter.data[val.key] = val;
					
					$btn = $header.find('#'+val.key);
					if($btn.length === 0) {
						$e_n_err.after('<LI id="'+val.key+'" class="btn '+val.jq_class+'" '+(val.url ? ' onclick="'+val.url+'"' : '')+'>&nbsp;'+(val.icon_url ? '<SPAN class="ui-icon '+val.icon_url+'">' : '<SPAN>'+val['lbl_'+fastFace.lang.cur])+'</SPAN> <SPAN>0</SPAN></LI>');
						$btn = $header.find('#'+val.key);
						$btn.attr('title', val['hint_'+fastFace.lang.cur]).tooltip().hide();
					}
				});
				
				fastFace.timer.add('pid', 300000, 'counter', {
					req: ['ff\\tbl_get::get', {
						SELECT: ['key', 'count'],
						FROM:   ['ff.ff_counter'],
						WHERE:  [{'=':['is_act',true]},{'>':['count',{val:0}]}],
						assoc:  true
					}],
					success: function(resultObj) {
						$.each(fastFace.counter.data, function(key, val) {
							val.count = 0;
						});
						
						$.each(resultObj.data, function(key, val) {
							fastFace.counter.data[val.key].count = val.count;
						});
						
						
						$.each(fastFace.counter.data, function(key, val) {
							$btn = $header.find('#'+val.key);
							$btn.find("SPAN:eq(1)").html(val.count);
							if(val.count === 0) {
								$btn.hide();
							} else {
								$btn.show();
							}
						});
					}
				});
			}
		);
	}

};
