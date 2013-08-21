///////////////////////////////////////////////
//               DB RUN
///////////////////////////////////////////////
fastFace.tbl.run = function(tblUrl, fn, arg, success, fail, finalFn) {

	fastFace.sync.start();

	//try {

		//var storageKey = fastFace.lang.cur+'_'+fastFace.login.data.user.id+'_'+fastFace.tbl.name(tblUrl);
		//if(this.isLS && !fastFace.tbl.isLoaded(tblUrl)) {
			//fastFace.tbl.load(tblUrl, {data: $.parseJSON(window.localStorage.getItem(storageKey))});
		//}
		//$.filterArrVal(/^arr_|^cache_/, $.keys(fastFace.tbl.data), false)
	
		var id = this.getId(tblUrl);
		if(!id || !this.isLoaded(id)) {
			
			if(!fastFace.tbl.toLoad[tblUrl]) {
				fastFace.tbl.toLoad[tblUrl] = true;

				fastFace.sync.wrap(
					'tbl_def_tbl_loader',
					[
						['ff\\tbl_def::loaded', 'arr', $.intVal($.keys(fastFace.arr.data))],
						['ff\\tbl_def::loaded', 'tbl', $.intVal($.keys(fastFace.tbl.data))],
						['ff\\tbl_def::load', $.keys(fastFace.tbl.toLoad)]
					],
					function() {
						fastFace.tbl.toLoad = {};
						fastFace.sync.start();
					},
					null,
					null,
					null,
					function() {
						fastFace.sync.end();
						fastFace.gui.updateLogoInfo();
					}
				);
			}

			if(fn || arg || success || fail || finalFn) {
				fastFace.sync.add(null, function() {
					var id = fastFace.tbl.getId(tblUrl);
					if(id && fastFace.tbl.isLoaded(id)) {
						fastFace.tbl.run(tblUrl, fn, arg, success, fail, finalFn);
					} else {
						throw new Error('Definition for TBL [<b>'+tblUrl+'</b>] not loaded.');
					}
				});
			}
				
		} else {
			
				if(typeof fn === 'function') {
					
					fn(arg);
					
				} else if(typeof fn === 'string') {
					
					if(fastFace.db['_'+fn]) {
						fastFace.db['_'+fn](id, arg);
					} else if(fn) {
						fastFace.msg.err('Fn[<b>'+fn+'</b>] for tbl[<b>'+tblUrl+'</b>] not permited.');
					}
					
				}
				
				if(typeof success === 'function') {
					success();
				}
				if(typeof finalFn === 'function') {
					finalFn();
				}
			
		}

	//} catch(err) {
	//  fastFace.err.js(err);
	//}

	fastFace.sync.end();
	
	return false;
};