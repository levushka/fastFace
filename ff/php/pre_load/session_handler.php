<?
namespace ff;

class session_handler implements \SessionHandlerInterface {
	private $savePath;

	public static function config(array $ff_opt) {
		session_cache_expire(30);
		$session_handler = new \ff\session_handler();
		session_set_save_handler($session_handler, true);
		session_start();
	}

	public function open($savePath, $sessionName) {
		$this->savePath = $savePath;
	}

	public function close() {
		return TRUE;
	}

	public function read($id) {
		return \ff\cache::get( 'sess/'.$id );
	}

	public function write($id, $data) {
		\ff\cache::set( 'sess/'.$id, $data );
	}

	public function destroy($id) {
		\ff\cache::del( 'sess/'.$id );
	}

	public function gc($maxlifetime) {
		return TRUE;
	}
}
