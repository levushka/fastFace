<?
namespace ff;

class err {
	
	private static $php_err_type = array (
		E_ERROR              => 'Error',
		E_WARNING            => 'Warning',
		E_PARSE              => 'Parsing Error',
		E_NOTICE             => 'Notice',
		E_CORE_ERROR         => 'Core Error',
		E_CORE_WARNING       => 'Core Warning',
		E_COMPILE_ERROR      => 'Compile Error',
		E_COMPILE_WARNING    => 'Compile Warning',
		E_USER_ERROR         => 'User Error',
		E_USER_WARNING       => 'User Warning',
		E_USER_NOTICE        => 'User Notice',
		E_STRICT             => 'Runtime Notice',
		E_RECOVERABLE_ERROR  => 'Catchable Fatal Error',
		E_DEPRECATED         => 'Catchable Fatal Error',
		E_USER_DEPRECATED    => 'Catchable Fatal Error'
	);

	private static $json_err_type = array (
		JSON_ERROR_NONE           => 'No errors',
		JSON_ERROR_DEPTH          => 'Naximum stack depth exceeded',
		JSON_ERROR_CTRL_CHAR      => 'Unexpected control character found',
		JSON_ERROR_STATE_MISMATCH => 'Invalid or malformed JSON',
		JSON_ERROR_SYNTAX         => 'Syntax error, malformed JSON',
		JSON_ERROR_UTF8           => 'Malformed UTF-8 characters, possibly incorrectly encoded JSON'
	);
	
	private static $file_err = TRUE;
	private static $file_err_detail = TRUE;
	private static $email_err = NULL;
	
	private static $is_throw_trace = FALSE;
	private static $is_throw_cookie = FALSE;
	private static $is_throw_get = FALSE;
	private static $is_throw_post = FALSE;
	private static $is_throw_request = FALSE;
	private static $is_throw_files = FALSE;
	private static $is_throw_env = FALSE;
	private static $is_throw_session = FALSE;
	private static $is_throw_server = FALSE;
	private static $is_throw_context = FALSE;

	public static function config(array $ff_opt) {
		static::$file_err = FF_DIR_TMP.'/error_'.FF_SERVER_NAME.FF_SERVER_PORT.'.log';
		static::$file_err_detail = FF_DIR_TMP.'/error_detail_'.FF_SERVER_NAME.FF_SERVER_PORT.'.log';
		static::$email_err = \ff\getVal($ff_opt, 'error.email', 'lev@kitsis.ca');
		
		static::$is_throw_trace = \ff\getVal($ff_opt, 'err.throw_trace', FALSE);
		static::$is_throw_cookie = \ff\getVal($ff_opt, 'err.throw_cookie', FALSE);
		static::$is_throw_get = \ff\getVal($ff_opt, 'err.throw_get', FALSE);
		static::$is_throw_post = \ff\getVal($ff_opt, 'err.throw_post', FALSE);
		static::$is_throw_request = \ff\getVal($ff_opt, 'err.throw_request', FALSE);
		static::$is_throw_files = \ff\getVal($ff_opt, 'err.throw_files', FALSE);
		static::$is_throw_env = \ff\getVal($ff_opt, 'err.throw_env', FALSE);
		static::$is_throw_session = \ff\getVal($ff_opt, 'err.throw_session', FALSE);
		static::$is_throw_server = \ff\getVal($ff_opt, 'err.throw_context', FALSE);
		static::$is_throw_context = \ff\getVal($ff_opt, 'err.throw_trace', FALSE);

		set_time_limit((FF_IS_DEBUG || FF_IS_DEV) ? 360 : 30);
		ini_set('display_errors', (FF_IS_DEBUG || FF_IS_DEV) ? '1' : '0');
		ini_set('html_errors', '0');
		ini_set('error_log', static::$file_err);
		ini_set('log_errors', '1');

		if(\ff\getVal($ff_opt, 'shutdown_function', TRUE)) {
			register_shutdown_function('\\ff\\err::shutdown_handler');
		}
		
		if(\ff\getVal($ff_opt, 'exception_handler', TRUE)) {
			set_exception_handler('\\ff\\err::exception_handler');
		}
		
		if(\ff\getVal($ff_opt, 'error_handler', TRUE)) {
			set_error_handler('\\ff\\err::error_handler', FF_IS_DEBUG ? error_reporting() : E_ERROR | E_PARSE );  //FF_IS_DEBUG ? E_ALL : E_ERROR | E_PARSE
		} else if(!FF_IS_DEBUG) {
			error_reporting(E_ERROR | E_PARSE);
		}
	}

