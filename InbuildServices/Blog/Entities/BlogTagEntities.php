<?php
namespace Hkm_services\Blog\Entities;

use Hkm_code\Entity\Entity;
use Hkm_services\Blog\BlogSystemProvider;
use Hkm_services\Blog\BlogTagInterface;
use Hkm_services\Tag\TaggUtility;
use Hkm_services\Tag\TagProvider;

class BlogTagEntities extends Entity implements BlogTagInterface
{

    protected $datamap = [
        'tag' => 'tag_id',
        'blog' => 'post_id'

    ];
	protected $dates   = [
		'created_at',
		'updated_at',
		'deleted_at',
	];
    protected $tagData = [
        'post_id'=>'',
        'tag_id'=> '',
    ];
    protected $tagDataPrepare = [
        'tags'=> '',
    ];
	protected $casts   = [];

    public function setPostId($value)
    {
        $this->attributes['post_id'] = $value;
        return $this;
    }
    public function setTagId($value)
    {
        $this->attributes['tag_id'] = $value;
        return $this;
    }
    
    public function getBlog()
    {
        $bsystem = new BlogSystemProvider();
        return $bsystem->retrieveById($this->attributes['post_id']);
    }


    public function getTag()
    {
        $tprovider = new TagProvider();
        return $tprovider->retrieveById($this->attributes['tag_id']);
    }
    public function setTags(string $tag)
    {
        $this->tagDataPrepare['tags'] =  $tag;
        return $this;
    }

    public function init(array &$data)
    {
        hkm_fill_data($this->tagData,$data,['postId']);
        hkm_fill_data($this->tagDataPrepare,$data,['postId']);

        $tags = TaggUtility::MAKE_TAG_ARRAY($this->tagDataPrepare['tags']);
        $t = [];
        if(count($tags)>1){
          foreach ($tags as $tag) {
            $clonedTag = clone $this;
            $clonedTag->setTags($tag);
            $t[] = $clonedTag;
          }
        }else{
            if(count($tags)==0||empty($this->tagDataPrepare['tags'])){
                return [];
            }else $t[] = $this;
        }

        
        return $t;
    }

    public function save()
    {
       $bsystem = new BlogSystemProvider();
       $tag = (int) $this->tagDataPrepare['tags'];

       if($tag>0){
          $this->tagData['tag_id'] = $tag;
          $bsystem->insertTagPost($this->tagData);
       }else{
         $tagSystem = new TagProvider();
         $id = $tagSystem->addTags($tag);
         $this->tagData['tag_id'] = $id[0];
         $bsystem->insertTagPost($this->tagData);

       }
    }
}