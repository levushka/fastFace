<?php

namespace ff;

function getCOOKIE($key, $default = NULL) {
	return isset($_COOKIE[$key]) ? $_COOKIE[$key] : $default;
}

function getSESSION($key, $default = NULL) {
	return isset($_SESSION[$key]) ? $_SESSION[$key] : $default;
}

function getSERVER($key, $default = NULL) {
	return isset($_SERVER[$key]) ? $_SERVER[$key] : $default;
}

function delREQUEST($key) {
	unset($_REQUEST[$key]);
	unset($_GET[$key]);
	unset($_POST[$key]);
}

function setREQUEST($key, $val) {
	$_REQUEST[$key] = $_GET[$key] = $_POST[$key] = $val;
}

function getREQUEST($key, $default = NULL) {
	return isset($_REQUEST[$key]) ? $_REQUEST[$key] : $default;
}

function getGET($key, $default = NULL) {
	return isset($_GET[$key]) ? $_GET[$key] : $default;
}

function getPOST($key, $default = NULL) {
	return isset($_POST[$key]) ? $_POST[$key] : $default;
}

function getVal4Key(array $arr, $key, $default = NULL) {
	return isset($arr[$key]) ? $arr[$key] : $default;
}

function getVal(array $arr, $keys, $default = NULL) {
	if(is_array($arr) && count($arr) > 0) {
		if(is_string($keys)) {
			$keys = explode('.', $keys);
		}
		if(is_array($keys) && count($keys) > 0 && isset($arr[$keys[0]])) {
			if(count($keys) === 1) {
				return $arr[$keys[0]];
			} else {
				return \ff\getVal($arr[$keys[0]], array_slice($keys, 1), $default);
			}
		}
	}
	return $default;
}

function getURL($url, $root = NULL, $current = NULL) {
	return \ff\getPathOrURL($url, isset($root) ? $root : FF_SERVER_URL, $current);
}

function getPATH($path, $root = NULL, $current = NULL) {
	return \ff\getPathOrURL($path, isset($root) ? $root : FF_DIR_ROOT, $current);
}

function getPathOrURL($pathORurl, $root, $current = NULL) {
	return 0 === strpos( $pathORurl, '//' ) ? $root . substr( $pathORurl, 1 ) : (0 === strpos( $pathORurl, '/' ) ? $pathORurl : (isset($current) ? $current . '/' : '') . $pathORurl);
}

function findKey(array $arr, array $keys) {
	foreach($keys as $key=>$val) {
		if(isset($arr[$val])) {
			return $val;
		}
	}
	return NULL;
}

function checkStruct($arg, $struct) {
	if(is_null($struct)) {
		return TRUE;
	} else if(gettype($struct) === gettype($arg)) {
		if(!empty($struct)) {
			if(is_array($struct)) {
				foreach($struct as $key=>$val) {
					if(isset($arg[$key])) {
						\ff\checkStruct($arg[$key], $val);
					} else {
						throw new \Exception(sprintf('%s: Wrong argument. Example: %s', __METHOD__, var_export($struct, TRUE) ));
					}
				}
			} else if(is_string($struct) && preg_match($struct, $arg) !== 1) {
				throw new \Exception(sprintf('%s: Wrong argument. Example: %s', __METHOD__, var_export($struct, TRUE) ));
			}
		}
		return TRUE;
	}
	throw new \Exception(sprintf('%s: Wrong argument. Example: %s', __METHOD__, var_export($struct, TRUE) ));
}

function boolV( $var ) {
	if( is_bool( $var ) ) {
		return $var;
	} elseif( is_int( $var ) || is_float($var)  ) {
		return (bool)$var;
	} elseif( is_string( $var ) ) {
		return is_int($var) ? (bool)intval($var) : in_array(strtolower( trim( $var ) ), ['true', 'on', 'yes', 'y', 't', '1'], TRUE);
	}
	return FALSE;
}

