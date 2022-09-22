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
	 * @return array
	 * 
	 * @since   🌱 0.0.0
	 * @version 🌴 0.1.0
	 * @author  ✍ Muhammad Mahmudul Hasan Mithu
	 */
	public function set(array $pairs): array
	{
		$file_keys = [];
		$config_file_names_only = collect($this->file_names)->keys()->toArray();
		$pairs = collect($pairs)->mapWithKeys(function($e, $k)use($config_file_names_only, &$file_keys){
			if(is_string($k) && ($e || is_bool($e) || is_numeric($e))){
				$k = htmlspecialchars(strtolower(trim($k)));
				if(in_array($k, $this->endorsement)){
					/** validate value */
					switch($k){
						case in_array($k, $config_file_names_only):
							if(is_object($e) && $e->extension() && $e->path() && in_array($e->extension(), $this->file_names[$k]['extensions'], true)){
								$file_keys[] = $k;
								// store new files in storage
								$filename = STR::random(40).'.'.$e->extension();
								Storage::putFileAs("$this->file_path/$k/", $e->path(), $filename);
								return [$k=>$filename];
							}
							break;
						case 'username':
							if(Syntax::username($e) && self::getId($e, 'u')===0) return [$k=>$e];
							break;
						case 'email':
							if(Syntax::email($e) && self::getId($e, 'e')===0) return [$k=>$e];
							break;
						case 'password':
							if(Password::entropy($e)>50) return [$k=>password_hash($e, PASSWORD_DEFAULT)];
							break;
						default:
						return [$k=>$e];
					}
				}
			}
			return [];
		})->toArray();

		// delete current files from storage
		if($file_keys){
			$file_values = User::where('id', $this->user_id)->first($file_keys)?->toArray();
			if($file_values){
				foreach($file_values as $key=>$value){
					if($value) Storage::delete("$this->file_path/$key/$value");
				}
			}
		}

		if($pairs) User::where('id', $this->user_id)->update($pairs);
		return array_keys($pairs);
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


	/**
	 * delete info
	 * 
	 * @param array $keys
	 * 
	 * @return array [ keys ]
	 * 
	 * @since   🌱 0.1.0
	 * @version 🌴 0.1.0
	 * @author  ✍ Muhammad Mahmudul Hasan Mithu
	 */
	public function delete(array $keys): array
	{
		$nullable = config('user.info.nullable', []);
		$keys = collect($keys)->map(fn($e)=>htmlspecialchars(strtolower(trim($e))))->unique()->intersect(collect($this->endorsement))->intersect($nullable);
		$file_keys = $keys->intersect(collect($this->file_names)->keys())->values()->toArray();
		$keys = $keys->toArray();

		if($keys){
			// delete current files from storage
			if($file_keys){
				$file_values = User::where('id', $this->user_id)->first($file_keys)?->toArray();
				if($file_values){
					foreach($file_values as $key=>$value){
						if($value) Storage::delete("$this->file_path/$key/$value");
					}
				}
			}

			// delete data from database
			$keyvalue = [];
			foreach($keys as $key) $keyvalue[$key] = null;
			User::where('id', $this->user_id)->update($keyvalue);
		}

		return $keys;
	}


}
