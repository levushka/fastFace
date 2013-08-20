<?
namespace ff;

class ff_user_grp {
	
	public static function perm() {
		return ['any_cmd', 'agents'];
	}

	public static function def($db_info = NULL) {
		$prm = \ff\login::get_prm(__CLASS__);
		
		return array_replace_recursive(
			$db_info,
			[
				'js_def' => [
					'hash_col' => NULL,
					'grp_col' => 'ff_user_type',
					'fltr_col' => 'ff_user_type',
					'get' => [
						'sel' => ['id', 'name', 'ff_user_type'],
						'ord' => [['ff_user_type', 'asc'], ['name', 'asc']]
					]
				],
				'get' => [
					'WHERE' => $prm['any_cmd'] ? NULL : ( $prm['agents'] ? [[['=', 'ff_user_type', [3,4]]]] : ( (FF_IS_USER_OFFICE || FF_IS_USER_BRANCH) ? [[['=','is_act',1]]] : [[['=','id',FF_USER_GRP]]] )),
					'ord' => [['ff_user_type', 'asc'], ['name', 'asc']]
				],
				'add' => $prm['any_cmd'] ? [] : ( $prm['agents'] ? ['prdef' => ['ff_user_type'=>3]] : NULL ),
				'upd' => $prm['any_cmd'] ? [
					'php'=>['post_win'=>[[__CLASS__, 'upd_user_grp', []], ['ff\\login', 'cache_update', []]]]
				] : (
					$prm['agents'] ? [
						'php'=>['post_win'=>[[__CLASS__, 'upd_user_grp', []], ['ff\\login', 'cache_update', []]]],
						'cols'=> array_values(array_diff($db_info['ck'], ['ff_user_type'])),
						'WHERE' => [[['=','ff_user_type', [3,4]]]]
					] : NULL
				),
				'fnd' => ( $prm['any_cmd'] || $prm['agents'] ) ? ['grid'=>['disp'=>'dlg']] : NULL
			]
		);
	}
	
	public static function upd_user_grp($arg) {
		$prm = \ff\login::get_prm(__CLASS__);
		if(!$prm['any_cmd'] && !$prm['agents']) {
			throw new \Exception(sprintf('%s: Wrong permission', __METHOD__));
		}
		
		if(empty($arg['row_id'])) {
			throw new \Exception(sprintf('%s: row_id not defined', __METHOD__));
		}
		$row_id = (int)($arg['row_id']);
		if(!empty($row_id)) {
			if(!isset($arg['data']) && !is_array($arg['data'])) {
				throw new \Exception(sprintf('%s: data not defined', __METHOD__));
			}
			$data = $arg['data'];
			
			if(isset($data['is_act']) && (int)($data['is_act']) !== 1) {
				\ff\dbh::upd('UPDATE `ff_user` SET `is_act` = FALSE WHERE `ff_user_grp` = \''.$row_id.'\'');
			}
		}
	}
	
}


