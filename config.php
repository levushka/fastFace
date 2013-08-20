<?

/**
 * Configuration for 'Fast Face' Framework
 *
 */

//file_put_contents(FF_DIR_TMP.'/debug.log', date("H:i:s").", "_______MSG_______\n", FILE_APPEND);

if($_SERVER["SERVER_NAME"] == "dev1.emalon.co.il") {
	$db_pref = 'dev1';
	$ff_opt['db']['pass'] = 'kau8eoYR4';
} else if($_SERVER["SERVER_NAME"] == "dev3.emalon.co.il") {
	$db_pref = 'dev3';
	$ff_opt['db']['pass'] = 'AFhDtZ3whc3aKM76';
} else {
	$db_pref = 'emalon';
	$ff_opt['db']['pass'] = 'em2008ka%@!';
}

$ff_opt['db']['proxy']['host'] = 'localhost:4040';
$ff_opt['db']['proxy']['sites'] = ['admin', 'new'];

$ff_opt['db']['host'] = 'localhost:3306';
$ff_opt['db']['names'] = ['ff'=>$db_pref.'_ff', 'ff_cache'=>$db_pref.'_cache', 'emalon'=>$db_pref];	// Site databases and aliases {alias:db_name}, ff & cache - reserved aliases, alias cannot be same as other db name
$ff_opt['db']['user'] = $db_pref;

$ff_opt['site_url'] = '/';
$ff_opt['admin_url'] = '/admin.php';
$ff_opt['api_url'] = '/third_party/fastFace/index.php';
$ff_opt['webmaster_email'] = 'lev@kitsis.ca';

$ff_opt['root_dir'] = realpath(__DIR__.'/../../'); // !empty($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] :
$ff_opt['home_dir'] = realpath($ff_opt['root_dir'].'/../');	// !empty($_SERVER['HOME']) ? $_SERVER['HOME'] :
$ff_opt['tmp_dir'] = is_dir($ff_opt['home_dir'].'/tmp') ? $ff_opt['home_dir'].'/tmp': sys_get_temp_dir();

// Site languages, First element - default language
$ff_opt['lang']['langs'] = ['he', 'ru', 'en'];
// Specify RTL languages
$ff_opt['lang']['rtl'] = ['he'];

// Directories with permited classes in subfolders
$ff_opt['cls']['dir'] = ['//ff_cls'];
$ff_opt['cls']['syntax_check'] = TRUE;
$ff_opt['cls']['syntax_check_cmd'] = 'php -l';

$ff_opt['pre_load'] = ['start'=>[], 'after_sess'=>[['//ff_cls/def/after_sess.php']], 'end'=>[]];

$ff_opt['login']['config'] = [['//ff_cls/def/login.php', '\emalon\login', 'config']];
$ff_opt['login']['ok']	 = [['//ff_cls/def/login.php', '\emalon\login', 'ok']];
$ff_opt['login']['err'] = [['//ff_cls/def/login.php', '\emalon\login', 'err']];
$ff_opt['login']['role']['skip'] = FALSE;
$ff_opt['login']['role']['super'] = 1;
$ff_opt['login']['role']['blocked'] = 16;
$ff_opt['login']['guest']['user'] = 2;
$ff_opt['login']['guest']['type'] = 6;
$ff_opt['login']['guest']['role'] = 3;

$ff_opt['err']['email'] = 'lev@kitsis.ca';
$ff_opt['err']['error_handler'] = TRUE;
$ff_opt['err']['exception_handler'] = TRUE;
$ff_opt['err']['shutdown_function'] = TRUE;
$ff_opt['err']['throw_trace'] = TRUE;
$ff_opt['err']['throw_cookie'] = TRUE;
$ff_opt['err']['throw_get'] = TRUE;
$ff_opt['err']['throw_post'] = TRUE;
$ff_opt['err']['throw_request'] = FALSE;
$ff_opt['err']['throw_files'] = TRUE;
$ff_opt['err']['throw_env'] = TRUE;
$ff_opt['err']['throw_session'] = TRUE;
$ff_opt['err']['throw_server'] = TRUE;
$ff_opt['err']['throw_context'] = TRUE;

$ff_opt['prod']['sites'] = ['www.emalon.co.il', 'admin.emalon.co.il', 'www1.emalon.co.il', 'emalon.co.il'];
$ff_opt['prod']['debug'] = TRUE;
$ff_opt['prod']['debug_code'] = date('md');

$ff_opt['debug']['jsremote'] = FALSE;
$ff_opt['debug']['firebug_lite'] = FALSE;

$ff_opt['dev']['jshint'] = TRUE;
$ff_opt['dev']['jshint_url'] = '//js_lib/other/jshint.js';
$ff_opt['dev']['jshint_predef'] = ['fastFace', 'fastFace_cls', 'Slick', 'nicEditor', 'str_repeat', 'str_pad', 'date', 'strtotime', 'sprintf', 'number_format', 'pack', 'escape'];
$ff_opt['dev']['jshint_min_keys'] = ['ff_lib'];

$ff_opt['cache']['server'] = ['file_storage', 'apc', 'memcached'];
$ff_opt['cache']['js_storage'] = FALSE;

$ff_opt['pinba'] = TRUE;
$ff_opt['rrdtool'] = TRUE;

$ff_opt['output_gzip'] = FALSE;

$ff_opt['minify']['path'] = '//third_party/min';
$ff_opt['minify']['url'] = '//third_party/min/index.php';
$ff_opt['minify']['is_dev'] = FALSE;
$ff_opt['minify']['is_min_js'] = TRUE;
$ff_opt['minify']['is_min_css'] = TRUE;
$ff_opt['minify']['is_group'] = TRUE;
$ff_opt['minify']['group_files'] = ['//ff_cls/def/minify_group.php'];
$ff_opt['minify']['css_keys'] = NULL;
$ff_opt['minify']['js_keys'] = NULL;

$ff_opt['lib']['phpexcel'] = '//third_party/lib/PHPExcel';
$ff_opt['lib']['phpmailer'] = '//third_party/lib/PHPMailer';
$ff_opt['lib']['phpreports'] = '//third_party/lib/phpreports';
$ff_opt['lib']['phpqrcode'] = '//third_party/lib/phpqrcode';
$ff_opt['lib']['minify'] = '//third_party/min/lib';
