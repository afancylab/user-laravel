<?php
declare(strict_types=1);
namespace User\Connection;

use App\Models\Login;
use User\Security\JWT;


class Authentication
{


	/**
	 * main authentication
	 * 
	 * @param string $token
	 * 
	 * @return false|object false | object
	 *                      - false  - failure
	 *                      - object - success
	 * 
	 * @since   🌱 0.0.0
	 * @version 🌴 0.0.0
	 * @author  ✍ Muhammad Mahmudul Hasan Mithu
	 */
	public static function main( string $token )
	{
		$key = Login::where('token', $token)->value('token_key');
		if($key) return JWT::decode($token, $key);

		return false;
	}


}
