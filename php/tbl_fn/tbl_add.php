<?
namespace ff;

class tbl_add {
	
	public static function add(array $arg = []) {
		$err = [];
		$ord_name  = NULL;
		$ord_sub  = [];
		$affected_rows = 0;
		$row_id = 0;
		$new_item = NULL;
		$cls_def  = \ff\tbl::load_and_check($cls, __FUNCTION__);
		$fn_def   = $cls_def[__FUNCTION__];
		if(isset($fn_def)) {
			$php_fn   = isset($fn_def['php']) ? $fn_def['php'] : [];
			$db_name      = $cls_def['tbl']['db'];
			$tbl_name      = $cls_def['tbl']['name'];
			$db_search = !isset($cls_def['no_search']);
			$rk = $cls_def['rk'];
			$pk       = $cls_def['pk'][0];
			$cols = $cls_def['cols'];;
			$fn_col   = $fn_def['cols'];
			$data     = array_merge(!empty($fn_def['dflt']) ? $fn_def['dflt'] : [], (isset($arg['data']) && is_array($arg['data'])) ? $arg['data'] : [], !empty($fn_def['prdef']) ? $fn_def['prdef'] : []);
			$insert_key = [];
			$insert_val = [];
			
			$data = \ff\tbl_fn::run_cls_fn($cls, 'pre', $php_fn, $arg, $data, $row_id, 0);

			if(!empty($cls_def['ord']) && !isset($data[$cls_def['ord'][0]])) {
				$ord_name = $cls_def['ord'][0];
				$ord_sub = \ff\tbl_fn::get_ord_sub_by_data($cls, $data, $cols[$ord_name]['ord']);
				$data[$ord_name] = \ff\tbl_fn::max_ord($tbl_name, $ord_name, $ord_sub);
			}

			foreach ($data as $key=>$val) {
				if(in_array($key, $rk, TRUE)) continue;
				if(!in_array($key, $fn_col, TRUE)) {
					$err_msg = sprintf('%s: Col[%s] not permitted for [%s]', __METHOD__, $key, $cls);
					if(FF_IS_DEBUG || FF_IS_DEV) {
						throw new \Exception($err_msg);
					}
					$err[] = ['message'=>$err_msg];
					continue;
				}
				if(!isset($cols[$key])) {
					$err_msg = sprintf('%s: Col[%s] not exists for [%s]', __METHOD__, $key, $cls);
					if(FF_IS_DEBUG || FF_IS_DEV) {
						throw new \Exception($err_msg);
					}
					$err[] = ['message'=>$err_msg];
					continue;
				}
				if(isset($val)) {
					$col_type = $cols[$key]['type'];
//          if($cols[$key]['type'] == 'char' && !isset($cols[$key]['code'])) {
//            $val = htmlspecialchars($val, ENT_QUOTES, 'UTF-8', FALSE);
					if($col_type === 'int' || $col_type === 'bool') {
						$val = (int)($val);
					} else if($col_type === 'decimal') {
						$val = (float)($val);
					} else if(is_array($val)) {
						$val = implode(',', $val);
					}
				} else if(isset($cols[$key]['def'])) {
					$val = $cols[$key]['def'];
				} else {
					continue;
				}
				
				if(empty($ord_name) && isset($cols[$key]['ord'])) {
					$ord_name = $key;
					$ord_sub = \ff\tbl_fn::get_ord_sub_by_data($cls, $data, $cols[$ord_name]['ord']);
					\ff\tbl_fn::pre_ord($tbl_name, $key, $val, $ord_sub);
				}

				$insert_key[] = '`'.\ff\dbh::esc($key).'`';
				$insert_val[] = '\''.\ff\dbh::esc($val).'\'';
			}
			
			if(!empty($insert_key) && !empty($insert_val)) {
				$row_id = \ff\dbh::add('INSERT INTO `'.$db_name.'`.`'.$tbl_name.'` ('.implode(', ', $insert_key).') VALUES ('.implode(', ', $insert_val).')');
				if(is_int($row_id)) {
					
					\ff\tbl_fn::post_ord($db_name, $tbl_name, $ord_name, $ord_sub);
					
					$affected_rows = 1;
					\ff\tbl_fn::run_cls_fn($cls, 'post_win', $php_fn, $arg, $data, $row_id, $affected_rows);
				} else {
					$affected_rows = 0;
					\ff\tbl_fn::run_cls_fn($cls, 'post_err', $php_fn, $arg, $data, $row_id, $affected_rows);
				}
				
				if(!empty($pk) && !empty($row_id)) {
					\ff\dbh::hist($cls, $row_id, $pk, 'CREATED');
					if($db_search && !isset($cols[$pk]['no_search'])) {
						\ff\dbh::search($cls, $row_id, $pk, strval($row_id), 1);
					}
					
					foreach ($data as $key => $val) {
						if(in_array($key, $rk, TRUE)) continue;
						if(in_array($key, $fn_col, TRUE) && isset($val)) {
							$col_type = $cols[$key]['type'];
		//          if($cols[$key]['type'] == 'char' && !isset($cols[$key]['code'])) {
		//            $val = htmlspecialchars($val, ENT_QUOTES, 'UTF-8', FALSE);
							if($col_type === 'int' || $col_type === 'bool') {
								$val = (int)($val);
							} else if($col_type === 'decimal') {
								$val = (float)($val);
							} else if(is_array($val)) {
								$val = implode(',', $val);
							}
							if(empty($cols[$key]['no_hist'])) {
								\ff\dbh::hist($cls, $row_id, $key, $val);
							}
							if(!isset($cls_def['no_search']) && !isset($cols[$key]['no_search']) && in_array($col_type, ['char', 'text'], TRUE)) {  // , 'html'
								\ff\dbh::search($cls, $row_id, $key, $val, 3);  // $col_type === 'html' ? strip_tags($val) : $val
							}
						}
					}
					unset($arg['data']);
					$new_item = \ff\cls::run([$cls, 'get', array_merge($arg, ['row_id'=>[$row_id], 'fn'=>'get', 'out'=>'return'])]);
				} else {
					$new_item = $data;
				}
			} else {
				$err_msg = sprintf('%s: Nothing to add for [%s]', __METHOD__, $cls);
				if(FF_IS_DEBUG || FF_IS_DEV) {
					throw new \Exception($err_msg);
				}
				$err[] = ['message'=>$err_msg];
			}
		}
		
		return \ff\tbl_fn::return_result($arg, ['rows'=>$affected_rows, 'row_id'=>$row_id, 'new_item'=>$new_item]);
	}

}

