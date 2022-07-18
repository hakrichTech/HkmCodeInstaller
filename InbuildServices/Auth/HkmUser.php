<?php
namespace Hkm_services\Auth;



use Hkm_code\Entity\Entity;


/**
 * User API: HkmUser class
 *
 * @package HkmCode
 * @subpackage HkmUsers
 */

/**
 * Core class used to implement the HkmUser object.
 *
 *
 * @property string $nickname
 * @property string $description
 * @property string $user_description
 * @property string $first_name
 * @property string $user_firstname
 * @property string $last_name
 * @property string $user_lastname
 * @property string $user_login
 * @property string $user_pass
 * @property string $user_nicename
 * @property string $user_email
 * @property string $user_url
 * @property string $user_registered
 * @property string $user_activation_key
 * @property string $user_status
 * @property int    $user_level
 * @property string $display_name
 * @property string $spam
 * @property string $deleted
 * @property string $created
 * @property string $updated
 */
class HkmUser extends Entity implements HkmUserInterface
{
    protected $datamap = [];
	protected $dates   = [
		'created_at',
		'updated_at',
		'deleted_at',
	];
	protected $casts   = [];

	public function setUsername(string $username)
	{
		$this->attributes['username'] = $username;

		return $this;
	}

	public function getUsername()
	{
		return $this->attributes['username'];
	}

	public function getFullname()
	{
		return $this->attributes['userFullname'];
	}

	public function setAvatar(string $value)
	{
		$this->attributes['avatar'] = $value;
		return $this;
	}

	public function getProfile()
	{
		return $this->attributes['avatar'];
		
	}

	public function getGender()
	{
		return $this->attributes['gender'];
	}

	public function setAddress(HkmUserAddressInterface $value)
	{
		$this->attributes['address'] = $value;
		return $this;
	}

	public function getAddress()
	{
		$this->attributes['address']->setUser_id($this->getAuthIdentifier());
		return $this->attributes['address'];
	}

	public function setGender(string $value)
	{
		$this->attributes['gender'] = $value;
		return $this;
	}

	public function update()
	{
		$or = $this->original;
		$data = [];
		foreach ($this->attributes as $key => $value) {
			if (isset($or[$key])){

				if ($or[$key]!=$value){
					if ($value != '' || $value != 'Not set') {
						$data[$key] = $value;
					}
				}
			}
		}
		
		if (!empty($data)) {
			$auth = new AuthProvider();
			$auth->update($this,$data);
		}
		return true;
	}

	public function getPhone()
	{
		return $this->attributes['phone'];
	}
	public function setEmail(string $email)
	{
		$this->attributes['email'] = $email;
		return $this;
		
	}

	public function getEmail()
	{
		return $this->attributes['email'];		
	}

    public function setUserfullname(string $value)
    {
		$this->attributes['userFullname'] = $value;
		return $this;
        
    }
    public function setPassword( $value)
    {
		$this->attributes['password'] = $value;
		return $this;
        
    }

    public function setPhone( $value)
    {
		$this->attributes['phone'] = $value;
		return $this;
        
    }

    public function setBirthdate( $value)
    {
		$this->attributes['birthDate'] = $value;
		return $this;
        
    }

	public function getBirthdate()
	{
		return $this->attributes['birthDate'];
	}
    public function setVerified( $value)
    {
		$this->attributes['verified'] = $value;
		return $this;
        
    }
	public function setUser_id($value)
	{
		$this->attributes['user_id'] = $value;
		return $this;

	}


	/**
	 * Get the unique identifier for the user.
	 *
	 * @return mixed
	 */
	public function getAuthIdentifier()
	{
		return $this->attributes['user_id'];
	}

	/**
	 * Get the password for the user.
	 *
	 * @return string
	 */
	public function getAuthPassword()
	{
		return $this->attributes['password'];
	}

	/**
	 * Check if user is verified.
	 *
	 * @return bool
	 */
	public function isVerified()
	{
		if($this->attributes['verified'] == 'true') return true;
		else return false;
	}

	/**
	 * Get the attempt number of login.
	 *
	 * @return bool
	 */
	public function getAttemptNumber()
	{

		return $this->attributes['attemptNumber'];
	}

	public function setAttemptnumber( $value)
    {
		$this->attributes['attemptNumber'] = $value;
        
    }

	public function setPermission(array $value)
	{
		$this->attributes['permissions'] = $value;
	}
	public function setAdmin(bool $value)
	{
		$this->attributes['isAdmin'] = $value;
		return $this;
	}

	public function hasPermission(int $permission)
	{
		if (isset($this->attributes['permissions'])) {
			return in_array($permission,$this->attributes['permissions']);
		}else{
			return false;
		}
	}

	public function isAdmin()
	{
		if (isset($this->attributes['isAdmin'])) {
			return $this->attributes['isAdmin'];
		}
		return false;
	}

	public function getPermissions()
	{
		if (isset($this->attributes['permissions'])) {
			return $this->attributes['permissions'];
		}else{
			return [];
		}
	}

	public function setApp(array $value)
	{
		$this->attributes['app'] = $value;
		return $this;
	}

	public function getApp()
	{
		if (isset($this->attributes['app'])) {
			return $this->attributes['app'][0];
		}else{
			return 'Not set';
		}
	}

	public function init($value)
	{
		$authPro = new AuthProvider();
		$user = $authPro->retrieveUser($value);
		return $user;
	}

	public function getAppIndentifier()
	{
		if (isset($this->attributes['app'])) {
			return $this->attributes['app'][1];
		}else{
			return 'Not set';
		}
	}





	/**
	 * Get the token value for the "remember me" session.
	 *
	 * @return string
	 */
	public function getRememberToken()
	{
		return $this->attributes[self::getRememberTokenName()];
	}

	/**
	 * Set the token value for the "remember me" session.
	 *
	 * @param  string  $value
	 * @return void
	 */
	public function setRememberToken($value)
	{
		$this->attributes[self::getRememberTokenName()] = $value;
		return $this;
	}

	/**
	 * Get the column name for the "remember me" token.
	 *
	 * @return string
	 */
	public function getRememberTokenName()
	{
		return 'remember_token';
	}

	/**
	 * Dynamically access the user's attributes.
	 *
	 * @param  string  $key
	 * @return mixed
	 */
	public function __get($key)
	{
		return $this->attributes[$key];
	}

	/**
	 * Dynamically set an attribute on the user.
	 *
	 * @param  string  $key
	 * @param  mixed   $value
	 * @return void
	 */
	public function __set($key, $value=null)
	{
		$this->attributes[$key] = $value;
	}

	/**
	 * Dynamically check if a value is set on the user.
	 *
	 * @param  string  $key
	 * @return bool
	 */
	public function __isset(string $key):bool
	{
		return isset($this->attributes[$key]);
	}

	/**
	 * Dynamically unset a value on the user.
	 *
	 * @param  string  $key
	 * @return void
	 */
	public function __unset( string $key):void
	{
		unset($this->attributes[$key]);
	}
}
