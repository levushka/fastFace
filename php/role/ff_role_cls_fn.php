<?
namespace ff;

class ff_role_cls_fn {
	
	public static function def($db_info = NULL) {
		$cols = FF_IS_USER_SUPER ? [
			'cls_fn' => ['lbl'=>'id'],
			'fn' => [],
			'js_def' => []
		] : [];
			
		return [
			'pk'=>['cls_fn'], 'cols' => $cols, 'ck'=>array_keys($cols),
			'js_def' => FF_IS_USER_SUPER ? [
				'fmt_val' => 'this.data[id][2]+" : "+this.data[id][1]',
				'grp_col' => 'cls',
				'get' => []
			] : NULL,
			'get' => FF_IS_USER_SUPER ? [] : NULL
		];
	}

	public static function get($arg) {
		$res = [];
		$db_info = \ff\tbl::get_fn();
		$req_names = \ff\cls::get();
		foreach ($req_names as $cls => $cls_data) {
			if(!empty($cls) && \ff\cls::is_req_cls($cls) && \ff\cls::is_req_fn($cls, 'role')) {
				$cls_full = \ff\cls::path($cls);
				if(!class_exists($cls_full)) { if(is_file($cls_data['path'])) { require_once($cls_data['path']); } }
				if(class_exists($cls_full)) {
					if(method_exists($cls_full, 'role')) {
						$perm_fn = call_user_func($cls_full.'::perm', isset($db_info[$cls])?$db_info[$cls]:NULL);
						foreach ($perm_fn as $tmp_key => $fn) {
							$res[] = [$cls.':'.$fn, \ff\dict::val($cls).':'.\ff\dict::val($fn), \ff\dict::val($cls).':'.\ff\dict::val($fn)]; //[$cls.':'.$fn, $fn, $cls]; //
						}
					}
				}
			}
		}
		
		usort($res, 'static::cmp');

		return \ff\tbl_fn::return_result($arg, $res);
	}

	private static function cmp($a, $b) {
		if ($a[2] == $b[2]) {
				return 0;
		}
		return ($a[2] < $b[2]) ? -1 : 1;
	}
	
}


