<?
namespace ff;

class ff_role {

	public static function perm() {
		return ['any_cmd', 'get'];
	}

	public static function def($db_info = NULL) {
		$prm = \ff\login::get_prm(__CLASS__);

		if(!$prm['any_cmd'] && !$prm['get']) {
			return NULL;
		}
		
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
				'get' => ($prm['any_cmd'] || $prm['get']) ? ['ord' => [['ff_user_type', 'asc'], ['name', 'asc']]] : NULL,
				'add' => $prm['any_cmd'] ? [] : NULL,
				'upd' => $prm['any_cmd'] ? [] : NULL,
				'fnd' => $prm['any_cmd'] ? ['grid'=>['disp'=>'dlg']] : NULL
			]
		);
	}
}

//        'add' => $prm['any_cmd'] ? ['php'=>['post_win'=>[['ff\\login', 'cache_update', []]]]] : NULL,
//        'del' => $prm['any_cmd'] ? ['php'=>['post_win'=>[['ff\\login', 'cache_update', []]]]] : NULL,
//        'upd' => $prm['any_cmd'] ? ['php'=>['post_win'=>[['ff\\login', 'cache_update', []]]]] : NULL
