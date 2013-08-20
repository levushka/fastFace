<?
namespace ff;

class sess {
	
	public static function def($db_info = NULL) {
		if(!FF_IS_USER_SUPER && empty($_SESSION[FF_TOKEN]['role'][__CLASS__])) {
			return NULL;
		}
		
		return array_replace_recursive(
			$db_info,
			[
				'js_def' => [
					'fn' => [
						'getVal' => 'function(id) {
							return id;
						}'
					]
				],
				'get' => [
					'ord' => [['id', 'asc']]
				],
				'fnd' => [
					'WHERE' => [[
						['=','user', FF_USER_ID],
						['off','ip_addr',''],
						['BETWEEN'=>['start_at',date('Y-m-d H:i:s', mktime(0,0,0,date("m"),date("d"),date("Y"))), date('Y-m-d H:i:s')]],
						['off','end_at',date('Y-m-d H:i:s')]
					]]
				]
			]
		);
	}
}


