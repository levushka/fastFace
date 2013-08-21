<?
namespace ff;

class ff_online_sess {
	
	public static function perm() {
		return ['any_cmd', 'view_only'];
	}

	public static function def($db_info = NULL) {
		$prm = \ff\login::get_prm(__CLASS__);
		if(!\ff\login::check_prm(__CLASS__, $prm)) {
			return NULL;
		}
		
		$cols = [
			'phpsess' => ['type'=>'char'],
			'token' => ['type'=>'char'],
			'sess' => ['type'=>'enum', 'fk'=>['cls'=>'ff_sess']],
			'type' => ['type'=>'enum', 'fk'=>['cls'=>'ff_user_type']],
			'grp' => ['type'=>'enum', 'fk'=>['cls'=>'ff_user_grp']],
			'role' => ['type'=>'enum', 'fk'=>['cls'=>'ff_role']],
			'id' => ['type'=>'int', 'lbl'=>'user', 'grid'=>['skip'=>TRUE]],
			'login' => ['type'=>'char'],
			'name' => ['type'=>'char'],
			'lang' => ['type'=>'char'],
			'ip_addr' => ['type'=>'char'],
			'php_self' => ['type'=>'char', 'lbl'=>'url', 'grid'=>['skip'=>TRUE]],
			'start_at' => ['type'=>'datetime'],
			'login_on' => ['type'=>'datetime', 'grid'=>['skip'=>TRUE]],
			'request_at' => ['type'=>'datetime']
		];
		
		return [
			'no_search'=>TRUE, 'pk'=>['phpsess', 'token'], 'cols' => $cols, 'ck'=>array_keys($cols),
			'grid'=> ['col'=>array_keys($cols)],
			'get' => [],
			'del' => $prm['any_cmd'] ? [] : NULL,
			'fnd' => ['WHERE' => [[['off','type',['val'=>1]], ['off','grp',['val'=>1]]]]]
		];
	}
	
	public static function del($arg) {
		$prm = \ff\login::get_prm(__CLASS__);
		if(!$prm['any_cmd']) {
			throw new \Exception(sprintf('%s: Have no permissions', __METHOD__));
		}
		
		$affected_rows = 0;
		if(!empty($arg['phpsess'])) {
			if(FF_IS_WIN && $arg['phpsess'] == session_id()) {
				if(FF_IS_OUT_JS) {
					echo 'fastFace.msg.err("You cannot remove your own session on windows!");'.PHP_EOL;
				}
			} else if(ctype_alnum($arg['phpsess'])) {
				$file_path = FF_DIR_SESSION.'/sess_'.$arg['phpsess'];
				if(is_file($file_path)) {
					$affected_rows = unlink($file_path) === TRUE ? 1 : 0;
				}
			}
		}
		if(FF_IS_OUT_JS) {
			echo 'fastFace.pid.done(\''.(!empty($arg['pid'])?$arg['pid']:0).'\', '.json_encode(['rows'=>$affected_rows]).');'.PHP_EOL;
		}
	}
	
	public static function del_all($arg) {
		$prm = \ff\login::get_prm(__CLASS__);
		if(!$prm['any_cmd']) {
			throw new \Exception(sprintf('%s: Have no permissions', __METHOD__));
		}
		
		$affected_rows = 0;
		$my_phpsessfile = 'sess_'.session_id();
		$handle = opendir(FF_DIR_SESSION);
		while (FALSE !== ($file = readdir($handle))) {
			try {
				if( strpos($file, 'sess_') === 0 && ( !FF_IS_WIN || (FF_IS_WIN && $file != $my_phpsessfile) ) ) {
					if(unlink(FF_DIR_SESSION.'/'.$file)) $affected_rows++;
				}
			} catch (\Exception $er) {}
		}
		closedir($handle);
		
		if(FF_IS_OUT_JS) echo 'fastFace.msg.info("Deleted ['.$affected_rows.'] files");'.PHP_EOL;
	}

