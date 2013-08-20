<?
namespace ff;

class login {
	
	private static $login_ok = NULL;
	private static $role_super = 0;
	private static $role_blocked = 0;
	private static $guest_user = 0;
	
	public static function config(array $ff_opt) {
		/**************
		*
		*   Auto LOGIN
		*
		***************/

		static::$role_super = (int)\ff\getVal($ff_opt, 'login.role.super', 0);
		static::$role_blocked = (int)\ff\getVal($ff_opt, 'login.role.blocked', 0);
		static::$guest_user = (int)\ff\getVal($ff_opt, 'login.guest.user', 0);
		static::$login_ok = \ff\getVal($ff_opt, 'login.ok');
		
		if(!empty($_REQUEST['login']) && !empty($_REQUEST['password']) ) {
			$token = \ff\login::access(['login' => $_REQUEST['login'], 'password' => $_REQUEST['password']]);
			if(empty($token)) {
				throw new \Exception( 'Wrong login or password' );
			}
		} else {
			$token = \ff\getREQUEST('t', \ff\getSESSION('FF_TOKEN'));
			$token = \ff\login::checkToken($token, \ff\getVal($_SESSION, [$token, 'user', 'id'], 0));
		}

		if(static::$guest_user > 0 && \ff\getVal($_SESSION, [$token, 'user', 'id'], 0) === 0) {
			$token = static::loadSessionData(static::checkToken($token, static::$guest_user), static::$guest_user, 0);
		}

		define('FF_TOKEN', $token);
		$_SESSION['FF_TOKEN'] = FF_TOKEN;
		
		
		/**************
		*
		*   Define user constants
		*
		***************/

		$_SESSION[ FF_TOKEN ][ 'user' ][ 'php_self' ]   = FF_PHP_SELF;
		$_SESSION[ FF_TOKEN ][ 'user' ][ 'request_at' ] = date( 'Y-m-d H:i:s' );
		$_SESSION[ FF_TOKEN ][ 'user' ][ 'lang' ]       = FF_LANG;

		\ff\define_arr(
			[
				'id'=>0,
				'type'=>0,
				'grp'=>0,
				'role'=>0,
				'super'=>0,
				'sess'=>0,
				'lang'=>FF_LANG,
				'name'=>'Guest',
				'email'=>'',
				'login'=>'guest',
				'role_name'=>'Guest',
				'grp_name'=>'Guest',
				'phpsess'=>session_id(),
				'token'=>FF_TOKEN,
				'ver_ff'=>FF_VER,
				'ver_cache'=>FF_VER_CACHE
			],
			$_SESSION[ FF_TOKEN ][ 'user' ],
			'FF_USER_'
		);

		define( 'FF_IS_USER_SUPER',  FF_USER_ROLE === static::$role_super );
		define( 'FF_IS_USER_GUEST',  FF_USER_ID === static::$guest_user );

		if(FF_USER_SESS > 0) {
			\ff\dbh::upd('UPDATE `ff_sess` SET `ended_at` = NOW() WHERE `id` = \''.(int)(FF_USER_SESS).'\'');
		}

		define( 'FF_VER_CLIENT', (int)(\ff\getREQUEST('v', FF_VER)));
		define( 'FF_VER_CLIENT_CACHE', (int)(\ff\getREQUEST('c', FF_VER_CACHE)));

		$config = \ff\getVal($ff_opt, 'login.config');
		if(!empty($config)) {
			\ff\load_and_call($config);
		}
	}

	
	public static function cleanToken($token) {
		if(empty($_SESSION['started_at'])) {
			$_SESSION['started_at'] = date("Y-m-d H:i:s");
			$_SESSION['ip_addr'] = \ff\getSERVER('REMOTE_ADDR', '');
		}

		if(!empty($token)) {
			$_SESSION[$token] = [
				'ver_ff'=>FF_VER,
				'ver_cache'=>FF_VER_CACHE,
				'user'=>NULL,
				'role'=>NULL
			];
		}
	}

	
	private static function delToken($token) {
		if(!empty($token)) {
			unset($_SESSION[$token]);
			if(in_array($token, $_SESSION[ 'tokens' ], TRUE)) {
				unset($_SESSION[ 'tokens' ][array_search($token, $_SESSION[ 'tokens' ])]);
			}
		}
	}

	
	private static function checkToken4Ver($token = NULL) {
		if(\ff\getVal($_SESSION, [$token, 'ver_ff']) !== FF_VER ) {
			static::delToken($token);
			return NULL;
		}
		return $token;
	}

	
	public static function checkToken($token = NULL, $user_id = 0) {
		if( !isset( $_SESSION[ 'tokens' ] ) ) {
			$_SESSION[ 'tokens' ] = [];
		}
		
		$token = \ff\getVal($_SESSION, [$token, 'user', 'id'], 0) !== $user_id ? NULL : $token;
		$token = static::checkToken4Ver($token);
		
		if( empty( $token ) ) {
			foreach ( $_SESSION[ 'tokens' ] as $tmp_key => $checkToken ) {
				$token = static::checkToken4Ver( \ff\getVal($_SESSION, [$checkToken, 'user', 'id'], 0) === $user_id ? $checkToken : NULL );
				if( !empty( $token ) ) {
					break;
				}
			}
		}
		
		if(empty($token)) {
			$token = uniqid();
			$_SESSION['tokens'][] = $token;
			static::cleanToken($token);
		}
		
		return $token;
	}

	
	public static function logout() {
		static::cleanToken(FF_TOKEN);
	}

	
	public static function get_user_id() {
		if(FF_IS_OUT_JS) {
			echo '
				alert('.FF_USER_ID.');
			';
		} else if(FF_IS_OUT_JSON) {
			echo json_encode(['id' => FF_USER_ID, 'token' => FF_TOKEN]);
		} else {
			echo FF_USER_ID;
		}
	}

	
	public static function cache_update(array $arg) {
		$out = isset($arg['out']) ? $arg['out'] : (FF_IS_OUT_JS ? 'js' : (FF_IS_OUT_JSON ? 'json' : NULL) );

		\ff\lcache::set( 'ver/cache', time() );

		if($out === 'js') {
			echo 'fastFace.msg.info("Cache version updated");'.PHP_EOL;
		}
		return isset($arg['data']) ? $arg['data'] : NULL;
	}

