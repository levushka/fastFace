<?
namespace ff;

class db_sql {
	
	public static function sql(array $arg, $fn) {
		if(empty($arg) || !is_array($arg) || empty($arg['FROM'])) {
			throw new \Exception(sprintf('%s: FROM not exists', $fn));
		}
		
		$from  = \ff\db_from::parse($arg['FROM'], $fn);
		
		if(!isset($arg['SELECT']) || !is_array($arg['SELECT'])) {
			$arg['SELECT'] = [];
		}
		$select  = \ff\db_select::sql($arg['SELECT'], $from);

		if(!isset($arg['WHERE']) || !is_array($arg['WHERE'])) {
			$arg['WHERE'] = [];
		}
		$where  = \ff\db_where::parse($arg['WHERE'], $from);

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
			
			$arg['WHERE'][] = ['='=>[$pk, ['val'=>$row_id]]];
			
		}

		$sql_str = 'SELECT '.( ( $arg['row'] || !empty($row_id) ) ? ' SQL_SMALL_RESULT' : ''/*( $lim_count ? ' SQL_CALC_FOUND_ROWS' : '' )*/ ).
			\ff\db_select::sql($arg['SELECT'], $struct).
			\ff\db_from::sql($struct).
			( !empty($arg['WHERE']) ? ' WHERE '.\ff\db_where::sql($arg['WHERE'], $struct) : '' ).
			( !empty($arg['ORDER BY']) ? \ff\db_select::order_by($arg['ORDER BY'], $struct) : '' ).
			( ( $arg['row'] || $row_id > 0 ) ? ' LIMIT 1' : \ff\db_select::limit(isset($arg['LIMIT']) ? $arg['LIMIT'] : [], $struct)  );
	
	}
	

}