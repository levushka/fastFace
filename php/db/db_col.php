<?
namespace ff;

class db_col {
	
	public static function sql($col, array $struct, $check4role = TRUE) {
		$res = static::parse($col, $struct, $check4role);
		return $res['path'];
	}

	public static function id($col, array $struct, $check4role = TRUE) {
		$res = static::parse($col, $struct, $check4role);
		return $res['id'];
	}

	public static function ids_to_urls(array $ids, array $struct, $check4role = TRUE) {
		$res = [];
		foreach($ids as $key=>$col_id) {
			if(empty($col_id) || !isset($struct['col2url'][$col_id])) {
				throw new \Exception( sprintf( '%s: COL [%s] not found', __METHOD__, $col_id ) );
			}
			$res[] = $struct['col2url'][$col_id];
		}
		return $res;
	}

	public static function parse($col, array $struct, $check4role = TRUE) {
		if(empty($col) || (!is_string($col) && !is_int($col))) {
			throw new \Exception( sprintf( '%s: Wrong COL format', __METHOD__ ) );
		}
		
		if(is_int($col) || is_numeric($col)) {
			if(isset($struct['col2url'][(int)$col])) {
				$col = $struct['col2url'][(int)$col];
			} else {
				throw new \Exception( sprintf( '%s: COL [%s] not found', __METHOD__, $col ) );
			}
		}
		
		$col_id = NULL;
		$col_arr = explode('.', $col);
		$size = count($col_arr);
		
		$db_name = dbh::db_name($size === 3 ? $col_arr[0] : FF_DB_NAME);
		$tbl_name = $size === 3 ? $col_arr[1] : ($size === 2 ? $col_arr[0] : $struct['as'][0]);
		$col_name = $size === 3 ? $col_arr[2] : ($size === 2 ? $col_arr[1] : $col_arr[0]);
		
		$tbl_alias = NULL;
		if(isset($struct['aliases'][$tbl_name])) {
			$tbl_alias = $tbl_name;
			$tmp = explode('.', $struct['aliases'][$tbl_name]);
			$db_name = $tmp[0];
			$tbl_name = $tmp[1];
		}
		
		foreach($struct['tbl'] as $key=>$val) {
			if($val['db'] === $db_name && $val['name'] === $tbl_name) {
				$col_id = \ff\getVal4Key($struct['keys'][$key]['col2id'], $col_name);
				
				if(empty($col_id) || !isset($struct['cols'][$key][$col_id])) {
					throw new \Exception( sprintf( '%s: COL [%s] not found in [%s.%s] [%s]', __METHOD__, $col_name, $db_name, $tbl_name, $col_id ) );
				}
				
				if($check4role && !in_array($col_id, $struct['role_cols'], TRUE)) {
					throw new \Exception(sprintf('%s: COL [%s] not permitted', __METHOD__, $col_id));
				}
				return ['id'=>$col_id, 'path'=>'`'.(!empty($tbl_alias) ? $tbl_alias : $struct['as'][$key]).'`.`'.$col_name.'`'];
			}
		}

		throw new \Exception( sprintf( '%s: COL not found for [%s]', __METHOD__, $col ) );
	}
	
}