	private static function clear_user_cache($arg) {
		return;
		$user_id = isset($arg['user_id']) ? (int)$arg['user_id'] : 0;
		$user_id = ($user_id > 0 && \ff\genPerm('user', ['any_cmd'])) ? $user_id : FF_USER_ID;

		\ff\lcache::del_all('/user/'.$user_id);
		
		if(FF_IS_OUT_JS && empty($arg['quiet'])) {
			echo 'fastFace.msg.info("User cache folder cleaned");'.PHP_EOL;
		}
		if(empty($arg['no_exit'])) {
			exit;
		}
	}

	
	public static function login_under_user($arg) {
		$user_id = isset($arg['user_id']) ? (int)$arg['user_id'] : 0;
		
		if( \ff\genPerm('user', ['any_cmd', 'agents'])) {
			if($user_id > 0) {
				$res = \ff\dbh::get_row('SELECT `id`, `login`, NULL as `code` FROM `'.FF_DB_NAME.'`.`ff_user` WHERE '.(\ff\genPerm('user', ['any_cmd']) ? '' : ' ff_user_type=3 AND ').' id=\''.\ff\dbh::esc($user_id).'\'');
				if(!empty($res) && isset($res['id']) && !empty($res['login']) && $res['id'] == $user_id) {
					if(isset($arg['stored_logins'])) {
						$stored_logins = @json_decode($arg['stored_logins'], TRUE);
						if(!json_last_error() && !empty($stored_logins) && is_array($stored_logins) && isset($stored_logins[$res['login']])) {
							$res['code'] = $stored_logins[$res['login']]['code'];
						}
					}

					if(!empty($res['code'])) {
						$res2 = \ff\dbh::get_row('SELECT `id` FROM `'.FF_DB_NAME.'`.`ff_user_auto_login` as ua WHERE ua.ff_user=\''.\ff\dbh::esc($user_id)."' AND ua.code='".\ff\dbh::esc(md5($res['code']))."'");
						if(empty($res2)) {
							$res['code'] = NULL;
						}
					}

					if(empty($res['code'])) {
						$res['code'] = rand_str(20);
						\ff\dbh::add("INSERT INTO `".FF_DB_NAME."`.`ff_user_auto_login` (`ff_user`, `ff_sess`, `code`, `accessed_at`, `created_at`) VALUES ('".\ff\dbh::esc($user_id)."', '".\ff\dbh::esc($_SESSION[FF_TOKEN]['user']['sess'])."', '".\ff\dbh::esc(md5($res['code']))."', NOW(), NOW())");
					}
					if(FF_IS_OUT_JS) {
						echo '
							try {
								fastFace.login.addAutoLogin("'.$res['login'].'", '.json_encode($res).');
								fastFace.login.autoLogin();
							} catch(e) {
								fastFace.err.js(e);
							}
						';
					}
				} else {
					throw new \Exception( sprintf( '%s: User [%s] not found!', __METHOD__, $user_id ) );
				}
			} else {
				throw new \Exception( sprintf( '%s: You must specify UserID!', __METHOD__ ) );
			}
		} else {
			throw new \Exception( sprintf( '%s: You have no permission to pervorm this operation!', __METHOD__ ) );
		}
	}
	
	

