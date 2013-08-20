<?
namespace ff;

class gen_cls {

	public static function generate( array $ff_opt ) {
		$lock = sem_get('ff/generate');
		if($lock === FALSE || sem_acquire($lock) === FALSE) {
			throw new \Exception( sprintf( '%s: Generation in process..., reload your page', __METHOD__ ) );
		} else {
			set_time_limit(900);
			
			if(defined('FF_REGENERATE') && FF_REGENERATE) {
				static::regenerate( $ff_opt );
			} 
				
			\ff\id2url::set('cls/fn', array_column(\ff\dbh::get_all('SELECT `f`.`id`, CONCAT(`n`.`ns`, \'\\\\\', `c`.`cls`, \'::\', `f`.`fn`) AS `url` FROM `'.FF_DB_NAME.'`.`ff_cls_fn` `f`, `'.FF_DB_NAME.'`.`ff_cls` `c`, `'.FF_DB_NAME.'`.`ff_ns` `n` WHERE `n`.`is_act`=TRUE AND `c`.`is_act`=TRUE AND `f`.`is_act`=TRUE AND `f`.`ff_cls`=`c`.`id` AND `c`.`ff_ns`=`n`.`id` ORDER BY `f`.`id`'), 1, 0));

			\ff\id2def::set_defs('cls', \ff\array_reindex(\ff\dbh::get_all('SELECT `c`.`id`, `c`.`path`, CONCAT(`n`.`ns`, \'\\\\\', `c`.`cls`) AS `url` FROM `'.FF_DB_NAME.'`.`ff_cls` `c`, `'.FF_DB_NAME.'`.`ff_ns` `n` WHERE `n`.`is_act`=TRUE AND `c`.`is_act`=TRUE AND `c`.`ff_ns`=`n`.`id` ORDER BY `c`.`id`', MYSQLI_ASSOC), 'id'));
			\ff\id2url::set('cls', array_column(\ff\dbh::get_all('SELECT `c`.`id`, CONCAT(`n`.`ns`, \'\\\\\', `c`.`cls`) AS `url` FROM `'.FF_DB_NAME.'`.`ff_cls` `c`, `'.FF_DB_NAME.'`.`ff_ns` `n` WHERE `n`.`is_act`=TRUE AND `c`.`is_act`=TRUE AND `c`.`ff_ns`=`n`.`id` ORDER BY `c`.`id`'), 1, 0));
			
			sem_release($lock);
		}
	}
	
	
	private static function regenerate( array $ff_opt ) {
		$folders = \ff\getVal($ff_opt, 'cls.dir', []);
		$syntax_check = \ff\getVal($ff_opt, 'cls.syntax_check', TRUE);
		$syntax_check_cmd = \ff\getVal($ff_opt, 'cls.syntax_check_cmd', TRUE);
		
		$included_files = get_included_files();
		$ns_url2id = array_column(\ff\dbh::get_all('SELECT `ns`, `id` FROM `'.FF_DB_NAME.'`.`ff_ns`'), 1, 0);
		$cls_defs = \ff\array_reindex(\ff\dbh::get_all('SELECT CONCAT(`n`.`ns`, \'\\\\\', `c`.`cls`) AS `url`, `c`.`id`, `c`.`path` FROM `'.FF_DB_NAME.'`.`ff_cls` `c`, `'.FF_DB_NAME.'`.`ff_ns` `n` WHERE `c`.`ff_ns`=`n`.`id`', MYSQLI_ASSOC), 'url');
		$cls_insert = [];
		$cls_fns = [];

		$folders = is_array( $folders ) ? $folders : [];
		array_unshift( $folders, FF_DIR_CLS );
		foreach ( $folders as $key => $val ) {
			$folders[$key] = 0 === strpos( $val, '//' ) ? FF_DIR_ROOT.'/'.substr( $val, 2 ) : $val;
		}
		$folders = array_unique( $folders );
		
		foreach ( $folders as $key=>$main_fld ) {
			if( !empty($main_fld) && $hd = opendir( $main_fld ) ) {
				while ( FALSE !== ( $fld = readdir( $hd ) ) ) {
					if( strpos( $fld, '.' ) !== 0 ) {
						if( is_dir( $main_fld.'/'.$fld ) ) {
							if( $sub_hd = opendir( $main_fld.'/'.$fld ) ) {
								while ( FALSE !== ( $fl = readdir( $sub_hd ) ) ) {
									if( substr( $fl, -4 ) === '.php' ) {
										$file_path = $main_fld.'/'.$fld.'/'.$fl;
										$file_path_short = str_replace(FF_DIR_ROOT.'/', '//', $file_path);
										$file_name = substr( $fl, 0, -4 );
										if( is_file( $file_path ) ) {
											$file_content = file_get_contents( $file_path );
											$namespace = static::find_namespace( $file_content );
											if( !empty( $namespace ) ) {
												if(empty($ns_url2id[$namespace])) {
													$ns_url2id[$namespace] = \ff\dbh::add('INSERT INTO `'.FF_DB_NAME.'`.`ff_ns` (`is_act`, `ns`) VALUES (TRUE, \''.\ff\dbh::esc($namespace).'\')');
													if(empty($ns_url2id[$namespace])) {
														throw new \Exception( sprintf( '%s: Can Not add namespace [%s]', __METHOD__, $namespace ) );
													}
												}
												
												$cls = preg_match('/class\s+'.$file_name.'(\s*|\s+.*){/i', $file_content) === 1 ? $file_name : static::find_classname($file_content);
												
												if(!empty($cls)) {
													$url = $namespace.'\\'.$cls;
													if( !class_exists( $url ) && !in_array($file_path, $included_files) ) {
														if($syntax_check) {
															if(preg_match('/No syntax errors detected/', shell_exec($syntax_check_cmd.' '.$file_path)) === 1) {
																require_once( $file_path );
															}
														} else {
															require_once( $file_path );
														}
													}
													if( class_exists( $url ) ) {
														if( empty($cls_defs[$url]) ) {
															$cls_insert[] = '(
																TRUE,
																'.$ns_url2id[$namespace].',
																\''.\ff\dbh::esc($cls).'\',
																\''.\ff\dbh::esc($file_path_short).'\'
															)';
														} else {
															if($cls_defs[$url]['path'] !== $file_path_short) {
																\ff\dbh::upd('UPDATE `'.FF_DB_NAME.'`.`ff_cls` SET `path`=\''.dbh::esc($file_path_short).'\' WHERE `id`='.$cls_defs[$url]['id']);
															}
															unset($cls_defs[$url]);
														}
														$cls_fns[$url] = get_class_methods( $url );
													}
												}
											}
										}
									}
								}
								closedir( $sub_hd );
							}
						}
					}
				}
				closedir( $hd );
			} else {
				throw new \Exception( sprintf( '%s: Can Not open directory [%s]', __METHOD__, $main_fld ) );
			}
		}

