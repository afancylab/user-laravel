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
	 * @version 🌴 0.1.0
	 * @author  ✍ Muhammad Mahmudul Hasan Mithu
	 */
	public static function main( string $token )
	{
		if($token && $key = Login::where('token', $token)->value('token_key'))
		return JWT::decode($token, $key);
		return false;
	}


}
