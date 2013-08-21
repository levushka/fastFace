<?
namespace ff;

class gui {

	public static function render() {
		
		if(\ff\getGET('l') != FF_LANG || \ff\getGET('v') != FF_VER || \ff\getGET('t') != FF_TOKEN) {
			header('Location: admin.php?l='.FF_LANG.'&v='.FF_VER.'&d='.FF_DEBUG_CODE.'&t='.FF_TOKEN);
			exit;
		} else {
			header('Content-type: text/html; charset=utf-8');
		}

?><!DOCTYPE html>
<HTML lang="<?=FF_LANG?>" dir="ltr">
<HEAD>
	<META http-equiv="Content-Type" content="text/html;charset=utf-8" />
	
	<TITLE>EMALON</TITLE>

	<LINK rev="made" href="mailto:lev@kitsis.ca" />
	<LINK rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
	<LINK rev="start" href="./" title="EMALON" />
<? if(FF_IS_FIREBUG_LITE) { echo '<script src="https://getfirebug.com/firebug-lite.js"></script>'; } ?>
</HEAD>
<?
		if((preg_match('/(?i)msie [2-6]/',FF_HTTP_USER_AGENT) && !preg_match('/(?i)msie [7-9]/',FF_HTTP_USER_AGENT)) || preg_match('/Firefox\/[2-3]/',FF_HTTP_USER_AGENT)) {
			
			echo '<BODY><CENTER><SMALL style="background-color:#eeeeee">'.FF_HTTP_USER_AGENT.'</SMALL>';
			echo <<<'EOD'
<table CELLPADDING=20>
<tr valign=top>
<td>
	<h1 style="background-color:#FFCCCC"><img src="img/no.gif" /> דפדפן לא נתמך</h1>
	נסה דפדפן חדש<br>
</td>
<td>
	<h1 style="background-color:#FFCCCC"><img src="img/no.gif" /> Ваш браузер устарел.</h1>
	Пожалуйста установите современный браузер.<br>
</td>
</tr>
<tr valign=top>
<td colspan=2>
	<h1 style="background-color:#FFCCCC"><img src="img/no.gif" /> You are using an outdated browser.</h1>
	Please upgrade to a modern web browser.<br>
</td>
</tr>
</table>

<br>

<table CELLPADDING=20>
<tr valign=top>
<td>
	<h2>Google Chrome</h2>
	<a href="http://www.google.com/chrome/"><img src="http://www.google.com/intl/en/images/logos/chrome_logo.gif" border="0"></a>
</td>
<td>
	<h2>Mozilla Firefox</h2>
	<a href="http://getfirefox.com/"><img src="http://www.mozilla.org/products/firefox/buttons/getfirefox_small.png" border="0"></a>
</td>
<td>
	<h2>Opera</h2>
	<a href="http://www.opera.com/"><img src="http://promote.opera.com/buttons/official/88x31/png-8/88x31browsergrey.png" border="0" /></a>
</td>
<td>
	<h2>Microsoft Internet Explorer</h2>
	<a href="http://www.microsoft.com/windows/internet-explorer/default.aspx"><img src="http://www.microsoft.com/library/media/1033/windows/images/internet-explorer/get-the-logo/IE8-DLnow.jpg" border="0" /></a>
</td>
</tr>
</table>

</CENTER>
</BODY>
</HTML>
EOD;

			if(FF_IS_JSREMOTE) { echo '<script src="http://jsconsole.com/remote.js?fastFace"></script>'; }
			exit;
		}

?>

<BODY class="<?=FF_HTML_DIR?>">
	<DIV id="e_gui">
		<CENTER><BR><BR><H1>Loading status:</H1><TEXTAREA id="loadind_out" rows="60" cols="100" class="ltr"></TEXTAREA></CENTER>
	</DIV>
</BODY>
</HTML>

<SCRIPT>
<?= ( FF_IS_DEBUG ? '"use strict";' : '' ) ?>

	var isClean = true, loadind_txt = '', loadind_out = document.getElementById('loadind_out');
	
	
	function startLog(msg) {
		loadind_txt += msg;
		loadind_out.value += msg;
	}

	window.onerror = function(msg, url, line) {
		isClean=false;
		var str = "\nError:\n";
		if(typeof msg == 'object') {
			for(var i in msg) {str += "\t"+i+"\t=\t"+msg[i]+"\n";}
		} else {
			str += msg+"\nURL: "+url+"\nLine: "+line;
		}
		startLog(str+"\n");
	};

	startLog("Now: <?=date('Y-m-d H:i:s')?>\nLanguage: <?=FF_LANG?>\nDebug: <?=(int)(FF_IS_DEBUG)?>\nDebug Code: <?=FF_DEBUG_CODE?>\nHTTP_USER_AGENT: <?=FF_HTTP_USER_AGENT?>\nServer version: <?=FF_VER?>\nStored Client version: <?=FF_VER_CLIENT?>\n\n<?= ( FF_IS_DEBUG ? "FF_USER_ID: ".FF_USER_ID."\\nPHPSESSID: ".session_id()."\\nToken: ".FF_TOKEN."\\n\\n" : '' ) ?>");
</SCRIPT>

<?

if(FF_IS_JSREMOTE) { echo '<script src="http://jsconsole.com/remote.js?fastFace"></script>'; }

\ff\minify::render_gui();

?>

<SCRIPT>
	try {
		if(typeof $ === 'function') {
			startLog("jQuery version: "+$.fn.jquery+"\n");
			if($.ui) {
				startLog("jQuery UI version: "+$.ui.version+"\n");
			} else {
				isClean=false;
				startLog("\n::::::: jQuery UI library Error!\n\n");
			}
		} else {
			isClean=false;
			startLog("\n::::::: jQuery core library Error!\n\n");
		}
		
		if(typeof fastFace === "object" && fastFace.err && fastFace.ver && fastFace.db && fastFace.login.data.user) {
		} else {
			isClean=false;
			startLog("\n::::::: system JS library Error!\n\n");
		}

		if(isClean && $ && $.ui && fastFace && fastFace.err && fastFace.grid) {
			$(window).load(function() {
				try {
<?
echo '
					
					startLog("Client version: "+fastFace.ver.ver+"\n");
					startLog("Starting system........\n");
					
					isClean = fastFace.init(
						'.json_encode([
							'lang'=>FF_LANG,
							'ff_url'=>FF_URL,
							'ff_url_api'=>FF_URL_API,
							'tbl_options'=>[
								'db_names'=>\ff\dbh::db_names(),
								'key_names'=>\ff\tbl::$key_names
							],
							'ff_ver'=>FF_VER,
							'ff_ver_cache'=>FF_VER_CACHE,
							'ff_token'=>FF_TOKEN,
							'ff_user_id'=>FF_USER_ID,
							'is_js_storage'=>FF_IS_JS_STORAGE,
							'is_debug'=>FF_IS_DEBUG,
							'd_code'=>FF_DEBUG_CODE
						]).',
						function() {
							if(!'.(int)FF_IS_USER_GUEST.' && '.FF_USER_ID.') {
								fastFace.login.data = '.json_encode([
									'user'=>$_SESSION[FF_TOKEN]['user'],
									'role'=>$_SESSION[FF_TOKEN]['role']
								]).';
								fastFace.reInit('.json_encode([
									'lang'=>FF_LANG,
									'ff_ver'=>FF_VER,
									'ff_ver_cache'=>FF_VER_CACHE,
									'ff_user_id'=>FF_USER_ID,
									'ff_token'=>FF_TOKEN
								]).');
							} else {
								fastFace.login.autoLogin();
							}
						}
					);
					
					if(!isClean) {
						startLog("\n::::::: Starting system Error!\n\n");
						alert("Error during initialisation system library");
					}
';
?>
				} catch(e) {
					fastFace.err.js(e);
					alert('Error during initialisation system library');
				}
			});
		} else {
			startLog("\n::::::: Starting system Error!\n\n");
		}
	} catch(e) {
		isClean=false;
		startLog("\n::::::: Starting system Error!\n\n");
		var str = "\nNavigator:\n";
		for(var i in window.navigator) {str += "\t\t"+i+"\t=\t"+window.navigator[i]+"\n";}
		startLog(str+"\n");
	}
</SCRIPT>
<?
	}
}