		if(!empty($cls_defs)) {
			\ff\dbh::upd('UPDATE `'.FF_DB_NAME.'`.`ff_cls` SET `is_act`=FALSE WHERE `is_act`=TRUE AND `id` IN ('.implode(',', array_values(array_column($cls_defs, 'id'))).')');
		}

		if(!empty($cls_insert)) {
			\ff\dbh::add('INSERT INTO `'.FF_DB_NAME.'`.`ff_cls` (`is_act`, `ff_ns`, `cls`, `path`) VALUES '.implode(',', $cls_insert).'');
		}
		
		static::regenerate_fn($cls_fns);
	}

	
	private static function regenerate_fn( array $cls_fns ) {
		$fn_url2id = array_column(\ff\dbh::get_all('SELECT CONCAT(`n`.`ns`, \'\\\\\', `c`.`cls`, \'::\', `f`.`fn`) AS `url`, `f`.`id` FROM `'.FF_DB_NAME.'`.`ff_cls_fn` `f`, `'.FF_DB_NAME.'`.`ff_cls` `c`, `'.FF_DB_NAME.'`.`ff_ns` `n` WHERE `f`.`ff_cls`=`c`.`id` AND `c`.`ff_ns`=`n`.`id`'), 1, 0);
		$insert = [];
		
		$cls_defs = \ff\array_reindex(\ff\dbh::get_all('SELECT CONCAT(`n`.`ns`, \'\\\\\', `c`.`cls`) AS `url`, `c`.`id`, `n`.`ns` FROM `'.FF_DB_NAME.'`.`ff_cls` `c`, `'.FF_DB_NAME.'`.`ff_ns` `n` WHERE `c`.`ff_ns`=`n`.`id`', MYSQLI_ASSOC), 'url');
		foreach ( $cls_fns as $cls_url=>$cls_fn ) {
			if(isset($cls_defs[$cls_url])) {
				foreach ($cls_fn as $key=>$fn) {
					if(empty($fn_url2id[$cls_url.'::'.$fn])) {
						$insert[] = '(TRUE, '.$cls_defs[$cls_url]['id'].', \''.\ff\dbh::esc($fn).'\')';
					} else {
						unset($fn_url2id[$cls_url.'::'.$fn]);
					}
				}
			}
		}

		if(!empty($fn_url2id)) {
			\ff\dbh::add('UPDATE `'.FF_DB_NAME.'`.`ff_cls_fn` `f` SET `f`.`is_act`=FALSE WHERE `f`.`is_act`=TRUE AND (`f`.`id` IN ('.implode(',', array_values($fn_url2id)).') OR `f`.`ff_cls` NOT IN (SELECT `c`.`id` FROM `'.FF_DB_NAME.'`.`ff_cls` `c` WHERE `c`.`is_act`=TRUE))');
		}

		if(!empty($insert)) {
			\ff\dbh::add('INSERT INTO `'.FF_DB_NAME.'`.`ff_cls_fn` (`is_act`, `ff_cls`, `fn`) VALUES '.implode(',', $insert).'');
		}
	}
	
	
	
	
	
	private static function find_namespace( $file_content ) {
		$start = strpos( $file_content, 'namespace' );
		if( $start !== FALSE ) {
			$start += 10;
			$end = strpos( $file_content, ';', $start );
			if( $end > $start && $end - $start < 20 ) {
				return trim( substr( $file_content, $start, $end - $start ) );
			}
		}
		return NULL;
	}

	
	private static function find_classname( $file_content ) {
		$start = strpos( $file_content, 'class' );
		if( $start !== FALSE ) {
			$start += 6;
			$end = strpos( $file_content, '{', $start );
			if( $end > $start && $end - $start < 50 ) {
				$class_name = trim( substr( $file_content, $start, $end - $start ) );
				$class_name_arr = preg_split('/\s+/i', $class_name);
				return $class_name_arr[0];
			}
		}
		return NULL;
	}

	
	private static function find_extends( $cls, $file_content ) {
		$start = strpos( $file_content, 'extends' );
		if( $start !== FALSE ) {
			$start += 8;
			$end = strpos( $file_content, '{', $start );
			if( $end > $start && $end - $start < 40 ) {
				return trim( substr( $file_content, $start, $end - $start ) );
			}
		}
		return NULL;
	}
	
}