	public static function change_password($arg) {
		$user_id = isset($arg['user_id']) ? (int)$arg['user_id'] : 0;
		$old_password = isset($arg['old_password']) ? $arg['old_password'] : '';
		$new_password = isset($arg['new_password']) ? $arg['new_password'] : '';
		
		$affected_rows = 0;
		if( !empty($new_password) ) {
			if( \ff\genPerm('user', ['any_cmd']) && $user_id > 0 ) {
				$affected_rows = \ff\dbh::upd("UPDATE `".FF_DB_NAME."`.`ff_user` SET `password` = '".\ff\dbh::esc(md5($new_password))."' WHERE `id` = '".\ff\dbh::esc($user_id)."'");
			} else if(\ff\genPerm('user', ['agents']) && $user_id > 0) {
				$affected_rows = \ff\dbh::upd("UPDATE `".FF_DB_NAME."`.`ff_user` SET `password` = '".\ff\dbh::esc(md5($new_password))."' WHERE `ff_user_type` in (3,4) AND `id` = '".\ff\dbh::esc($user_id)."'");
			} else if(FF_USER_ID > 0 && !empty($old_password)) {
				$affected_rows = \ff\dbh::upd("UPDATE `".FF_DB_NAME."`.`ff_user` SET `password` = '".\ff\dbh::esc(md5($new_password))."' WHERE `id` = '".\ff\dbh::esc(FF_USER_ID)."' AND `password` = '".\ff\dbh::esc(md5($old_password))."'");
			}
			if($affected_rows) {
				\ff\ff_user::password_changed($user_id, $new_password);
			}
		}
		if(FF_IS_OUT_JS) {
			echo '
				try {
					fastFace.msg.info("Password changed!");
				} catch(e) {
					fastFace.err.js(e);
				}
			';
		} else {
			echo $affected_rows;
		}
	}

