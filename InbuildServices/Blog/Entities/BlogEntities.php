<?php
namespace Hkm_services\Blog\Entities;

use Hkm_code\Entity\Entity;
use Hkm_code\Vezirion\ServicesSystem;
use Hkm_services\Auth\HkmUser;
use Hkm_services\Auth\HkmUserInterface;
use Hkm_services\Blog\BlogInterface;
use Hkm_services\Blog\BlogSystemProvider;

class BlogEntities extends Entity implements BlogInterface
{
    protected $datamap = [
        'author' => 'user_id',
        'url' => 'slug',
        'blogIdentifier' => 'parent_id'

    ];
	protected $dates   = [
		'created_at',
		'updated_at',
		'deleted_at',
	];
	protected $casts   = [];

    protected $tags = [];
    protected $categs = [];

    /**
     * @var BlogMetaEntities
     */
    protected $metas;

    /**
     * @var HkmUserInterface
     */
    protected $author;

    protected static $id = [];

    protected $blogData = [
        'user_id'=>'',
        'parent_id'=> '',
        'title'=>'No Title',
        'meta_title'=>'No Meta Title',
        'slug'=>'',
        'summary'=>'No summary',
        'published'=>0,
        'content'=>''
    ];


    public function setParentId($value)
    {
        
        $this->attributes['parent_id'] = $value;
        return $this;

    }

    public function setUserId($value)
    {
        if (!empty($value)) {
            $this->attributes['user_id'] = $value;
            $user = new HkmUser();
            $this->author = $user->init($value);
        }
        return $this;

    }

    public function setMetaTitle($value)
    {
        $this->attributes['meta_title'] = $value;
        return $this;

    }

    public function setContent($value)
    {
        $this->attributes['content'] = $value;
        return $this;

    }

    public function setPublished($value)
    {
        $this->attributes['published'] = $value;
        return $this;

    }

    public function setTitle($value)
    {
        $this->attributes['title'] = $value;
        return $this;

    }
    public function setSlug($value)
    {
        $this->attributes['slug'] = $value;
        return $this;

    }
    public function setSummary($value)
    {
        $this->attributes['summary'] = $value;
        return $this;

    }

    public function getAuthor()
    {
        return $this->attributes['user_id'];

    }
    public function getAuthorObject()
    {
        return $this->author;

    }
    public function getBlogIdentifier()
    {
        return $this->attributes['parent_id'];

    }
    public function getContent()
    {
        return $this->attributes['content'];
    }

    public function getMetaTitle()
    {
        return $this->attributes['meta_title'];
    }

    public function getStatus()
    {
        $this->attributes['published'];
    }

    public function getSummary()
    {
        return $this->attributes['summary'];
    }

    public function getTitle()
    {
        return $this->attributes['title'];
    }

    public function getUrl()
    {
        return $this->attributes['slug'];
    }

    public function tags()
    {
        return $this->tags;
    }

    public function categories()
    {
        return $this->categs;
    }

    public function setTags($values)
    {
        $this->tags = $values;
        return $this;
    }

    public function setCategs($values)
    {
        $this->categs = $values;
        return $this;
    }

    public function init(array $data):int
    {
        
        $defaults = [
          'parentId'=> explode(' ',microtime())[1]
        ];

        $post_data = hkm_parse_args($data, $defaults);

        hkm_fill_data($this->blogData,$post_data,['parentId']);
        
        $post_data['postId'] = $post_data['parentId'];
        unset($post_data['parentId']);


        $this->metas = new BlogMetaEntities();
        $this->metas->init($post_data);

        $tags = new BlogTagEntities();
        $this->tags = $tags->init($post_data);

        $categs = new BlogCategEntities();
        $this->categs = $categs->init($post_data);

        return $defaults['parentId'];
    }


    public function save()
    {
        if (empty($this->blogData['user_id']) || empty($this->blogData['title'])) return 0;
        else{
           $bsystem = new BlogSystemProvider();
           if($bsystem->checkBlog($this->blogData)){
             unset($this->blogData['user_id']);
             $bsystem->update($this->blogData);
           }else $bsystem->insert($this->blogData);
           foreach ($this->tags as $tag) {
             $tag->save();
           }

           foreach ($this->categs as $categ) {
            $categ->save();
           }

            $this->metas->save();
        }

        
        
    }

    public function setMetas($values)
    {
        

        $d = [];
        if(!empty($values)){
            if(hkm_isAssoc($values)){
                $d = [$values['name']=>$values['content']];
            }else{
                foreach ($values as $value) {
                    $d[$value['name']] = $value['content'];
                }
            }
            $this->metas = new BlogMetaEntities($d);
        }
        $this->updateJsonSync();
        return $this;
    }

    public function getOptions()
    {
        return $this->metas;
    }

    public function updateJsonSync()
    {

       

        $up =[
        'is_featureImage'=> $this->metas->getHeaderImage()!='https://imge.com/file.png',
        'featureImage' => hkm_site_url("file/".$this->metas->getHeaderImage())
        ];
        if(isset(self::$jsonData[$this->getBlogIdentifier()])){
            self::$jsonData['Blog'][$this->getBlogIdentifier()] = array_merge(self::$jsonData['Blog'][$this->getBlogIdentifier()],$up);
            self::$arrayData['Blog'][$this->getBlogIdentifier()] = array_merge(self::$arrayData['Blog'][$this->getBlogIdentifier()],$up);
        }else{
            if(in_array($this->getBlogIdentifier(),self::$id)){
               $index = array_search($this->getBlogIdentifier(),self::$id);
               self::$jsonData['Blog'][$index] = array_merge(self::$jsonData['Blog'][$index],$up);
               self::$arrayData['Blog'][$index] = array_merge(self::$arrayData['Blog'][$index],$up);
               self::$arrayData['Blog'][$this->getBlogIdentifier()] = self::$arrayData['Blog'][$index];
               self::$jsonData['Blog'][$this->getBlogIdentifier()] = self::$jsonData['Blog'][$index];
               unset(self::$jsonData['Blog'][$index]);
               unset(self::$arrayData['Blog'][$index]);
            }
        }

        return $this;
    }


    public function jsonSync()
	{
        self::$id[] = $this->getBlogIdentifier();
        hkm_helper('url');
        $ar = [
			$this->getBlogIdentifier()=>[
                'title' => $this->getTitle(),
                'summary'=> $this->getSummary(),
                'contents'=>$this->getContent(),
                'link'=>$this->getUrl(),
                'author'=> [
                    'name'=>$this->getAuthor()

                ],
			]
			
		];

		if(isset(self::$arrayData['Blog'])) self::$arrayData['Blog'] = array_merge(self::$arrayData['Blog'],$ar);
		else self::$arrayData['Blog'] = $ar;

        if (isset(self::$jsonData['Blog'])) self::$jsonData['Blog']  = array_merge(self::$jsonData['Blog'],$ar);
		else self::$jsonData['Blog'] = $ar;


    }

    public static function jsonReturn()
	{
		$body = ServicesSystem::FORMAT()::GET_FORMATTER("application/json")::FORMAT(self::$jsonData['Blog']??[]);
		return $body;
	}


}
