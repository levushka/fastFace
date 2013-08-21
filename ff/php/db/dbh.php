<?
namespace ff;

class dbh {
	
	private static $db_ver = NULL;

	private static $db_host = NULL;
	private static $db_user = NULL;
	private static $db_pass = NULL;
	private static $db_names = NULL;
	
	private static $curent_db = NULL;
	private static $mysqli = NULL;
	private static $hist_stmt = NULL;
	private static $search_add_stmt = NULL;
	private static $search_word_stmt = NULL;
	private static $search_del_stmt = NULL;
	private static $search_del_row_stmt = NULL;

	public static function config(array $ff_opt) {
		define('FF_DB_SELECT', 1 << 0); // 0001
		define('FF_DB_INSERT', 1 << 1); // 0010
		define('FF_DB_UPDATE', 1 << 2); // 0100
		define('FF_DB_DELETE', 1 << 3); // 1000
		define('FF_DB_ALL_OP', FF_DB_SELECT | FF_DB_INSERT | FF_DB_UPDATE | FF_DB_DELETE); // 1111

		define('FF_ROW_GUEST', 1 << 0); // 0001
		define('FF_ROW_OWNER', 1 << 1); // 0010
		define('FF_ROW_GROUP', 1 << 2); // 0100
		define('FF_ROW_SPEC', 1 << 3);  // 1000
		define('FF_ROW_ALL', FF_ROW_GUEST | FF_ROW_OWNER | FF_ROW_GROUP | FF_ROW_SPEC); // 1111
		
		static::$db_host = \ff\getVal($ff_opt, 'db.host', 'localhost:3306');
		static::$db_user = \ff\getVal($ff_opt, 'db.user', '');
		static::$db_pass = \ff\getVal($ff_opt, 'db.pass', '');
		static::$db_names = \ff\getVal($ff_opt, 'db.names', []);
		
		\ff\checkStruct(static::$db_names, ['ff'=>'/^[a-z0-9\_\-]+$/i', 'ff_cache'=>'/^[a-z0-9\_\-]+$/i']);
		
		array_walk(static::$db_names, function(&$item, $key) {
			$item = preg_replace('/[^a-z0-9\_\-]/i', '', strtolower($item));
		});
		
		define('FF_DB_NAME',  static::$db_names['ff']);
		define('FF_DB_CACHE', static::$db_names['ff_cache']);
	}

	public static function db_names() {
		return static::$db_names;
	}

	public static function db_name($alias) {
		return isset(static::$db_names[$alias]) ? static::$db_names[$alias] : $alias;
	}
	

	public static function open() {
		static::connect();
	}

	public static function close() {
		if(static::$mysqli !== NULL) {
			static::$mysqli->kill(static::$mysqli->thread_id);
			static::$mysqli->close();
			static::$mysqli = NULL;
		}
	}

	public static function esc($str) {
		static::connect();
		return static::$mysqli->real_escape_string($str);
	}

	public static function select_db($db_name) {
		static::connect();
		if(!empty($db_name) && $db_name !== static::$curent_db) {
			if(!static::$mysqli->select_db($db_name)) {
				throw new \Exception(static::$mysqli->error, static::$mysqli->errno);
			}
			static::$curent_db = $db_name;
		}
	}
	
	private static function connect() {
		if(static::$mysqli === NULL) {
			static::$mysqli = new \mysqli(static::$db_host, static::$db_user, static::$db_pass, FF_DB_NAME);
			if (static::$mysqli->connect_error) {
				throw new \Exception(static::$mysqli->connect_error, static::$mysqli->connect_errno);
			}
			static::$curent_db = FF_DB_NAME;
			if(!static::$mysqli->options(MYSQLI_INIT_COMMAND, 'SET @SESSION.sql_mode = \'TRADITIONAL, STRICT_TRANS_TABLES, STRICT_ALL_TABLES, ERROR_FOR_DIVISION_BY_ZERO, NO_ENGINE_SUBSTITUTION, NO_UNSIGNED_SUBTRACTION, NO_ZERO_DATE, NO_ZERO_IN_DATE, ONLY_FULL_GROUP_BY\'')) {
				throw new \Exception(static::$mysqli->error, static::$mysqli->errno);
			}
			if(!static::$mysqli->set_charset('utf8')) {
				throw new \Exception(static::$mysqli->error, static::$mysqli->errno);
			}
			if(empty(static::$db_ver)) {
				static::$db_ver = \ff\cache::get('db/ver');
				if(empty(static::$db_ver)) {
					$row = static::get_row('SELECT VERSION()', MYSQLI_NUM);
					if(empty($row)) {
						throw new \Exception('Unknown MySQL version');
					}
					static::$db_ver = $row[0];
					\ff\cache::set('db/ver', static::$db_ver);
				}
			}
//			set global general_log_file='/tmp/mysql_query.log';
//			set global general_log = 1;
//			set global general_log = 0;	
//slow_query_log
//slow_query_log_file=/var/log/mysql/slow_mysql.log
//long_query_time=0		
		}
	}