function require_and_apply( array $list, $args = NULL) {
	foreach ( $list as $key => $val ) {
		if( is_string($val) && strpos( $val, '.php' ) !== FALSE ) {
			$file = 0 === strpos( $val, '//' ) ? FF_DIR_ROOT.'/'.substr( $val, 2 ) : $val;
			if( is_file($file) ) {
				require_once($file);
			} else {
				throw new \Exception( sprintf( '%s: File [%s] not found', __METHOD__, $val ) );
			}
		} else {
			if(is_array($val)) {
				$func_method = $val[0];
				$func_method_args = isset($val[1]) ? $val[1] : $args;
			} else {
				$func_method = $val;
				$func_method_args = $args;
			}
			$delimeter_pos = strpos( $func_method, '::' );
			if( $delimeter_pos === FALSE ) {
				if(function_exists( $func_method )) {
					return call_user_func_array( $func_method, $func_method_args );
				} else {
					throw new \Exception( sprintf( '%s: Function [%s] not found', __METHOD__, $func_method ) );
				}
			} else {
				$class = substr($func_method, 0, $delimeter_pos);
				$method = substr($func_method, $delimeter_pos+2);
				if( !class_exists( $class )) {
					\ff\cls::autoload($class);
				}
				if( class_exists( $class ) ){
					if( method_exists( $class, $method ) ){
						return call_user_func_array( $func_method, $func_method_args );
					} else {
						throw new \Exception( sprintf( '%s: Method [%s] not found', __METHOD__, $func_method ) );
					}				
				} else {
					throw new \Exception( sprintf( '%s: Class [%s] not found', __METHOD__, $func_method ) );
				}				
			}
		}

	}
			
			if( is_array($val) ) {
				if( class_exists( $val[1] ) ) {
					if( method_exists( $val[1], $val[2] ) ) {
						return call_user_func( $val[1].'::'.$val[2], !empty($val[3]) ? $val[3] : $args );
					} else {
						throw new \Exception( sprintf( '%s: Method %s::%s not found', __METHOD__, $val[1], $val[2] ) );
					}
				} else {
					throw new \Exception( sprintf( '%s: Class [%s] not loaded', __METHOD__, $val[1] ) );
				}
			}
		}
	}
}

function get_file_int( $file ) {
	$tmp = 0;
	if( is_readable( $file ) ) {
		$fp = fopen( $file, 'r' );
		$tmp = (int)( fread( $fp, 16 ) );
		fclose( $fp );
	}
	return $tmp;
}

function define_arr(array $keys, array $vals = NULL, $pef = '') {
	foreach( $keys as $key=>$val ) {
		$name = strtoupper($pef.(is_int($key) ? $val : $key));
		if(!defined($name)) {
			define($name, isset($vals[$key]) ? $vals[$key] : (is_int($key) ? (isset($vals[$val]) ? $vals[$val] : FALSE) : $val));
		}
	}
}

function arr_bool2int( array $arr ) {
	foreach ( $arr as $key => $val ) {
		if( is_array( $val ) ) {
			$arr[$key] = \ff\arr_bool2int($val);
		} else if(is_bool($val)) {
			$arr[$key] = (int)$val;
		}
	}
	return $arr;
}

function arr_search( $needle, array $haystack ) {
	foreach ( $haystack as $key => $sub_arr ) {
		if( in_array( $needle, $sub_arr, TRUE ) ) {
			return $key;
		}
	}
	return NULL;
}

function arr_replace( array $arr, array $arr1 ) {
	foreach ( $arr as $key => $val ) {
		$arr[$key] = isset($arr1[$val]) ? $arr1[$val] : $val;
	}
	return $arr;
}

function arr_in_arr( array $needle_arr, array $haystack, $any = TRUE ) {
	$found = FALSE;
	foreach ( $needle_arr as $key => $val ) {
		if ( in_array( $val, $haystack, TRUE ) ) {
			if($any) {
				return TRUE;
			} else {
				$found = TRUE;
			}
		} else if(!$any) {
			return FALSE;
		}
	}
	return $found;
}

function in_arr( $needle, array $haystack, $from_left = true ) {
	if( is_array( $needle ) ) {
		foreach( $needle as $k=>$v ) {
			if( is_string( $k ) ) {
				$res = \ff\in_arr( $k, $haystack, $from_left );
				if( $res ) {
					return $from_left ? ( is_array( $v ) ? $k : $v ) : $res;
				}
			}
			$res = \ff\in_arr( $v, $haystack, $from_left );
			if( $res ) {
				return $v;
			}
		}
	} else {
		foreach( $haystack as $k=>$v ) {
			$res = is_string( $k ) ? $needle === $k : FALSE;
			if( $res ) {
				return $needle;
			}
			if( is_array( $v ) ) {
				$res = \ff\in_arr( $needle, $v,  $from_left );
			} else {
				$res = $needle === $v;
			}
			if( $res ) {
				return $res;
			}
		}
	}
	return FALSE;
}

