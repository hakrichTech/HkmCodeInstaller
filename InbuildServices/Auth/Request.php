<?php
namespace Hkm_services\Auth;


use Hkm_code\Entity\Entity;


final class Request extends Entity
{
    protected  $datamap = [];
	protected  $dates   = [
		'created_at',
		'updated_at',
		'deleted_at',
	];
	protected  $casts   = [];


    public  function setId($user_id)
	{
		$this->attributes['user'] = $user_id;

		return $this;
	}
    public  function setType($value)
	{
		$this->attributes['type'] = $value;

		return $this;
	}

    public  function setTimestamp($value)
	{
		$this->attributes['timestamp'] = $value;

		return $this;
	}

    public  function setHash($value)
	{
		$this->attributes['hash'] = $value;

		return $this;
	}
}