	public static function shutdown_handler() {
		$err = error_get_last();
		if($err !== NULL) {
			return static::handler('[SHUTDOWN ERROR] '.$err['message'], 0, $err['type'], $err['file'], $err['line'], NULL, (static::$is_throw_trace && function_exists('debug_backtrace')) ? debug_backtrace(TRUE) : NULL);
		}
	}

	public static function error_handler($lvl, $msg, $file, $line, array $context) {
		return static::handler($msg, 0, $lvl, $file, $line, $context, (static::$is_throw_trace && function_exists('debug_backtrace')) ? debug_backtrace(TRUE) : NULL);
	}

	public static function exception_handler(\Exception $e) {
		return static::handler(
			$e->getMessage(),
			$e->getCode(),
			get_class($e) === 'ErrorException' ? $e->getSeverity() : E_USER_NOTICE,
			$e->getFile(),
			$e->getLine(),
			NULL,
			static::$is_throw_trace ? $e->getTrace() : NULL
		);
	}
	
	
	
	public static function json_err_code($lvl) {
		return isset(static::$json_err_type[$lvl]) ? static::$json_err_type[$lvl] : 'Unknown json error';
	}
	
	

	private static function out(array $err, $isExit = FALSE) {
		$err_txt = print_r($err, TRUE).PHP_EOL;
		if(defined('FF_IS_OUT_HTML') && FF_IS_OUT_HTML) {
			echo '>">\'>'.PHP_EOL.
				'</script>'.PHP_EOL.
				'<PRE dir=ltr align=left style="direction: ltr; text-align: left;">'.PHP_EOL.
				$err_txt.
				'</PRE>';
				echo '<script>var e='.json_encode($err_txt).'; if(typeof fastFace === \'undefined\') {alert(e);} else {fastFace.msg.err(e);};</script>'.PHP_EOL;
		} else if(defined('FF_IS_OUT_JS') && defined('FF_IS_OUT_JSON') && (FF_IS_OUT_JS || FF_IS_OUT_JSON)) {
			if(strpos($err_txt,'*RECURSION*') === FALSE) {
				$err_txt = @json_encode($err);
				if( json_last_error() ) {
					$err_txt = @json_encode(print_r($err, TRUE));
				}
			}
			if(FF_IS_OUT_JS) {
				echo 'if(typeof fastFace === \'undefined\') { alert("Error:\n"+'.$err_txt.'); } else { fastFace.err.php('.$err_txt.'); }'.PHP_EOL;
			} else if(FF_IS_OUT_JSON) {
				echo '{"err":'.$err_txt.'}'.PHP_EOL;
			}
		} else {
			echo $err_txt;
		}

		if($isExit) {
			exit;
		}
	}
	
