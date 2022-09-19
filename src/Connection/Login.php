<?php
declare(strict_types=1);
namespace User\Connection;

use App\Models\{Login as ModelLogin, User};
use User\Security\{JWT, Syntax};


class Login
{


	/**
	 * main login
	 * 
	 * @param string $username_or_email
	 * @param string $password
	 * @param array  $usertype (optional). default: any usertype
	 * 
	 * @return false|string  success | failure
	 *                      - string - success - return a token
	 *                      - false  - failure
	 * 
	 * @since   🌱 0.0.0
	 * @version 🌴 0.0.0
	 * @author  ✍ Muhammad Mahmudul Hasan Mithu
	 */
	public static function main( string $username_or_email, string $password, array $usertype=null ): false|string
	{
		$username_or_email = htmlspecialchars(strtolower(trim($username_or_email)));

		// check syntax
		$syntax_checker = (object) [
			'is_username' => Syntax::username($username_or_email),  // true - it is a valid username syntax
			'is_email'    => Syntax::email($username_or_email)      // true - it is a valid email syntax
		];

		// get user
		if($syntax_checker->is_username || $syntax_checker->is_email){
			$user =
			User::where(function($query)use($username_or_email, $syntax_checker){
					if($syntax_checker->is_username) $query->orWhere('username', '=', $username_or_email);
					if($syntax_checker->is_email)    $query->orWhere('email',    '=', $username_or_email);
				})
				->where(function($query)use($usertype){
					// usertype
					if(is_array($usertype)){
						$usertype = array_map(function($e){ return htmlspecialchars(strtolower(trim($e))); }, $usertype);
						$query->whereIn('usertype', $usertype);
					}
					// userstatus, is_email_verified
					$query->where('userstatus', 'active');
					$query->where('is_email_verified', true);
				})
				->get()[0] ?? null;

			// verify the user
			if($user && password_verify($password, $user->password)){
				$time = time();
				$token = JWT::encode([
					'user_id' => $user->id,
					'created_at' => $user->created_at,
					'iat'	=> $time
				]);
				$login = new ModelLogin();
				$login->user_id = $user->id;
				$login->token = $token['jwt'];
				$login->token_key = $token['key'];
				$login->save();
				return $token['jwt'];
			}
		}

		return false;
	}


}
