<?
namespace ff;

class role_gen {

	public static function generate( ) {
		set_time_limit(900);
		
		$perm_arr = \ff\dbh::get_all('
			SELECT
				`p`.`ff_role`, `p`.`ff_cls`, `p`.`ff_cls_fn`, `p`.`ff_cls_spec`
			FROM
				`'.FF_DB_NAME.'`.`ff_role_ff_cls` `p`
			ORDER BY
				`p`.`ff_role`, `p`.`ff_cls`
		');
		
		$perm = [];
		foreach ($perm_arr as $key=>$val) {
			$perm[(int)$val[0]][(int)$val[1]] = ['fn'=>empty($val[2]) ? [] : array_map('intval', explode(',', $val[2])), 'spec'=>empty($val[3]) ? [] : array_map('intval', explode(',', $val[3]))];
		}
		
		foreach ($perm as $role_id=>$perm_obj) {
			foreach ($perm_obj as $cls_id=>$cls_perm) {
				\ff\lcache::set( 'role/'.$role_id.'/cls/'.$cls_id, $cls_perm );
			}
		}
		
		$perm_arr = \ff\dbh::get_all('
		 SELECT
				`p`.`ff_role`, `p`.`ff_tbl`, `p`.`ff_tbl_fn`, `p`.`ff_tbl_spec`, `p`.`ff_tbl_col`
			FROM
				`'.FF_DB_NAME.'`.`ff_role_ff_tbl` `p`
			ORDER BY
				`p`.`ff_role`, `p`.`ff_tbl`, `p`.`ff_tbl_fn`, `p`.`ff_tbl_spec`
		');
		
		$perm = [];
		foreach ($perm_arr as $key=>$val) {
			$perm[(int)$val[0]][(int)$val[1]][(int)$val[2]] = ['spec' => (int)$val[3], 'cols' => empty($val[4]) ? [] : array_map('intval', explode(',', $val[4]))];
		}
		
		foreach ($perm as $role_id=>$perm_obj) {
			foreach ($perm_obj as $tbl_id=>$tbl_perm) {
				\ff\lcache::set( 'role/'.$role_id.'/tbl/'.$tbl_id, $tbl_perm );
			}
		}

		\ff\lcache::set( 'role/list', [FF_VER] );
	}

}
