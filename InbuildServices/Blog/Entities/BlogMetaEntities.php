<?php
namespace Hkm_services\Blog\Entities;

use Hkm_code\Entity\Entity;
use Hkm_services\Blog\BlogMetaInterface;
use Hkm_services\Blog\BlogSystemProvider;

class BlogMetaEntities extends Entity implements BlogMetaInterface
{

    protected $datamap = [];
	protected $dates   = [
		'created_at',
		'updated_at',
		'deleted_at',
	];
	protected $casts   = [];

    protected $blogMeta = [
        'name'=> '',
        'post_id'=>'',
        'content'=> ''
    ];

    protected $blogMetaPepare = [
        'comment_permission'=> 1,
        'header_image'=>'https://imge.com/file.png',
        'visibility'=> 'private'
    ];

    protected $blogMetaDefaults = [
        'comment_permission'=> 1,
        'header_image'=>'https://imge.com/file.png',
        'visibility'=> 'private'
    ];


    public function setCommentPermission($value)
    {
        $this->attributes['comment_permission'] = $value;
        return $this;
    }

    public function setHeaderImage($value)
    {

        $this->attributes['header_image'] = $value;
        return $this;
    }

    public function setVisibility($value)
    {
        $this->attributes['visibility'] = $value;
        return $this;
    }

    public function getVisibility()
    {
        return $this->attributes['visibility'];
    }
    public function getHeaderImage()
    {
        return $this->attributes['header_image'];
    }
    public function getCommentPermission()
    {
        return (int) ($this->attributes['comment_permission']) == 1;
    }

    public function init(array &$data)
    {
        hkm_fill_data($this->blogMeta,$data,['postId']);
        hkm_fill_data($this->blogMetaPepare,$data,['postId']);
        
        return $this;
    }

    public function save()
    {
        $bsystem = new BlogSystemProvider();
        foreach ($this->blogMetaPepare as $key => $value) {
            $this->blogMeta['name']=$key;
            $this->blogMeta['content']=$value;
            $bsystem->insertMetas(
                $this->blogMeta,
                $this->blogMetaDefaults[$key]
            );

        }
        return 1;
    }

}