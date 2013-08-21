<?
namespace ff;

class db_where {
	
	private static $one_before = ['NOT', '!', 'BINARY'];
	private static $one_after = ['IS NULL', 'IS NOT NULL', 'ASC', 'DESC'];
	private static $two = ['=', '<=>', '<>', '!=', '>', '>=', '<', '<=', 'LIKE', 'NOT LIKE', 'IS', 'IS NOT', 'RLIKE', 'REGEXP', 'NOT REGEXP', 'SOUNDS LIKE'];
	private static $many = ['DIV', '/', '-', '%', 'MOD', '+', '*', 'AND', '&&', 'OR', '||', 'XOR'];
	private static $func_and = ['BETWEEN', 'NOT BETWEEN'];
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
	
	public static function sql(array $where, array $struct) {
		if(empty($where) || !is_array($where)) {
			throw new \Exception( sprintf( '%s: Wrong WHERE', __METHOD__ ) );
		}

		$res = [];
		foreach ($where as $key => $where_and) {
			if(empty($where_and) || !is_array($where_and)) {
				throw new \Exception( sprintf( '%s: Wrong WHERE', __METHOD__ ) );
			}
			$res[] = \ff\db_cmd::sql($where_and, $struct, FALSE);
		}
		
		if(empty($res)) {
			throw new \Exception(sprintf('%s: Empty WHERE', __METHOD__));
		}
		return '('.implode(') AND (', $res).')';
	}


}

