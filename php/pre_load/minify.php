<?

namespace ff;

class minify {

	public static $path = NULL;
	public static $lib = NULL;
	public static $url = NULL;
	private static $is_dev = NULL;
	private static $is_min_js = NULL;
	private static $is_min_css = NULL;
	private static $is_group = NULL;
	
	private static $add_grp = NULL;
	private static $add_css_keys = NULL;
	private static $add_js_keys = NULL;

	private static $css_keys = NULL;
	private static $js_keys = NULL;
	
	private static $is_jshint = NULL;
	private static $jshint_url = NULL;
	private static $jshint_err = NULL;
	private static $jshint_keys = NULL;

	
	public static function config(array $ff_opt) {
		static::$url = \ff\getURL(\ff\getVal($ff_opt, 'minify.url'));
		static::$path = \ff\getPATH(\ff\getVal($ff_opt, 'minify.path'));
		static::$lib = static::$path.'/lib';
		set_include_path(static::$lib . PATH_SEPARATOR . get_include_path());
		
		static::$is_dev = \ff\getVal($ff_opt, 'minify.is_dev', FALSE);
		static::$is_min_js = (!FF_IS_DEV || static::$is_dev) && \ff\getVal($ff_opt, 'minify.is_min_js', TRUE);
		static::$is_min_css = (!FF_IS_DEV || static::$is_dev) && \ff\getVal($ff_opt, 'minify.is_min_css', TRUE);
		static::$is_group = (!FF_IS_DEV || static::$is_dev) && \ff\getVal($ff_opt, 'minify.is_group', TRUE);
		
		static::$add_grp = \ff\getVal($ff_opt, 'minify.group_files', []);
		
		static::$css_keys = array_merge(\ff\getVal($ff_opt, 'minify.css_keys', []), ['ff_css']);
		static::$js_keys = array_merge(\ff\getVal($ff_opt, 'minify.js_keys', []), ['ff_js', 'ff_lib']);
		
		static::$is_jshint = FF_IS_DEV && \ff\getVal($ff_opt, 'dev.jshint', TRUE);
		static::$jshint_url = \ff\getURL(\ff\getVal($ff_opt, 'dev.jshint_url'));
		static::$jshint_keys = \ff\getVal($ff_opt, 'dev.jshint_min_keys', ['ff_lib']);
		static::$jshint_err = [
			'predef'=>array_merge(
				\ff\getVal($ff_opt, 'dev.jshint_predef', []),
				['fastFace', 'Slick', 'nicEditor', 'str_repeat', 'str_pad', 'date', 'strtotime', 'sprintf', 'number_format', 'array_fill_keys', 'pack', 'escape']
			),
			'bitwise'=>FALSE, 'browser'=>TRUE, 'curly'=>TRUE, 'debug'=>FALSE, 'devel'=>TRUE, 'eqeqeq'=>TRUE, 'evil'=>TRUE, 'jquery'=>TRUE, 'forin'=>TRUE, 'immed'=>TRUE, 'latedef'=>TRUE, 'newcap'=>FALSE, 'noarg'=>FALSE, 'noempty'=>TRUE, 'nonew'=>TRUE, 'nomen'=>FALSE, 'onevar'=>FALSE, 'plusplus'=>FALSE, 'regexp'=>TRUE, 'strict'=>FALSE, 'trailing'=>TRUE, 'undef'=>TRUE, 'white'=>FALSE
		];
	}

	
	public static function get($key = NULL) {
		$group = \ff\lcache::get( 'minify/'.FF_LANG.'/grp' );
		if( empty( $group ) || !is_array($group) ) {
			$group = static::generate();
			if( empty( $group ) || !is_array($group) ) {
				throw new \Exception( sprintf( '%s: Minify groups is not cached', __METHOD__ ) );
			}
		}
	
		if(empty($key)) {
			return $group;
		} else {
			if(empty($group[$key])) {
				throw new \Exception( sprintf( '%s: Key [%s] not exists in groups', __METHOD__, $key ) );
			}
			return $group[$key];
		}
	}

