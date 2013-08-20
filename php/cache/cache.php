<?
namespace ff;

class cache {

	const FILE = 1;
	const APC = 2;
	const WINCACHE = 3;
	const MEMCACHE = 4;
	const MEMCACHED = 5;
	
	private static $type = 1;

	private static $is_file = FALSE;
	private static $is_apc = FALSE;
	private static $is_wincache = FALSE;
	private static $is_memcache = FALSE;
	private static $is_memcached = FALSE;
		
	public static function config(array $ff_opt) {
		define('FF_IS_JS_STORAGE', \ff\getVal($ff_opt, 'cache.js_storage', FALSE));
		define('FF_IS_COMPRESS', \ff\getVal($ff_opt, 'output_gzip', TRUE) && strpos(\ff\getSERVER('HTTP_ACCEPT_ENCODING'), 'gzip' ) !== FALSE );

		$cache = \ff\getVal($ff_opt, 'cache.server', ['file_storage', 'apc', 'memcached']);
		static::$is_apc = $cache[0] === 'apc' && extension_loaded('apc');
		static::$is_wincache = $cache[0] === 'wincache' && extension_loaded('wincache');
		static::$is_memcache = $cache[0] === 'memcache' && extension_loaded('memcache');
		static::$is_memcached = $cache[0] === 'memcached' && extension_loaded('memcached');
		static::$is_file = !static::$is_apc && !static::$is_wincache && !static::$is_memcache && !static::$is_memcached;
		
		if(static::$is_file) {
			require_once(FF_DIR_CLS.'/cache/fcache.php');
			\ff\fcache::config();
		}

		static::$type = static::$is_apc ? static::APC : ( static::$is_wincache ? static::WINCACHE : ( static::$is_memcache ? static::MEMCACHE : ( static::$is_memcached ? static::MEMCACHED : static::FILE ) ) );
		
		$cache_ver = static::get( 'ver/cache', NULL, FALSE );
		if(empty($cache_ver)) {
			$cache_ver = time();
			static::set( 'ver/cache', $cache_ver, FALSE );
		}
		define('FF_VER_CACHE', $cache_ver);

		$ff_ver = static::get( 'ver/ff', NULL, FALSE );
		if(empty($ff_ver)) {
			$ff_ver = FF_VER_CACHE;
			static::set( 'ver/ff', $ff_ver, FALSE );
		}
		define('FF_VER', $ff_ver);
	}


	

	
	


	public static function get_type() {
		return static::$type;
	}

	

	public static function exists( $key ) {
		if(empty($key)) {
			throw new \Exception( sprintf( '%s: Key cannot be empty', __METHOD__ ) );
		}
		
		if( static::$is_file ) {
			return \ff\fcache::exists($key);
		} else if( static::$is_apc ) {
			return apc_exists( FF_SERVER_CRC.'/'.$key );
		} else if( static::$is_wincache ) {
			return wincache_ucache_exists( FF_SERVER_CRC.'/'.$key, $success );
		} else if( static::$is_memcache ) {
			return Memcache::get( FF_SERVER_CRC.'/'.$key ) !== NULL;
		} else if( static::$is_memcached ) {
			return Memcached::get( FF_SERVER_CRC.'/'.$key ) !== NULL;
		}
		
		return FALSE;
	}


	public static function get( $key ) {
		if(empty($key)) {
			throw new \Exception( sprintf( '%s: Key cannot be empty', __METHOD__ ) );
		}

		if( static::$is_file ) {
			return \ff\fcache::get($key);
		} else if( static::$is_apc ) {
			return apc_fetch( FF_SERVER_CRC.'/'.$key );
		} else if( static::$is_wincache ) {
			return wincache_ucache_get( FF_SERVER_CRC.'/'.$key );
		} else if( static::$is_memcache ) {
			return Memcache::get( FF_SERVER_CRC.'/'.$key );
		} else if( static::$is_memcached ) {
			return Memcached::get( FF_SERVER_CRC.'/'.$key );
		}
		
		return NULL;
	}
	
	public static function set( $key, $data, $ttl = 0) {
		if(empty($key)) {
			throw new \Exception( sprintf( '%s: Key cannot be empty', __METHOD__ ) );
		}

		if( static::$is_file ) {
			return \ff\fcache::set( $key, $data, $ttl );
		} else if( static::$is_apc ) {
			return apc_store( FF_SERVER_CRC.'/'.$key, $data, $ttl );
		} else if( static::$is_wincache ) {
			return wincache_ucache_add( FF_SERVER_CRC.'/'.$key, $data, $ttl );
		} else if( static::$is_memcache ) {
			return Memcache::set( FF_SERVER_CRC.'/'.$key, $data, 0, $ttl );
		} else if( static::$is_memcached ) {
			return Memcached::set( FF_SERVER_CRC.'/'.$key, $data, $ttl );
		}
		
		return FALSE;
	}

	
	public static function del( $key ) {
		if(empty($key)) {
			throw new \Exception( sprintf( '%s: Key cannot be empty', __METHOD__ ) );
		}

		if(static::$is_file) {
			return \ff\fcache::del( $key );
		} else if(static::$is_apc) {
			return apc_delete(FF_SERVER_CRC.'/'.$key);
		} else if(static::$is_wincache) {
			return wincache_ucache_delete(FF_SERVER_CRC.'/'.$key);
		} else if(static::$is_memcache) {
			return Memcache::delete(FF_SERVER_CRC.'/'.$key);
		} else if(static::$is_memcached) {
			return Memcached::delete(FF_SERVER_CRC.'/'.$key);
		}
	}

	
	public static function del_all( $key_prefix = NULL ) {
			
		if(static::$is_file) {
			return \ff\fcache::del_all( $key_prefix );
		} else if(static::$is_apc) {
			if(empty($key_prefix)) {
				return apc_clear_cache('user');
			} else {
				return apc_delete(new APCIterator('user', '/^'.FF_SERVER_CRC.str_replace( '/', '\\/', str_replace( '_', '\\_', $key_prefix)).'/'));
			}
		} else if(static::$is_wincache) {
			if(empty($key_prefix)) {
				return wincache_ucache_clear();
			} else {
				die('NOT READY');
			}
		} else if(static::$is_memcache) {
			die('NOT READY');
		} else if(static::$is_memcached) {
			die('NOT READY');
		}
	}
	



}