	public static function handler($msg, $code = NULL, $lvl = NULL, $file = NULL, $line = NULL, array $context = NULL, array $trace = NULL) {
		try {
			error_log(date('Y-m-d H:i:s').', '.$code.', '.$lvl.', '.$file.', '.$line.', '.$msg.PHP_EOL, 3, static::$file_err);

			$err = [
				'time'=> date('Y-m-d H:i:s'),
				'ver'=> defined('FF_VER') ? FF_VER : 0,
				'c_ver'=> defined('FF_VER_CLIENT') ? FF_VER_CLIENT : 0,
				'from'=> 'php',
				'lvl' => $lvl,
				'type'=> isset(static::$php_err_type[$lvl]) ? static::$php_err_type[$lvl] : 'Unknown severity',
				'message' => $msg,
				'code' => $code,
				'file' => $file,
				'line' => $line,
				'user_id' => defined('FF_USER_ID') ? FF_USER_ID : 0,
				'user_grp' => defined('FF_USER_GRP') ? FF_USER_GRP : 0,
				'user_type' => defined('FF_USER_TYPE') ? FF_USER_TYPE : 0,
				'user_perm' => defined('FF_USER_ROLE') ? FF_USER_ROLE : 0,
				'user_super' => defined('FF_IS_USER_SUPER') ? FF_IS_USER_SUPER : FALSE,
				'user_login' => defined('FF_USER_LOGIN') ? FF_USER_LOGIN : '',
				'user_name' => defined('FF_USER_NAME') ? FF_USER_NAME : '',
				'server_name' => defined('FF_SERVER_NAME') ? FF_SERVER_NAME : '',
				'server_port' => defined('FF_SERVER_PORT') ? FF_SERVER_PORT : '',
				'server_port' => defined('FF_SERVER_PORT') ? FF_SERVER_PORT : '',
				'method' => defined('FF_REQUEST_METHOD') ? FF_REQUEST_METHOD : '',
				'URI' => (isset($_SERVER) && isset($_SERVER['REQUEST_URI'])) ? $_SERVER['REQUEST_URI'] : '',
				'PHP_SELF' => defined('FF_PHP_SELF') ? FF_PHP_SELF : '',
				'PHP_VERSION' => PHP_VERSION,
				'PHP_OS' => PHP_OS,
				'lang' => defined('FF_LANG') ? FF_LANG : '',
				'trace' => static::$is_throw_trace ? ((empty($trace) && function_exists('debug_backtrace')) ? debug_backtrace(TRUE) : $trace ) : NULL,
				'COOKIE' => (static::$is_throw_cookie && isset($_COOKIE)) ? $_COOKIE : NULL,
				'GET' => (static::$is_throw_get && isset($_GET)) ? $_GET : NULL,
				'POST' => (static::$is_throw_post && isset($_POST)) ? $_POST : NULL,
				'REQUEST' => (static::$is_throw_request && isset($_REQUEST)) ? $_REQUEST : NULL,
				'FILES' => (static::$is_throw_files && isset($_FILES)) ? $_FILES : NULL,
				'ENV' => (static::$is_throw_env && isset($_ENV)) ? $_ENV : NULL,
				'SESSION' => (static::$is_throw_session && isset($_SESSION)) ? $_SESSION : NULL,
				'SERVER' => (static::$is_throw_server && isset($_SERVER)) ? $_SERVER : NULL,
				'context' => static::$is_throw_context ? $context : NULL
			];
			
			$err_txt = print_r($err, TRUE).PHP_EOL;
			error_log($err_txt, 3, static::$file_err_detail);
			if(!(FF_IS_DEBUG || FF_IS_DEV)) {
				error_log($err_txt, 1, static::$email_err); //mail(DEVELOPER_EMAIL, 'Critical User Error', $err_out);
			}
		
			if(!defined('FF_IS_DEBUG') || !defined('FF_IS_DEV') || (!FF_IS_DEBUG && !FF_IS_DEV)) {
				$err = [
					'time'=> date('Y-m-d H:i:s'),
					'from'=> 'php',
					'type'=> isset(static::$php_err_type[$lvl]) ? static::$php_err_type[$lvl] : 'Unknown severity',
					'message' => $msg
				];
			}
			
			static::out($err, ($lvl & ((!defined('FF_IS_DEBUG') || FF_IS_DEBUG) ? E_ALL : E_ERROR | E_PARSE)));
			
		} catch(\Exception $e) {
			print_r($e);
		}
		return TRUE;
	}

}

