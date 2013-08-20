<?
namespace ff;

class tbl_def {
	
	private static $loaded = ['arr'=>[], 'tbl'=>[]];
	
	public static function is_loaded($type, $id) {
		return in_array($id, static::$loaded[$type], TRUE);
	}

	public static function loaded($type, array $loaded) {
		foreach($loaded as $key=>$id_or_url) {
			$id = is_int($id_or_url) ? $id_or_url : ($type === 'tbl' ? \ff\tbl::get_id($id_or_url): \ff\id2url::url2id( $type, $id_or_url));
			if(!static::is_loaded($type, $id)) {
				static::$loaded[$type][] = $id;
			}
		}
	}

	public static function load(array $def2load) {
		if(!empty($def2load) && is_array($def2load)) {
			foreach($def2load as $key => $id_or_url) {
				static::def($id_or_url);
			}
		}
	}

	public static function def($id_or_url) {
		if(empty($id_or_url) || (!is_int($id_or_url) && !is_string($id_or_url))) {
			throw new \Exception( sprintf( '%s: Must provide TBL URL', __METHOD__ ) );
		}
		
		$id = \ff\tbl::get_id($id_or_url);
		
		if(static::is_loaded('tbl', $id)) {
			return;
		} else {
			static::loaded('tbl', [$id]);
		}
		
		$tbl  = \ff\id2def::get( 'tbl', $id);
		$cols = \ff\tbl::get_cols($id);
		$role_cols = [];
		
		if(empty($tbl['id2fn'])) {
			throw new \Exception( sprintf( '%s: No FN for [%s]', __METHOD__, $id ) );
		}

		$fns  = [];
		foreach ($tbl['id2fn'] as $fn_id => $fn) {
			if(\ff\role::is_tbl_fn($id, $fn_id)) {
				$fns[$fn] = \ff\tbl::get_fn($id, $fn_id);
				$role_cols = array_merge($role_cols, $fns[$fn]['cols']);
			}
		}
		
		if(empty($fns) || empty($role_cols)) {
			throw new \Exception( sprintf( '%s: No permited FN or COLS for [%s]', __METHOD__, $id ) );
		}
		
		foreach ($cols as $key => $val) {
			if(!in_array($key, $role_cols, TRUE)) {
				unset($cols[$key]);
			} else {
				if(isset($val['arr']) && !static::is_loaded('arr', $val['arr'])) {
					static::loaded('arr', [$val['arr']]);
					\ff\tbl_fn::return_result(['callback'=>'fastFace.arr.load('.json_encode($val['arr']).', '], \ff\arr::get($val['arr']));
				} else if(\ff\findKey($val, ['fk', 'sk', 'ik']) !== NULL && !empty($val['tbl']) && !static::is_loaded('tbl', $val['tbl'])) {
					static::def($val['tbl']);
				}
			}
		}
		
//    if( isset($user_tbl['tbl']['cls']) && \ff\cls::is_fn($user_tbl['tbl']['cls'], 'def') ) {
//      $user_tbl = \ff\cls::call($user_tbl['tbl']['cls'], 'def', $user_tbl, $fn);
//    }
//    if(isset($fns['get']['js_def'])) {
//      $js_def = $fns['get']['js_def'];
//      $get_arg = (isset($arg['no_data']) && $arg['no_data']) ? NULL : ( isset($arg['get']) ? $arg['get'] : ( isset($js_def['get']) ? $js_def['get'] : NULL ) );
//      if(isset($get_arg)) {
//        $get_arg['out'] = 'return';
//        if(!array_key_exists('hash_col', $get_arg)) {
//          $get_arg['hash_col'] = array_key_exists('hash_col', $js_def) ? $js_def['hash_col'] : 0;
//        }
//        $get_arg['tbl'] = $id;
//        $fns['data'] = \ff\tbl_get::get($get_arg);
//      }
//    }
	
		return \ff\tbl_fn::return_result(['callback'=>'fastFace.tbl.load('.json_encode($id).', '], ['tbl'=>$tbl, 'cols'=>$cols, 'fns'=>$fns]);
	}

}