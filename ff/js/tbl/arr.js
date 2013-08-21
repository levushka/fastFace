///////////////////////////////////////////////
//               Array
///////////////////////////////////////////////

fastFace.arr = {
	
	data:     {},
//  id2url:   {},
//  url2id:   {},
	
	reInit: function() {
		return;
//    fastFace.sync.start();
//    fastFace.pid.run(
//      [
//        'ff\\tbl_get::get',
//        {
//          SELECT: ['id', 'url'],
//          FROM:   ['ff_arr_grp']
//        }
//      ],
//      function(resultObj) {
//        var arr = fastFace.arr, resData = resultObj.data, len = resData.length, id, url;
//        arr.id2url = {};
//        arr.url2id = {};
//        for(var i=0; i<len; i++) {
//          id = resData[i][0];
//          url = resData[i][1];
//          arr.id2url[id] = url;
//          arr.url2id[url] = id;
//        }
//      }
//    );
//    fastFace.pid.run(
//      [
//        'ff\\tbl_get::get',
//        {
//          SELECT:     ['ff_arr_grp', 'key', 'val'],
//          FROM:       ['ff_arr'],
//          'ORDER BY': ['ord', 'key']
//        }
//      ],
//      function(resultObj) {
//        var d = fastFace.dict, arr = fastFace.arr, resData = resultObj.data, len = resData.length, id;
//        arr.data = {};
//        for(var i=0; i<len; i++) {
//          id = resData[i][0];
//          if(typeof arr.data[id] === 'undefined') {
//            arr.data[id] = [];
//          }
//          arr.data[id].push([resData[i][1], d.val(resData[i][2])]);
//        }
//      }
//    );
//    fastFace.sync.end();
	},
	
	load: function(id, arr) {
		fastFace.arr.data[id] = arr;
	}

};