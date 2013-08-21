<?
namespace ff;

class fcache {

	public static $dir = NULL;
	
	public static function config() {
		static::$dir = FF_DIR_TMP.'/ff_'.FF_SERVER_NAME.FF_SERVER_PORT;

		if( !is_dir( static::$dir ) ) {
			mkdir( static::$dir, 0777, TRUE );
			if( !is_dir( static::$dir ) ) {
				throw new \Exception( sprintf( '%s: Cache folder for [%s] not created', __METHOD__, FF_SERVER_NAME ) );
			}
		}
	}
	
	private static function path($key) {
		$filtered = preg_replace('/\/(\/)+/ims', '/', preg_replace('/([^a-zA-Z0-9\_\/]+)/ims', '_', $key));
		$filtered = str_replace('//', '/', static::$dir.'/'.((empty($filtered) || !is_string($filtered))? sha1($key) : $filtered ));
		return strrpos($filtered, '/') === (strlen($filtered) - 1) ? substr($filtered, 0, -1) : $filtered;
	}
	
	
		
	public static function check($file) {
		if( is_file( $file.'.ttl' ) ) {
			$ttl = intval( file_get_contents ( $file.'.ttl' ) );
			
			if($ttl > 0) {
				$time = time() - $ttl;
				if( is_file( $file.'.txt' ) && filemtime($file.'.txt') < $time ) {
					unlink( $file.'.txt');
					unlink( $file.'.ttl');
				} else if( is_file( $file.'.json' ) && filemtime($file.'.json') < $time ) {
					unlink( $file.'.json');
					unlink( $file.'.ttl');
				}
			} else {
				unlink( $file.'.ttl');
			}
		}
	}
		
	public static function exists($key) {
		$file = static::path($key);
		static::check($file);
		return is_file( $file.'.txt' ) ? TRUE : (is_file( $file.'.json' ) ? TRUE : FALSE);
	}

	public static function read($key) {
		$file = static::path($key);

		static::check($file);
		
		if( is_file( $file.'.txt' ) ) {
			return readfile( $file.'.txt' );
		} else if(is_file( $file.'.json' ) ) {
			return readfile( $file.'.json' );
		}
		return FALSE;
	}
	
	public static function get($key) {
		$is_txt = FALSE;
		$file = static::path($key);

		static::check($file);
		
		if( is_file( $file.'.txt' ) ) {
			$is_txt = TRUE;
		} else if(!is_file( $file.'.json' ) ) {
			return NULL;
		}
		
		return $is_txt ? file_get_contents ( $file.'.txt' ) : json_decode ( file_get_contents ( $file.'.json' ), TRUE );
	}

	public static function set($key, $data, $ttl = 0) {
		$file = static::path($key);
		$dir = dirname($file);
		
		if( !is_dir( $dir ) ) {
			mkdir( $dir, 0777, TRUE );
			if( !is_dir( $dir ) ) {
				throw new \Exception( sprintf( '%s: Cache folder for [%s] is not created', __METHOD__, $key ) );
			}
		}
		
		if($ttl) {
			file_put_contents( $file.'.ttl', $ttl );
		}
		
		$is_txt = is_string($data);
		return file_put_contents( $file.($is_txt ? '.txt' : '.json'), $is_txt ? $data : json_encode( $data ) );
	}
	
	public static function del($key) {
		$file = static::path($key);
		if( is_file( $file.'.txt' ) ) {
			unlink( $file.'.txt');
		} else if(is_file( $file.'.json' ) ) {
			unlink( $file.'.json');
		}
		if( is_file( $file.'.ttl' ) ) {
			unlink( $file.'.ttl');
		}
		return TRUE;
	}

	public static function del_all($key_prefix = NULL) {
		if(empty($key_prefix)) {
			return \ff\rrmdir(static::$dir, NULL, NULL, TRUE);
		} else {
			$file = static::path($key_prefix);
			if( is_dir( $file ) ) {
				return \ff\rrmdir($file, NULL, NULL, TRUE);
			} else {
				return \ff\rrmdir(dirname($file), basename($file), NULL, TRUE);
			}
		}
	}


	
}



