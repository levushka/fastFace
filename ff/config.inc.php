<?

/**
 * Configuration for 'Fast Face' Framework
 *
 */

$ff_opt['db']['names'] = ['ff'=>'fastFace main db', 'ff_cache'=>'fastFace cache db']; // Site databases, names and aliases: {db_alias:db_name}; "ff" & "cache" - reserved aliases; alias cannot be same as other db name
$ff_opt['db']['host']  = 'localhost:3306';
$ff_opt['db']['user']  = 'db user';
$ff_opt['db']['pass']  = 'db password';

$ff_opt['api_url']  = '/api.php';

$ff_opt['dir']['root'] = !empty($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] : __DIR__;
$ff_opt['dir']['home'] = !empty($_ENV['HOME']) ? $_ENV['HOME'] : realpath($ff_opt['dir']['root'].'/../');
$ff_opt['dir']['tmp']  = is_dir($ff_opt['home_dir'].'/tmp') ? $ff_opt['home_dir'].'/tmp': sys_get_temp_dir();


$ff_opt['lang']['langs'] = ['en', 'ru', 'he']; // Site languages, First element - default language
$ff_opt['lang']['rtl'] = ['he']; // Specify RTL languages

$ff_opt['extra_call'] = ['start'=>[], 'after_sess'=>[], 'end'=>[]]; // extra_call of files or function and argument or class::method and argument

// Additional directories with permited classes in subfolders
$ff_opt['cls']['dir'] = [];
$ff_opt['cls']['syntax_check'] = TRUE;
$ff_opt['cls']['syntax_check_cmd'] = 'php -l';

$ff_opt['login']['config'] = [];
$ff_opt['login']['ok']	 = [];
$ff_opt['login']['err'] = [];
$ff_opt['login']['role']['skip'] = FALSE;
$ff_opt['login']['role']['super'] = 1;
$ff_opt['login']['role']['blocked'] = 16;
$ff_opt['login']['guest']['user'] = 2;
$ff_opt['login']['guest']['type'] = 6;
$ff_opt['login']['guest']['role'] = 3;

$ff_opt['err']['email'] = ''; // developer email
$ff_opt['err']['error_handler'] = TRUE;
$ff_opt['err']['exception_handler'] = TRUE;
$ff_opt['err']['shutdown_function'] = TRUE;
$ff_opt['err']['throw']['trace'] = TRUE;
$ff_opt['err']['throw']['cookie'] = TRUE;
$ff_opt['err']['throw']['get'] = TRUE;
$ff_opt['err']['throw']['post'] = TRUE;
$ff_opt['err']['throw']['request'] = FALSE;
$ff_opt['err']['throw']['files'] = TRUE;
$ff_opt['err']['throw']['env'] = TRUE;
$ff_opt['err']['throw']['session'] = TRUE;
$ff_opt['err']['throw']['server'] = TRUE;
$ff_opt['err']['throw']['context'] = TRUE;

$ff_opt['dev']['is_debug'] = TRUE;
$ff_opt['dev']['debug_code'] = date('md');
$ff_opt['dev']['jsremote'] = FALSE;
$ff_opt['dev']['firebug_lite'] = FALSE;

$ff_opt['dev']['is_jshint'] = TRUE;
$ff_opt['dev']['jshint']['url'] = '//js_lib/other/jshint.js';
$ff_opt['dev']['jshint']['predef'] = ['fastFace', 'fastFace_cls', 'nicEditor', 'str_repeat', 'str_pad', 'date', 'strtotime', 'sprintf', 'number_format', 'pack', 'escape'];
$ff_opt['dev']['jshint']['min_keys'] = ['ff_lib'];

$ff_opt['cache']['server'] = 'file_storage'; // Cache engine: 'file_storage', 'apc', 'memcache', 'memcached', 'wincache'
$ff_opt['cache']['js_storage'] = FALSE; // Client storage

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

$ff_opt['lib']['phpexcel'] = NULL;
$ff_opt['lib']['phpmailer'] = NULL;
$ff_opt['lib']['phpreports'] = NULL;
$ff_opt['lib']['phpqrcode'] = NULL;
$ff_opt['lib']['minify'] = NULL;
