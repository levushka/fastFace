<?
namespace ff;

class db_from {
	
	private static $join = ['JOIN', 'LEFT JOIN', 'RIGHT JOIN'];
	
	public static function parse(array $from, $fn) {
		$res = [];
		$role_cols = [];
		$col2url = [];
		
		foreach ($from as $key => $val) {
			$table = NULL;
			$join = 'JOIN';
			if(is_int($val) || is_string($val)) {
				$table = $val;
			} else if(is_array($val)) {
				$join = \ff\findKey($val, static::$join);
				if(empty($join)) {
					throw new \Exception( sprintf( '%s: JOIN not found', __METHOD__ ) );
				}
				$table = $val[$join];
			}
			
			if(empty($table)) {
				throw new \Exception( sprintf( '%s: TABLE not found', __METHOD__ ) );
			}
			
			$tbl_id = \ff\tbl::get_id($table);
			
			if(!\ff\role::is_tbl($tbl_id)) {
				throw new \Exception( sprintf( '%s: TABLE [%s] not permited', __METHOD__, $tbl_id ) );
			} else {
				$res['as'][$key] = (is_array($val) && !empty($val['AS']) && is_string($val['AS']) && preg_match('/^[A-Za-z0-9]+$/', $val['AS'])) ? $val['AS'] : 'a'.$key;
				$res['join'][$key] = $join;
				$res['on'][$key] = isset($val['WHERE']) ? $val['WHERE'] : NULL;
				$res['tbl'][$key] = \ff\id2def::get('tbl', $tbl_id);
				$res['aliases'][$res['as'][$key]] = $res['tbl'][$key]['url'];
				$res['fn'][$key]  = \ff\tbl::get_fn($tbl_id, $fn);
				$role_cols = array_merge($role_cols, $res['fn'][$key]['cols']);
				$res['cols'][$key] = \ff\tbl::get_cols($tbl_id);
				$res['keys'][$key] = \ff\tbl::get_keys($tbl_id);
				foreach($res['keys'][$key]['id2col'] as $col_id=>$col_name) {
					$col2url[$col_id] = $res['tbl'][$key]['url'].'.'.$col_name;
				}
			}
		}
		
		if(empty($res)) {
			throw new \Exception( sprintf( '%s: TABLE not found in FROM argument', __METHOD__ ) );
		}
		$res['role_cols'] = array_unique($role_cols);
		$res['col2url'] = $col2url;
		
		return $res;
	}
	

	
	public static function sql(array $struct) {
		$res = [];
		foreach ($struct['join'] as $key => $val) {
			$res[$key] = ($key !== 0 ? $struct['join'][$key].' ' : '').$struct['tbl'][$key]['path'].' AS `'.$struct['as'][$key].'`';
			if($key !== 0 && isset($struct['on'][$key])) {
				$res[$key] .= ' ON ('.\ff\db_where::sql($struct['on'][$key], $struct, FALSE).')';
			}
		}
		if(empty($res)) {
			throw new \Exception( sprintf( '%s: Empty FROM result', __METHOD__ ) );
		}
		return ' FROM '.implode(' ', $res);
	}

}