<?
namespace ff;

class arr {
	
	public static function config( array $ff_opt ) {
		if( !\ff\id2url::exists( 'arr' ) ) {
			require_once(FF_DIR_CLS.'/gen/gen_arr.php');
			\ff\gen_arr::generate( );
		}
	}

	public static function get($id_or_url, $lang = FF_LANG) {
		return \ff\id2def::get('arr', is_int($id_or_url) ? $id_or_url : \ff\id2url::url2id('arr', $id_or_url), $lang);
	}

	public static function keys($id_or_url, $lang = FF_LANG) {
		return array_keys(static::get($id_or_url, $lang));
	}

	public static function val($id_or_url, $key, $def = NULL, $lang = FF_LANG) {
		$arr = static::get($id_or_url, $lang);
		return isset($arr[$key]) ? $arr[$key] : (isset($def) ? $def : $key);
	}

}

