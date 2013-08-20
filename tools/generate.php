<?
namespace ff;

error_reporting(E_ERROR | E_PARSE);
set_time_limit(900);

if(empty($argv) || count($argv) < 3) {
	die('Provide server name and port. Example: localhost 80 [gen | regen | clean]'.PHP_EOL);
}

$_SERVER['SERVER_NAME'] = $argv[1];
$_SERVER['SERVER_PORT'] = $argv[2];
$_REQUEST['o'] = 'html';

$time = date("H:i:s");

if(!empty($argv[3]) && ($argv[3] === 'regen' || $argv[3] === 'gen')) {
	define('FF_GENERATE', TRUE);
	if($argv[3] === 'regen') {
		define('FF_REGENERATE', TRUE);
	}
}

require_once(__DIR__.'/../entry_point.php');
if(!empty($argv[3]) && $argv[3] === 'regen') {
	echo 'Regenerate time: '.$time.' - '.date("H:i:s").PHP_EOL.PHP_EOL;
} else {
	echo 'Init time: '.$time.' - '.date("H:i:s").PHP_EOL.PHP_EOL;
}

if(!empty($argv[3]) && $argv[3] === 'clean') {
	$time = date("H:i:s");
	\ff\cache::del_all();
	echo 'Clean time: '.$time.' - '.date("H:i:s").PHP_EOL.PHP_EOL;
}


