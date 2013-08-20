<?
namespace ff;

class id2def {
	
	// DEF
	public static function is_def( $type, $id, $lang = NULL ) {
		return \ff\lcache::exists( $type.'/'.$id.'/def'.($lang === NULL ? '' : '_'.$lang) );
	}

	public static function get( $type, $id, $lang = NULL ) {
		$def = \ff\lcache::get( $type.'/'.$id.'/def'.($lang === NULL ? '' : '_'.$lang) );
		if( isset( $def ) ) {
			return $def;
		} else if( static::is_id( $type, $id ) ) {
			throw new \Exception( sprintf( '%s: [%s] %s [%s] is not cached', __METHOD__, $type, $id, $lang ) );
		} else {
			throw new \Exception( sprintf( '%s: [%s] %s [%s] is not exists', __METHOD__, $type, $id, $lang) );
		}
	}

	public static function set( $type, $id, array $def, $lang = NULL ) {
		\ff\lcache::set( $type.'/'.$id.'/def'.($lang === NULL ? '' : '_'.$lang), $def );
	}

	
	// DEF LANGs
	public static function get_langs( $type, $id ) {
		$res = [];
		foreach(\ff\lang::$langs as $lang_id=>$lang) {
			$res[$lang] = static::get($type, $id, $lang);
		}
		return $res;
	}
	
	public static function set_langs( $type, $id, array $defs_lang ) {
		foreach(\ff\lang::$langs as $lang_id=>$lang) {
			if(isset($defs_lang[$lang])) {
				static::set($type, $id, $defs_lang[$lang], $lang);
			} else {
				throw new \Exception( sprintf( '%s: [%s] DEF [%s] for LANG [%s] not exists', __METHOD__, $type, $id, $lang ) );
			}
		}
	}
	
	
	// DEFs
	public static function get_defs( $type, $is_lang = FALSE ) {
		$res = [];
		$id2url = \ff\lcache::get( $type.'/id2url' );
		foreach ($id2url as $id=>$url) {
			if($is_lang) {
				$res[$id] = static::get_langs( $type, $id);
			} else {
				$res[$id] = static::get( $type, $id);
			}
		}
		return $res;
	}

	public static function set_defs( $type, $defs, $is_lang = FALSE ) {
		ksort($defs);
		foreach ($defs as $id=>$def) {
			if($is_lang) {
				static::set_langs($type, $id, $def);
			} else {
				static::set($type, $id, $def);
			}
		}
	}
		
}

