<?
namespace ff;

error_reporting(E_ALL);

try {

	require_once('./entry_point.php');
	
	if(FF_IS_PINBA) {
		$pinba_handler = pinba_timer_start(['type'=>'file', 'fn'=>basename(__FILE__)]);
	}
	
	\ff\api::process();
	
	if(FF_IS_PINBA) {
		pinba_timer_stop($pinba_handler);
	}
	
} catch(\Exception $e) {
	
	if(class_exists('\\ff\\err')) {
		\ff\err::exception_handler($e);
	} else {
		echo 'Error : '.$e->getCode().' : '.$e->getMessage().' : '.$e->getFile().' : '.$e->getLine();
	}
	
}
