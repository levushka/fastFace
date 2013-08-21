<?
namespace ff;

class tbl_del {
	
	public static function del(array $arg) {
		
		if(empty($arg) || !is_array($arg) || empty($arg['FROM'])) {
			throw new \Exception(sprintf('%s: FROM not exists', __METHOD__));
		}
		
		$struct  = \ff\db_from::parse($arg['FROM'], __FUNCTION__);
		if(count($struct) > 1) {
			throw new \Exception( sprintf( '%s: Multiple tables not implemented', __METHOD__ ) );
		}

		if(!isset($arg['WHERE']) || !is_array($arg['WHERE'])) {
			$arg['WHERE'] = [];
		}

		if(!empty($arg['row_id']) && is_array($arg['row_id'])) {
			$row_id   = (int)$arg['row_id'][0];
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

		//$php_fn   = isset($fn['php']) ? $fn['php'] : [];
		//$data = \ff\tbl_fn::run_cls_fn('pre', $php_fn, $arg, [], $row_id, 0);

echo '
			DELETE
				'.\ff\db_from::sql($arg['FROM'], $struct).'
			WHERE '.
				\ff\db_where::sql($arg['WHERE'], $struct)
;
exit;

		$affected_rows = \ff\dbh::del('
			DELETE
				'.\ff\db_from::sql($arg['FROM'], $struct).'
			WHERE '.
				\ff\db_where::sql($arg['WHERE'], $struct)
		);

		if(is_int($affected_rows) && $affected_rows > 0) {
			
			//\ff\tbl_fn::run_cls_fn('post_win', $php_fn, $arg, $data, $row_id, $affected_rows);
			\ff\dbh::hist($pk_id, $row_id, 'DELETED');
//      if(!isset($tbl['no_search'])) {
//        \ff\dbh::search_del($id, $row_id, NULL);
//      }
			
		} else {
			
			//\ff\tbl_fn::run_cls_fn('post_err', $php_fn, $arg, $data, $row_id, 0);
			
		}
		return \ff\tbl_fn::return_result($arg, ['rows'=>(int)$affected_rows]);
	}

}

