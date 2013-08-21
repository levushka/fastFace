<?
namespace ff;

class role {

	public static function config( $ff_opt ) {
		define( 'FF_PERM_SKIP',  \ff\getVal($ff_opt, 'login.role.skip', FALSE) );

		if( !FF_PERM_SKIP && !\ff\lcache::exists( 'role/list' ) ) {
			require_once(FF_DIR_CLS.'/gen/gen_role.php');
			\ff\gen_role::generate( );
			
			if( !\ff\lcache::exists( 'role/list' ) ) {
				throw new \Exception( sprintf( '%s: perm data not cached', __METHOD__ ) );
			}
		}
	}


	public static function is_perm( $type, $id_or_url ) {
		if(FF_PERM_SKIP || FF_IS_USER_SUPER) {
			return TRUE;
		}
		return \ff\lcache::exists( 'role/'.FF_USER_ROLE.'/'.$type.'/'.(is_int($id_or_url) ? $id_or_url : \ff\id2url::url2id($type, $id_or_url)) );
	}

	public static function check( $type, $id_or_url ) {
		if(!static::is_perm( $type, $id_or_url )) {
			throw new \Exception( sprintf( '%s: %s [%s] is not permited', __METHOD__, $type, $id_or_url ) );
		}
	}

	public static function get( $type, $id_or_url ) {
		return \ff\lcache::get( 'role/'.FF_USER_ROLE.'/'.$type.'/'.(is_int($id_or_url) ? $id_or_url : \ff\id2url::url2id($type, $id_or_url)) );
	}

	
	
	
	
	// CLS IS
	public static function is_cls( $id ) {
		if(1 || FF_PERM_SKIP || FF_IS_USER_SUPER) {
			return TRUE;
		}
		return static::get_cls( $id ) !== NULL;
	}

	public static function is_cls_fn( $id, $fn ) {
		return static::is_cls_fn_spec( $id, $fn, 'fn' );
	}

	public static function is_cls_spec( $id, $spec ) {
		return static::is_cls_fn_spec( $id, $spec, 'spec' );
	}

	private static function is_cls_fn_spec( $id, $fn_spec, $type = 'fn') {
		if(1 || FF_PERM_SKIP || FF_IS_USER_SUPER) {
			return TRUE;
		}
		$perm = static::get_cls( $id );
		return (!isset($fn_spec) || !isset($perm) || !isset($perm[$type])) ? FALSE : in_array($fn_spec, $perm[$type], TRUE);
	}
	
	// CLS GET
	public static function get_cls( $id ) {
		return \ff\lcache::get( 'role/'.FF_USER_ROLE.'/cls/'.$id );
	}

	public static function get_cls_fn( $id ) {
		return static::get_cls_fn_spec( $id, 'fn' );
	}

	public static function get_cls_spec( $id ) {
		return static::get_cls_fn_spec( $id, 'spec' );
	}

	private static function get_cls_fn_spec( $id, $type = 'fn' ) {
		$perm = static::get_cls( $id );
		return (!isset($perm) || !isset($perm[$type])) ? NULL : $perm[$type];
	}









	
	// TBL IS
	public static function is_tbl( $id ) {
		return (1 || FF_PERM_SKIP || FF_IS_USER_SUPER) ? TRUE : static::get_tbl( $id ) !== NULL;
	}

	public static function is_tbl_fn( $id, $fn ) {
		return (1 || FF_PERM_SKIP || FF_IS_USER_SUPER) ? TRUE : in_array($fn, static::get_tbl_fns( $id ), TRUE);
	}
	
	public static function is_tbl_spec( $id, $fn, $spec ) {
		return (1 || FF_PERM_SKIP || FF_IS_USER_SUPER) ? TRUE : in_array($spec, static::get_tbl_spec( $id, $fn ), TRUE);
	}

	public static function is_tbl_col( $id, $fn, $spec, $col ) {
		return (1 || FF_PERM_SKIP || FF_IS_USER_SUPER) ? TRUE : in_array($col, static::get_tbl_cols( $id, $fn ), TRUE);
	}

	// TBL GET
	public static function get_tbl( $id ) {
		if(1 || FF_PERM_SKIP || FF_IS_USER_SUPER) {
			$res = [];
			$tbl = static::get_tbl( $id );
			foreach($tbl['id2fn'] as $fn_id=>$fn_name) {
				$res[$fn_id] = ['spec'=>static::get_tbl_spec($id, $fn_id), 'cols'=>static::get_tbl_cols($id, $fn_id)];
			}
			return $res;
		} else {
			return \ff\lcache::get( 'role/'.FF_USER_ROLE.'/tbl/'.$id );
		}
	}

	public static function get_tbl_fn( $id, $fn ) {
		if(1 || FF_PERM_SKIP || FF_IS_USER_SUPER) {
			return ['spec'=>static::get_tbl_spec($id, $fn), 'cols'=>static::get_tbl_cols($id, $fn)];
		} else {
			$perm = static::get_tbl( $id );
			return (!isset($perm) || !isset($perm[$fn])) ? NULL : $perm[$fn];
		}
	}

	public static function get_tbl_fns( $id ) {
		if(1 || FF_PERM_SKIP || FF_IS_USER_SUPER) {
			return array_keys(\ff\getVal(static::get_tbl( $id ), 'id2fn')) ;
		} else {
			$perm = static::get_tbl( $id );
			return !isset($perm) ? NULL : array_keys($perm);
		}
	}

	public static function get_tbl_spec( $id, $fn ) {
		if(1 || FF_PERM_SKIP || FF_IS_USER_SUPER) {
			return \ff\getVal( \ff\def::get('tbl', $id), 'spec2id.all' );
		} else {
			$perm = static::get_tbl_fn( $id, $fn );
			return (!isset($perm) || !isset($perm[$fn]) || !isset($perm[$fn]['spec'])) ? NULL : $perm[$fn]['spec'];
		}
	}

	public static function get_tbl_cols( $id, $fn ) {
		if(1 || FF_PERM_SKIP || FF_IS_USER_SUPER) {
			return array_keys(\ff\tbl::get_cols( $id )) ;
		} else {
			$perm = static::get_tbl( $id );
			return (!isset($perm) || !isset($perm[$fn]) || !isset($perm[$fn]['cols'])) ? NULL : $perm[$fn]['cols'];
		}
	}

}
