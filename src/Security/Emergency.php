<?php
declare(strict_types=1);
namespace User\Security;

use App\Models\User;
use User\Connection\Logout;

class Emergency
{


	/**
	 * block an account
	 * 
	 * @param int $user_id
	 * 
	 * @return void
	 * 
	 * @since   🌱 0.0.0
	 * @version 🌴 0.0.0
	 * @author  ✍ Muhammad Mahmudul Hasan Mithu
	 */
	public static function block(int $user_id): void
	{
		if(User::where(['id'=>$user_id, 'userstatus'=>'active'])->update(['userstatus' => 'block']))
		Logout::userId($user_id);
	}


	/**
	 * unblock an account
	 * 
	 * @param int $user_id
	 * 
	 * @return void
	 * 
	 * @since   🌱 0.0.0
	 * @version 🌴 0.0.0
	 * @author  ✍ Muhammad Mahmudul Hasan Mithu
	 */
	public static function unblock(int $user_id): void
	{
		User::where(['id'=>$user_id, 'userstatus'=>'block'])->update(['userstatus' => 'active']);
	}


}
