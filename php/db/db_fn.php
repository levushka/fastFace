<?
namespace ff;

class db_fn {
	
//	array_walk(static::key_json_decode, $data, $indexKey);
	public static function walk_json_decode(&$value, $key, $indexKey) {
		if(is_array($indexKey)) {
			foreach($indexKeys as $indexKey) {
				$value[$indexKey] = @json_decode($value[$indexKey], TRUE);
			}
		} else {
			$value[$indexKey] = @json_decode($value[$indexKey], TRUE);
		}
	}
	
	public static function map_json_decode(array &$rows, array $indexKey) {
		if(is_array($indexKey)) {
			foreach($indexKeys as $indexKey) {
				$value[$indexKey] = @json_decode($value[$indexKey], TRUE);
			}
		} else {
			foreach($rows as $key=>$value) {
				$rows[$key][$indexKey] = @json_decode($value[$indexKey], TRUE);
			}
		}
		return $rows;
	}
	
	public static function rows2arr_format(array &$rows, array $export_format) {
		foreach($export_format as $indexKey=>$format) {
			if($format === 'json') {
				foreach($rows as $key=>$value) {
					$rows[$key][$indexKey] = !empty($value[$indexKey]) ? @json_decode($value[$indexKey], TRUE) : $value[$indexKey];
				}
			} else if($format === 'php') {
				foreach($rows as $key=>$value) {
					$rows[$key][$indexKey] = !empty($value[$indexKey]) ? @eval('return '.$value[$indexKey].';') : $value[$indexKey];
				}
			} else if($format === 'arr') {
				foreach($rows as $key=>$value) {
					$rows[$key][$indexKey] = (is_string($value[$indexKey]) && $value[$indexKey] !== '') ? array_map('intval', explode(',', $value[$indexKey])) : [];
				}
			}
		}
		return $rows;
	}

	public static function config_json($str) {
		$conf = ( !empty($str) && is_string($str) && $str[0] == '{' ) ? @json_decode($str, TRUE) : [];
		if(json_last_error()) {
			throw new \Exception(sprintf('%s: Cannot decode json config: [%s]', __METHOD__, $str));
		}
		return $conf;
	}
	
	public static function config_php($str) {
		return ( !empty($str) && is_string($str) && $str[0] == '[' ) ?  eval('return '.$str.';') : [];
	}

	public static function db_len($str, $type) {
		if(($left = strpos($str, '(')) &&( $right = strrpos($str, ')'))) {
			$res = str_getcsv(substr($str, $left+1,$right-$left-1), ',', '\'');
			if($type === 'decimal') {
				return array_map('intval', $res);
			} else if($type === 'int' || $type === 'char') {
				return (int)$res[0];
			} else if($type === 'enum' || $type === 'set') {
				return $res;
			} else {
				throw new \Exception(sprintf('%s: Unknown type: [%s]', __METHOD__, $type));
			}
		}
		return NULL;
	}

	public static function type_php2db($type) {
		if($type === 'boolean' || $type === 'integer') {
			return 'int';
		} else if($type === 'string') {
			return 'char';
		} else if($type === 'double' || $type === 'float') {
			return 'decimal';
		} else {
			throw new \Exception(sprintf('%s: Unknown type: [%s]', __METHOD__, $type));
		}
	}
	
	//Not implemented: MYSQLI_TYPE_NULL MYSQLI_TYPE_INTERVAL MYSQLI_TYPE_GEOMETRY
	public static function type_db2str($type, $len) {
		if(\ff\db_fn::is_db_int($type)) {
			return 'int';
		} else if(\ff\db_fn::is_db_decimal($type)) {
			return 'decimal';
		} else if($type === MYSQLI_TYPE_VAR_STRING || $type === MYSQLI_TYPE_CHAR || $type === MYSQLI_TYPE_STRING) {
			return 'char';
		} else if($type === MYSQLI_TYPE_DATETIME || $type === MYSQLI_TYPE_TIMESTAMP) {
			return 'datetime';
		} else if($type === MYSQLI_TYPE_DATE || $type === MYSQLI_TYPE_NEWDATE) {
			return 'date';
		} else if($type === MYSQLI_TYPE_TIME) {
			return 'time';
		} else if($type === MYSQLI_TYPE_SET) {
			return 'set';
		} else if($type === MYSQLI_TYPE_ENUM) {
			return 'enum';
		} else if($type === MYSQLI_TYPE_BLOB || $type === MYSQLI_TYPE_TINY_BLOB || $type === MYSQLI_TYPE_MEDIUM_BLOB || $type === MYSQLI_TYPE_LONG_BLOB) {
			return 'text';
		}
		return 'char';
	}

	// IS MYSQL TYPE
	public static function is_db_int($type) {
		return  $type === MYSQLI_TYPE_BIT || $type === MYSQLI_TYPE_LONG || $type === MYSQLI_TYPE_TINY || $type === MYSQLI_TYPE_SHORT || $type === MYSQLI_TYPE_INT24 || $type === MYSQLI_TYPE_LONGLONG || $type === MYSQLI_TYPE_YEAR;
	}
	
	public static function is_db_decimal($type) {
		return $type === MYSQLI_TYPE_FLOAT || $type === MYSQLI_TYPE_DOUBLE || $type === MYSQLI_TYPE_DECIMAL || $type === MYSQLI_TYPE_NEWDECIMAL;
	}
	
}

