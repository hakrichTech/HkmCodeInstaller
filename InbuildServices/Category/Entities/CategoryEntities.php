<?php
namespace Hkm_services\Category\Entities;

use Hkm_code\Entity\Entity;
use Hkm_code\Vezirion\ServicesSystem;
use Hkm_services\Blog\BlogSystemProvider;
use Hkm_services\Category\CategoryInterface;

class CategoryEntities extends Entity implements CategoryInterface
{
	protected  $datamap = [
		'categoryIdentifier'=>'category_id'
	];
	protected $dates   = [
		'created_at',
		'updated_at',
		'deleted_at',
	];

	protected $blogs   = [];

	protected $casts   = [];

	public function setCategoryId($value)
	{
		$this->attributes['category_id'] = $value;

		return $this;
	}

	public function sync()
	{
		$prov = new BlogSystemProvider();
		$this->blogs = $prov->retrieveByCategory($this);

		return $this;
	}

	public function setMetaTitle($value)
	{
		$this->attributes['meta_title'] = $value;
		return $this;
	}

	public function setName($value)
	{
		$this->attributes['name'] = $value;
		return $this;
		
	}
    public function setSlug($value)
    {
        $this->attributes['slug'] = $value;
		return $this;
    }

	public function getName()
	{
		return $this->attributes['name'];
	}
	public function getMetaTitle()
	{
		return $this->attributes['meta_title'];
	}

	public function getCategoryIdentifier()
	{
		return $this->attributes['category_id'];
		
	}

	public function blogs(){
       return $this->blogs;
	}

	public function getUrl()
	{
		return $this->attributes['slug'];
	}

	public function jsonSync()
	{
		$ar = [
			$this->getCategoryIdentifier()."_categ"=>[
				'name'=>ucfirst($this->getName()),
				'url'=>$this->getUrl()
			]
			
		];

		if(isset(self::$arrayData['categ'])) self::$arrayData['categ'] = array_merge(self::$arrayData['categ'],$ar);
		else self::$arrayData['categ'] = $ar;
		
		if (isset(self::$jsonData['categ'])) self::$jsonData['categ']  = array_merge(self::$jsonData['categ'],$ar);
		else self::$jsonData['categ'] = $ar;

		return [];

	}
	public static function jsonReturn()
	{
		$body = ServicesSystem::FORMAT()::GET_FORMATTER("application/json")::FORMAT(self::$jsonData['categ']??[]);
		return $body;
	}


}
