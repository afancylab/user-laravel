<?php
declare(strict_types=1);
namespace User\Account;

use App\Models\User;
use User\Security\{Password, Syntax};

class Create
{


	/**
	 * create
	 * 
	 * @param string $username
	 * @param string $email
	 * @param string $usertype
	 * @param string $password
	 * 
	 * @return object
	 * - is_success: boolean
	 * _____________________________________________
	 * | is_success: true,													|
	 * | user_id: int																|
	 * |--------------------------------------------|
	 * | is_success: false,													|
	 * | username_syntax_error: boolean,						|
	 * | email_syntax_error	 : boolean,							|
	 * | password_entropy_error	 : boolean,					|
	 * | username_exists: boolean,									|
	 * | email_exists	 : boolean										|
	 * |____________________________________________|
	 * 
	 * @since		🌱 0.0.0
	 * @version	🌴 0.0.0
	 * @author	✍ Muhammad Mahmudul Hasan Mithu
	 */
	public static function addNewAccount(string $username, string $email, string $usertype, string $password): object
	{
		$username = htmlspecialchars(strtolower(trim($username)));
		$email		= htmlspecialchars(strtolower(trim($email)));
		$usertype = htmlspecialchars(strtolower(trim($usertype)));

		// error checker
		$checker = [
			'username_syntax_error'	 => !Syntax::username($username),
			'email_syntax_error'		 => !Syntax::email($email),
			'password_entropy_error' => Password::entropy($password)<51?true:false,
			'username_exists'	=> false,
			'email_exists'	 	=> false
		];

		// check existence
		if(!$checker['username_syntax_error'] || !$checker['email_syntax_error']){
			$users =
			User::where(function($query)use($username, $email, $checker){
				if(!$checker['username_syntax_error']) $query->orWhere('username', '=', $username);
				if(!$checker['email_syntax_error']) $query->orWhere('email', '=', $email);
			})->get();
			foreach($users as $user){
				if(!$user->is_email_verified) Delete::main($user->id);
				elseif($user->is_email_verified) {
					if($user->username===$username) $checker['username_exists'] = true;
					if($user->email===$email) $checker['email_exists'] = true;
				}
			}
		}
		// create account
		if(!$checker['username_syntax_error'] && !$checker['email_syntax_error'] && !$checker['password_entropy_error'] && !$checker['username_exists'] && !$checker['email_exists']){
			$user = new User();
			$user->username = $username;
			$user->email = $email;
			$user->password = password_hash($password, PASSWORD_DEFAULT);
			$user->usertype = $usertype;
			$user->userstatus = 'pending';
			$user->is_email_verified = false;
			$user->save();
			$user_id = $user->id;
			return (object) [
				'is_success' => true,
				'user_id'=> $user_id
			];
		}
		else return (object) ['is_success'=>false, ...$checker];
	}


	/**
	 * verify new account
	 * 
	 * @param int $user_id
	 * 
	 * @return void
	 * 
	 * @since   🌱 0.0.0
	 * @version 🌴 0.0.0
	 * @author  ✍ Muhammad Mahmudul Hasan Mithu
	 */
	public static function verifyNewAccount(int $user_id): void
	{
		User::where('id', $user_id)->update([
			'is_email_verified'=>true,
			'userstatus'=>'active'
		]);
	}


}
