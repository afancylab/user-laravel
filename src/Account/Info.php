<?php
declare(strict_types=1);
namespace User\Account;

use App\Models\User;
use Illuminate\Support\{Str, Facades\Storage};
use User\Security\{Password, Syntax};

class Info
{


	/**
	 * @property array $endorsement
	 * 
	 * @since   🌱 0.0.0
	 * @version 🌴 0.0.0
	 * @author  ✍ Muhammad Mahmudul Hasan Mithu
	 */
	public array $endorsement = [];


	/**
	 * @property string $file_path
	 * 
	 * @since   🌱 0.0.0
	 * @version 🌴 0.0.0
	 * @author  ✍ Muhammad Mahmudul Hasan Mithu
	 */
	public string $file_path = '';


	/**
	 * @property array $file_names
	 * 
	 * @since   🌱 0.0.0
	 * @version 🌴 0.0.0
	 * @author  ✍ Muhammad Mahmudul Hasan Mithu
	 */
	public array $file_names = [];


	/**
	 * construct
	 * 
	 * @param int $user_id
	 * @param string $endorsement
	 * 
	 * @since   🌱 0.0.0
	 * @version 🌴 0.0.0
	 * @author  ✍ Muhammad Mahmudul Hasan Mithu
	 */
	function __construct(public int $user_id, string $endorsement)
	{
		$this->endorsement = config("user.info.endorsement.$endorsement", []);
		$this->file_path   = config('user.info.file.path', 'user');
		$this->file_names  = config('user.info.file.names', []);
	}


	/**
	 * set info
	 * 
	 * @param array [ key => value ]
	 * 
	 * @return bool
	 * 
	 * @since   🌱 0.0.0
	 * @version 🌴 0.0.0
	 * @author  ✍ Muhammad Mahmudul Hasan Mithu
	 */
	public function set(array $pairs): bool
	{
		$pairs = collect($pairs)->mapWithKeys(function($e, $k){
			if(gettype($k)==='string') $k = htmlspecialchars(strtolower(trim($k)));
			return [$k=>$e];
		})->filter(fn($v, $k)=>collect($this->endorsement)->contains($k));
		$file_keys = $pairs->intersectByKeys($this->file_names)->keys()->toArray();
		$pairs = $pairs->toArray();

		// remove current files
		if($file_keys){
			$file_values = User::where('id', $this->user_id)->first($file_keys)->toArray();
			foreach($file_values as $key=>$value){
				if($value) Storage::delete("$this->file_path/$key/$value");
			}
		}

		// process info
		foreach($pairs as $key=>$value){
			if(in_array($key, ['username', 'email', 'password'])){
				switch($key){
					case 'username':
						if(!(Syntax::username($value) && self::getId($value, 'u')===0))
						return false;
						break;
					case 'email':
						if(!(Syntax::email($value) && self::getId($value, 'u')===0))
						return false;
						break;
					case 'password':
						if(Password::entropy($value)<51) return false;
						else $pairs[$key] = password_hash($value, PASSWORD_DEFAULT);
						break;
				}
			}
			else if(in_array($key, $file_keys)){
				if(gettype($value)==='object' && $value->extension() && $value->path() && in_array($value->extension(), $this->file_names[$key]['extensions'], true)){
					$filename = STR::random(40).'.'.$value->extension();
					Storage::putFileAs("$this->file_path/$key/", $value->path(), $filename);
					$pairs[$key] = $filename;
				}
				else $pairs[$key] = null;
			}
		}

		return (bool) User::where('id', $this->user_id)->update($pairs);
	}


	/**
	 * get info
	 * 
	 * @param array $keys
	 * 
	 * @return array  [ key => value ]
	 * 
	 * @since   🌱 0.0.0
	 * @version 🌴 0.0.0
	 * @author  ✍ Muhammad Mahmudul Hasan Mithu
	 */
	public function get(array $keys): array
	{
		$keys = collect($keys)->map(fn($e)=>htmlspecialchars(strtolower(trim($e))))->unique()->intersect(collect($this->endorsement));
		$file_keys = $keys->intersect(collect($this->file_names)->keys());

		if(!$keys->toArray()) return [];
		$values = User::where('id', $this->user_id)->first($keys->toArray());

		if($values && $values = $values->toArray()){
			foreach($values as $key=>$value){
				if($file_keys->contains($key)){
					if(!$value && $default=$this->file_names[$key]['default']??null ) $value = $default;
					if($value) $values[$key] = rawurlencode(Storage::url("$this->file_path/$key/$value"));
				}
			}
		}

		return $values ?? [];
	}


	/**
	 * # get user_id based on user_id, username, email
	 * also get the info of user existence
	 * It supports complex column names
	 * 
	 * @param int|string $value
	 *                   user_id | username | email
	 * @param string     $column
	 *                   specify search column
	 *                   - `i` for id
	 *                   - `u` for username
	 *                   - `e` for email
	 *                   - `iue` for id, username, email
	 * 
	 * @return int  0 or >0
	 *              - \>0  - user exists
	 *              - 0  - user does not exists
	 * 
	 * @since   🌱 0.0.0
	 * @version 🌴 0.0.0
	 * @author  ✍ Muhammad Mahmudul Hasan Mithu
	 */
	public static function getId(int|string $value, string $column='iue' ): int
	{
		$pairs = [ 'i' => 'id', 'u' => 'username', 'e' => 'email' ];
		$user_id = 0;

		foreach(mb_str_split($column) as $c){
			if(array_key_exists($c, $pairs)){
				if($user_id===0) $user_id = (int) User::where( $pairs[$c], $value )->value('id');
				else break;
			}
		}
		return $user_id;
	}


}
