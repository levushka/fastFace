<?
namespace ff;

class ff_menu {
	
	public static function def($db_info = NULL) {
		$prm = \ff\login::get_prm(__CLASS__);
		
		$menu_list = !empty($_SESSION[FF_TOKEN]['user']['menu']) ? $_SESSION[FF_TOKEN]['user']['menu'] : '76,77,78';
		return array_replace_recursive(
			$db_info,
			[
				'js_def' => [
					'hash_col' => NULL,
					'fmt_val' => 'this.data[id][1]+" ("+fastFace_cls.ff_menu_grp.getVal(this.data[id][2])+"]"',
					'grp_col' => 'ff_menu_grp',
					'get' => [
						'fltr' => [[['=','is_act',1], ['IN', 'id', $menu_list]]], //$prm['any_cmd'] ? NULL :
						'sel' => ['id', 'name', 'ff_menu_grp', 'url']
					]
				],
				'get' => [
					'fltr' => [[['=','is_act',1], ['IN', 'id', $menu_list]]],  //$prm['any_cmd'] ? NULL :
					'ord' => [['ord', 'asc']]
				]
			],
			$prm['any_cmd'] ? [
				'add' => [],
				'upd' => [],
				'del' => [],
				'fnd' => ['fltr' => [[['off','is_act',NULL],['off','ff_menu_grp',NULL]]]]
			] : []
		);
	}
}


