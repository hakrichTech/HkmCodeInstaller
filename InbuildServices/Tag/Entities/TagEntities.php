<?php
namespace Hkm_services\Tag\Entities;

use Hkm_code\Entity\Entity;
use Hkm_code\Vezirion\ServicesSystem;
use Hkm_services\Tag\TagInterface;
use Hkm_services\Blog\BlogSystemProvider;

class TagEntities extends Entity implements TagInterface
{
	protected  $datamap = [
		'name'=>'tag_name',
		'tagIdentifier'=>'tag_id'
	];
	protected $dates   = [
		'created_at',
		'updated_at',
		'deleted_at',
	];
	protected $casts   = [];
	protected $blogs   = [];

	public function setTagId($value)
	{
		$this->attributes['tag_id'] = $value;
		return $this;
	}

	public function sync()
	{
		$prov = new BlogSystemProvider();
		$this->blogs = $prov->retrieveByTag($this);

		return $this;
	}

	public function setTagName($value)
	{
		$this->attributes['tag_name'] = $value;
		return $this;
		
	}
    public function setTagSlug($value)
    {
        $this->attributes['tag_slug'] = $value;
		return $this;
    }
	public function setMetaTitle($value)
	{
		$this->attributes['meta_title'] = $value;
		return $this;
	}
	public function getName()
	{
		return $this->attributes['tag_name'];
	}
	public function getMetaTitle()
	{
		return $this->attributes['meta_title'];
	}

	public function getTagIdentifier()
	{
		return $this->attributes['tag_id'];
		
	}

	public function blogs(){
       return $this->blogs;
	}

	public function getUrl()
	{
		return $this->attributes['tag_slug'];
	}

	public function jsonSync()
	{
		$ar = [
			$this->getTagIdentifier()."_tag"=>[
				'name'=>ucfirst($this->getName()),
				'url'=>$this->getUrl()
			]
			
		];

		if(isset(self::$arrayData['tag'])) self::$arrayData['tag'] = array_merge(self::$arrayData['tag'],$ar);
		else self::$arrayData['tag'] = $ar;
		
		if (isset(self::$jsonData['tag'])) self::$jsonData['tag']  = array_merge(self::$jsonData['tag'],$ar);
		else self::$jsonData['tag'] = $ar;

		return [];

	}
	public static function jsonReturn()
	{
		$body = ServicesSystem::FORMAT()::GET_FORMATTER("application/json")::FORMAT(self::$jsonData['tag']??[]);
		return $body;
	}



}
