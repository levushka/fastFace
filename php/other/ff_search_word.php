<?
namespace ff;

class ff_search_word {

	public static function def($db_info = NULL) {
		if(!FF_IS_USER_SUPER && empty($_SESSION[FF_TOKEN]['role'][__CLASS__])) {
			return NULL;
		}
		
		return array_replace_recursive(
			$db_info,
			[
				'get' => [
					'ord' => [['word', 'asc']]
				],
				'fnd' => [
					'WHERE' => [[
						['off','word',''],
					]],
				]
			]
		);
	}
}