	public static function prepare($query) {
		static::connect();
		return static::$mysqli->prepare($query);
	}

	public static function hist($col_id, $row_id, $val) {
		return;
		static::connect();
		if(static::$hist_stmt === NULL) {
			static::$hist_stmt = static::$mysqli->prepare('INSERT IGNORE INTO `'.FF_DB_NAME.'`.`ff_hist` (`created_at`, `ff_user`, `ff_sess`, `ff_tbl_col`, `row`, `val`) VALUES (NOW(), '.FF_USER_ID.', '.FF_USER_SESS.', ?, ?, ?)');
		}
		static::$hist_stmt->bind_param('iis', $col_id, $row_id, $val);
		if(!static::$hist_stmt->execute()) {
			if(FF_IS_DEBUG || FF_IS_DEV) {
				throw new \Exception(static::$mysqli->error, static::$mysqli->errno);
			}
		}
	}

	public static function search_del($cls, $row_id, $col = NULL) {
		return;
		static::connect();
		if(static::$search_del_row_stmt === NULL) { static::$search_del_row_stmt = static::$mysqli->prepare('DELETE FROM `'.FF_DB_NAME.'`.`search` WHERE `cls`=? AND `row`=?'); }
		if(static::$search_del_stmt === NULL) { static::$search_del_stmt = static::$mysqli->prepare('DELETE FROM `'.FF_DB_NAME.'`.`search` WHERE `cls`=? AND `row`=? AND `col`=?'); }
		
		$res = FALSE;
		if(isset($col)) {
			static::$search_del_stmt->bind_param('sis', $cls, $row_id, $col);
			$res = static::$search_del_stmt->execute();
		} else {
			static::$search_del_row_stmt->bind_param('si', $cls, $row_id);
			$res = static::$search_del_row_stmt->execute();
		}
		if(!$res) {
			if(FF_IS_DEBUG || FF_IS_DEV) {
				throw new \Exception(static::$mysqli->error, static::$mysqli->errno);
			}
		}
	}

	public static function search($cls, $row_id, $col, $text, $min_len = 3) {
		return;
		static::connect();
		if(static::$search_word_stmt === NULL) {
			static::$search_word_stmt = static::$mysqli->prepare('INSERT IGNORE INTO `'.FF_DB_NAME.'`.`search_word` (`word`) VALUES (?)');
		}
		if(static::$search_add_stmt === NULL) {
			static::$search_add_stmt = static::$mysqli->prepare('INSERT IGNORE INTO `'.FF_DB_NAME.'`.`search` (`cls`, `row`, `col`, `word`) SELECT ?, ?, ?, `search_word`.`id` FROM `search_word` WHERE `word`=?');
		}

		static::search_del($cls, $row_id, $col);
		if(!empty($text)) {
			$words = array_unique(preg_split('/[\s]+/', html_entity_decode($text, ENT_QUOTES, 'UTF-8')));
			$query = [];
			foreach ($words as $key=>$val) {
				$val = preg_replace('/[\[\]\(\)\{\}\\\\\/\^\$\.\|\?\*\+\-\"\'`<>,;~!@#%&_=]+/', '', $val);
				if(!empty($val) && mb_strlen($val, 'UTF-8') >= $min_len) {
					$query[] = '\''.\ff\dbh::esc($val).'\'';
				}
			}
			if ( !empty($query) ) {
				if ( static::$mysqli->query('INSERT IGNORE INTO `'.FF_DB_NAME.'`.`search_word` (`word`) VALUES ('.implode('), (', $query).')' ) ) {
					if ( !static::$mysqli->query('INSERT IGNORE INTO `'.FF_DB_NAME.'`.`search` (`cls`, `row`, `col`, `word`) SELECT \''.\ff\dbh::esc($cls).'\', '.$row_id.', \''.\ff\dbh::esc($col).'\', `search_word`.`id` FROM `search_word` WHERE `word` IN ('.implode(',', $query).')' ) ) {
						if(FF_IS_DEBUG || FF_IS_DEV) {
							throw new \Exception(static::$mysqli->error.' ['.$query.']', static::$mysqli->errno);
						}
						return;
					}
				} else {
					if(FF_IS_DEBUG || FF_IS_DEV) {
						throw new \Exception(static::$mysqli->error.' ['.$query.']', static::$mysqli->errno);
					}
					return;
				}
			}
		}
	}
	
	public static function query($query) {
		static::connect();
		return static::$mysqli->query($query);
	}

