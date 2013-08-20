<?
namespace ff;

class gen_tbl {
	
	public static function generate( array $ff_opt ) {
		$lock = sem_get('ff/generate');
		if($lock === FALSE || sem_acquire($lock) === FALSE) {
			throw new \Exception( sprintf( '%s: Generation in process..., reload your page', __METHOD__ ) );
		} else {
			set_time_limit(900);

			if(defined('FF_REGENERATE') && FF_REGENERATE) {
				static::regenerate(  );
			}
			
			require_once(FF_DIR_CLS.'/gen/gen_col.php');
			\ff\gen_col::generate( $ff_opt );

//			\ff\id2url::set('tbl/spec', array_column(\ff\dbh::get_all('SELECT `s`.`id`, CONCAT(`d`.`db`, \'.\', `t`.`tbl`, \'::\', `s`.`spec`) AS `url` FROM `'.FF_DB_NAME.'`.`ff_tbl_spec` `s`, `'.FF_DB_NAME.'`.`ff_tbl` `t`, `'.FF_DB_NAME.'`.`ff_db` `d` WHERE `s`.`is_act`=TRUE AND `t`.`is_act`=TRUE AND `d`.`is_act`=TRUE AND `s`.`ff_tbl`=`t`.`id` AND `t`.`ff_db`=`d`.`id` ORDER BY `s`.`id`'), 1, 0));
			$tbl_defs = \ff\array_reindex(array_map(function($value) { return array_replace($value, @json_decode($value['json'], TRUE)); }, \ff\dbh::get_all('SELECT `t`.`id`, `t`.`ff_db`, `t`.`tbl`, `t`.`json`, `d`.`db`, CONCAT(`d`.`db`, \'.\', `t`.`tbl`) AS `url`, CONCAT(\'`\', `d`.`db`, \'`.`\', `t`.`tbl`, \'`\') AS `path`, `i`.`table_rows` as `rows`, `i`.`auto_increment` as `auto_incr`, `i`.`update_time` as `updated_at` FROM `'.FF_DB_NAME.'`.`ff_db` `d` JOIN `'.FF_DB_NAME.'`.`ff_tbl` `t` ON (`d`.`id`=`t`.`ff_db`) JOIN `information_schema`.`tables` `i` ON (`i`.`table_schema`=`d`.`db` AND `i`.`table_name`=`t`.`tbl`) ORDER BY `t`.`id` ', MYSQLI_ASSOC)), 'id');
			\ff\id2def::set_defs('tbl', $tbl_defs);
			\ff\id2url::set('tbl', array_column(\ff\dbh::get_all('SELECT `t`.`id`, CONCAT(`d`.`db`, \'.\', `t`.`tbl`) AS `url` FROM `'.FF_DB_NAME.'`.`ff_tbl` `t`, `'.FF_DB_NAME.'`.`ff_db` `d` WHERE `d`.`is_act`=TRUE AND `t`.`is_act`=TRUE AND `t`.`ff_db`=`d`.`id` ORDER BY `t`.`id`'), 1, 0));
			
			sem_release($lock);
		} 
	}
	
