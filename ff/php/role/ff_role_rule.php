<?
namespace ff;

class ff_role_rule {
	public static function def($db_info = NULL) {
		$prm = \ff\login::get_prm('role');
		
		if(!$prm['any_cmd'] && !$prm['get']) {
			return NULL;
		}
		
		return array_replace_recursive(
			$db_info,
			[
				'get' => ($prm['any_cmd'] || $prm['get']) ? ['ord' => [['perm_cls_fn', 'asc']]] : NULL,
				'add' => $prm['any_cmd'] ? ['php'=>['post_win'=>[['ff\\login', 'cache_update', []]]]] : NULL,
				'del' => $prm['any_cmd'] ? ['php'=>['post_win'=>[['ff\\login', 'cache_update', []]]]] : NULL,
				'upd' => $prm['any_cmd'] ? ['php'=>['post_win'=>[['ff\\login', 'cache_update', []]]]] : NULL
			]
		);
	}
}


