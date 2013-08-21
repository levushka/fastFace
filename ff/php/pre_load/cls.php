<?
namespace ff;

class cls {

	public static function config( array $ff_opt ) {
		if( !\ff\id2url::exists( 'cls' ) || (defined('FF_GENERATE') && FF_GENERATE)) {
			require_once(FF_DIR_CLS.'/gen/gen_cls.php');
			\ff\gen_cls::generate( $ff_opt );
		}
		
		spl_autoload_register('\\ff\\cls::autoload');
	}

	
	public static function autoload( $id_or_url ) {
		if( (is_int($id_or_url) && \ff\id2url::is_id( 'cls', $id_or_url )) || (!is_int($id_or_url) && \ff\id2url::is_url( 'cls', $id_or_url ))) {
			$cls_id = is_int($id_or_url) ? $id_or_url : \ff\id2url::url2id( 'cls', $id_or_url );
			$cls_url = is_int($id_or_url) ? \ff\id2url::id2url( 'cls', $id_or_url ) : $id_or_url;
			
			if( !class_exists( $cls_url ) ) {
				$cls_def = \ff\id2def::get( 'cls', $cls_id );
				$cls_path = str_replace('//', FF_DIR_ROOT.'/', $cls_def['path']);
				if(!empty($cls_path) && is_file($cls_path)) {
					require_once( $cls_path );
					if( !class_exists( $cls_url ) ) {
						throw new \Exception( sprintf( '%s: %s [%s] is not loaded', __METHOD__, $id_or_url, $cls_url ) );
					}
				} else {
					throw new \Exception( sprintf( '%s: %s [%s] file [%s] not exists', __METHOD__, $id_or_url, $cls_url, $cls_path ) );
				}
			}
		}
	}

	
	public static function run( $id_or_url, array $args ) {
		$fn_id = is_int($id_or_url) ? $id_or_url : \ff\id2url::url2id( 'cls/fn', $id_or_url );
		\ff\role::check( 'cls/fn', $fn_id);
		
		$fn_url = is_int($id_or_url) ? \ff\id2url::id2url( 'cls/fn', $id_or_url ) : $id_or_url;
		
		$fn_url_arr = explode('::', $fn_url);
		static::autoload( $fn_url_arr[0] );
		if( !class_exists( $fn_url_arr[0] ) || !method_exists( $fn_url_arr[0], $fn_url_arr[1] ) ) {
			throw new \Exception( sprintf( '%s: %s is not exists', __METHOD__, $fn_url ) );
		}
		
		return call_user_func_array( $fn_url, $args );
	}
	
}
