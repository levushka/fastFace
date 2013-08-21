<?
namespace ff;

class id2url {
	
	public static function exists( $type ) {
		return \ff\lcache::exists( $type.'/id2url' );
	}

	public static function is_id( $type, $id ) {
		$id2url = \ff\lcache::get( $type.'/id2url' );
		return isset($id2url[(int)$id]);
	}

	public static function is_url( $type, $url ) {
		$id2url = \ff\lcache::get( $type.'/id2url' );
		return in_array($url, $id2url, TRUE);
	}

	
	public static function url2id( $type, $url ) {
		$id2url = \ff\lcache::get( $type.'/id2url' );
		if( in_array($url, $id2url, TRUE) ) {
			return array_search($url, $id2url, TRUE);
		} else {
			throw new \Exception( sprintf( '%s: [%s] %s is not exists', __METHOD__, $type, $url ) );
		}
	}

	public static function id2url( $type, $id ) {
		$id2url = \ff\lcache::get( $type.'/id2url' );
		if( isset($id2url[(int)$id]) ) {
			return $id2url[(int)$id];
		} else {
			throw new \Exception( sprintf( '%s: [%s] %s is not exists', __METHOD__, $type, $id ) );
		}
	}

	public static function add( $type, $id, $url ) {
		$id2url = \ff\lcache::get( $type.'/id2url' );
		$id2url[(int)$id] = $url;
		static::set($type, $id2url);
	}

	public static function del( $type, $id ) {
		$id2url = \ff\lcache::get( $type.'/id2url' );
		unset($id2url[(int)$id]);
		static::set($id2url);
	}
	
	public static function get( $type ) {
		return \ff\lcache::get( $type.'/id2url' );
	}

	public static function set( $type, array $id2url ) {
		ksort($id2url);
		\ff\lcache::set( $type.'/id2url', $id2url );
	}
		
}