	public static function get($arg) {
		$res = [];
		$prm = \ff\login::get_prm(__CLASS__);
		if($prm['any_cmd'] || $prm['view_only']) {
			$cls_def  = static::def(NULL);
			$arg['sel'] = (isset($arg['sel']) && is_array($arg['sel'])) ? $arg['sel'] : $cls_def['ck'];
			$arg['assoc'] = isset($arg['assoc']) ? (bool)$arg['assoc'] : FALSE;
			$arg['row'] = isset($arg['row']) ? (bool)$arg['row'] : FALSE;
			$fltr_arr  = (isset($arg['WHERE']) && is_array($arg['WHERE'])) ? $arg['WHERE'] : NULL;
			$my_phpsessfile = 'sess_'.session_id();
			$found = FALSE;
			$handle = opendir(FF_DIR_SESSION);
			while (FALSE !== ($file = readdir($handle))) {
				if (strlen($file) > 20 && strpos($file, 'sess_') === 0 && is_readable(FF_DIR_SESSION.'/'.$file)) {
					$filedata = stat(FF_DIR_SESSION.'/'.$file);
					 $file_phpsessid = substr($file, 5);
					 if(FF_IS_WIN && $my_phpsessfile === $file) {
						$sess_arr = $_SESSION;
					 } else {
						$sess_arr = static::unserialize_session(file_get_contents(FF_DIR_SESSION.'/'.$file));
					 }
					
					if(isset($sess_arr['tokens'])) {
						foreach ($sess_arr['tokens'] as $tmp_key => $token) {
							if(isset($sess_arr[$token]['user'])) {
								$user_arr = $sess_arr[$token]['user'];
								if($fltr_arr === NULL || \ff\db_fn::fltr_check($user_arr, $fltr_arr)) {
									if($arg['row']) {
										$found = TRUE;
										$res = [];
										foreach ($arg['sel'] as $tmp_key => $key) {
											$res[] = !empty($user_arr[$key]) ? $user_arr[$key] : ( isset($sess_arr[$key]) ? $sess_arr[$key] : '' );
										}
										break;
									} else {
										$tmp_arr = [];
										foreach ($arg['sel'] as $tmp_key => $key) {
											$tmp_arr[] = !empty($user_arr[$key]) ? $user_arr[$key] : ( isset($sess_arr[$key]) ? $sess_arr[$key] : '' );
										}
										$res[] = $tmp_arr;
									}
								}
							} else if($fltr_arr === NULL) {
								$tmp_arr = [];
								foreach ($arg['sel'] as $tmp_key => $key) {
									$tmp_arr[] = isset($sess_arr[$key]) ? $sess_arr[$key] : '' ;
								}
								$res[] = $tmp_arr;
							}
						}
					} else if($fltr_arr === NULL) {
						$tmp_arr = [];
						foreach ($arg['sel'] as $tmp_key => $key) {
							$tmp_arr[] = isset($sess_arr[$key]) ? $sess_arr[$key] : '' ;
						}
						$res[] = $tmp_arr;
					}
				}
				if($found) { break; }
			}
			closedir($handle);
		} else if(FF_IS_DEBUG || FF_IS_DEV) {
			throw new \Exception(sprintf('%s: Have no permissions', __METHOD__));
		}
		
		return \ff\tbl_fn::return_result($arg, $res);
	}
	
	
	private static function unserialize_session($data) {
		if(empty($data) || !is_string($data)) { return []; }
		
		// match all the session keys and offsets
		preg_match_all('/(^|;|\})([a-zA-Z0-9_]+)\|/i', $data, $matchesarray, PREG_OFFSET_CAPTURE);

		$returnArray = [];

		$lastOffset = NULL;
		$currentKey = '';
		foreach ( $matchesarray[2] as $tmp_key => $value ) {
			$offset = $value[1];
			if( !is_NULL( $lastOffset ) ) {
					$valueText = substr($data, $lastOffset, $offset - $lastOffset );
					$returnArray[$currentKey] = unserialize($valueText);
			}
			$currentKey = $value[0];

			$lastOffset = $offset + strlen( $currentKey )+1;
		}

		$valueText = substr( $data, $lastOffset );
		$returnArray[$currentKey] = @unserialize( $valueText );
		
		return $returnArray;
	}
	
}


//  public static function fltr_check($obj, $fltr_arr) {
//    foreach ($fltr_arr as $tmp_key => $where_or) {
//      foreach ($where_or as $tmp_key => $where_and) {
//        $cmd = $where_and[0];
//        $key_arr = !isset($where_and[1]) ? [] : (isset($where_and[1]) && is_array($where_and[1]) ? $where_and[1] : (is_string($where_and[1]) ? explode(',', $where_and[1]) : [$where_and[1]]));
//        $val_arr = !isset($where_and[2]) ? [] : (isset($where_and[2]) && is_array($where_and[2]) ? $where_and[2] : (is_string($where_and[2]) ? explode(',', $where_and[2]) : [$where_and[2]]));
//        if(!empty($val_arr)) {
//          if(in_array($cmd, ['=', '<>'], TRUE) && count($val_arr) > 1) {$cmd = $cmd === '=' ? 'IN' : 'NOT IN';}
//          foreach ($key_arr as $key_id => $key) {
//            if(!isset($obj[$key])) continue;
//            if($cmd === '=' && $obj[$key] != $val_arr[0]) {
//              return FALSE;
//            } else if($cmd === '<>' && $obj[$key] == $val_arr[0]) {
//              return FALSE;
//            } else if($cmd === 'IN' && !in_array($obj[$key], $val_arr, TRUE)) {
//              return FALSE;
//            } else if($cmd === 'NOT IN' && in_array($obj[$key], $val_arr, TRUE)) {
//              return FALSE;
//            } else if($cmd === 'BETWEEN' && count($val_arr) == 2 && ($obj[$key] < $val_arr[0] || $obj[$key] > $val_arr[1])) {
//              return FALSE;
//            } else if($cmd === 'NOT BETWEEN' && count($val_arr) == 2 && $obj[$key] >= $val_arr[0] && $obj[$key] <= $val_arr[1]) {
//              return FALSE;
//            }
//          }
//        }
//      }
//    }
//    return TRUE;
//  }