	private static function regenerate( ) {
		if(FF_IS_PINBA) {
			$pinba_handler = pinba_timer_start( ['server_name'=>FF_SERVER_NAME, 'fn'=>__METHOD__] );
		}
		
		static::regenerate_db();
		
		\ff\dbh::upd('UPDATE `'.FF_DB_NAME.'`.`ff_tbl` `t` LEFT JOIN `'.FF_DB_NAME.'`.`ff_db` `d` ON (`t`.`is_act`=TRUE AND `d`.`is_act`=TRUE AND `d`.`id`=`t`.`ff_db`) LEFT JOIN `information_schema`.`tables` `i` ON (`i`.`table_schema`=`d`.`db` AND `i`.`table_name`=`t`.`tbl`) SET `t`.`is_act`=FALSE WHERE `t`.`is_act`=TRUE AND (`d`.`id` IS NULL OR `d`.`is_act`=FALSE OR `i`.`table_name` IS NULL)');
		
		\ff\dbh::add('INSERT INTO `'.FF_DB_NAME.'`.`ff_tbl` (`is_act`, `ff_db`, `tbl`) (SELECT TRUE, `d`.`id`, `i`.`table_name` FROM `'.FF_DB_NAME.'`.`ff_db` `d` JOIN `information_schema`.`tables` `i` ON (`d`.`is_act`=TRUE AND `i`.`table_schema`=`d`.`db`) LEFT JOIN `'.FF_DB_NAME.'`.`ff_tbl` `t` ON (`d`.`id`=`t`.`ff_db` AND `i`.`table_name`=`t`.`tbl`) WHERE `t`.`id` IS NULL ORDER BY IF(`i`.`table_schema`=\''.FF_DB_NAME.'\', 0, 1), `i`.`table_schema`, `i`.`table_name`)');

		$tbl_stmt = static::$mysqli->prepare('UPDATE `'.FF_DB_NAME.'`.`ff_tbl` SET `json`=? WHERE `id`=?');
		$tbl_defs = \ff\dbh::get_all('SELECT `t`.`id`, `t`.`tbl`, `t`.`json`, `d`.`db` FROM `'.FF_DB_NAME.'`.`ff_tbl` `t`, `'.FF_DB_NAME.'`.`ff_db` `d` WHERE `d`.`is_act`=TRUE AND `t`.`is_act`=TRUE AND `t`.`ff_db`=`d`.`id` ORDER BY `t`.`id`');
		foreach($tbl_defs AS $key=>$value) {
			$tbl_json = $value['json'];
			$new_json = [];
			if() {
				$tbl_stmt->bind_param('si', $new_json, $value['id']);
				$tbl_stmt->execute();
			}
		}
		
		if(FF_IS_PINBA) {
			pinba_timer_stop( $pinba_handler );
		}
	}
	
	
	private static function regenerate_db( ) {
		$db_alias2db = array_column(\ff\dbh::get_all('SELECT `alias`, `db` FROM `'.FF_DB_NAME.'`.`ff_db`'), 1, 0);
		$db_names = \ff\dbh::db_names();
		foreach ($db_names as $db_alias=>$db_name) {
			if(!isset($db_alias2db[$db_alias])) {
				\ff\dbh::add('INSERT INTO `'.FF_DB_NAME.'`.`ff_db` (`is_act`, `alias`, `db`) VALUES (TRUE, \''.\ff\dbh::esc($db_alias).'\', \''.\ff\dbh::esc($db_name).'\')');
			} else {
				if($db_alias2db[$db_alias] !== $db_name) {
					\ff\dbh::upd('UPDATE `'.FF_DB_NAME.'`.`ff_db` SET `db`=\''.\ff\dbh::esc($db_name).'\' WHERE `alias` = \''.\ff\dbh::esc($db_alias).'\'');
				}
				unset($db_alias2db[$db_alias]);
			}
		}
		if(!empty($db_alias2db)) {
			\ff\dbh::upd('UPDATE `'.FF_DB_NAME.'`.`ff_db` SET `is_act`=FALSE WHERE `is_act`=TRUE AND `alias` IN (\''.implode('\',\'', array_map('\\ff\\dbh::esc', array_keys($db_alias2db))).'\')');
		}
	}
	

//	private static function fn_spec( array $tbl_defs, $prefix = 'fn' ) {
//		$tmp = \ff\dbh::get_all('SELECT `t`.`ff_tbl`, `t`.`'.$prefix.'`, `t`.`id`, `t`.`json`, `t`.`php` FROM `'.FF_DB_NAME.'`.`ff_tbl_'.$prefix.'` `t` ORDER BY `t`.`ff_tbl`, `t`.`name`');
//		
//		foreach ($tmp as $key=>$val) {
//			$tbl_defs[(int)$val[0]][$prefix.'2id'][$val[1]] = (int)$val[2];
//			$tbl_defs[(int)$val[0]]['id2'.$prefix][(int)$val[2]] = $val[1];
//			
//			$tbl_defs[(int)$val[0]][$prefix][(int)$val[2]] = array_replace_recursive(
//				[
//					'id'=>(int)$val[2], 'name'=>$val[1]
//				],
//				\ff\db_fn::config_json($val[3]),
//				\ff\db_fn::config_php($val[4])
//			);
//		}
//		return $tbl_defs;
//	}
	
}

