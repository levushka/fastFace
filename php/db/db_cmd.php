<?
namespace ff;

class db_cmd {
	
	private static $one_before = ['NOT', '!', 'BINARY'];
	private static $one_after = ['IS NULL', 'IS NOT NULL'];
	private static $two = ['=', '<=>', '<>', '!=', '>', '>=', '<', '<=', 'LIKE', 'NOT LIKE', 'IS', 'IS NOT', 'RLIKE', 'REGEXP', 'NOT REGEXP', 'SOUNDS LIKE'];
	private static $many = ['DIV', '/', '-', '%', 'MOD', '+', '*', 'AND', '&&', 'OR', '||', 'XOR'];
	private static $between = ['BETWEEN', 'NOT BETWEEN'];
	private static $func_after = ['IN', 'NOT IN'];
	private static $func_no_arg = [
		'PI', 'RAND',
		'CURDATE', 'CURRENT_DATE', 'CURTIME', 'CURRENT_TIME', 'CURRENT_TIMESTAMP', 'LOCALTIME', 'LOCALTIMESTAMP', 'NOW', 'SYSDATE', 'UTC_DATE', 'UTC_TIME', 'UTC_TIMESTAMP'
	];
	private static $func_str = ['POSITION', 'TRIM', 'WEIGHT_STRING'];
	private static $func_numb = ['CAST', 'CONVERT'];
	private static $func_flow = ['CASE'];
	private static $func_date = ['ADDDATE', 'DATE_ADD', 'DATE_SUB', 'EXTRACT', 'GET_FORMAT', 'SUBDATE', 'TIMESTAMPADD', 'TIMESTAMPDIFF'];
	private static $func_blocked = ['LOAD_FILE', 'MATCH'];
	private static $func = [
		'ISNULL', 'NOT ISNULL', 'COALESCE', 'GREATEST', 'INTERVAL', 'LEAST', 'IF', 'IFNULL', 'NULLIF', 'INET_ATON', 'INET_NTOA', 'MD5',
		'ABS', 'ACOS', 'ASIN', 'ATAN2', 'ATAN', 'CEIL', 'CEILING', 'CONV', 'COS', 'COT', 'CRC32', 'DEGREES', 'EXP', 'FLOOR', 'LN', 'LOG10', 'LOG2', 'LOG', 'MOD', 'POW', 'POWER', 'RADIANS', 'ROUND', 'SIGN', 'SIN', 'SQRT', 'TAN', 'TRUNCATE',
		'ASCII', 'BIN', 'BIT_LENGTH', 'CHAR_LENGTH', 'CHAR', 'CHARACTER_LENGTH', 'CONCAT_WS', 'CONCAT', 'ELT', 'EXPORT_SET', 'FIELD', 'FIND_IN_SET', 'FORMAT', 'FROM_BASE64', 'HEX', 'INSERT', 'INSTR', 'LCASE', 'LEFT', 'LENGTH', 'LOCATE', 'LOWER', 'LPAD', 'LTRIM', 'MAKE_SET', 'MID', 'OCT', 'OCTET_LENGTH', 'ORD', 'QUOTE', 'REPEAT', 'REPLACE', 'REVERSE', 'RIGHT', 'RPAD', 'RTRIM', 'SOUNDEX', 'SPACE', 'STRCMP', 'SUBSTR', 'SUBSTRING_INDEX', 'SUBSTRING', 'TO_BASE64', 'UCASE', 'UNHEX', 'UPPER',
		'ADDTIME', 'CONVERT_TZ', 'DATE_FORMAT', 'DATE', 'DATEDIFF', 'DAY', 'DAYNAME', 'DAYOFMONTH', 'DAYOFWEEK', 'DAYOFYEAR', 'FROM_DAYS', 'FROM_UNIXTIME', 'HOUR', 'LAST_DAY', 'MAKEDATE', 'MAKETIME', 'MICROSECOND', 'MINUTE', 'MONTH', 'MONTHNAME', 'PERIOD_ADD', 'PERIOD_DIFF', 'QUARTER', 'SEC_TO_TIME', 'SECOND', 'STR_TO_DATE', 'SUBTIME', 'TIME_FORMAT', 'TIME_TO_SEC', 'TIME', 'TIMEDIFF', 'TIMESTAMP', 'TO_DAYS', 'TO_SECONDS', 'UNIX_TIMESTAMP', 'WEEK', 'WEEKDAY', 'WEEKOFYEAR', 'YEAR', 'YEARWEEK'
	];
	
