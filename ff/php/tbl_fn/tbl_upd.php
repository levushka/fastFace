<?
namespace ff;

class tbl_upd {
	
	public static function upd(array $arg = []) {
		if(empty($arg) || !is_array($arg) || (empty($arg['FROM']) && empty($arg['tbl']))) {
			throw new \Exception(sprintf('%s: FROM or TBL not exists', __METHOD__));
		}
		
		$struct  = \ff\db_from::parse(empty($arg['FROM']) ? [$arg['tbl']] : $arg['FROM'], __FUNCTION__);
		if(count($struct['tbl']) > 1) {
			throw new \Exception( sprintf( '%s: Multiple tables not implemented', __METHOD__ ) );
		}
		if(!isset($struct['tbl'][0]['id'])) {
			throw new \Exception( sprintf( '%s: No TBL found', __METHOD__ ) );
		}
		$tbl_id = $struct['tbl'][0]['id'];
		
		if(!isset($arg['WHERE']) || !is_array($arg['WHERE'])) {
			$arg['WHERE'] = [];
		}

		if(!empty($arg['row_id']) && is_array($arg['row_id'])) {
			$row_id = (int)$arg['row_id'][0];
			if(empty($row_id)) {
				throw new \Exception( sprintf( '%s: Must provide int row_id in arr', __METHOD__ ) );
			}
			
			$pk_id    = $struct['keys'][0]['pk'][0];
			$pk       = $struct['cols'][0][$pk_id]['name'];
			if(empty($pk)) {
				throw new \Exception( sprintf( '%s: PK not exists', __METHOD__ ) );
			}
			
			$arg['WHERE'][] = ['='=>[$pk, ['val'=>$row_id]]];
		} else {
			throw new \Exception( sprintf( '%s: Delete without row_id disabled', __METHOD__ ) );
		}

		if(isset($struct['fn'][0]['WHERE'])) {
			$arg['WHERE'][] = $struct['fn'][0]['WHERE'];
		} else if(isset($struct['fn'][0]['spec']) && $struct['fn'][0]['spec'] !== 'all') {
				// TODO: deal with spec
		}

		$err = [];
		$ord_name  = NULL;
		$ord_sub  = [];
		$affected_rows = 0;
		if(isset($struct['fn'][0])) {
//      $php_fn   = isset($fn_def['php']) ? $fn_def['php'] : [];
//      $db_search = !isset($tbl_def['no_search']);
			$data     = (isset($arg['data']) && is_array($arg['data'])) ? $arg['data']: [];
			$update   = [];

//      $data = \ff\tbl_fn::run_cls_fn($tbl_id, 'pre', $php_fn, $arg, $data, $row_id, 0);

//      $where = \ff\db_fn::w_parse($tbl, $cols, $id_or_urls, $fn_def, TRUE);
			
			foreach ($data as $id_or_url => $val) {
				$col = \ff\db_col::parse($id_or_url, $struct);
				if(!isset($struct['cols'][0][$col['id']])) {
					throw new \Exception(sprintf('%s: Col[%s] not exists. [%s]', __METHOD__, $id_or_url, $tbl_id));
				}
				if(!in_array($col['id'], $struct['fn'][0]['cols'], TRUE)) {
					throw new \Exception(sprintf('%s: Col[%s] not permitted. [%s]', __METHOD__, $id_or_url, $tbl_id));
				}
				if(isset($val)) {
					$col_type = $struct['cols'][0][$col['id']]['type'];
	//        if($cols[$id_or_url]['type'] == 'char' && !isset($cols[$id_or_url]['code'])) {
	//          $val = htmlspecialchars($val, ENT_QUOTES, 'UTF-8', FALSE);
					if($col_type === 'int' || $col_type === 'bool') {
						$val = (int)($val);
					} else if($col_type === 'decimal') {
						$val = (float)($val);
					} else if(is_array($val)) {
						$val = implode(',', $val);
					}
					$update[] = $col['path'].'=\''.\ff\dbh::esc($val).'\'';
					
//          if(empty($cols[$id_or_url]['no_hist'])) {
//            \ff\dbh::hist($tbl_id, $row_id, $id_or_url, $val);
//          }
//
//          if($db_search && empty($cols[$id_or_url]['no_search']) && in_array($col_type, ['char', 'text'], TRUE)) { // , 'html'
//            \ff\dbh::search($tbl_id, $row_id, $id_or_url, $val, 3);  // $col_type === 'html' ? strip_tags($val) : $val
//          }
//
//          if(isset($cols[$id_or_url]['ord'])) {
//            $ord_name = $id_or_url;
//            $ord_sub = \ff\tbl_fn::get_ord_sub_by_id($tbl_name, $pk, $row_id, $cols[$id_or_url]['ord']);
//            \ff\tbl_fn::pre_ord($tbl_name, $id_or_url, $val, $ord_sub);
//          }
				}
			}
			
			$affected_rows = 0;
			if(!empty($update)) {
				$affected_rows = \ff\dbh::upd('UPDATE '.$struct['tbl'][0]['path'].' AS `'.$struct['as'][0].'` SET '.implode(', ', $update).' WHERE '.\ff\db_where::sql($arg['WHERE'], $struct));
				if(is_int($affected_rows)) {
					
//          \ff\tbl_fn::post_ord($db_name, $tbl_name, $ord_name, $ord_sub);
//
//          \ff\tbl_fn::run_cls_fn($tbl_id, 'post_win', $php_fn, $arg, $data, $row_id, $affected_rows);
					
				} else {
					
					$affected_rows = 0;
//          \ff\tbl_fn::run_cls_fn($tbl_id, 'post_err', $php_fn, $arg, $data, $row_id, $affected_rows);
//          $old_item = \ff\cls::run([$tbl_id, 'get', array_merge($arg, ['row_id'=>[$row_id], 'fn'=>'get', 'out'=>'return'])]);
					
				}
			} else {
				throw new \Exception(sprintf('%s: Nothing to update for [%s]', __METHOD__, $tbl_id));
			}
		}
		
		return \ff\tbl_fn::return_result($arg, ['rows'=>$affected_rows]);
	}
	
}

