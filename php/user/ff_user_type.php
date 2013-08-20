<?
namespace ff;

class ff_user_type {
	
	public static function def($db_info = NULL) {
		$prm = \ff\login::get_prm(__CLASS__);
		
		return array_replace_recursive(
			$db_info,
			[
				'js_def' => [
					'hash_col' => NULL,
					'get' => [
						'WHERE' => [[['=','is_act',['val'=>1]]]],
						'sel' => ['id', 'name']
					]
				],
				'get' => [
					'WHERE' => $prm['any_cmd'] ? NULL : ( (FF_IS_USER_OFFICE || FF_IS_USER_BRANCH) ? [[['=','is_act',['val'=>1]]]] : [[['=','id',['val'=>FF_USER_TYPE]]]] ),
					'ord' => [['id', 'asc']]
				]
			],
			$prm['any_cmd'] ? [
				'add' => [],
				'upd' => [],
				'fnd' => ['grid'=>['disp'=>'dlg']]
			] : []
		);
	}
}