	public static function sql($arg, array $struct, $check4role = FALSE) {
		if(is_null($arg)) {
			return 'NULL';
		} else if(is_bool($arg)) {
			return $arg ? 'TRUE' : 'FALSE';
		} if(is_float($arg)) {
			return (float)$arg;
		} if(is_int($arg) || is_string($arg)) {
			return \ff\db_col::sql($arg, $struct, $check4role);
		} if(is_array($arg)) {
			
			$cmd = key($arg);
			if(!is_string($cmd)) {
				throw new \Exception(sprintf('%s: Wrong CMD type', __METHOD__));
			}
			$cmd_arg = current($arg);
			$alias = (!empty($arg['AS']) && is_string($arg['AS']) && preg_match('/^[A-Za-z0-9]+$/', $arg['AS'])) ? ' AS `'.$arg['AS'].'`' : '';
			if($cmd === 'col') {
				return \ff\db_col::sql($cmd_arg, $struct, $check4role).$alias;
			} else if($cmd === 'val') {
				
				if(is_int($cmd_arg) || is_bool($cmd_arg)) {
					return (int)$cmd_arg.$alias;
				} if(is_float($cmd_arg)) {
					return (float)$cmd_arg.$alias;
				} if(is_string($cmd_arg)) {
					return '\''.\ff\dbh::esc($cmd_arg).'\''.$alias;
				} if(is_array($cmd_arg)) {
					return '\''.\ff\dbh::esc(implode(', ', $cmd_arg)).'\''.$alias;
				}
				throw new \Exception(sprintf('%s: Wrong VAL type', __METHOD__));
				
			} else if(in_array($cmd, static::$one_before)) {
				return '('.$cmd.' '.static::sql($cmd_arg, $struct, $check4role).')'.$alias;
			} else if(in_array($cmd, static::$one_after)) {
				return '('.static::sql($cmd_arg, $struct, $check4role).' '.$cmd.')'.$alias;
			} else if(in_array($cmd, static::$two) && \ff\checkStruct($cmd_arg, [NULL, NULL])) {
			 return '('.static::sql($cmd_arg[0], $struct, $check4role).' '.$cmd.' '.static::sql($cmd_arg[1], $struct, $check4role).')'.$alias;
			} else if(in_array($cmd, static::$many) && \ff\checkStruct($cmd_arg, [NULL, NULL])) {
				return '('.static::many($cmd, $cmd_arg, $struct, $check4role).')'.$alias;
			} else if(in_array($cmd, static::$func_no_arg)) {
				return $cmd.'()'.$alias;
			} else if(in_array($cmd, static::$func_after) && \ff\checkStruct($cmd_arg, [NULL, [NULL, NULL]])) {
				return static::func_after($cmd, $cmd_arg, $struct, $check4role).$alias;
			} else if(in_array($cmd, static::$between) && \ff\checkStruct($cmd_arg, [NULL, NULL, NULL])) {
				return static::between($cmd, $cmd_arg, $struct, $check4role).$alias;
			} else if(in_array($cmd, static::$func) && \ff\checkStruct($cmd_arg, [NULL])) {
				return static::func($cmd, $cmd_arg, $struct, $check4role).$alias;
			} else if($cmd === 'off') {
				return 'TRUE'.$alias;
			} else {
				throw new \Exception(sprintf('%s: Unrecognize CMD [%s]', __METHOD__, $name));
			}
			
		} else {
			throw new \Exception(sprintf('%s: Wrong CMD', __METHOD__));
		}
		
	}
	
	private static function many($cmd, $arg, array $struct, $check4role) {
		$res = [];
		foreach($arg as $key=>$val) {
			$res[] = static::sql($val, $struct, $check4role);
		}
		return implode(' '.$cmd.' ', $res);
	}
	
	private static function func_after($cmd, $arg, array $struct, $check4role) {
		$res = [];
		foreach($arg[1] as $key=>$val) {
			$res[] = static::sql($val, $struct, $check4role);
		}
		return static::sql($arg[0], $struct, $check4role).' '.$cmd.' ('.implode(', ', $res).')';
	}

	private static function func($cmd, $arg, array $struct, $check4role) {
		$res = [];
		foreach($arg as $key=>$val) {
			$res[] = static::sql($val, $struct, $check4role);
		}
		return $cmd.'('.implode(', ', $res).')';
	}

	private static function between($cmd, $arg, array $struct, $check4role) {
		if(empty($arg) || !is_array($arg) || count($arg) !== 3 || !isset($arg[0]) || !isset($arg[1]) || !isset($arg[2])) {
			throw new \Exception(sprintf('%s: Wrong argument for CMD [%s]', __METHOD__, $cmd));
		}
		return static::sql($arg[0], $struct, $check4role).' '.$cmd.' '.static::sql($arg[1]).' AND '.static::sql($arg[2], $struct, $check4role);
	}

	
}

