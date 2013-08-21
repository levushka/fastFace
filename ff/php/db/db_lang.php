<?
namespace ff;

class db_lang {
	
	public static function parse(array $stract) {
		return $stract;
	}

	// SELECT
	public static function select($tbl_alias, array $langs = NULL) {
		$res = [];
		foreach(\ff\lang::$langs as $lang_id=>$val) {
			$res[] = (!empty($tbl_alias) ? '`'.$tbl_alias.'`.' : '').'`'.$val.'`';
		}
		return implode(', ', $res);
	}

	// UPDADE
	public static function update_vals($vals, $tbl_alias) {
		$res = [];
		foreach(\ff\lang::$langs as $lang_id=>$lang) {
			$res[] = (!empty($tbl_alias) ? '`'.$tbl_alias.'`.' : '').'`'.$lang.'` = \''.\ff\dbh::esc(!is_array($vals) ? $vals : (isset($vals[$lang]) ? $vals[$lang] : (isset($vals[$lang_id]) ? $vals[$lang_id] : $lang))).'\'';
		}
		return implode(', ', $res);
	}

	
	public static function update($id, $row_id, $col_id, $vals, $tbl = 'ff_lang_char') {
		return \ff\dbh::upd('UPDATE '.\ff\tbl::get_path($tbl).' AS `l` SET
			'.static::update_vals($vals, 'l').'
			WHERE '.(isset($id) ? '`l`.`id`='.(int)$id : '`l`.`row`='.(int)$row_id.' AND `l`.`col`='.(int)$col_id));
	}

	// INSERT
	public static function insert_cols() {
		$res = [];
		foreach(\ff\lang::$langs as $lang_id=>$lang) {
			$res[] = '`'.$lang.'`';
		}
		return implode(', ', $res);
	}

	public static function insert_vals($vals) {
		$res = [];
		foreach($langs as $lang_id=>$lang) {
			$res[] = '\''.\ff\dbh::esc(!is_array($vals) ? $vals : (isset($vals[$lang]) ? $vals[$lang] : (isset($vals[$lang_id]) ? $vals[$lang_id] : $lang))).'\'';
		}
		
		if(empty($res)) {
			throw new \Exception( sprintf( '%s: LANG values not found', __METHOD__ ) );
		}
		return implode(', ', $res);
	}

	public static function insert($row_id, $col_id, $vals, $tbl = 'ff_lang_char') {
		return \ff\dbh::add('INSERT INTO '.\ff\tbl::get_path($tbl).' (`row`, `col`, '.static::insert_cols().') VALUES ('.(int)$row_id.', '.(int)$col_id.', '.static::insert_vals($vals).')');
	}
	
}