<?
namespace ff;

error_reporting(E_ALL);

try {

	require_once('./entry_point.php');

	\ff\api::process();
	
} catch(\Exception $e) {
	
	if(class_exists('\\ff\\err')) {
		\ff\err::exception_handler($e);
	} else {
		echo 'Error : '.$e->getCode().' : '.$e->getMessage().' : '.$e->getFile().' : '.$e->getLine();
	}
	
}
