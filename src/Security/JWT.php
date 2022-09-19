<?php
declare(strict_types=1);
namespace User\Security;

use Firebase\JWT\{JWT as FirebaseJWT, Key};

class JWT
{


	/**
	 * encode data
	 * 
	 * @param array  $payload
	 * @param string $key (optional)
	 * 
	 * @return array jwt, key
	 * eg.
	 * $variable['jwt']
	 * $variable['key']
	 * 
	 * @since   🌱 0.0.0
	 * @version 🌴 0.0.0
	 * @author  ✍ Muhammad Mahmudul Hasan Mithu
	 */
	public static function encode( array $payload, string $key=null ): array
	{
		$alg = 'HS512';
		if($key===null) $key = bin2hex(random_bytes(32));

		$jwt = FirebaseJWT::encode($payload, $key, $alg);
		return [
			'jwt'=>$jwt,
			'key'=>$key
		];
	}


	/**
	 * decode data
	 * 
	 * @param string $jwt
	 * @param string $key
	 * 
	 * @return false|object
	 *                      - false,  when signature is invalid
	 *                      - object, when signature is valid, return the payload
	 * 
	 * @since   🌱 0.0.0
	 * @version 🌴 0.0.0
	 * @author  ✍ Muhammad Mahmudul Hasan Mithu
	 */
	public static function decode(string $jwt, string $key)
	{
		$alg = 'HS512';

		try{
			$payload = FirebaseJWT::decode($jwt, new Key($key, $alg));
		}
		catch(\Exception $e){
			return false;
		}

		return $payload;
	}


}
