<?
namespace ff;

class ff_select_query {
	
	public static function def($db_info = NULL) {
		if(!FF_IS_USER_SUPER) {
			return NULL;
		}
		
		return [
			'pk'=>['id'], 'cols' => [], 'ck'=>[],
			'grid'=> ['options'=>['skipView'=>TRUE], 'col'=>[]],
			'get' => []
		];
		
	}
	
	public static function get($arg) {
		$cls = __CLASS__;
		$res_arr = [];
		$cols = [];
		$ck = [];
		$arg['sel'] = [];
		if(FF_IS_USER_SUPER && !empty($arg['sql_txt'])) {
			$res_sql = \ff\dbh::get_res($arg['sql_txt']);
			$num_rows = $res_sql->num_rows;
			$fields = $res_sql->fetch_fields();
			$res_arr = $res_sql->fetch_all(MYSQLI_NUM);
			$res_sql->free();
			$accos = FALSE;
			foreach ($fields as $fld_name => $field) {
				$cols[$field->name] = ['type'=>\ff\db_fn::type_db2str($field->type, $field->length), 'len'=>$field->length];
				
				if(\ff\db_fn::is_db_int($field->type)) {
					for ($i = 0; $i < $num_rows; $i++) { if( isset( $res_arr[$i][ $fld_name ] ) ) $res_arr[$i][ $fld_name ] = (int)$res_arr[$i][ $fld_name ];}
				} else if(\ff\db_fn::is_db_decimal($field->type)) {
					for ($i = 0; $i < $num_rows; $i++) { if( isset( $res_arr[$i][ $fld_name ] ) ) $res_arr[$i][ $fld_name ] = (float)$res_arr[$i][ $fld_name ];}
				}
			}
			$cls_def = static::def();
			$ck = array_keys($cols);
			$arg['sel'] = $ck;
			$cls_def['cols'] = $cols;
			$cls_def['ck'] = $ck;
			$cls_def['grid']['col'] = $cls_def['ck'];
			$out = isset($arg['out']) ? $arg['out'] : ( FF_IS_OUT_JS ? 'js' : ( FF_IS_OUT_JSON ? 'json' : FF_OUTPUT ) );
			if($out === 'js') {
				\ff\tbl_fn::return_result(['out'=>'js', 'fn'=>'fastFace.tbl.load('.json_encode($cls).', '], $cls_def);
			}
		} else if(FF_IS_DEBUG || FF_IS_DEV) {
			throw new \Exception(sprintf('%s: Have no permissions', __METHOD__));
		}
		
		return \ff\tbl_fn::return_result($arg, $res_arr);
	}
	
}


