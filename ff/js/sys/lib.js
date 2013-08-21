/*
Lev js library
*/

(function () {
	if( !this.localStorage ) {
		this.localStorage = { clear: function() {}, getItem: function() { return null; }, setItem: function() {}, removeItem: function() {} };
	}
	if( !this.sessionStorage ) {
		this.sessionStorage = { clear: function() {}, getItem: function() { return null; }, setItem: function() {}, removeItem: function() {} };
	}

	if( !this.console ) {
		this.console = { log: function() {} };
	}
	var methods = ['assert', 'count', 'debug', 'dir', 'dirxml', 'group', 'groupCollapsed', 'groupEnd', 'error', 'info', 'log', 'markTimeline', 'profile', 'profileEnd', 'table', 'time', 'timeEnd', 'trace', 'warn'];
	for ( var i = methods.length; i--; ) {
		if ( !this.console[ methods[i] ] ) {
			this.console[ methods[i] ] =  this.console.log;
		}
	}
}());

$.extend($.ui.tabs.prototype, {
	removeAll: function() {
		for ( var i = this.lis.length - 1; i >= 0; i-- ) {
			this.remove(i);
		}
	}
});

$.extend({
	
	sizeOf: function( obj ) {
		if (typeof obj === 'string') {
			return obj.length;
		}
		var bytes = 0;
		for(var i in obj) {
			if(typeof obj[i] === 'string') {
				bytes += obj[i].length;
			} else if($.isPlainObject(obj[i])) {
				bytes += $.sizeOf(obj[i]);
			}
		}
		return bytes;
	},
	
	count: function( obj ) {
		if($.isArray(obj)) {
			return obj.length;
		}
		var count = 0, key;
		for (key in obj) {
			if (obj.hasOwnProperty(key)) {
				count++;
			}
		}
		return count;
	},
	
	isRTL: function( str ) {
		return str.search(/[א-ת]/gim) >= 0 ? true : false;
	},
	
	getVal: function(obj, chain, def) {
		if (typeof chain === 'string') {
			chain = chain.split('.');
		}
		
		if(!$.isArray(chain) || !$.isPlainObject(obj)) {
			return def || null;
		}
		
		if (chain.length === 1) {
			return obj[chain[0]] || def || null;
		}
		
		return $.getVal(obj[chain.shift()], chain, def);
	},
	
	setVal: function(obj, chain, val) {
		if (typeof chain === 'string') {
			chain = chain.split('.');
		}
		
		if(!$.isArray(chain) || !$.isPlainObject(obj)) {
			return false;
		}
		
		if (chain.length === 1) {
			obj[chain[0]] = val;
			return true;
		}
		var key = chain.shift();
		
		if(!$.isPlainObject(obj[key])) {
			obj[key] = {};
		}
		return $.setVal(obj[key], chain, val);
	},
	
	intVal: function( arr ) {
		return $.map(arr, function( val, key ) { return ~~val; });
	},
	
	keys: function( obj ) {
		return $.map(obj, function( val, key ) { return key; });
	},
	
	vals: function( obj ) {
		var res = [];
		$.each(obj, function( key, val ) { res.push(val); });
		return res;
	},
	
	mergeArr: function( arr ) {
		var res = [];
		$.each(arr, function( key, val ) { $.merge(res, val); });
		return res;
	},

	unique: function( arr ) {
		return $.grep(arr, function(v, k){ return $.inArray(v, arr) === k; });
	},
	
	grepArr: function( keys, arr ) {
		return $.map(keys, function( val, key) { return arr[ val ]; });
	},
	
	grepObj: function( keys, obj ) {
		var ret = {};
		for ( var i = keys.length; i--; ) {
			ret[ keys[ i ] ] = obj[ keys[ i ] ];
		}
		return ret;
	},
	
	filterArrVal: function( filter, arr, inMatch ) {
		inMatch = typeof inMatch === 'undefined' ? true : inMatch;
		return $.map(arr, function( val, key) {
			return (val && val.match(filter)) ? (inMatch ? val : null) : (inMatch ? null : val);
		});
	},
	
	placeInArr: function( vals, arr ) {
		return $.map(vals, function( val, key) {
			return $.inArray(val, arr);
		});
	},
	
	oneOfInArr: function( vals, arr) {
		return $.arrInArr(vals, arr, false);
	},
	
	allInArr: function( vals, arr) {
		return $.arrInArr(vals, arr, true);
	},

	arrInArr: function( vals, arr, allIn) {
		var found = false;
		allIn = allIn || false;
		for ( var i = vals.length; i--; ) {
			if($.inArray(vals[i], arr) === -1) {
				if(allIn) {
					return false;
				}
			} else {
				if(allIn) {
					found = true;
				} else {
					return true;
				}
			}
		}
		return found;
	},

	makeObj: function( keys, vals ) {
		var ret = {};
		vals = vals || keys;
		if ( typeof keys === 'string' ) {
			ret[ keys ] = vals;
		} else {
			for(var i = keys.length; i--;) {
				ret[ keys[ i ] ] = vals[ i ];
			}
		}
		return ret;
	},
	
	boolV: function( val ) {
		if ( typeof val === 'boolean' ) {
			return val;
		} else if ( typeof val === 'number' ) {
			return !!val;
		} else if ( typeof val === 'string' ) {
			return $.inArray( val.toLowerCase(), ['1', 'true', 'on', 'yes', 'y', 't'] ) >= 0;
		}
		return false;
	},
	
	bytesToSize: function( bytes, precision ) {
		if ( !precision ) { precision = 2; }
		if ( bytes < 1024 ) { return bytes+' B'; }
		else if ( bytes < 1048576 ) { return ( bytes / 1024 ).toFixed(precision)+' KB'; }
		else if ( bytes < 1073741824 ) { return ( bytes / 1048576 ).toFixed(precision)+' MB'; }
		else if ( bytes < 1099511627776 ) { return ( bytes / 1073741824 ).toFixed(precision)+' GB'; }
		else { return ( bytes / 1099511627776 ).toFixed(precision)+' TB'; }
	}
});


