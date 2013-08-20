<?
namespace ff;

class gen_arr {
	
	public static function generate( ) {
		$url2id = [];
		$id2url = [];
		$defs = [];
		$tmp_rows = \ff\dbh::get_all('
			SELECT `g`.`id`, `g`.`url`, `g`.`is_int`, `a`.`key`, '.\ff\db_lang::select('la').'
			FROM
				`'.FF_DB_NAME.'`.`ff_arr_grp` `g`
				JOIN `'.FF_DB_NAME.'`.`ff_arr` `a` ON (`a`.`ff_arr_grp` = `g`.`id`)
				JOIN `'.FF_DB_NAME.'`.`ff_lang_char` `la` ON (`la`.`id` = `a`.`name`)
			ORDER BY `g`.`id`, `a`.`ord`
		');
		
		foreach ($tmp_rows as $key=>$val) {
			$id  = $val[0];
			$url = $val[1];
			
			$url2id[$url] = $id;
			$id2url[$id] = $url;
			
			foreach(\ff\lang::$langs as $lang_id=>$lang) {
				$defs[ $id ][$lang][ $val[2] ? (int)$val[3] : $val[3] ] = $val[4+$lang_id];
			}
		}
		
		\ff\id2url::set('arr', $id2url);
		\ff\id2def::set_defs('arr', $defs, TRUE);
	}



	public static function add($url, array $arr) {
		$key = key($arr[FF_LANG]);
		$id = \ff\dbh::add('INSERT INTO `'.FF_DB_NAME.'`.`ff_arr_grp` (`is_int`, `url`) VALUES ('.(int)is_int($key).', \''.\ff\dbh::esc($url).'\')');
		static::upd($id, $arr);
		\ff\id2url::add('arr', $id, $url);
	}

	
	public static function upd($id, array $arr) {
		$def = [];
		if(\ff\id2def::is_def('arr', $id, FF_LANG)) {
			$def = \ff\id2def::get_langs('arr', $id);

			$del = [];
			foreach($def[FF_LANG] as $key=>$val) {
				if(!isset($arr[FF_LANG][$key])) {
					$del[] = $key;
				}
			}
			if(!empty($del)) {
				\ff\dbh::del('
					DELETE FROM
						`'.FF_DB_NAME.'`.`ff_arr`
					WHERE
						`ff_arr_grp` ='.(int)$id.' AND `key` IN (\''.implode('\',\'', array_map('\\ff\\dbh::esc', $del) ).'\') '
				);
				\ff\dbh::del('
					DELETE FROM
						`'.FF_DB_NAME.'`.`ff_lang_char` `l`
					WHERE
						`l`.`col` ='.(int)\ff\arr::$col.' AND `l`.`row` NOT IN (SELECT `a`.`id` FROM `'.FF_DB_NAME.'`.`ff_arr` AS `a` WHERE `a`.`ff_arr_grp` ='.(int)$id.') '
				);
			}
		}
		
		
		$i = 1;
		$insert = [];
		$insert_lang = [];
		foreach($arr[FF_LANG] as $key=>$val) {
			if(!isset($def[FF_LANG][$key])) {
				$insert[] = '('.$id.', '.(int)$i.', \''.\ff\dbh::esc($key).'\', '.(($id*100)+$i).')';
				$tmp = [];
				foreach(\ff\lang::$langs as $lang_id=>$lang) {
					$tmp[] = '\''.\ff\dbh::esc($arr[$lang][$key]).'\'';
				}
				$insert_lang[] = '('.(($id*100)+$i).', '.(int)\ff\arr::$col.', '.implode(', ', $tmp).')';
			}
			$i++;
		}
		
		if(!empty($insert)) {
			\ff\dbh::add('INSERT INTO `'.FF_DB_NAME.'`.`ff_arr` (`ff_arr_grp`, `ord`, `key`, `name`) VALUES '.implode(',', $insert));
			\ff\dbh::add('INSERT INTO `'.FF_DB_NAME.'`.`ff_lang_char` (`row`, `col`, '.\ff\db_lang::insert_cols().') VALUES '.implode(',', $insert_lang));
			
			\ff\dbh::upd('UPDATE `'.FF_DB_NAME.'`.`ff_arr` AS `a`, `'.FF_DB_NAME.'`.`ff_lang_char` AS `l` SET
				`a`.`name`=`l`.`id`
				WHERE `l`.`row`=`a`.`name` AND `l`.`col`='.(int)\ff\arr::$col.' AND `a`.`ff_arr_grp`='.(int)$id);

			\ff\dbh::upd('UPDATE `'.FF_DB_NAME.'`.`ff_lang_char` AS `l`, `'.FF_DB_NAME.'`.`ff_arr` AS `a` SET
				`l`.`row`=`a`.`id`
				WHERE `a`.`name`=`l`.`id` AND `a`.`ff_arr_grp`='.(int)$id.' AND `l`.`col`='.(int)\ff\arr::$col);
		}

		\ff\id2def::set_langs('arr', $id, $arr);
	}


}

