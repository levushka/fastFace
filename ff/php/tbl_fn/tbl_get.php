<?
namespace ff;

class tbl_get {
	
	public static function get(array $arg) {

		if(empty($arg) || !is_array($arg) || (empty($arg['FROM']) && empty($arg['tbl']))) {
			throw new \Exception(sprintf('%s: FROM or TBL not exists', __METHOD__));
		}
		
		$struct  = \ff\db_from::parse(empty($arg['FROM']) ? [$arg['tbl']] : $arg['FROM'], __FUNCTION__);

		if(!isset($arg['WHERE']) || !is_array($arg['WHERE'])) {
			$arg['WHERE'] = [];
		}
		foreach($struct['fn'] as $key=>$val) {
			if(isset($val['WHERE'])) {
				$arg['WHERE'][] = \ff\db_where::sql($val['WHERE'], $struct);
			} else if(isset($val['spec']) && $val['spec'] !== 'all') {
				// TODO: deal with spec
			}
		}
		
		$row_id    = (int)((!empty($arg['row_id']) && is_array($arg['row_id'])) ? $arg['row_id'][0] : 0);
		if($row_id > 0) {
			
			// WHERE ID
			$pk_id    = $struct['keys'][0]['pk'][0];
			$pk       = $struct['cols'][0][$pk_id]['name'];
			if(empty($pk)) {
				throw new \Exception( sprintf( '%s: PK not exists', __METHOD__ ) );
			}
			
			$arg['WHERE'][] = ['='=>[$pk, $row_id]];
			
		} else {
			
//      // WHERE SEARCH
//      if(!isset($user_tbl['no_search']) && !empty($arg['search']) && (is_int($arg['search']) || mb_strlen($arg['search'], 'UTF-8') >= 3) ) {
//        $from .= ', `search`, `search_word`';
//        $where = (!empty($where)?' AND ':'') . '`search`.`cls`=\''.\ff\dbh::esc($url).'\' AND `search`.`word`=`search_word`.`id` AND `search_word`.`word` LIKE \''.( !empty($arg['search_full']) ? '%' : '' ).\ff\dbh::esc($arg['search']).'%\' AND `search`.`row` = '.$path.'.`'.$pk.'` ';
//      }

		}
		
		//$arg['SELECT'] = !empty($arg['SELECT']) ? $arg['SELECT'] : $role_cols;
		$arg['inline'] = !empty($arg['inline']) ? $arg['inline'] : FALSE;
		$arg['assoc'] = isset($arg['assoc']) ? (bool)$arg['assoc'] : FALSE;
		$arg['sk'] = isset($arg['sk']) ? (bool)$arg['sk'] : FALSE;
		$arg['row'] = isset($arg['row']) ? (bool)$arg['row'] : FALSE;
		$hash_col = !empty($arg['hash_col']) ? $arg['hash_col'] : NULL;
		
		if(empty($arg['SELECT'])) {
			$arg['SELECT'] = $struct['fn'][0]['cols'];
		}

		$sql_str = 'SELECT '.( ( $arg['row'] || !empty($row_id) ) ? ' SQL_SMALL_RESULT' : ''/*( $lim_count ? ' SQL_CALC_FOUND_ROWS' : '' )*/ ).
			\ff\db_select::sql($arg['SELECT'], $struct).
			\ff\db_from::sql($struct).
			( !empty($arg['WHERE']) ? ' WHERE '.\ff\db_where::sql($arg['WHERE'], $struct) : '' ).
			( !empty($arg['ORDER BY']) ? \ff\db_select::order_by($arg['ORDER BY'], $struct) : '' ).
			( ( $arg['row'] || $row_id > 0 ) ? ' LIMIT 1' : \ff\db_select::limit(isset($arg['LIMIT']) ? $arg['LIMIT'] : [], $struct)  );
		
// SELECT SQL_CALC_FOUND_ROWS xxxxx LIMIT zzzz; SELECT FOUND_ROWS(); - For a SELECT with a LIMIT clause, the number of rows that would be returned were there no LIMIT clause, better then COUNT(*)
// mysqli::multi_query - Executes one or multiple queries which are concatenated by a semicolon.

		if( $arg['row'] ) {
			$res = \ff\dbh::get_row($sql_str, (!empty($arg['assoc']) && (bool)$arg['assoc']) ? MYSQLI_ASSOC : MYSQLI_NUM, $hash_col);
		} else {
			$res = \ff\dbh::get_all($sql_str, (!empty($arg['assoc']) && (bool)$arg['assoc']) ? MYSQLI_ASSOC : MYSQLI_NUM, $hash_col);
		}
		
//    if( $arg['row'] || $arg['sk'] ) {
//      $sk = isset($keys['sk']) ? $keys['sk'] : NULL;;
//      if($arg['sk'] && !empty($res) && is_array($sk)) {
//        foreach ($sk as $tmp_key => $sub) {
//          if(!empty($cols[$sub]['frm']['skip'])) {
//            continue;
//          }
//          $sub_id = array_search($sub, $arg['sel'], TRUE);
//          $sub_def = $cols[$sub]['sk'];
//          $sub_cls_def = \ff\tbl::get_fn($sub_def['cls'], __FUNCTION__);
//          if(!empty($sub_cls_def) && isset($sub_cls_def['get'])) {
//            $new_sel = !empty($cols[$sub]['frm']['sub_frm']) ? (
//              isset($sub_cls_def['frm']['col']) ? (
//                $sub_cls_def['frm']['col']
//              ) : (
//                isset($sub_cls_def['get']['col']) ? $sub_cls_def['get']['col'] : NULL
//              )
//            ) : (
//              isset($sub_cls_def['get']['col']) ? $sub_cls_def['get']['col'] : NULL
//            );
//            if($arg['row']) {
//              $fltr = [];
//              foreach ($sub_def['sk'] as $res_col=>$sub_col) {
//                $fltr[] = ['=', $sub_col, $arg['assoc'] ? $res[ $res_col ] : $res[ array_search($res_col, $arg['sel'], TRUE) ]];
//              }
//              $res[ $arg['assoc'] ? $sub : $sub_id ] = \ff\cls::run([$sub_def['cls'], 'get', ['cls'=>$sub_def['cls'], 'sk'=>TRUE, 'sel'=>$new_sel, 'lim'=>[0,50], 'WHERE'=>[$fltr], 'out'=>'return']]);
//            } else {
//              foreach ($res as $col_name=>$val) {
//                $fltr = [];
//                foreach ($sub_def['sk'] as $res_col=>$sub_col) {
//                  $fltr[] = ['=', $sub_col, $arg['assoc'] ? $res[$col_name][ $res_col ] : $res[$col_name][ array_search($res_col, $arg['sel'], TRUE) ]];
//                }
//                $res[$col_name][ $arg['assoc'] ? $sub : $sub_id ] = \ff\cls::run([$sub_def['cls'], 'get', ['cls'=>$sub_def['cls'], 'sk'=>TRUE, 'sel'=>$new_sel, 'lim'=>[0,50], 'WHERE'=>[$fltr], 'out'=>'return']]);
//              }
//            }
//          } else {
//            if($arg['row']) {
//              $res[ $arg['assoc'] ? $sub : $sub_id ] = \ff\tbl_fn::return_result($sub_def['cls'], 'get', ['cls'=>$sub_def['cls'], 'sk'=>TRUE, 'sel'=>[], 'out'=>'return'], [], []);
//            } else {
//              foreach ($res as $col_name=>$val) {
//                $res[$col_name][ $arg['assoc'] ? $sub : $sub_id ] = \ff\tbl_fn::return_result($sub_def['cls'], 'get', ['cls'=>$sub_def['cls'], 'sk'=>TRUE, 'sel'=>[], 'out'=>'return'], [], []);
//              }
//            }
//          }
//        }
//      }
//    }
		
		return \ff\tbl_fn::return_result($arg, ['data'=>$res, 'arg'=>$arg]);
	}

	
}

