<?php
namespace Hkm_services\Blog\Entities;

use Hkm_code\Entity\Entity;
use Hkm_services\Blog\BlogSystemProvider;
use Hkm_services\Blog\BlogCategInterface;
use Hkm_services\Category\CategoryUtility;
use Hkm_services\Category\CategoryProvider;

class BlogCategEntities extends Entity implements BlogCategInterface
{

    protected $datamap = [
        'category' => 'category_id',
        'blog' => 'post_id'

    ];
	protected $dates   = [
		'created_at',
		'updated_at',
		'deleted_at',
	];
    protected $categData = [
        'post_id'=>'',
        'category_id'=> '',
    ];
    protected $categDataPrepare = [
        'categories'=> '',
    ];
	protected $casts   = [];

    public function setPostId($value)
    {
        $this->attributes['post_id'] = $value;
        return $this;
    }
    public function setCategoryId($value)
    {
        $this->attributes['category_id'] = $value;
        return $this;
    }
    
    public function getBlog()
    {
        $bsystem = new BlogSystemProvider();
        return $bsystem->retrieveById($this->attributes['post_id']);
    }


    public function getCateg()
    {
        $tprovider = new CategoryProvider();
        return $tprovider->retrieveById($this->attributes['category_id']);
    }
    public function setCategories(string $Category)
    {
        $this->categDataPrepare['categories'] =  $Category;
        return $this;
    }

    public function init(array &$data)
    {
        hkm_fill_data($this->categData,$data,['postId']);
        hkm_fill_data($this->categDataPrepare,$data,['postId']);

        $Categorys = CategoryUtility::MAKE_CATEG_ARRAY($this->categDataPrepare['categories']);
        $t = [];
        if(count($Categorys)>1){
          foreach ($Categorys as $Category) {
            $clonedCategory = clone $this;
            $clonedCategory->setCategories($Category);
            $t[] = $clonedCategory;
          }
        }else{
            if(count($Categorys)==0||empty($this->categDataPrepare['categories'])){
                return [];
            }else $t[] = $this;
        }

        
        return $t;
    }

    public function save()
    {
       $bsystem = new BlogSystemProvider();
       $categ = (int) $this->categDataPrepare['categories'];
       if($categ>0){
          $this->categData['category_id'] = $categ;
          $bsystem->insertCategPost($this->categData);
       }else{
         $categSystem = new CategoryProvider();
         $id = $categSystem->addCategories($categ);
         $this->categData['category_id'] = $id[0];
         $bsystem->insertCategPost($this->categData);

       }
    }
}