function is_equal_arr(array $arr1, array $arr2) {
	foreach($arr1 as $key=>$val) {
		if(!isset($arr2[$key]) || gettype($val) !== gettype($arr2[$key])) {
			return FALSE;
		} else if(is_array($val)) {
			if(count($val) !== count($arr2[$key])) {
				return FALSE;
			} else if(!\ff\is_equal_arr($val, $arr2)) {
				return FALSE;
			}
		} else if($val !== $arr2[$key]) {
			return FALSE;
		}
	}
	return TRUE;
}

function array_reindex(array $input, $indexKey = NULL, $merge = FALSE) {
	$result = [];
	if(isset($indexKey)) {
		if($merge) {
			foreach($input as $row_id=>$row) {
				$new_id = $row[$indexKey];
				if(isset($result[$new_id])) {
					$result[$new_id] = array_replace_recursive($result[$new_id], $row);
				} else {
					$result[$new_id] = $row;
				}
			}
		} else {
			foreach($input as $row_id=>$row) {
				$result[$row[$indexKey]] = $row;
			}
		}
	} else {
		foreach($input as $row_id=>$row) {
			$result[] = $row;
		}
	}
	return $result;
}

// Generate a random character string
function rand_str( $length = 32, $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890' ) {
	// Length of character list
	$chars_length = ( strlen( $chars ) - 1 );

	// Start our string
	$string = $chars{rand( 0, $chars_length )};

	// Generate random string
	for ( $i = 1; $i < $length; $i = strlen( $string ) ) {
		// Grab a random character from our list
		$r = $chars{rand( 0, $chars_length )};

		// Make sure the same two characters don't appear next to each other
		if ( $r != $string{$i - 1} ) $string .=  $r;
	}

	// Return the string
	return $string;
}


function format_bytes( $bytes, $precision = 2 ) {
	if ( $bytes < 1024 ) return $bytes.' B';
	elseif ( $bytes < 1048576 ) return round( $bytes / 1024, $precision ).' KB';
	elseif ( $bytes < 1073741824 ) return round( $bytes / 1048576, $precision ).' MB';
	elseif ( $bytes < 1099511627776 ) return round( $bytes / 1073741824, $precision ).' GB';
	else return round( $bytes / 1099511627776, $precision ).' TB';
}

function rrmdir( $dir, $prefix = NULL, $ext = NULL, $content_only = FALSE ) {
	if ( is_dir( $dir ) ) {
		$prefix_len = !empty($prefix) ? strlen($prefix) : 0;
		$ext_len = !empty($ext) ? strlen($ext) : 0;
		$objects = scandir( $dir );
		foreach ( $objects as $tmp_key => $object ) {
			if ( $object != '.' && $object != '..' ) {
				if((!$prefix_len || 0 === strpos($object, $prefix)) || (!$ext_len || (strlen($object) - $ext_len) === strrpos($object, $ext))) {
					if ( is_dir( $dir.'/'.$object ) ) {
						rrmdir( $dir.'/'.$object );
					} else {
						unlink( $dir.'/'.$object );
					}
				}
			}
		}
		reset( $objects );
		if(!$content_only) {
			rmdir( $dir );
		}
	}
}

function runlink( $dir, $prefix = NULL, $ext = NULL ) {
	if ( is_dir( $dir ) ) {
		$prefix_len = !empty($prefix) ? strlen($prefix) : 0;
		$ext_len = !empty($ext) ? strlen($ext) : 0;
		$objects = scandir( $dir );
		foreach ( $objects as $tmp_key => $object ) {
			if ( $object != '.' && $object != '..' ) {
				if((!$prefix_len || 0 === strpos($object, $prefix)) || (!$ext_len || (strlen($object) - $ext_len) === strrpos($object, $ext))) {
					if ( is_dir( $dir.'/'.$object ) ) {
						runlink( $dir.'/'.$object );
					} else {
						unlink( $dir.'/'.$object );
					}
				}
			}
		}
		reset( $objects );
	}
}

function checkPerm( $cls, array $fnArr ) {
	return TRUE;
	
	if( !FF_IS_USER_SUPER && empty( $_SESSION[ FF_TOKEN ][ 'role' ][ $cls ] ) ) {
		return FALSE;
	}

	if( FF_IS_USER_SUPER || isset( $_SESSION[ FF_TOKEN ][ 'role' ][ $cls ][ 'any_cmd' ] ) ) {
		return TRUE;
	}

	return \ff\arr_in_arr( $fnArr, $_SESSION[ FF_TOKEN ][ 'role' ][ $cls ] );
}

