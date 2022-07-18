<?php

namespace Hkm_services\ShortenUrl\Entities;

use Hkm_code\Entity\Entity;

class ShortenUrl extends Entity
{
	protected  $datamap = [];
	protected  $dates   = [
		'created_at',
		'updated_at',
		'deleted_at',
	];
	protected  $casts   = [];

	public  function setShorten_url(string $email)
	{
		$this->attributes['shorten_url'] = $email;

		return $this;
	}

	public  function setId(string $email)
	{
		$this->attributes['id'] = $email;
		return $this;
		
	}

	public  function setFull_url(string $PAS)
	{
		$this->attributes['full_url'] = $PAS;
		return $this;
		
	}

	
}
