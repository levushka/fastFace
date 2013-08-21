<?
namespace ff;

error_reporting(E_ALL);


try {

	require_once('./php/entry_point.php');

	\ff\api::process();
	
} catch(\Exception $e) {
	
	if(class_exists('\\ff\\err')) {
		\ff\err::exception_handler($e);
	} else {
		echo 'Error : '.$e->getCode().((!defined('FF_IS_DEBUG') || !defined('FF_IS_DEV') || (!FF_IS_DEBUG && !FF_IS_DEV)) ? '' : ' : '.$e->getMessage().' : '.$e->getFile().' : '.$e->getLine());
	}
	
}