	public static function render_gui() {
		static::render(static::$css_keys, FALSE);
		static::render(static::$js_keys, TRUE);
		
		if(static::$is_jshint) {
			echo PHP_EOL.'<script src="'.static::$jshint_url.'?v='.FF_VER.'" type="text/javascript"></script>'.PHP_EOL;
			
			echo '<script type="text/javascript">
				function jshint_check(js_content) {
					JSHINT(js_content, '.json_encode(static::$jshint_err).') || console.error("JSHINT:", JSHINT.data());
				}
			'.PHP_EOL;
			$group = static::get( );
			foreach (static::$jshint_keys as $tmp_key => $key) {
				foreach ($group[$key] as $tmp_key2 => $file) {
					echo '$.get("'.\ff\getURL($file).'?v='.FF_VER.'", null, jshint_check, "text");'.PHP_EOL;
				}
			}
			echo '</script>'.PHP_EOL;
		}
	}
	
	public static function render(array $keys, $is_js = TRUE) {
		if(empty($keys) || !is_array($keys)) {
			throw new \Exception( sprintf( '%s: Must provide keys', __METHOD__ ) );
		}
		
		if(static::$is_group) {
			
			if($is_js) {
				echo PHP_EOL.'<script src="'.static::$url.'?g='.implode(',', $keys).'&l='.FF_LANG.'&v='.FF_VER.'&123456" type="text/javascript"></script>'.PHP_EOL;
			} else {
				echo PHP_EOL.'<link rel="stylesheet" href="'.static::$url.'?g='.implode(',', $keys).'&l='.FF_LANG.'&v='.FF_VER.'&123456" >'.PHP_EOL;
			}
			
		} else {
				
			$group = static::get( );
			
			foreach ($keys as $tmp_key => $key) {
				if(!empty($group[$key]) && is_array($group[$key])) {
					foreach ($group[$key] as $tmp_key2 => $file) {
						if($is_js) {
							echo PHP_EOL.'<script src="'.(static::$is_min_js ? static::$url.'?f='.urlencode(str_replace('//', '/', $file)).'&l='.FF_LANG.'&v='.FF_VER.'&123456' : \ff\getURL($file).'?v='.FF_VER ).'" type="text/javascript"></script>'.PHP_EOL;
						} else {
							echo PHP_EOL.'<link rel="stylesheet" href="'.(static::$is_min_css ? static::$url.'?f='.urlencode(str_replace('//', '/', $file)).'&l='.FF_LANG.'&v='.FF_VER.'&123456' : \ff\getURL($file).'?v='.FF_VER ).'">'.PHP_EOL;
						}
					}
				} else {
					throw new \Exception( sprintf( '%s: Key [%s] not exists in groups', __METHOD__, $key ) );
				}
			}

		}

	}












	
	private static function generate() {
		$add_grp = [];
		if(!empty(static::$add_grp) && is_array(static::$add_grp)) {
			foreach ( static::$add_grp as $key => $val ) {
				$val = \ff\getPATH($val);
				if(!is_file($val)) {
					throw new \Exception( sprintf( '%s: Minify groups file [%s] not found', __METHOD__, $val ) );
				}
				$add_grp = array_merge($add_grp, (require_once($val)));
			}
		}

		$group = array_merge(
			[
						
				// FF JS
				'ff_lib' => [
					'/'.FF_URL.'/js/entryPoint.js',
					'/'.FF_URL.'/js/sys/lib.js',
					'/'.FF_URL.'/js/sys/err.js',
					'/'.FF_URL.'/js/sys/ver.js',
					'/'.FF_URL.'/js/sys/lang.js',
					'/'.FF_URL.'/js/sys/sync.js',
					'/'.FF_URL.'/js/sys/timer.js',
					'/'.FF_URL.'/js/sys/counter.js',
					'/'.FF_URL.'/js/sys/dict.js',
					'/'.FF_URL.'/js/sys/menu.js',
					'/'.FF_URL.'/js/sys/msg.js',
					'/'.FF_URL.'/js/sys/pid.js',
					'/'.FF_URL.'/js/sys/cache.js',
					'/'.FF_URL.'/js/sys/login.js',
					'/'.FF_URL.'/js/sys/report.js',
					'/'.FF_URL.'/js/render/render.js',
					'/'.FF_URL.'/js/render/bg.js',
					'/'.FF_URL.'/js/render/dialog.js',
					'/'.FF_URL.'/js/render/grid.js',
					'/'.FF_URL.'/js/render/iframe.js',
					'/'.FF_URL.'/js/render/view.js',
					'/'.FF_URL.'/js/render/edit.js',
					'/'.FF_URL.'/js/grid/grid.js',
					'/'.FF_URL.'/js/grid/head.js',
					'/'.FF_URL.'/js/grid/data.js',
					'/'.FF_URL.'/js/grid/column.js',
					'/'.FF_URL.'/js/grid/group.js',
					'/'.FF_URL.'/js/grid/total.js',
					'/'.FF_URL.'/js/gui/gui.js',
					'/'.FF_URL.'/js/tbl/arr.js',
					'/'.FF_URL.'/js/tbl/tbl.js',
					'/'.FF_URL.'/js/tbl/load.js',
					'/'.FF_URL.'/js/tbl/jsfn.js',
					'/'.FF_URL.'/js/tbl/run.js',
					'/'.FF_URL.'/js/db/db.js',
					'/'.FF_URL.'/js/db/edit.js',
					'/'.FF_URL.'/js/db/find.js',
					'/'.FF_URL.'/js/db/add.js',
					'/'.FF_URL.'/js/db/form.js',
					'/'.FF_URL.'/js/db/grid.js'
				],

				'ff_css' => [
				
					// jQuery UI CSS
					'//js_lib/jquery/css/jquery-ui.css',
					
					// jQuery Plugins CSS
					'//js_lib/other/normalize.css',
					'//js_lib/other/boilerplate.css',
					'//js_lib/jquery/plugins/fastFace/dialog/print/dialog.print.css',
					'//js_lib/jquery/plugins/dialog.fullscreen/dialog.fullscreen.css',
					'//js_lib/jquery/plugins/timepicker/timepicker.css',
					'//js_lib/jquery/plugins/daterangepicker/daterangepicker.css',
					'//js_lib/jquery/plugins/wColorPicker/wColorPicker.css',
					'//js_lib/jquery/plugins/pnotify/pnotify.css',
					'//js_lib/jquery/plugins/pnotify/icons.css',
					
					// FF CSS
					'/'.FF_URL.'/css/main.css',
				],
				

				'ff_js' => [
				
					//  PHP.js
					'//js_lib/phpjs/str_repeat.js',
					'//js_lib/phpjs/str_pad.js',
					'//js_lib/phpjs/date.js',
					'//js_lib/phpjs/strtotime.js',
					'//js_lib/phpjs/pack.js',
					'//js_lib/phpjs/sprintf.js',
					'//js_lib/phpjs/number_format.js',
					'//js_lib/phpjs/array_fill_keys.js',

					// Date
					'//js_lib/date/'.(FF_LANG === 'he' ? 'he-IL' : (FF_LANG === 'ru' ? 'ru-RU' : 'en-US')).'.js',
					'//js_lib/date/core.js',
					'//js_lib/date/parser.js',
					'//js_lib/date/sugarpak.js',
					'//js_lib/date/time.js',

					// jQuery
					'//js_lib/other/json2.js',
					'//js_lib/jquery/jquery.js',
					'//js_lib/jquery/plugins/core/cookie.js',
					'//js_lib/jquery/plugins/core/event.destroyed.js',
					'//js_lib/jquery/jquery-ui.js',
					'//js_lib/jquery/plugins/ui/datepicker-'.FF_LANG.'.js',
					'//js_lib/jquery/plugins/ui/event.drag.js',
					'//js_lib/jquery/plugins/ui/event.drop.js',

					// Ben Alman jQuery plugins
					'//js_lib/jquery/plugins/ba/ba-each2.js',
					'//js_lib/jquery/plugins/ba/ba-getobject.js',
					'//js_lib/jquery/plugins/ba/ba-iff.js',

					// jQuery plugins
					'//js_lib/jquery/plugins/ui/labelify.js',
					'//js_lib/jquery/plugins/ui/autoGrowInput.js',
					'//js_lib/jquery/plugins/ui/autoNumeric.js',
					'//js_lib/jquery/plugins/ui/collapsible.js',
					'//js_lib/jquery/plugins/fastFace/dialog/print/dialog.print.js',
					'//js_lib/jquery/plugins/dialog.fullscreen/dialog.fullscreen.js',
					'//js_lib/jquery/plugins/timepicker/timepicker-addon.js',
					'//js_lib/jquery/plugins/timepicker/timepicker-addon-'.FF_LANG.'.js',
					'//js_lib/jquery/plugins/daterangepicker/daterangepicker.js',
					'//js_lib/jquery/plugins/wColorPicker/wColorPicker.js',
					'//js_lib/jquery/plugins/pnotify/pnotify.js',
					'//js_lib/nicEdit/nicEdit.js'

				],
				
			],
			
			$add_grp
			
		);
		
		\ff\lcache::set( 'minify/'.FF_LANG.'/grp', $group );
		return $group;
	}
	
}