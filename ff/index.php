<?
namespace ff;

error_reporting(E_ALL);
$_REQUEST['o'] = 'html';

try {
	
	require_once('./php/entry_point.php');
  
  \ff\gui::render();
  
} catch(\Exception $e) {
	
  if(class_exists('\\ff\\err')) {
		\ff\err::exception_handler($e);
	} else {
		echo 'Error : '.$e->getCode().((!defined('FF_IS_DEBUG') || !defined('FF_IS_DEV') || (!FF_IS_DEBUG && !FF_IS_DEV)) ? '' : ' : '.$e->getMessage().' : '.$e->getFile().' : '.$e->getLine());
	}
	
}