	public static function del($query) {
		static::connect();
		if(static::$mysqli->query($query)) {
			return static::$mysqli->affected_rows;
		} else {
			if(FF_IS_DEBUG || FF_IS_DEV) {
				throw new \Exception(static::$mysqli->error.' ['.$query.']', static::$mysqli->errno);
			} else {
				throw new \Exception('Can not delete', static::$mysqli->errno);
			}
		}
	}

	public static function add($query) {
		static::connect();
		if(static::$mysqli->query($query)) {
			return static::$mysqli->insert_id;
		} else {
			if(FF_IS_DEBUG || FF_IS_DEV) {
				throw new \Exception(static::$mysqli->error.' ['.$query.']', static::$mysqli->errno);
			} else {
				throw new \Exception('Can not add', static::$mysqli->errno);
			}
		}
	}

	public static function upd($query) {
		static::connect();
		if(static::$mysqli->query($query)) {
			return static::$mysqli->affected_rows;
		} else {
			if(FF_IS_DEBUG || FF_IS_DEV) {
				throw new \Exception(static::$mysqli->error.' ['.$query.']', static::$mysqli->errno);
			} else {
				throw new \Exception('Can not update', static::$mysqli->errno);
			}
		}
	}

	public static function get_res($query) {
		static::connect();
		if($res_sql = static::$mysqli->query($query)) {
			return $res_sql;
		} else {
			if(FF_IS_DEBUG || FF_IS_DEV) {
				throw new \Exception(static::$mysqli->error.' ['.$query.']', static::$mysqli->errno);
			} else {
				throw new \Exception('Can not execute query', static::$mysqli->errno);
			}
		}
	}

	public static function get_row($query, $resulttype = MYSQLI_ASSOC) {
		static::connect();
		if($res_sql = static::$mysqli->query($query)) {
			$num_rows = $res_sql->num_rows;
			$fields = $res_sql->fetch_fields();
			$res_arr = $res_sql->fetch_array($resulttype);
			$res_sql->free();
			$accos = $resulttype === MYSQLI_ASSOC ? TRUE : FALSE;
			foreach ($fields as $key=>$field) {
				$fld_name = $accos ? $field->name : $key;
				if(isset($res_arr[ $fld_name ])) {
					if(\ff\db_fn::is_db_int($field->type)) {
						$res_arr[ $fld_name ] = (int)$res_arr[ $fld_name ];
					} else if(\ff\db_fn::is_db_decimal($field->type)) {
						$res_arr[ $fld_name ] = (float)$res_arr[ $fld_name ];
					}
				}
			}
			return $res_arr;
		} else {
			if(FF_IS_DEBUG || FF_IS_DEV) {
				throw new \Exception(static::$mysqli->error.' ['.$query.']', static::$mysqli->errno);
			} else {
				throw new \Exception('Can not execute query', static::$mysqli->errno);
			}
		}
	}
	
	public static function get_all($query, $resulttype = MYSQLI_NUM, $hash_col = NULL) {
		static::connect();
		if($res_sql = static::$mysqli->query($query)) {
			$num_rows = $res_sql->num_rows;
			$fields = $res_sql->fetch_fields();
			$res_arr = $res_sql->fetch_all($resulttype);
			$res_sql->free();
			$accos = $resulttype === MYSQLI_ASSOC ? TRUE : FALSE;
			foreach ($fields as $key=>$field) {
				$fld_name = $accos ? $field->name : $key;
				if(\ff\db_fn::is_db_int($field->type)) {
					for ($i = 0; $i < $num_rows; $i++) { if( isset( $res_arr[$i][ $fld_name ] ) ) $res_arr[$i][ $fld_name ] = (int)$res_arr[$i][ $fld_name ];}
				} else if(\ff\db_fn::is_db_decimal($field->type)) {
					for ($i = 0; $i < $num_rows; $i++) { if( isset( $res_arr[$i][ $fld_name ] ) ) $res_arr[$i][ $fld_name ] = (float)$res_arr[$i][ $fld_name ];}
				}
			}
			
			if($num_rows > 0 && isset($hash_col) && $hash_col !== '' && array_key_exists($hash_col, $res_arr[0])) {
				$res_hash = [];
				for ($i = 0; $i < $num_rows; $i++) { $res_hash[$res_arr[$i][$hash_col]] = $res_arr[$i]; }
				return $res_hash;
			}
			return $res_arr;
		} else {
			if(FF_IS_DEBUG || FF_IS_DEV) {
				throw new \Exception(static::$mysqli->error.' ['.$query.']', static::$mysqli->errno);
			} else {
				throw new \Exception('Can not execute query', static::$mysqli->errno);
			}
		}
	}
	
}

