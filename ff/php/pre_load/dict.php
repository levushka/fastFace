<?
namespace ff;

class dict {
	
	public static function val($key, $default = NULL, $lang = FF_LANG) {
		if(empty($key)) {
			throw new \Exception( sprintf( '%s: Empty dict key', __METHOD__ ) );
		}
		
		$dict = \ff\lcache::get( 'dict/'.$lang );
		if(!isset($dict)) {
			$dict = static::generate($lang);
		}
		return isset($dict[$key]) ? $dict[$key] : (isset($default) ? $default : $key);
	}

	public static function generate_all( ) {
		foreach(\ff\lang::$langs as $lang_id=>$lang) {
			static::generate( $lang, FALSE );
		}
	}

	private static function generate( $lang = FF_LANG , $store_in_mem = TRUE) {
		if(!in_array($lang, \ff\lang::$langs, TRUE)) {
			throw new \Exception( sprintf( '%s: Wrong language %s', __METHOD__, $lang ) );
		}
		
		$dict = array_column(\ff\dbh::get_all('SELECT `key`, `'.\ff\dbh::esc($lang).'` FROM `'.FF_DB_NAME.'`.`ff_dict` WHERE `is_act`=TRUE'), 1, 0);
		
		if(empty($dict)) {
			throw new \Exception( sprintf( '%s: Dictionary is empty', __METHOD__ ) );
		}
		
		if($store_in_mem) {
			\ff\lcache::set( 'dict/'.$lang, $dict );
		} else {
			\ff\cache::set( 'dict/'.$lang, $dict );
		}
		return $dict;
	}
	
}

