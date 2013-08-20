<?php

/*  PHP.INI
 *  sendmail_path = C:\WnMp\php\php.exe C:\Projects\Emalon\third_party\fastFace\tools\sendmail.php C:\Projects\tmp\mail
 */

$mail_dir = (empty($argv) || empty($argv[1])) ? sys_get_temp_dir().'/mail' : $argv[1];
if( !is_dir( $mail_dir ) ) {
	mkdir( $mail_dir, 0777, TRUE );
	if( !is_dir( $mail_dir ) ) {
		throw new \Exception( sprintf( 'Mail folder [%s] not created', $mail_dir ) );
	}
}

$stream = '';
$fp = fopen('php://stdin','r');
while($t=fread($fp,2048)) {
	if( $t===chr(0) ) {
		break;
	}
	$stream .= $t;
}
fclose($fp);

$fp = fopen(mkname(),'w');
fwrite($fp, $stream);
fclose($fp);

function mkname($i=0) {
	global $mail_dir;
	$fn = $mail_dir.'/'.date('Y-m-d_H-i-s_').$i.'.eml';
	return file_exists($fn) ? mkname(++$i) : $fn;
}
