///////////////////////////////////////////////
//               DB
///////////////////////////////////////////////
fastFace.db = {

	get: function(arg, fnOnOk, fnOnErr) {
		fastFace.pid.run(
			['ff\\tbl_get::get', arg],
			fnOnOk || function(resultObj) {
				if(arg.row || ($.isArray(resultObj.data) && resultObj.data.length === 1)) {
					fastFace.db.form.bg(resultObj);
				} else {
					fastFace.db.grid.bg(resultObj);
				}
			},
			fnOnErr || function() {
				fastFace.msg.err(sprintf(fastFace.dict.cache.not_found, '', ''));
			}
		);
	},
				
	upd: function(arg, fnOnOk, fnOnErr) {
		fastFace.pid.run(
			['ff\\tbl_upd::upd', arg],
			fnOnOk || function(resultObj) {
				fastFace.msg.info(sprintf(fastFace.dict.cache.upd_ok, '', ''));
			},
			fnOnErr || function() {
				fastFace.msg.err(sprintf(fastFace.dict.cache.not_found, '', ''));
			}
		);
	},

	cpy: function(tblId, row_id, addArg, fnOnOk, fnOnErr) {
		return;
//    if(confirm(sprintf(fastFace.dict.cache.cpy_conf, fastFace.dict.val([tblId, fastFace.tbl.small(tblId)]), row_id[0]))) {
//      fastFace.db.get(tblId, {tbl: tblId, row_id: row_id, assoc: true, row: true}, function(resultObj) {
//        var uk = fastFace.tbl.get(tblId).db.uk;
//        if(uk.length) {
//          for(var i=0; i<uk.length; i++) {
//            if(typeof(resultObj.data[uk[i]]) !== 'undefined') {
//              delete resultObj.data[uk[i]];
//            }
//          }
//        }
//        fastFace.db.add(tblId, $.extend({data: resultObj.data}, addArg), fnOnOk, fnOnErr);
//      }, fnOnErr);
//    }
	},
				
	add: function(tblId, addArg, fnOnOk, fnOnErr) {
		return;
//    fastFace.pid.run(['ff\\tbl_add::add', addArg],
//      function(resultObj) {
//        if(resultObj.data.rows === 1 && resultObj.data.row_id > 0 && resultObj.data.new_item !== null) {
//          if(typeof fnOnOk === 'function') {
//            fnOnOk(resultObj.data.new_item);
//          } else {
//            fastFace.db.form.dlg(tblId, resultObj.data.new_item);
//          }
//        } else {
//          if(typeof fnOnErr === 'function') {
//            fnOnErr(resultObj);
//          } else {
//            fastFace.msg.err(sprintf(fastFace.dict.cache.add_err, fastFace.dict.val([tblId, fastFace.tbl.small(tblId)])));
//          }
//        }
//      },
//      function() {
//        if(typeof fnOnErr === 'function') {
//          fnOnErr({tbl: tblId, fn: 'add', arg: addArg});
//        } else {
//          fastFace.msg.err(sprintf(fastFace.dict.cache.add_err, fastFace.dict.val([tblId, fastFace.tbl.small(tblId)])));
//        }
//      }
//    );
	},
				
	del: function(tblId, row_id, fnOnOk, fnOnErr) {
		return;
//    if(confirm(sprintf(fastFace.dict.cache.del_conf, fastFace.dict.val([tblId, fastFace.tbl.small(tblId)]), row_id[0]))) {
//      fastFace.pid.run(['ff\\tbl_del::del', {tbl: tblId, row_id: row_id}],
//        function(resultObj) {
//          if(resultObj.data.rows === 1) {
//            if(typeof fnOnOk === 'function') {
//              fnOnOk(resultObj);
//            } else {
//              fastFace.msg.info(sprintf(fastFace.dict.cache.del_ok, fastFace.dict.val([tblId, fastFace.tbl.small(tblId)]), row_id[0]));
//            }
//          } else {
//            if(typeof fnOnErr === 'function') {
//              fnOnErr(resultObj);
//            } else {
//              fastFace.msg.err(sprintf(fastFace.dict.cache.del_err, fastFace.dict.val([tblId, fastFace.tbl.small(tblId)]), row_id[0]));
//            }
//          }
//        },
//        function() {
//          if(typeof fnOnErr === 'function') {
//            fnOnErr({tbl: tblId, row_id: row_id, fn: 'del'});
//          } else {
//            fastFace.msg.err(sprintf(fastFace.dict.cache.del_err, fastFace.dict.val([tblId, fastFace.tbl.small(tblId)]), row_id[0]));
//          }
//        }
//      );
//    }
	},
				
	_grid: function(tblId, arg) {
		arg = arg || {tbl: tblId};
		var def = fastFace.tbl.get(tblId), gridDef = def.grid || {}, fndDef = def.fns.fnd || {}, gridDisp = 'bg'; //gridDisp = (fndDef.grid && fndDef.grid.disp ? fndDef.grid.disp : 'bg');
		if(gridDef.col) { arg.sel = arg.sel || gridDef.col; }
		fastFace.pid.run(['ff\\tbl_get::get', arg],
			function(resultObj) {
				fastFace.render[gridDisp](
					{options: $.extend({title: fastFace.dict.val([tblId, fastFace.tbl.small(tblId)])}, gridDisp === 'dlg' ? {closeOnEscape: false, width: '90%', height: $(window).height()/2} : {})},
				null,
				[['grid', resultObj, null]]
				);
			},
			function() { fastFace.msg.err('Cannot load results for [<b>'+tblId+'</b>].'); }
		);
	}

};
