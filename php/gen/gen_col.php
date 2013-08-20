<?
namespace ff;

class gen_col {
	
	public static function generate( array $ff_opt ) {
			if(defined('FF_REGENERATE') && FF_REGENERATE) {
				static::regenerate( $ff_opt );
			}
			
//			\ff\id2def::set_defs('tbl/col', \ff\array_reindex(\ff\dbh::get_all('SELECT `c`.`id`, CONCAT(`d`.`db`, \'.\', `t`.`tbl`, \'.\', `c`.`col`) AS `url` FROM `'.FF_DB_NAME.'`.`ff_tbl_col` `c`, `'.FF_DB_NAME.'`.`ff_tbl` `t`, `'.FF_DB_NAME.'`.`ff_db` `d` WHERE `c`.`is_act`=TRUE AND `t`.`is_act`=TRUE AND `d`.`is_act`=TRUE AND `c`.`ff_tbl`=`t`.`id` AND `t`.`ff_db`=`d`.`id` ORDER BY `c`.`id`', MYSQLI_ASSOC), 'id'));
			\ff\id2url::set('tbl/col', array_column(\ff\dbh::get_all('SELECT `c`.`id`, CONCAT(`d`.`db`, \'.\', `t`.`tbl`, \'.\', `c`.`col`) AS `url` FROM `'.FF_DB_NAME.'`.`ff_tbl_col` `c`, `'.FF_DB_NAME.'`.`ff_tbl` `t`, `'.FF_DB_NAME.'`.`ff_db` `d` WHERE `c`.`is_act`=TRUE AND `t`.`is_act`=TRUE AND `d`.`is_act`=TRUE AND `c`.`ff_tbl`=`t`.`id` AND `t`.`ff_db`=`d`.`id` ORDER BY `c`.`id`'), 1, 0));
	}

	
	public static function regenerate( array $ff_opt ) {
		$languages = \ff\getVal($ff_opt, 'lang', ['he', 'ru', 'en']);
		//require_once(FF_DIR_CLS.'/gen/gen_arr.php');

		\ff\dbh::upd('UPDATE `'.FF_DB_NAME.'`.`ff_tbl_col` `c` LEFT JOIN `'.FF_DB_NAME.'`.`ff_tbl` `t` ON (`c`.`is_act`=TRUE AND `t`.`is_act`=TRUE AND `c`.`ff_tbl`=`t`.`id`) LEFT JOIN `'.FF_DB_NAME.'`.`ff_db` `d` ON (`d`.`is_act`=TRUE AND `t`.`ff_db`=`d`.`id`) LEFT JOIN `information_schema`.`columns` `i` ON (`i`.`table_schema`=`d`.`db` AND `i`.`table_name`=`t`.`tbl` AND `i`.`column_name`=`c`.`col`) SET `c`.`is_act`=FALSE WHERE `c`.`is_act`=TRUE AND (`t`.`id` IS NULL OR `t`.`id` IS NULL OR `i`.`column_name` IS NULL)');
		
	  \ff\dbh::add('INSERT INTO `'.FF_DB_NAME.'`.`ff_tbl_col` (`is_act`, `ff_tbl`, `col`, `json`) (SELECT IF(`d`.`db`=\''.FF_DB_NAME.'\', TRUE, FALSE), `t`.`id`, `i`.`column_name`, `i`.`column_comment` FROM `'.FF_DB_NAME.'`.`ff_db` `d` JOIN `'.FF_DB_NAME.'`.`ff_tbl` `t` ON (`d`.`is_act`=TRUE AND `t`.`is_act`=TRUE AND `d`.`id`=`t`.`ff_db`) JOIN `information_schema`.`columns` `i` ON (`i`.`table_schema`=`d`.`db` AND `i`.`table_name`=`t`.`tbl`) LEFT JOIN `'.FF_DB_NAME.'`.`ff_tbl_col` `c` ON (`c`.`ff_tbl`=`t`.`id` AND `i`.`column_name`=`c`.`col`) WHERE `c`.`id` IS NULL ORDER BY `t`.`id`, `i`.`ordinal_position`)');

		$cols = [];
		$cur_tbl = NULL;
		$arr_lang = ['he'=>'he', 'heb'=>'he', '_he'=>'he', '_heb'=>'he', 'ru'=>'ru', 'rus'=>'ru', '_ru'=>'ru', '_rus'=>'ru', 'en'=>'en', 'eng'=>'en', '_en'=>'en', '_eng'=>'en'];
		
		$cols_constr = \ff\dbh::get_all('
			SELECT 
				`c`.`id`, `c`.`ff_tbl`, `tc`.`constraint_name` `c_name`, `cu`.`ordinal_position` `pos`, `cu`.`POSITION_IN_UNIQUE_CONSTRAINT` `c_pos`, `rc`.`ff_tbl` AS `rtbl_id`, `rc`.`id` AS `rcol_id`
				IF(`tc`.`constraint_type` = \'FOREIGN KEY\', TRUE, FALSE) AS `fk`,
				IF(`tc`.`constraint_type` = \'PRIMARY KEY\', TRUE, FALSE) AS `pk`,
				IF(`tc`.`constraint_type` = \'UNIQUE\', TRUE, FALSE) AS `uk`
			FROM
				`'.FF_DB_NAME.'`.`ff_db` `d`
				JOIN `'.FF_DB_NAME.'`.`ff_tbl` `t` ON (`d`.`is_act`=TRUE AND `t`.`is_act`=TRUE AND `d`.`id`=`t`.`ff_db`)
				JOIN `'.FF_DB_NAME.'`.`ff_tbl_col` `c` ON (`c`.`is_act`=TRUE AND `c`.`ff_tbl`=`t`.`id`)
				JOIN `information_schema`.`table_constraints` `tc` ON (`tc`.`table_schema`=`d`.`db` AND `tc`.`table_name`=`t`.`tbl` AND))
				JOIN `information_schema`.`key_column_usage` `cu` ON (`tc`.`table_schema`=`cu`.`table_schema` AND `tc`.`table_name`=`cu`.`table_name` AND `tc`.`constraint_name`=`cu`.`constraint_name` AND `cu`.`column_name`=`c`.`col`)
				LEFT JOIN `'.FF_DB_NAME.'`.`ff_db` `rd` ON (`rd`.`is_act`=TRUE AND `cu`.`referenced_table_schema`=`rd`.`db`)
				LEFT JOIN `'.FF_DB_NAME.'`.`ff_tbl` `rt` ON (`rt`.`is_act`=TRUE AND `rt`.`ff_db`=`rd`.`id` AND `cu`.`referenced_table_name`=`rt`.`tbl`)
				LEFT JOIN `'.FF_DB_NAME.'`.`ff_tbl_col` `rc` ON (`rc`.`is_act`=TRUE AND `rc`.`ff_tbl`=`rt`.`id` AND `cu`.`referenced_column_name`=`rc`.`col`)
			ORDER BY `d`.`id`, `t`.`id`, `tc`.`constraint_name`, `cu`.`ordinal_position`
		', MYSQLI_ASSOC);

		
		$cols_arr = \ff\array_reindex(\ff\dbh::get_all('
			SELECT
				`c`.`id`, `c`.`ff_tbl`, `c`.`col`, `c`.`json`, `c`.`php`,
				`t`.`ff_db`, `t`.`tbl`, `d`.`db`,
				`i`.`ordinal_position` AS `pos`,
				`i`.`column_default` AS `dflt`,
				IF(`i`.`is_nullable` = \'YES\', TRUE, FALSE) AS `is_null`,
				`i`.`data_type`,
				`i`.`character_maximum_length` AS `char_len`,
				`i`.`numeric_precision` AS `num_pre`,
				`i`.`numeric_scale` AS `num_sca`,
				`i`.`column_type` AS `col_type`,
				IF(`i`.`column_key` = \'PRI\', TRUE, FALSE) AS `pk`,
				IF(`i`.`column_key` = \'UNI\', TRUE, FALSE) AS `uk`,
				IF(INSTR(`i`.`data_type`, \'int\') > 0, \'int\', IF(INSTR(`i`.`data_type`, \'char\') > 0, \'char\', IF(INSTR(`i`.`data_type`, \'text\') > 0, \'text\', IF(`i`.`data_type` IN (\'decimal\', \'double\', \'float\'), \'decimal\', IF(`i`.`data_type`=\'timestamp\', \'datetime\', IF(INSTR(`i`.`data_type`, \'blob\') > 0, \'blob\', `i`.`data_type`)))))) AS `db_type`
			FROM
				`information_schema`.`columns` `i`
				JOIN `'.FF_DB_NAME.'`.`ff_db` `d` ON (`d`.`is_act`=TRUE AND `d`.`db`=`i`.`table_schema`)
				JOIN `'.FF_DB_NAME.'`.`ff_tbl` `t` ON (`t`.`is_act`=TRUE AND `t`.`ff_db`=`d`.`id` AND `t`.`tbl`=`i`.`table_name`)
				JOIN `'.FF_DB_NAME.'`.`ff_tbl_col` `c` ON (`c`.`is_act`=TRUE AND `c`.`ff_tbl`=`t`.`id` AND `i`.`column_name`=`c`.`col`)
			ORDER BY
				`t`.`id`, `i`.`ordinal_position`
		', MYSQLI_ASSOC), 'id');

		foreach ($cols_arr as $col_key=>$def) {
			$db_name  = $def['db'];
			$tbl_id   = $def['ff_tbl'];
			$tbl_name = $def['tbl'];
			$tbl_url  = $def['db'].'.'.$def['tbl'];
			
			$col_id   = $def['id'];
			$col_name = $def['col'];
			$col_url  = $tbl_url.'.'.$def['col'];
			
			if($cur_tbl !== $tbl_id) {
				$cur_tbl = $tbl_id;
				$ln = 0;
				$ln_id = 0;
			}
			
			$def = array_replace_recursive(
				$def,
				[
					'len'=>\ff\db_fn::db_len($def['col_type'], $def['type'])
				],
				($def['col_type'] === 'tinyint(1)' ? ['fmt'=>'bool'] : []),
				isset($cols_use[$col_id]) ? $cols_use[$col_id] : [],
				\ff\db_fn::config_json($def['json']),
				\ff\db_fn::config_php($def['php'])
			);
			unset($def['col_type']);
			unset($def['json']);
			unset($def['php']);
			
			// TODO: Remove - use `is_act` instead
			// SKIP
			if(!empty($def['skip'])) {
				continue;
			}

			// ORDER
			if(isset($def['ord'])) {
				if($def['type'] !== 'int') {
					throw new \Exception(sprintf('%s: Set INT type for ORD col (%s)%s', __METHOD__, $def['type'], $col_url));
				}
				if(!is_array($def['ord'])) {
					$def['ord'] = [$col_name];
				}
			}

			 // ORDER
			if(!empty($def['ord_by']) && !is_array($def['ord'])) {
				$def['ord_by'] = [is_int($def['ord']) ? $def['ord'] : NULL, is_string($def['ord']) ? $def['ord'] : 'asc'];
			}

			// ACTIVE
			if(!empty($def['act']) || $col_name === 'is_act' || (isset($def['lbl']) && $def['lbl'] === 'is_act')) {
				$def['act'] = TRUE;
				$def['fmt'] = 'bool';
				if($def['type'] !== 'int') {
					throw new \Exception(sprintf('%s: Set BOOL type for ACT col (%s)%s', __METHOD__, $def['type'], $col_url));
				}
			}

			// TODO: Remove this fix
			if(isset($def['fk']['arr'])) {
				$def['arr'] = $def['fk']['arr'];
				unset($def['fk']);
			}
			
			// TODO: Remove this fix
			if(isset($def['fk']['cache'])) {
				$def['arr'] = $def['fk']['cache'];
				unset($def['fk']);
			}

			// ARR
			if(isset($def['arr'])) {
				$def['fmt'] = isset($def['fmt']) ? $def['fmt'] : 'enum';
				if(is_array($def['arr'])) {
					$arr = [];
					foreach($def['arr'] as $key=>$val) {
						if(!empty($val)) {
							if(is_array($val) && !empty($val[1])) {
								foreach(\ff\lang::$langs as $lang_id=>$lang) {
									$arr[$lang][$val[0]] = \ff\dict::val($val[1], NULL, $lang);
								}
							} else if(is_string($val)) {
								foreach(\ff\lang::$langs as $lang_id=>$lang) {
									$arr[$lang][$val] = \ff\dict::val($val, NULL, $lang);
								}
							}
						}
					}
					if(!\ff\id2url::is_url('arr', $tbl_url.'.'.$col_name)) {
						\ff\gen_arr::add($tbl_url.'.'.$col_name, $arr);
					} else {
						$id = \ff\id2url::url2id('arr', $tbl_url.'.'.$col_name);
						if(\ff\is_equal_arr($arr, \ff\id2def::get_langs('arr', $id))) {
							\ff\gen_arr::upd($id, $arr);
						}
					}
				}
				$def['arr'] = is_int($def['arr']) ? $def['arr'] : (is_string($def['arr']) ? \ff\id2url::url2id('arr', $def['arr']) : (is_array($def['arr']) ? \ff\id2url::url2id('arr', $tbl_url.'.'.$col_name) : NULL));
				$def['len'] = count(\ff\id2def::get('arr', $def['arr'], FF_LANG));
			}

			// SET || ENUM
			if($def['type'] === 'set' || $def['type'] === 'enum') {
				if(empty($def['len']) || count($def['len']) === 0) {
					continue;
				}
				$def['fmt'] = $def['type'];
				$arr = [];
				foreach($def['len'] as $key=>$val) {
					foreach(\ff\lang::$langs as $lang_id=>$lang) {
						$arr[$lang][$val] = \ff\dict::val($val, NULL, $lang);
					}
				}
				if(!\ff\id2url::is_url('arr', $tbl_url.'.'.$col_name)) {
					\ff\gen_arr::add($tbl_url.'.'.$col_name, $arr);
				} else {
					$id = \ff\id2url::url2id('arr', $tbl_url.'.'.$col_name);
					if(\ff\is_equal_arr($arr, \ff\id2def::get_langs('arr', $id))) {
						\ff\gen_arr::upd($id, $arr);
					}
				}
				$def['arr'] = \ff\id2url::url2id('arr', $tbl_url.'.'.$col_name);
				$def['len'] = count($arr[FF_LANG]);
			}



			// FF_LANG
			if(!empty($def['lang'])) {
				if(!is_array($def['lang'])) {
					
					$def['fmt'] = 'lang';
					$def['ik'] = ['tbl' => is_bool($def['lang']) ? 'ff_lang_char' : $def['lang']];
					$def['lang'] = $languages;
					
				} else {
				
					if(isset($arr_lang[$col_name])) {
						$def['lang'] = [
							'lang'=>$arr_lang[$col_name],
							'name'=>$tbl_name
						];
					} else if($undpos = strrpos($col_name, '_')) {
						$lang = substr($col_name, $undpos);
						if(isset($arr_lang[$lang])) {
							$def['lang'] = [
								'lang'=>$arr_lang[$lang],
								'name'=>substr($col_name, 0, $undpos)
							];
						}
					}
					
					//FORM FF_LANG LINE
					if(isset($def['lang']) && $def['type'] != 'text' && !isset($def['frm']['ln'])) {
						$def['frm']['ln'] = 'ln_'.(isset($def['lang']['name']) ? $def['lang']['name'] : $col_name);
					}
				}
			}
			
			// FK
			if(isset($def['fk'])) {
				if(!is_array($def['fk'])) {
					$def['fmt'] = isset($def['fmt']) ? $def['fmt'] : ($def['fk'] === 'set' ? 'set' : 'enum');
					$def['fk'] = ['tbl'=>static::get_tbl($def['fk'], $col_name, $db_name)];
				} else if(!is_int($def['fk']['tbl'])) {
					$def['fk']['tbl'] = static::get_tbl($def['fk']['tbl'], $col_name, $db_name);
				}
				$def['fmt'] = isset($def['fmt']) ? $def['fmt'] : 'enum';
				$def['len'] = isset($def['len']) ? $def['len'] : 30;
				
				if(!empty($cols_use[$def['fk']['tbl']]) && (empty($def['fk']['col']) || !is_int($def['fk']['col']))) {
					$def['fk']['col'] = static::get_pk(
						$def['fk']['tbl'],
						$cols_use[$def['fk']['tbl']],
						empty($def['fk']['col']) ? NULL : $def['fk']['col']
					);
				}
			}

			$cols[$tbl_id][$col_id] = $def;
		}

		foreach($cols as $tbl_id=>$cols_def) {
			foreach($cols_def as $col_id=>$def) {
				// SK
				if(isset($def['sk'])) {
					if(!is_array($def['sk'])) {
						$def['sk'] = ['tbl'=>static::get_tbl($def['sk'], $def['name'], $db_name)];
					} else if(!is_int($def['sk']['tbl'])) {
						$def['sk']['tbl'] = static::get_tbl($def['sk']['tbl'], $def['name'], $db_name);
					}
					
					if(empty($def['sk']['col']) || !is_array($def['sk']['col'])) {
						$col_pri = static::get_pk($tbl_id, $cols_def, NULL);
						$def['sk']['col'][$col_pri] = static::get_fk($def['sk']['tbl'], $cols[$def['sk']['tbl']], $tbl_id, $col_pri);
					}
					$cols_def[$col_id] = $def;
				}
				
			}
			
			\ff\lcache::set( 'tbl/'.$tbl_id.'/cols', $cols_def );
			\ff\lcache::set( 'tbl/'.$tbl_id.'/keys', static::fill_keys($cols_def) );
		}

		\ff\gen_arr::generate( );
	}


	private static function get_tbl($tbl, $col_name, $db_name) {
		if( strpos($tbl, '.') !== FALSE ) {
			return \ff\id2url::url2id( 'tbl', $tbl );
		} else if( \ff\id2url::is_url( 'tbl', (strpos($col_name, 'ff_') === 0 ? FF_DB_NAME : $db_name) .'.'.$col_name ) ) {
			return \ff\id2url::url2id( 'tbl', (strpos($col_name, 'ff_') === 0 ? FF_DB_NAME : $db_name) .'.'.$col_name );
		} else if( \ff\id2url::is_url( 'tbl', (strpos($tbl, 'ff_') === 0 ? FF_DB_NAME : $db_name) .'.'.$tbl ) ) {
			return \ff\id2url::url2id( 'tbl', (strpos($tbl, 'ff_') === 0 ? FF_DB_NAME : $db_name) .'.'.$tbl );
		}
		throw new \Exception(sprintf('%s: Cannot find tbl [%s] for [%s] in [%s]', __METHOD__, $tbl, $col_name, $db_name));
	}
	

	private static function get_pk($tbl, array $cols, $col_name = NULL) {
		foreach($cols as $col_id=>$col_def) {
			if(isset($col_def['pk']) && ($col_name === NULL || $col_name === $col_def['name'])) {
				return $col_id;
			}
		}
		throw new \Exception(sprintf('%s: Cannot find PRI col [%s] in [%s]', __METHOD__, $col_name, $tbl));
	}

	
	private static function get_fk($tbl_id, array $cols, $rtbl_id, $rcol_id) {
		foreach($cols as $col_id=>$col_def) {
			if(isset($col_def['fk']) && $col_def['fk']['tbl'] === $rtbl_id && $col_def['fk']['col'] === $rcol_id)  {
				return $col_id;
			}
		}
		throw new \Exception(sprintf('%s: Cannot find FK in [%s] for [%s] [%s]', __METHOD__, $tbl_id, $rtbl_id, $rcol_id));
	}
	
	
	
	
	private static function fill_keys(array $cols) {
		$keys = array_fill_keys(array_merge(['col2id', 'id2col'], \ff\tbl::$key_names), []);
		
		foreach($cols as $col_id => $col) {
			$keys['col2id'][$col['name']] = $col_id;
			$keys['id2col'][$col_id] = $col['name'];
			foreach(\ff\tbl::$key_names as $key_id=>$key_name) {
				if(!empty($col[$key_name])) {
					$keys[$key_name][] = $col_id;
				}
			}
		}

		$keys['ord_by'] = static::fix_order_by($cols, array_unique(array_merge($keys['ord'], $keys['ord_by'])));
		$keys['ro'] = array_unique(array_merge($keys['pk'], $keys['sk'], $keys['ro']));

		return $keys;
	}

	
	
	
	
	
	private static function fix_order_by(array $cols, array $ord_by) {
		$ord = [];
		$place = [];
		$no_place = [];
		foreach($ord_by as $key => $col_id) {
			$col = $cols[$col_id];
			if(isset($col['ord']) && is_array($col['ord'])) {
				foreach($col['ord'] as $tmp_key => $tmp_val) {
					if(!empty($cols[$tmp_val])) {
						$ord[] = $tmp_val;
					}
				}
			} else if(isset($col['ord_by']) && is_array($col['ord_by'])) {
				if(isset($col['ord_by'][0])) {
					$place[$col['ord_by'][0]] = $col_id;
				} else {
					$no_place[] = $col_id;
				}
			}
		}
		
		$res = array_unique(array_merge($ord, $place, $no_place));
		foreach($res as $key => $col_id) {
			$res[$key] = [$col_id, isset($cols[$col_id]['ord_by'][1]) ? $cols[$col_id]['ord_by'][1] : 'asc'];
		}
		
		return $res;
	}

	
}

