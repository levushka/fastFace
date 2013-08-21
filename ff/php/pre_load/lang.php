<?
namespace ff;

class lang {

	public static $langs = ['he', 'ru', 'en'];
	public static $cur = NULL;
	public static $def = NULL;
	
	public static function config(array $ff_opt) {
		static::$langs = \ff\getVal($ff_opt, 'lang.langs', static::$langs);
		\ff\checkStruct(static::$langs, ['/[a-z]+/i']);
		static::$def = static::$langs[0];
		$rtl = \ff\getVal($ff_opt, 'lang.rtl', []);
		if(is_array($rtl) && !empty($rtl) && !\ff\arr_in_arr($rtl, static::$langs)) {
			throw new \Exception(sprintf('%s: RTL languges [%s] not in the languages list [%s]', __METHOD__, var_export($rtl, TRUE), var_export(static::$langs, TRUE) ));
		}

		$REQUEST_URI = \ff\getSERVER('REQUEST_URI');
		$req = \ff\getREQUEST('l', \ff\getREQUEST('lang',null));
		if (!$req) {
			if (!empty($REQUEST_URI) && 0 === strpos($REQUEST_URI, '/')) {
				if (strlen($REQUEST_URI) === 3 || 3 === strpos($REQUEST_URI, '/', 1) || 3 === strpos($REQUEST_URI, '?', 1)) {
					static::$cur = substr($REQUEST_URI, 1, 2);
				} else {
					static::$cur = 'he';
				}
			}
		} else {
			static::$cur = $req;
		}

		if(empty(static::$cur) || !in_array(static::$cur, static::$langs, TRUE)) {
			static::$cur = \ff\getREQUEST('l', \ff\getCOOKIE('l', static::$def));
			static::$cur = (empty(static::$cur) || !in_array(static::$cur, static::$langs, TRUE)) ? static::$def : static::$cur;
		}
		define('FF_LANG', static::$cur);
		setcookie('l', FF_LANG, time()+60*60*24*30, '/', FF_SERVER_NAME, FALSE, TRUE);

		\ff\define_arr(
			static::$langs,
			[FF_LANG=>TRUE],
			'FF_IS_'
		);

		define('FF_IS_RTL', in_array(FF_LANG, $rtl, TRUE));
		define('FF_HTML_DIR', FF_IS_RTL ? 'rtl' : 'ltr');
		define('FF_HTML_DIR_MIR', FF_IS_RTL ? 'ltr' : 'rtl');
		define('FF_HTML_ALIGN', FF_IS_RTL ? 'right' : 'left');
		define('FF_HTML_ALIGN_MIR', FF_IS_RTL ? 'left' : 'right');
	}
}