function array_to_csv( array $array, $header_row = TRUE, $col_sep = ",", $row_sep = "\n", $qut = '"' ) {
	if ( !is_array( $array ) or !is_array( $array[ 0 ] ) ) return FALSE;

	$output = '';
	//Header row.
	if ( $header_row )
	{
		foreach ( $array[ 0 ] as $key => $val )
		{
			//Escaping quotes.
			$key = str_replace( $qut, "$qut$qut", $key );
			$output .= "$col_sep$qut$key$qut";
		}
		$output = substr( $output, 1 )."\n";
	}
	//Data rows.
	foreach ( $array as $key => $val )
	{
		$tmp = '';
		foreach ( $val as $cell_key => $cell_val )
		{
			//Escaping quotes.
			$cell_val = str_replace( $qut, "$qut$qut", $cell_val );
			$tmp .= "$col_sep$qut$cell_val$qut";
		}
		$output .= substr( $tmp, 1 ).$row_sep;
	}

	return $output;
}

function xlsBOF( ) {
	return pack( "ssssss", 0x809, 0x8, 0x0, 0x10, 0x0, 0x0 );
}

function xlsEOF( ) {
	return pack( "ss", 0x0A, 0x00 );
}

function xlsCodepage( $codepage = 65001 ) {
	return pack( 'vv', 0x0042, 0x0002 ) . pack( 'v',  $codepage );
}

function xlsWriteNumber( $Row, $Col, $Value ) {
	return pack( "sssss", 0x203, 14, $Row, $Col, 0x0 ) . pack( "d", $Value );
}

function xlsWriteLabel( $Row, $Col, $Value ) {
	$L = strlen( $Value );
	return pack( "ssssss", 0x204, 8 + $L, $Row, $Col, 0x0, $L ) . $Value;
}

function heb_translit( $string ) {
	return implode(array_map(function($char) {
		$heb=[
			'א'=>'a', 'ב'=>'b', 'ג'=>'g', 'ד'=>'d', 'ה'=>'ha', 'ו'=>'v', 'ז'=>'z', 'ח'=>'h', 'ט'=>'t', 'י'=>'y',
			'כ'=>'k', 'ך'=>'k', 'ל'=>'l', 'מ'=>'m', 'ם'=>'m', 'נ'=>'n', 'ן'=>'n', 'ס'=>'s', 'ע'=>'e', 'פ'=>'p',
			'ף'=>'p', 'צ'=>'ts', 'ץ'=>'ts', 'ק'=>'q', 'ר'=>'r', 'ש'=>'sh', 'ת'=>'t'];
		return isset($heb[$char]) ? $heb[$char] : $char;
	}, preg_split('//u', $string, -1, PREG_SPLIT_NO_EMPTY)));
}

function check_url( $url ) {
	return preg_match_all( '/^( ?:( ?:https?|ftp|telnet ):\/\/( ?:[ a-z0-9_- ]{1,32}( ?::[ a-z0-9_- ]{1,32} )?@ )? )?( ?:( ?:[ a-z0-9- ]{1,128}\. )+( ?:com|net|org|mil|edu|arpa|ru|gov|biz|info|aero|inc|name|[ a-z ]{2} )|( ?!0 )( ?:( ?!0[ ^. ]|255 )[ 0-9 ]{1,3}\. ){3}( ?!0|255 )[ 0-9 ]{1,3} )( ?:\/[ a-z0-9.,_@%&?+=\~\/- ]* )?( ?:#[ ^ \'\"&<> ]* )?$/i', $url );
}

function my_ob_gzhandler( &$buffer ) {
	$compresed = ob_gzhandler( $buffer, 5 );
	header( 'Content-Length: '.strlen( $compresed ) );
	return $compresed;
}

if ( !function_exists('sem_get') ) { 
    function sem_get($key) {
    	return 'sem/'.$key;
    } 
    
    function sem_acquire($sem_id) {
    	$flag = \ff\lcache::get($sem_id);
    	if($flag === NULL || $flag === FALSE) {
				\ff\lcache::set($sem_id, TRUE, 10);
				return TRUE;
    	}
			return FALSE;
    }
     
    function sem_release($sem_id) {
			\ff\lcache::del($sem_id);
    } 
} 

if ( !function_exists('array_column') ) { 
	function array_column(array $input, $columnKey = 0, $indexKey = NULL) {
		$result = [];
		if(isset($indexKey)) {
			foreach($input as $row_id=>$row) {
				$result[$row[$indexKey]] = $row[$columnKey];
			}
		} else {
			foreach($input as $row_id=>$row) {
				$result[] = $row[$columnKey];
			}
		}
		return $result;
	}
}
