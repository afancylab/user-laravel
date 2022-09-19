<?php
declare(strict_types=1);
namespace User\Security;


class Password
{

	/**
	 * special characters
	 * 
	 * @property $special_chars
	 * 
	 * @since   🌱 0.0.0
	 * @version 🌴 0.0.0
	 * @author  ✍ Muhammad Mahmudul Hasan Mithu
	 */
	public static string $special_chars = " !\"#$%&'()*+,-./:;<=>?@[\]^_`{|}~";


	/**
	 * generate random password
	 * 
	 * @param int  $length length of the password
	 * @param bool $lowercase
	 * @param bool $uppercase
	 * @param bool $numbers
	 * @param bool $special_chars
	 * 
	 * @return string password
	 * 
	 * @since   🌱 0.0.0
	 * @version 🌴 0.0.0
	 * @author  ✍ Muhammad Mahmudul Hasan Mithu
	 */
	public static function generate(int $length=16, bool $lowercase=true, bool $uppercase=true, bool $numbers=true, bool $special_chars=true): string
	{
		if($length<1) throw new \Exception('Password length must be greater than 0');

		$combination = ($lowercase?'l':'').($uppercase?'u':'').($numbers?'n':'').($special_chars?'s':'');
		if($combination==='') throw new \Exception('Requires at least one character set');
		if($special_chars && self::$special_chars==='') throw new \Exception('Requires at least one character from $special_chars');

		$password = '';
		for ($i=0; $i < $length; $i++) {
			$password .= match($combination[random_int(0, strlen($combination)-1)]){
				'l' => range('a', 'z')[random_int(0, 25)],
				'u' => range('A', 'Z')[random_int(0, 25)],
				'n' => random_int(0, 9),
				's' => self::$special_chars[random_int(0, strlen(self::$special_chars)-1)]
			};
		}
		return $password;
	}


	/**
	 * Find out the strength of a password
	 * 
	 * @param string $password
	 * 
	 * @return int strength
	 * - upto 25				: poor password
	 * - 26 to 50				: weak password
	 * - 51 to 75				: reasonable password
	 * - 76 to 100			: good password
	 * - more than 100	: excellent password
	 * 
	 * @since   🌱 0.0.0
	 * @version 🌴 0.0.0
	 * @author  ✍ Muhammad Mahmudul Hasan Mithu
	 */
	public static function entropy(string $password): int
	{
		$char_size =
		(preg_match('/[a-z]/', $password)?26:0)+
		(preg_match('/[A-Z]/', $password)?26:0)+
		(preg_match('/[0-9]/', $password)?10:0)+
		(preg_match("/[".preg_quote(self::$special_chars, '/')."]/", $password)===1?strlen(self::$special_chars):0)+
		strlen(preg_replace("/[a-zA-Z0-9".preg_quote(self::$special_chars, '/')."]*/", '', $password));
		if($char_size>128) $char_size = 128;

		$length = strlen($password);
		if($length>128) $length = 128;

		return (int) round(log($char_size**$length, 2));
	}

}
