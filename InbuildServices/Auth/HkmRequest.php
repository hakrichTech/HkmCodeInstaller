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
 * @property string $deleted
 * @property string $created
 * @property string $updated
 */
class HkmRequest extends Entity implements HkmRequestInterface
{
    protected $datamap = [];
	protected $dates   = [
		'created_at',
		'updated_at',
		'deleted_at',
	];
	protected $casts   = [];

	public function setUser(string $value)
	{
		$this->attributes['user'] = $value;

		return $this;
	}

    public function setId(string $value)
	{
		$this->attributes['id'] = $value;

		return $this;
	}

	public function setHash(string $value)
	{
		$this->attributes['hash'] = $value;
		return $this;
		
	}

	public function setTimestamp(string $value)
	{
		$this->attributes['timestamp'] = $value;
		return $this;

	}

    public function setType(string $value)
    {
		$this->attributes['type'] = $value;
        return $this;
        
    }
    public function setToken( $value)
    {
		$this->attributes['token'] = $value;
        return $this;
        
    }



	/**
	 * Get the unique identifier for the user.
	 *
	 * @return HkmUserInterface
	 */
	public function getUser()
	{
       $id = $this->attributes['user'];
       return (new AuthProvider())->retrieveById($id);
	}

    public function getHash()
    {
        return $this->attributes['hash'];
    }

    public function getRequest()
    {
        return (int) $this->attributes['type'];
    }
    
    public function getToken()
    {
        return $this->attributes['token'];
    }

    public function getPayload()
    {
        $auth = new AuthProvider();
        return $auth->validateToken($this->attributes['token']);
    }

	public function delete()
	{
        $auth = new AuthProvider();
		$auth->deleteRequest($this->getToken());
	}

    public function isRequestValid()
    {
        if($this->getPayload())return true;
        else return false;
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