	public static function access($arg) {
		if(empty($arg['login'])) {
			if(FF_IS_OUT_JS) {
				echo '
					try {
						fastFace.login.show(null, fastFace.dict.val("login_empty"));
					} catch(e) {
						fastFace.err.js(e);
					}
				';
			} else {
				echo \ff\dict::val('login_empty');
			}
			return;
		}
		
		$login = isset($arg['login']) ? strtolower(trim($arg['login'])) : '';
		$password = isset($arg['password']) ? trim($arg['password']) : '';
		$login_code = isset($arg['login_code']) ? $arg['login_code'] : '';
		
		$token = static::log_in($login, $password, $login_code);
		
		if(!empty($token)) {
			
			if(isset($arg['store_login']) && $arg['store_login'] === TRUE) {
				if(empty($login_code)) {
					$login_code = rand_str(20);
					\ff\dbh::add("INSERT INTO `".FF_DB_NAME."`.`ff_user_auto_login` (`ff_user`, `ff_sess`, `code`, `accessed_at`, `created_at`) VALUES ('".\ff\dbh::esc($_SESSION[$token]['user']['id'])."', '".\ff\dbh::esc($_SESSION[$token]['user']['sess'])."', '".\ff\dbh::esc(md5($login_code))."', NOW(), NOW())");
				} else {
					\ff\dbh::upd("UPDATE `".FF_DB_NAME."`.`ff_user_auto_login` SET `accessed_at`=NOW(), `ff_sess`='".\ff\dbh::esc($_SESSION[$token]['user']['sess'])."' WHERE `ff_user`='".\ff\dbh::esc($_SESSION[$token]['user']['id'])."' and `code`='".\ff\dbh::esc(md5($login_code))."'");
				}
				
				if(FF_IS_OUT_JS) {
					echo '
						try {
							fastFace.login.addAutoLogin("'.$login.'", '.json_encode(['id'=>0, 'type'=>0, 'grp'=>0, 'code'=>$login_code, 'name'=>$_SESSION[$token]['user']['name']]).');
						} catch(e) {
							fastFace.err.js(e);
						}
					';
				}
			}
			
			if(FF_IS_OUT_JS) {
				echo '
					try {
						
						if(top.location.href.indexOf("t='.$token.'") <= 0) {
							top.location.href="admin.php?l='.FF_LANG.'&v='.FF_VER.'&t='.$token.'";
						} else {
							fastFace.login.data = '.json_encode([
									'user'=>$_SESSION[$token]['user'],
									'role'=>$_SESSION[$token]['role']
								]).';
							fastFace.err.isDebug = '.(int)(FF_IS_DEBUG).';
							fastFace.reInit('.json_encode([
								'lang'=>FF_LANG,
								'ff_ver'=>FF_VER,
								'ff_ver_cache'=>FF_VER_CACHE,
								'ff_token'=>FF_TOKEN
							]).');
						}
						
					} catch(e) {
						fastFace.err.js(e);
					}
				';
			}
			
		} else {
			
			if(!empty($login_code)) {
				\ff\dbh::del("DELETE FROM `".FF_DB_NAME."`.`ff_user_auto_login` WHERE `code`='".\ff\dbh::esc(md5($login_code))."'");
			}
			
			if(FF_IS_OUT_JS) {
				echo '
					try {
						fastFace.login.show("'.$login.'", fastFace.dict.val("login_error"));
					} catch(e) {
						fastFace.err.js(e);
					}
					';
			} else {
				echo \ff\dict::val('login_error');
			}
			
		}
		return $token;
	}

	
	
	
	
	
	private static function log_in($login, $password, $login_code) {
		$login = !empty($login) ? \ff\dbh::esc($login) : '';
		$password = !empty($password) ? \ff\dbh::esc(md5($password)) : '';
		$login_code = !empty($login_code) ? \ff\dbh::esc(md5($login_code)) : '';
		
		$row = \ff\dbh::get_row(
				"
				SELECT
					`u`.`id`
				FROM
					`".FF_DB_NAME."`.`ff_user` as `u`, `".FF_DB_NAME."`.`ff_role` as `p`, `".FF_DB_NAME."`.`ff_user_grp` as `g`
				WHERE
							`u`.`ff_user_grp` = `g`.`id`
					AND `u`.`ff_user_type` = `g`.`ff_user_type`
					AND `u`.`ff_role` = `p`.`id`
					AND `u`.`ff_user_type` = `p`.`ff_user_type`
					AND `u`.`is_act` = TRUE
					AND `g`.`is_act` = TRUE
					AND `u`.`ff_role` <> ".static::$role_blocked."
					AND `u`.`login` = '$login'
					AND (
								(`u`.`password` = '$password')
						OR (EXISTS (SELECT `id` FROM `".FF_DB_NAME."`.`ff_user_auto_login` as ua WHERE `ua`.`ff_user`=`u`.id AND `ua`.`code`='$login_code'))
					)
				ORDER BY
					`u`.`ff_user_type` DESC
				LIMIT 0, 1
				",
				MYSQLI_NUM
			);
		
		$user_id = !empty($row) ? \ff\getVal4Key(
			$row,
			0
		) : 0;

		if(!empty($user_id)) {
			$sess = \ff\dbh::add("INSERT INTO `".FF_DB_NAME."`.`ff_sess` (`ff_user`, `ip_addr`, `started_at`, `ended_at`) VALUES ('".(int)$user_id."', '".\ff\dbh::esc(\ff\getSERVER('REMOTE_ADDR', ''))."', NOW(), NOW())");
			return static::loadSessionData(static::checkToken(\ff\getREQUEST('t', \ff\getSESSION('FF_TOKEN')), $user_id), $user_id, $sess);
		} else {
			return NULL;
		}
	}
	


	
	public static function get_prm($cls) {
		$res = array_fill_keys(\ff\cls::is_fn($cls, 'role') ? \ff\cls::run([$cls, 'role']) : ['any_cmd', 'add', 'upd', 'get', 'del'], (FF_PERM_SKIP || FF_IS_USER_SUPER) ? TRUE : TRUE);

		if ( !FF_PERM_SKIP && !FF_IS_USER_SUPER && !empty( $_SESSION[FF_TOKEN]['role'][$cls] ) ) {
			foreach ($_SESSION[FF_TOKEN]['role'][$cls] as $key => $val) {
				$res[$val] = TRUE;
			}
			
			if ( $res['any_cmd'] ) {
				foreach ($res as $key => $val) {
					$res[$key] = TRUE;
				}
			}
		}

		return $res;
	}
	
	public static function check_prm($cls = NULL, $prm = NULL, $prm2check = NULL) {
		if(FF_PERM_SKIP || FF_IS_USER_SUPER) {
			return TRUE;
		}
		
		if(!is_array($prm)) {
			if(!empty($cls)) {
				$prm = static::get_prm($cls);
			} if(FF_IS_DEBUG || FF_IS_DEV) {
				throw new \Exception(sprintf('%s: Have no permissions for [%s]', __METHOD__, $cls));
			} {
				return FALSE;
			}
		}
		
		if(is_array($prm2check)) {
			foreach ($prm2check as $key => $val) {
				if(isset($prm[$val]) && $prm[$val] === TRUE) {
					return TRUE;
				}
			}
		} else {
			foreach ($prm as $key => $val) {
				if($val === TRUE) {
					return TRUE;
				}
			}
		}
		
		if(FF_IS_DEBUG || FF_IS_DEV) {
			throw new \Exception(sprintf('%s: Have no permissions for [%s]', __METHOD__, $cls));
		}
		
		return FALSE;
	}

	
	
	
	public static function reloadUserData() {
		static::loadSessionData(FF_TOKEN, FF_USER_ID, FF_USER_SESS);
	}

	
	private static function loadSessionData($token, $user_id, $sess) {
		static::cleanToken($token);
		$user = static::getUser($user_id);
		if(!empty($user)) {
			$user['menu'] = array_map('intval', explode(',', $user['menu']));
			$user['ver_ff'] = FF_VER;
			$user['ver_cache'] = FF_VER_CACHE;
			$user['phpsess'] = session_id();
			$user['php_self'] = FF_PHP_SELF;
			$user['login_on'] = date("Y-m-d H:i:s");
			$user['request_at'] = date("Y-m-d H:i:s");
			$user['token'] = $token;
			$user['super'] = $user['role'] === static::$role_super;
			$user['sess'] = $sess;

			$_SESSION[$token]['user'] = $user;
			$_SESSION[$token]['role'] =  static::getPermissions($user['role'], $user['type']);

			if(!empty(static::$login_ok)) {
				\ff\load_and_call(static::$login_ok, $user);
			}
			
			static::clear_user_cache(['user_id'=>$user['id'], 'no_exit'=>TRUE, 'quiet'=>TRUE]);
			return $token;
		}
		return NULL;
	}

	private static function getUser($user_id) {
		if((int)($user_id) > 0) {
			return \ff\dbh::get_row("
				SELECT
					`u`.`id`,
					`u`.`ff_user_type` AS `type`,
					`u`.`ff_user_grp` AS `grp`,
					`u`.`ff_role` AS `role`,
					`lu`.`".FF_LANG."` as `name`,
					`u`.`login`, `u`.`email`,
					`u`.`phone`, `u`.`fax`,
					`lg`.`".FF_LANG."` as `grp_name`,
					`lp`.`".FF_LANG."` as `role_name`,
					`p`.`ff_menu` as `menu`
				FROM
					`".FF_DB_NAME."`.`ff_user` `u`,
					`".FF_DB_NAME."`.`ff_role` `p`,
					`".FF_DB_NAME."`.`ff_user_grp` `g`,
					`".FF_DB_NAME."`.`ff_lang_char` `lu`,
					`".FF_DB_NAME."`.`ff_lang_char` `lg`,
					`".FF_DB_NAME."`.`ff_lang_char` `lp`
				WHERE
							`u`.`id` = '".(int)$user_id."'
					AND `u`.`ff_user_grp` = `g`.`id`
					AND `u`.`ff_user_type` = `g`.`ff_user_type`
					AND `u`.`ff_role` = `p`.`id`
					AND `u`.`ff_user_type` = `p`.`ff_user_type`
					AND `u`.`is_act` = TRUE
					AND `g`.`is_act` = TRUE
					AND `u`.`ff_role` <> 16
					AND `u`.`name` = `lu`.`id`
					AND `g`.`name` = `lg`.`id`
					AND `p`.`name` = `lp`.`id`
				LIMIT 0, 1
			");
		} else {
			return NULL;
		}
	}
	
	private static function getPermissions($role_id, $user_type) {
		return NULL;
		$user_types = ['guest', 'office_user', 'branch_user', 'agent_user', 'hotel_user', 'client_user', 'guest'];
		$role = ['menu'=>['anyone', $user_types[$user_type]]];
		if($role_id === 1) { $role['menu'][] = 'super_user'; }
		
		$res = \ff\dbh::get_all("
			SELECT
				`r`.`perm_cls_fn`
			FROM
				`".FF_DB_NAME."`.`ff_role` as `p`, `".FF_DB_NAME."`.`ff_role_rule` as `r`
			WHERE
						(`p`.`id` = '".(int)($role_id)."')
				AND (`p`.`ff_user_type` = '".(int)($user_type)."')
				AND (`p`.`id` = `r`.`ff_role`)
			ORDER BY
				`r`.`perm_cls_fn`
		", MYSQLI_ASSOC);
		
		foreach ($res as $tmp_key => $row) {
			$role_cls_fn = explode(':', $row['perm_cls_fn'], 2);
			if( !isset($role[ $role_cls_fn[0] ]) ) { $role[ $role_cls_fn[0] ] = []; }
			$role[ $role_cls_fn[0] ][] = $role_cls_fn[1];
		}
		
		return $role;
	}
	
}
