<?
namespace ff;

class api {

	public static function config(array $ff_opt) {
		define('FF_PHP_SELF', \ff\getSERVER('PHP_SELF', ''));
		define('FF_REQUEST_METHOD', \ff\getSERVER('REQUEST_METHOD', ''));
		define('FF_IS_GET', FF_REQUEST_METHOD === 'GET');
		define('FF_IS_POST', FF_REQUEST_METHOD === 'POST');

		define('FF_HTTP_REFERER', \ff\getSERVER('HTTP_REFERER', ''));
		define('FF_HTTP_USER_AGENT', strtolower(\ff\getSERVER('HTTP_USER_AGENT', '')));

		define('FF_IS_IE', strpos(FF_HTTP_USER_AGENT, 'msie') !== FALSE);
		define('FF_IS_OPERA', !FF_IS_IE && strpos(FF_HTTP_USER_AGENT, 'opera') !== FALSE);
		define('FF_IS_WEBKIT', !FF_IS_IE && !FF_IS_OPERA && (strpos(FF_HTTP_USER_AGENT, 'chrome') !== FALSE || strpos(FF_HTTP_USER_AGENT, 'safari') !== FALSE));
		define('FF_IS_MOZ', !FF_IS_IE && !FF_IS_WEBKIT && !FF_IS_OPERA);
		define('FF_BR_CSS_CLASS', (FF_IS_IE ? 'br-IE' : 'br-not-IE '.(FF_IS_WEBKIT ? 'br-WK' : (FF_IS_MOZ ? 'br-MZ' : (FF_IS_OPERA ? 'br-O' : 'br-WK'))))); //@@@ nod defined browser marked as WebKit

		define('FF_URL', str_replace('\\', '/', str_replace(FF_DIR_ROOT, '', FF_DIR)) );
		define('FF_URL_API', \ff\getVal($ff_opt, 'api_url', FF_URL) );

		define('FF_IS_JSREMOTE', FF_IS_DEBUG && !FF_IS_DEV && \ff\getVal($ff_opt, 'debug.jsremote', FALSE));
		define('FF_IS_FIREBUG_LITE', FF_IS_DEBUG && !FF_IS_DEV && \ff\getVal($ff_opt, 'debug.firebug_lite', FALSE));

		define('FF_PHPEXCEL_DIR', \ff\getVal($ff_opt, 'lib.phpexcel'));
		
		/**************
		*
		*   FF_OUTPUT
		*
		***************/
		$arr_output_types = [ 'js', 'json', 'html', 'xml', 'csv', 'pdf', 'xls' ];

		define('FF_OUTPUT', in_array(\ff\getREQUEST('o'), $arr_output_types, TRUE) ? $_REQUEST['o'] : 'js');

		\ff\define_arr(
			$arr_output_types,
			[FF_OUTPUT=>TRUE],
			'FF_IS_OUT_'
		);
	}

	public static function ver_check() {
		if( FF_VER_CLIENT !== FF_VER ) {
			if( FF_IS_OUT_JS ) {
				echo '
				if(fastFace && fastFace.ver) {
					fastFace.ver.err( "'.FF_VER.'", "'.FF_VER_CLIENT.'" );
				} else {
					alert("New version of system!\n\n Please press F5 or Refresh button");
				}
				';
				exit;
			} else {
				throw new \Exception(sprintf('%s: Client version [%s] not equal to server version [%s]', __METHOD__, FF_VER_CLIENT, FF_VER));
			}
		}
	}
	
	
	
	public static function process( ) {
		
		if( FF_IS_OUT_JS ) {
			header( 'Content-type: application/javascript; charset=utf-8' );
		} else if( FF_IS_OUT_JSON ) {
			header( 'Content-type: application/json; charset=utf-8' );
		} else if( FF_IS_OUT_HTML ) {
			header( 'Content-type: text/html; charset=utf-8' );
		} else if( FF_IS_OUT_XML ) {
			header( 'Content-type: text/xml; charset=utf-8' );
		} else if( FF_IS_OUT_CSV ) {
			header( 'Content-type: text/csv; charset=utf-8' );
			//header( 'Content-Disposition: attachment; filename="output.csv"' );
		} else if( FF_IS_OUT_PDF || FF_IS_OUT_XLS ) {
			header( 'Pragma: public' );
			header( 'Expires: 0' );
			header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
			header( 'Content-Type: application/force-download' );
			header( 'Content-Type: application/octet-stream' );
			header( 'Content-Type: application/download' );
			header( 'Content-Transfer-Encoding: binary' );
		}
		header( 'Content-Language: '.FF_LANG );

		static::ver_check();
		
		if( FF_IS_COMPRESS && !FF_IS_OUT_XLS && !FF_IS_OUT_PDF ) {
			//ob_start( 'ob_gzhandler' );
			//ini_set( 'zlib.output_compression', '1' );
			//ini_set( 'zlib.output_compression_level', '5' );
			ob_start( '\\ff\\my_ob_gzhandler' );
			header( 'Content-Encoding: gzip' );
		}
		
		$input = \ff\getREQUEST('i', 'json');
		if( $input === 'json' ) {
			$req_arr = @json_decode( \ff\getREQUEST('cmd'), TRUE );
			if( json_last_error( ) ) {
				throw new \Exception( \ff\err::json_err_code( json_last_error( ) ), json_last_error( ) );
			}
		} else {
			throw new \Exception(sprintf('%s: Wrong input type [%s]', __METHOD__, $input));
		}
		
		if( !is_array( $req_arr ) ) {
			throw new \Exception(sprintf('%s: Wrong API command', __METHOD__));
		}

		foreach ( $req_arr as $req_key => $req_obj ) {
			if( !is_array( $req_obj ) || empty($req_obj) || count($req_obj) < 1 ) {
				throw new \Exception(sprintf('%s: Wrong API command', __METHOD__));
			}
			
			$id_or_url = array_shift( $req_obj );
			\ff\cls::run( $id_or_url, $req_obj );
		}


		if( FF_IS_COMPRESS ) {
			$gzip_contents = ob_get_contents( );
			ob_end_clean( );

			$gzip_size = strlen( $gzip_contents );
			$gzip_crc = crc32( $gzip_contents );

			$gzip_contents = gzcompress( $gzip_contents, 9 );
			$gzip_contents = substr( $gzip_contents, 0, strlen( $gzip_contents ) - 4 );

			header( 'Content-Length: '.$gzip_size );
			echo "\x1f\x8b\x08\x00\x00\x00\x00\x00";
			echo $gzip_contents;
			echo pack( 'V', $gzip_crc );
			echo pack( 'V', $gzip_size );
		}

	}


}
