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
class HkmUserAddress extends Entity implements HkmUserAddressInterface
{
    protected $datamap = [];
	protected $dates   = [
		'created_at',
		'updated_at',
		'deleted_at',
	];
	protected $casts   = [];


	public function setAddress($value)
	{
		$this->attributes['address'] = $value;

		return $this;
	}

	public function getAddress()
	{
		return $this->attributes['address'];
	}

	public function setCountry($value)
	{
		$this->attributes['country'] = $value;
		return $this;
	}

	public function getCountry()
	{
		return $this->attributes['country'];
	}

    public function setPostalcode($value)
	{
		$this->attributes['postalcode'] = $value;
		return $this;
	}

	public function getPostalcode()
	{
		return $this->attributes['postalcode'];
	}


	public function update()
	{
		$or = $this->original;
		$data = [];
		$arr = array_keys($or);

		if ($or['id']!='') {
			foreach ($this->attributes as $key => $value) {

				if (in_array($key,$arr)){
	               
					if (!empty(trim($value))){
                        
						if ($value != $or[$key] || $value != 'Not set') {
							$data[$key] = $value;
						}
					}
				}
			}
		}else{

			foreach ($this->attributes as $key => $value) {

				if (in_array($key,$arr)){

					if ($key!= 'created_at' && $key!= 'updated_at' && $key!= 'deleted_at'){
							$data[$key] = $value;
					}
				}
				
			}
		}
		
		
		if (!empty($data)) {
			$auth = new AuthProvider();
			$auth->updateAddress($this,$data);
		}
		return true;
	}

	public function setState($value)
	{
		$this->attributes['state'] = $value;
		return $this;
	}
	public function getState()
	{
		return $this->attributes['state'];
	}
	public function setUser_id($value)
	{
		$this->attributes['user_id'] = $value;
		return $this;

	}

	public function setId($value)
	{
		$this->attributes['id'] = $value;
		return $this;

	}

	public function getAddressIdentifier()
	{
		return $this->attributes['id'];
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
	public function __isset($key):bool
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
