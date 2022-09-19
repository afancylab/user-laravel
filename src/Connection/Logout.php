<?php
declare(strict_types=1);
namespace User\Connection;

use App\Models\Login;


class Logout
{


	/**
	 * logout by token
	 * 
	 * @param string $token
	 * 
	 * @return void
	 * 
	 * @since   🌱 0.0.0
	 * @version 🌴 0.0.0
	 * @author  ✍ Muhammad Mahmudul Hasan Mithu
	 */
	public static function token( string $token ): void {Login::where('token', $token)->delete();}


	/**
	 * logout by user_id
	 * 
	 * @param int $user_id
	 * 
	 * @return void
	 * 
	 * @since   🌱 0.0.0
	 * @version 🌴 0.0.0
	 * @author  ✍ Muhammad Mahmudul Hasan Mithu
	 */
	public static function userId( int $user_id ): void {Login::where('user_id', $user_id)->delete();}


}
