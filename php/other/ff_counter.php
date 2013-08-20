<?
namespace ff;

class ff_counter {

	public static function perm() {
		return ['any_cmd'];
	}

	public static function def($db_info = NULL) {
		$prm = \ff\login::get_prm(__CLASS__);

		return array_replace_recursive(
			$db_info,
			[
				'js_def' => [
					'hash_col' => NULL,
					'get' => [
						'ord' => [['ord', 'asc'], ['name_'.FF_LANG, 'asc']]
					]
				],
				'get' => [
					'WHERE' =>  $prm['any_cmd']  ? NULL : [[['=','is_act',TRUE], ['=','ff_role',FF_USER_ROLE]]],
				],
				'add' => $prm['any_cmd'] ? [] : NULL,
				'upd' => $prm['any_cmd'] ? [] : NULL,
				'fnd' => $prm['any_cmd'] ? [] : NULL
			]
		);
	}
}
