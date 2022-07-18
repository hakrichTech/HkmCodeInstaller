<?php
namespace Hkm_services\Blog;

use Hkm_services\Blog\Models\PostTagModel;
use Hkm_code\Database\BaseBuilder;
use Hkm_services\Tag\TagInterface;
use Hkm_services\Blog\Models\PostCategoryModel;
use Hkm_services\Blog\Models\PostMetaModel;
use Hkm_services\Blog\Models\PostModel;
use Hkm_services\Category\CategoryInterface;
use Hkm_services\Blog\BlogSystemProvideInterface;

class BlogSystemProvider implements BlogSystemProvideInterface
{
    
    /**
     * @var PostModel $postModel
     */
    protected $postModel;


    /**
     * @var BaseBuilder $postBuilder
     */
    protected $postBuilder;
    
    function __construct()
    {
        $model = new PostModel();
        $model::CHECK_ENGINE();
        $this->postBuilder = $model::BUILDER();
        $this->postModel = $model;
    }

    public function retrieveByCategory(CategoryInterface $categ)
    {
        $blogs = $this->postBuilder->select('post.parent_id, user_id, title, post.meta_title, post.slug , summary, published , content, post.created_at, post.updated_at, post.deleted_at' )
                                  ->join('post_category', 'post.parent_id = post_category.post_id', 'left')
                                  ->where('post_category.category_id',$categ->categoryIdentifier)
                                  ->get()->getResult('Hkm_services\Blog\Entities\BlogEntities');
        if ( ! is_null($blogs))
        {
            $bs = [];
            foreach ($blogs as $blog) {
                $bs[] = $this->BlogMetas($this->retrievePostCateg($this->retrievePostTag($blog)));
            }
            return $bs;

        }return [];  
    }

    public function checkBlog($data)
    {
        $id = 0;
        if (is_array($data)) {
            $id = $data['parent_id'];
        }else{
            $id = $data;
        }

        $res = $this->postModel::FIND($id);
        return !empty($res);

    }

    public function insert(array $data)
    {
        return $this->postModel::INSERT($data);
    }

    public function update(array $data)
    {
        $id = $data['parent_id'];
        unset($data['parent_id']);
        return $this->postModel::UPDATE($id,$data);
    }

    public function retrieveById($identifier)
    {
		$blog = $this->postBuilder->select('parent_id, user_id, title, meta_title, slug , summary, published , content, created_at, updated_at, deleted_at' )
		                   ->where('parent_id',$identifier)->get()->getFirstRow('Hkm_services\Blog\Entities\BlogEntities');
        if ( ! is_null($blog))
		{
			return $this->BlogMetas($this->retrievePostCateg($this->retrievePostTag($blog)));

		}return null;

    }

    public function retrieveBySearch($title)
    {
        
    }

    public function retrieveByTag(TagInterface $tag)
    {
	   	$blogs = $this->postBuilder->select('post.parent_id, user_id, title, post.meta_title, post.slug , summary, published , content, post.created_at, post.updated_at, post.deleted_at' )
                                  ->join('post_tag', 'post.parent_id = post_tag.post_id', 'left')
                                  ->where('post_tag.tag_id',$tag->tagIdentifier)
                                  ->get()->getResult('Hkm_services\Blog\Entities\BlogEntities');
        if ( ! is_null($blogs))
        {
            $bs = [];
            foreach ($blogs as $blog) {
                $bs[] = $this->BlogMetas($this->retrievePostCateg($this->retrievePostTag($blog)));
            }
            return $bs;

        }return [];   
    }

    protected function retrievePostTag(BlogInterface $blog) :BlogInterface
    {
      $blogTag = new PostTagModel();
      $blogTag::CHECK_ENGINE();
      $tags = $blogTag::FIND($blog->getBlogIdentifier);
      return $blog->setTags($tags);
    }



    public function insertTagPost(array $tagData)
    {
      
      $blogTag = new PostTagModel();
      $blogTag::CHECK_ENGINE();
      $RES = $blogTag::BUILDER()->where('post_id',$tagData['post_id'])->where('tag_id',$tagData['tag_id'])->get()->getResultArray();
      if(empty($RES)) return $blogTag::INSERT($tagData);
      else return true;
    }

    protected function retrievePostCateg(BlogInterface $blog) :BlogInterface
    {
      $blogCateg = new PostCategoryModel();
      $blogCateg::CHECK_ENGINE();
      $CATEGS = $blogCateg::FIND($blog->getBlogIdentifier);
      return $blog->setCategs($CATEGS);
    }


    public function insertCategPost(array $categData)
    {
      $blogcateg = new PostCategoryModel();
      $blogcateg::CHECK_ENGINE();
      $RES = $blogcateg::BUILDER()->where('post_id',$categData['post_id'])->where('category_id',$categData['category_id'])->get()->getResultArray();
      if(empty($RES)) return $blogcateg::INSERT($categData);
      else return true;
    }

   public function Published()
   {
        $blogs = $this->postBuilder->select('parent_id, user_id, title, meta_title, slug , summary, published , content, created_at, updated_at, deleted_at' )
                                  ->where('published',1)->get()->getResult('Hkm_services\Blog\Entities\BlogEntities');
        if ( ! is_null($blogs))
        {
            $bs = [];
            foreach ($blogs as $blog) {
                $bs[] = $this->BlogMetas($this->retrievePostCateg($this->retrievePostTag($blog)));
            }
            return $bs;

        }return null;
   }

   public function unPublished()
   {
        $blogs = $this->postBuilder->select('parent_id, user_id, title, meta_title, slug , summary, published , content, created_at, updated_at, deleted_at' )
                                   ->where('published',0)->get()->getResult('Hkm_services\Blog\Entities\BlogEntities');
        if ( ! is_null($blogs))
        {
            $bs = [];
            foreach ($blogs as $blog) {
                $bs[] = $this->BlogMetas($this->retrievePostCateg($this->retrievePostTag($blog)));
            }
            return $bs;

        }return null;
   }

   public function allBlogs()
   {
       $blogs = $this->postBuilder->select('parent_id, user_id, title, meta_title, slug , summary, published , content, created_at, updated_at, deleted_at' )
                                   ->where('deleted_at','')->orWhere('deleted_at!=','')->get()->getResult('Hkm_services\Blog\Entities\BlogEntities');
        if ( ! is_null($blogs))
        {
            $bs = [];
            foreach ($blogs as $blog) {
                $bs[] = $this->BlogMetas($this->retrievePostCateg($this->retrievePostTag($blog)));
            }
            return $bs;

        }return null;
   }

   public function BlogMetas(BlogInterface $blog) :BlogInterface
   {
     $model = new PostMetaModel();
     $model::CHECK_ENGINE();
     $RES = $model::BUILDER()->where('post_id',$blog->getBlogIdentifier())->get()->getResultArray();
     $blog->setMetas($RES);

     return $blog;
   }

   public function insertMetas(array $metasData,$default)
   {
        $model = new PostMetaModel();
        $model::CHECK_ENGINE();
        $RES = $model::BUILDER()->where('post_id',$metasData['post_id'])->where('name',$metasData['name'])->get()->getResultArray();
        if(empty($RES)) return $model::INSERT($metasData);
        if($metasData['content'] == $default) return 1;
        else return $model::UPDATE($metasData['post_id'],['content'=>$metasData['content']]);

   }
}


