<?
namespace ff;

class ff_timer {

	public static function check() {
		if(FF_VER_CLIENT_CACHE < FF_VER_CACHE) {
			if(FF_TOKEN && FF_USER_ID > 0) {
				\ff\login::reloadUserData();
				echo '
				try {
					fastFace.login.data = '.json_encode([
						'user'=>$_SESSION[FF_TOKEN]['user'],
						'role'=>$_SESSION[FF_TOKEN]['role']
					]).';
				} catch(e) {
					fastFace.err.js(e);
				}'.PHP_EOL;
			}
			
			echo '
			try {
				fastFace.cache.ver = '.FF_VER_CACHE.';
				fastFace.dict.reInit('.FF_LANG.');
			} catch(e) {
				fastFace.err.js(e);
			}'.PHP_EOL;
		}
	}

}


