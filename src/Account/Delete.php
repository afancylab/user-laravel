<?php
declare(strict_types=1);
namespace User\Account;

use App\Models\{Login, User};


class Delete
{


	/**
	 * delete whole account
	 * 
	 * @param int $user_id
	 * 
	 * @return void
	 * 
	 * @since   🌱 0.0.0
	 * @version 🌴 0.0.0
	 * @author  ✍ Muhammad Mahmudul Hasan Mithu
	 */
	public static function main( int $user_id ): void
	{
		User::where( 'id', $user_id )->delete();
		Login::where( 'user_id', $user_id )->delete();
	}


}
