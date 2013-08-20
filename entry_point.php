<?
namespace ff;

define('FF_DIR', str_replace( '\\', '/', __DIR__));

require_once(FF_DIR.'/config.php');

define('FF_DIR_CLS', FF_DIR.'/php');
define('FF_DIR_PHP_LIB', FF_DIR.'/php_lib');
define('FF_DIR_JS_LIB', FF_DIR.'/js_lib');


require_once(FF_DIR_CLS.'/pre_load/func.php');
require_once(FF_DIR_CLS.'/pre_load/error.php');
require_once(FF_DIR_CLS.'/cache/cache.php');
require_once(FF_DIR_CLS.'/cache/lcache.php');
require_once(FF_DIR_CLS.'/pre_load/session_handler.php');
require_once(FF_DIR_CLS.'/pre_load/id2url.php');
require_once(FF_DIR_CLS.'/pre_load/id2def.php');
require_once(FF_DIR_CLS.'/pre_load/api.php');
require_once(FF_DIR_CLS.'/pre_load/lang.php');
require_once(FF_DIR_CLS.'/pre_load/minify.php');
require_once(FF_DIR_CLS.'/pre_load/role.php');
require_once(FF_DIR_CLS.'/pre_load/arr.php');
require_once(FF_DIR_CLS.'/pre_load/cls.php');
require_once(FF_DIR_CLS.'/pre_load/tbl.php');

require_once(FF_DIR_CLS.'/user/login.php');

require_once(FF_DIR_CLS.'/db/dbh.php');
require_once(FF_DIR_CLS.'/db/db_fn.php');


define('FF_DIR_ROOT', str_replace( '\\', '/', \ff\getVal($ff_opt, 'root_dir', \ff\getSERVER('DOCUMENT_ROOT', realpath(FF_DIR.'/../../')))) );
define('FF_DIR_HOME', str_replace( '\\', '/', \ff\getVal($ff_opt, 'home_dir', \ff\getSERVER('HOME', realpath(FF_DIR_ROOT.'/../')))) );
define('FF_DIR_TMP',  str_replace( '\\', '/', \ff\getVal($ff_opt, 'tmp_dir', is_dir(FF_DIR_HOME.'/tmp') ? FF_DIR_HOME.'/tmp': sys_get_temp_dir())) );

$pre_load = \ff\getVal($ff_opt, 'pre_load', []);
if(!empty($pre_load['start'])) {
	\ff\load_and_call($pre_load['start']);
}

define('FF_IS_WIN', strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? TRUE : FALSE);
define('FF_SERVER_NAME', \ff\getSERVER('SERVER_NAME', 'no_server_name'));
define('FF_SERVER_PORT', \ff\getSERVER('SERVER_PORT', 'no_server_port'));
$host = explode(':', \ff\getSERVER('HTTP_HOST', FF_SERVER_NAME));
define('FF_HTTP_HOST', $host[0]);
define('FF_SERVER_URL', 'http://'.FF_HTTP_HOST.(FF_SERVER_PORT === '80' ? '' : ':'.FF_SERVER_PORT));
define('FF_SERVER_CRC', crc32(FF_SERVER_NAME.FF_SERVER_PORT));

define('FF_DEBUG_CODE', \ff\getREQUEST('d', '0'));
define('FF_IS_DEV', !in_array(FF_SERVER_NAME, \ff\getVal($ff_opt, 'prod.sites', [])) && FF_DEBUG_CODE !== '999');
define('FF_IS_DEBUG', (
	(FF_IS_DEV && FF_DEBUG_CODE !== '999')
	||
	(!FF_IS_DEV && FF_DEBUG_CODE === \ff\getVal($ff_opt, 'prod.debug_code', date('md')))
	) ? TRUE : FALSE);

\ff\api::config($ff_opt);
\ff\cache::config($ff_opt);
\ff\session_handler::config($ff_opt);


if(!empty($pre_load['after_sess'])) {
	\ff\load_and_call($pre_load['after_sess']);
}

\ff\lang::config($ff_opt);
\ff\err::config($ff_opt);
\ff\dbh::config($ff_opt);
\ff\cls::config($ff_opt);
//\ff\arr::config($ff_opt);
\ff\tbl::config($ff_opt);
\ff\role::config($ff_opt);
\ff\minify::config($ff_opt);
\ff\login::config($ff_opt);

if(!empty($pre_load['end'])) {
	\ff\load_and_call($pre_load['end']);
}

unset($ff_opt);
unset($pre_load);