function lzw_encode(s) {
	if(s === null) { return null; }
	if(s === '') { return ''; }
	var i;
	var dict = {};
	var data = (s + "").split("");
	var out = [];
	var currChar;
	var phrase = data[0];
	var code = 256;
	for (i=1; i<data.length; i++) {
		currChar=data[i];
		if (dict[phrase + currChar] !== null) {
			phrase += currChar;
		}
		else {
			out.push(phrase.length > 1 ? dict[phrase] : phrase.charCodeAt(0));
			dict[phrase + currChar] = code;
			code++;
			phrase=currChar;
		}
	}
	out.push(phrase.length > 1 ? dict[phrase] : phrase.charCodeAt(0));
	for (i=0; i<out.length; i++) {
		out[i] = String.fromCharCode(out[i]);
	}
	return out.join("");
}

function lzw_decode(s) {
	var dict = {};
	var data = (s + "").split("");
	var currChar = data[0];
	var oldPhrase = currChar;
	var out = [currChar];
	var code = 256;
	var phrase;
	for (var i=1; i<data.length; i++) {
		var currCode = data[i].charCodeAt(0);
		if (currCode < 256) {
			phrase = data[i];
		}
		else {
			phrase = dict[currCode] ? dict[currCode] : (oldPhrase + currChar);
		}
		out.push(phrase);
		currChar = phrase.charAt(0);
		dict[code] = oldPhrase + currChar;
		code++;
		oldPhrase = phrase;
	}
	return out.join("");
}


function xlsBOF() {
	return pack("ssssss", 0x809, 0x8, 0x0, 0x10, 0x0, 0x0);
}

function xlsEOF() {
	return pack("ss", 0x0A, 0x00);
}

function xlsCodepage($codepage) {
	var $record   = 0x0042;    // Codepage Record identifier
	var $length   = 0x0002;    // Number of bytes to follow
	return pack('vv', $record, $length) + pack('v',  $codepage || 65001); // UTF-8
}

function xlsWriteNumber($Row, $Col, $Value) {
	return pack("sssss", 0x203, 14, $Row, $Col, 0x0) + pack("d", $Value);
}

function xlsWriteLabel($Row, $Col, $Value) {
	var $L = ($Value || '').length;
	return pack("ssssss", 0x204, 8 + $L, $Row, $Col, 0x0, $L) + $Value;
}

function checkURL(url) {
	//var regURL = /^(?:(?:https?|ftp|telnet):\/\/(?:[a-z0-9_-]{1,32}(?::[a-z0-9_-]{1,32})?@)?)?(?:(?:[a-z0-9-]{1,128}\.)+(?:com|net|org|mil|edu|arpa|ru|gov|biz|info|aero|inc|name|[a-z]{2})|(?!0)(?:(?!0[^.]|255)[0-9]{1,3}\.){3}(?!0|255)[0-9]{1,3})(?:\/[a-z0-9.,_@%&?+=\~\/-]*)?(?:#[^ \'\"&<>]*)?$/i;
	//return regURL.test(url);
}

//Math.round = (function() {
//  var oldRound = Math.round;
//  return function(number, precision) {
//    precision = Math.abs(parseInt(precision, 10)) || 0;
//    var coefficient = Math.pow(10).toFixed(precision);
//    return oldRound(number*coefficient)/coefficient;
//  };
//}());

//Number.prototype.round = Math.round;

//template = '<option value="[value]">[name]</option>';
//function T (data, template) {
//  var array = [];
//  $(data).each(function(key, val){
//    array.push(template.replace(/(\[([^\[\]]+)\])/g, function($0, $1, $2){
//      return $2 in val ? val[$2] : "";
//    }));
//  });
//  return array.join("");
//}
