<?
namespace ff;

class ff_hist {

	public static function def($db_info = NULL) {
		if(!FF_IS_USER_SUPER && empty($_SESSION[FF_TOKEN]['role'][__CLASS__])) {
			return NULL;
		}
		
		return array_replace_recursive(
			$db_info,
			[
				'get' => [
					'ord' => [['cls', 'asc'],['row', 'asc'],['id', 'asc']]
				],
				'fnd' => [
					'WHERE' => [[
						['=','cls','dict'],
						['off','row',''],
						['off','time',date('Y-m-d H:i:s')],
						['off','user',1]
					]],
				]
			]
		);
	}
}

