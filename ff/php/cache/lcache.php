<?
namespace ff;

class lcache {

	private static $local_storage = [];
	
	public static function exists( $key ) {
		if(empty($key)) {
			throw new \Exception( sprintf( '%s: Key cannot be empty', __METHOD__ ) );
		}
		
		if(isset(static::$local_storage[$key])) {
			return TRUE;
		} else {
			return \ff\cache::exists($key);
		}
		
		return FALSE;
	}


	public static function get( $key ) {
		if(empty($key)) {
			throw new \Exception( sprintf( '%s: Key cannot be empty', __METHOD__ ) );
		}

		if(isset(static::$local_storage[$key])) {
			
			return static::$local_storage[$key];
			
		} else {
			
			$data = \ff\cache::get($key);
			
			if(isset($data)) {
				static::$local_storage[$key] = $data;
			}
			
			return $data;

		}
		
	}
	
	public static function set( $key, $data, $ttl = 0) {
		if(empty($key)) {
			throw new \Exception( sprintf( '%s: Key cannot be empty', __METHOD__ ) );
		} else if($data === NULL) {
			unset(static::$local_storage[$key]);
			return static::del($key);
		} else {
			static::$local_storage[$key] = $data;
			return \ff\cache::set($key, $data, $ttl);
		}
	}

	
	public static function del( $key ) {
		if(empty($key)) {
			throw new \Exception( sprintf( '%s: Key cannot be empty', __METHOD__ ) );
		}

		unset(static::$local_storage[$key]);
		return \ff\cache::del($key);
	}

	
	public static function del_all( $key_prefix = NULL) {
		if(empty($key_prefix)) {
			
			static::$local_storage = [];
			
		} else {
			
			$keys = array_keys(static::$local_storage);
			foreach($keys as $key_id=>$key) {
				if(0 === strpos( $key, $key_prefix )) {
					unset(static::$local_storage[$key]);
				}
			}
			
		}
		
		return \ff\cache::del_all($key_prefix);
	}
	



}



