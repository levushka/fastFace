<?
namespace ff;

class db_select {
	
	public static function sql(array $select, array $struct, $check4role = TRUE) {
		if(empty($select) || !is_array($select)) {
			throw new \Exception(sprintf('%s: SELECT not exists', __METHOD__));
		}

		$res = [];
		foreach ($select as $key => $val) {
			$res[] = \ff\db_cmd::sql($val, $struct, $check4role);
		}
		
		if(empty($res)) {
			throw new \Exception(sprintf('%s: Empty SELECT', __METHOD__));
		}
		return implode(', ', $res);
	}

	
	public static function limit(array $limit, array $struct) {
		$max = 2000;
		$size = count($limit);
		$limit = $size === 2 ? $limit : ($size === 1 ? [0, $limit[0]] : [0, $max]);
		if($limit[0] >= $limit[1] || $limit[0] < 0 || $limit[1] > $max) {
			$limit = [0, $max];
		}
		return ' LIMIT '.(int)$limit[0].', '.(int)$limit[1];
	}

	public static function order_by(array $order_by, array $struct) {
		return ' ORDER BY '.static::sql($order_by, $struct, FALSE);
	}
	
	
}

