<?
namespace ff;

class ff_user {
	
	public static function perm() {
		return ['any_cmd', 'agents'];
	}

	public static function def($db_info = NULL) {
		$prm = \ff\login::get_prm(__CLASS__);

		return array_replace_recursive(
			$db_info,
			[
				'js_def' => [
					'fn' => [
						'addUser' => 'function() {
						}'
					],
					'hash_col' => NULL,
					'fmt_val' => 'this.data[id][1]+" ("+fastFace_cls.ff_user_grp.getVal(this.data[id][2])+")"',
					'sort' => 'function(l, r) { return (l[1]==r[1] && l[2]==r[2]) ? 0 : ((fastFace_cls.ff_user_grp.getVal(l[2])<fastFace_cls.ff_user_grp.getVal(r[2]) || (l[1]<r[1] && fastFace_cls.ff_user_grp.getVal(l[2])==fastFace_cls.ff_user_grp.getVal(r[2])))?-1:1); }',
					'grp_col' => 'ff_user_grp',
					'fltr_col' => 'ff_user_type',
					'get' => [
						'WHERE' => [[['=','is_act',1], ['IN', 'ff_user_type', [1,2,3,4]]]],
						'sel'  => ['id', 'name', 'ff_user_grp', 'ff_user_type'],
						'ord' => [['ff_user_type', 'asc'], ['ff_user_grp', 'asc'], ['name', 'asc']]
					]
				],
				'get' => [
					'WHERE' => $prm['any_cmd'] ? NULL : ( $prm['agents'] ? [[['=','ff_user_type',[3,4]]]] : ( (FF_IS_USER_OFFICE || FF_IS_USER_BRANCH) ? [[['=','is_act',1], ['=','ff_user_type',[3,4]]]] : ( (FF_IS_USER_AGENT || FF_IS_USER_HOTEL) ? [[[['=','ff_user_type',FF_USER_TYPE],['=', 'ff_user_grp', FF_USER_GRP]]]] : [[['=', 'id', FF_USER_ID]]] ) ) ),
					'ord' => [['ff_user_type', 'asc'], ['ff_user_grp', 'asc'], ['name', 'asc']]
				],
				'add' => ($prm['any_cmd'] || $prm['agents']) ? ['grid'=>['skip'=>TRUE], 'prdef' => $prm['any_cmd'] ? NULL : ['ff_user_type'=>3, 'ff_role'=>17], 'php'=>['pre'=>[['user', 'pre_add_upd', []], ['ff\\login', 'cache_update', []]]]] : NULL,
				'upd' => $prm['any_cmd'] ? ['php'=>['pre'=>[['user', 'pre_add_upd', []], ['ff\\login', 'cache_update', []]]]] : ( $prm['agents'] ? ['cols'=> array_values(array_diff($db_info['ck'], ['ff_user_type'])), 'WHERE' => [[['=', 'ff_user_type', 3]]], 'php'=>['pre'=>[['user', 'pre_add_upd', []], ['ff\\login', 'cache_update', []]]]] : NULL),
				'fnd' => ($prm['any_cmd'] || $prm['agents']) ? [
					'WHERE' => [[
						['=','is_act',NULL],
						['=','ff_user_type',NULL],
						['off','ff_user_grp',NULL],
						['off','ff_role',NULL]
					]]
				] : NULL
			]
		);
	}

	public static function pre_add_upd($arg) {
		$prm = \ff\login::get_prm(__CLASS__);
		
		if(!$prm['any_cmd'] && !$prm['agents']) {
			return;
		}

		if(!isset($arg['data']) && !is_array($arg['data'])) {
			throw new \Exception(sprintf('%s: data not defined', __METHOD__));
		}
		$data = $arg['data'];

		if(isset($data['password']) && strlen($data['password']) != 32) {
			if(empty($data['password'])) {
				$data['password'] = substr(uniqid(), 0, 6);
			}
			if(!empty($arg['row_id'])) {
				static::password_changed((int)($arg['row_id']), $data['password']);
			}
			$data['password'] = md5($data['password']);
		}
		
		return $data;
	}
	
	public static function password_changed($row_id, $new_password) {
		if(FF_USER_ID === $row_id) {
			$email = FF_USER_EMAIL;
			$name = FF_USER_NAME;
			$login = FF_USER_LOGIN;
		} else {
			$user = \ff\dbh::get_row('
				SELECT
					`l`.`'.FF_LANG.'` as `name`, `u`.`login`, `u`.`email`
				FROM
					`'.FF_DB_NAME.'`.`ff_user` `u`, `'.FF_DB_NAME.'`.`ff_lang_char` `l`
				WHERE
					`u`.`id`='.(int)($row_id).' AND `u`.`name`=`l`.`id`
			');
			if(!empty($user)) {
				$email = $user['email'];
				$name = $user['name'];
				$login = $user['login'];
			} else {
				throw new \Exception(sprintf('%s: user [%s] not found', __METHOD__, $row_id));
			}
		}
		
		if(!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$headers  = 'MIME-Version: 1.0' . "\r\n";
			$headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
			$headers .= 'From: System <inna@travellux.com>' . "\r\n";
			if(FF_USER_ID !== $row_id && filter_var(FF_USER_EMAIL, FILTER_VALIDATE_EMAIL)) {
				$headers .= 'Bcc: ' . FF_USER_EMAIL . "\r\n";
			}
			
			$msg = '<HTML><BODY dir="ltr">
Hi '.$name.',
<BR>
Your have new password for Emalon web site administration system.
<BR><BR>
Login: <B>'.$login.'</B>
<BR>
Password: <B>'.$new_password.'</B>
<BR>
Web site: <B><A href="http://www.emalon.co.il/admin/">http://www.emalon.co.il/admin/</A></B>
<BR>
</BODY></HTML>';
			
			mail($email, 'Emalon: New password', $msg, $headers);
		}
	}
		
}

