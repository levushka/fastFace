<?
namespace ff;

class tbl {
	
	public static $key_names = ['pk', 'fk', 'sk', 'ik', 'uk', 'ro', 'act', 'arr', 'ord', 'ord_by', 'lang']; //, 'crd', 'crr', 'upd', 'upr', 'owr', 'owg'

	public static function config(array $ff_opt) {
		if( !\ff\id2url::exists( 'tbl' ) || (defined('FF_GENERATE') && FF_GENERATE)) {
			require_once(FF_DIR_CLS.'/gen/gen_tbl.php');
			\ff\gen_tbl::generate( $ff_opt );
		}
	}


	
	
	public static function get_id($id_or_url) {
		if(is_int($id_or_url) || is_numeric($id_or_url)) {
			if(\ff\id2url::is_id( 'tbl', $id_or_url )) {
				return $id_or_url;
			} else {
				throw new \Exception( sprintf( '%s: Wrong TBL ID [%s]', __METHOD__, $id_or_url ) );
			}
		} if(is_string($id_or_url)) {
			$url_arr = explode('.', $id_or_url);
			return \ff\id2url::url2id( 'tbl', count($url_arr) === 2 ?  \ff\dbh::db_name($url_arr[0]).'.'.$url_arr[1] : FF_DB_NAME.'.'.$url_arr[0] );
		} else {
			throw new \Exception( sprintf( '%s: Wrong TBL type', __METHOD__ ) );
		}
	}

	public static function get_url($id_or_url) {
		if(is_int($id_or_url) || is_numeric($id_or_url)) {
			if(\ff\id2url::is_id( 'tbl', $id_or_url )) {
				return \ff\id2url::id2url( 'tbl', $id_or_url);
			} else {
				throw new \Exception( sprintf( '%s: Wrong TBL ID [%s]', __METHOD__, $id_or_url ) );
			}
		} if(is_string($id_or_url)) {
			$url_arr = explode('.', $id_or_url);
			$url = count($url_arr) === 2 ?  \ff\dbh::db_name($url_arr[0]).'.'.$url_arr[1] : FF_DB_NAME.'.'.$url_arr[0];
			if(\ff\id2url::is_url( 'tbl', $url )) {
				return $url;
			} else {
				throw new \Exception( sprintf( '%s: Wrong TBL URL [%s]', __METHOD__, $id_or_url ) );
			}
		} else {
			throw new \Exception( sprintf( '%s: Wrong TBL type', __METHOD__ ) );
		}
	}

	public static function get_path($id_or_url) {
		$url = explode('.', \ff\tbl::get_url($id_or_url));
		return '`'.$url[0].'`.`'.$url[1].'`';
	}
	
	public static function get_cols( $id ) {
		if( \ff\id2url::is_id( 'tbl', $id ) ) {
			$cols = \ff\lcache::get( 'tbl/'.$id.'/cols' );
			if( isset( $cols ) ) {
				return $cols;
			} else {
				throw new \Exception( sprintf( '%s: cols for [%s] is not cached', __METHOD__, $id ) );
			}
		} else {
			throw new \Exception( sprintf( '%s: Wrong tbl id', __METHOD__ ) );
		}
	}

	public static function get_keys( $id ) {
		if( \ff\id2url::is_id( 'tbl', $id ) ) {
			$keys = \ff\lcache::get( 'tbl/'.$id.'/keys' );
			if( isset( $keys ) ) {
				return $keys;
			} else {
				throw new \Exception( sprintf( '%s: keys for [%s] is not cached', __METHOD__, $id ) );
			}
		} else {
			throw new \Exception( sprintf( '%s: Wrong tbl id', __METHOD__ ) );
		}
	}
	

	public static function get_col( $url, $name ) {
		$cols = static::get_cols(\ff\id2url::url2id( 'tbl', $url ));
		foreach($cols as $id=>$def) {
			if($def['name'] === $name) {
				return $def;
			}
		}
		throw new \Exception( sprintf( '%s: Col [%s] not found in [%s]', __METHOD__, $name, $url ) );
	}




	
	public static function get_fn( $id_or_url, $id_or_fn ) {

		if(is_int($id_or_url)) {
			$id = $id_or_url;
		} else if(is_string($id_or_url)) {
			if(strpos($id_or_url, '.') === FALSE) {
				$id_or_url = FF_DB_NAME.'.'.$id_or_url;
			}
			$id = \ff\id2url::url2id( 'tbl', $id_or_url );
		} else {
			throw new \Exception( sprintf( '%s: Wrong TBL URL', __METHOD__ ) );
		}

		$tbl = \ff\id2def::get( 'tbl', $id );
		
		$fn_id = is_int($id_or_fn) ? (int)$id_or_fn : \ff\getVal( $tbl, 'fn2id.'.$id_or_fn );

		$def = \ff\lcache::get( 'user/'.FF_USER_ID.'/'.FF_LANG.'/fn/'.$id.'/'.$fn_id );
		if(!empty($def)) {
			return $def;
		}

		if( !\ff\role::is_tbl_fn($id, $fn_id) ) {
			throw new \Exception( sprintf( '%s: Operation %s::%s not permited', __METHOD__, $id, $fn_id ) );
		}
		
		$def = \ff\role::get_tbl_fn($id, $fn_id);
		
		if( !isset($def) ) {
			throw new \Exception( sprintf( '%s: Operation %s::%s not permited', __METHOD__, $id, $fn_id ) );
		}

		\ff\lcache::set( 'user/'.FF_USER_ID.'/'.FF_LANG.'/tbl/'.$id.'/'.$fn_id, $def );
		
		return $def;
	}

}

