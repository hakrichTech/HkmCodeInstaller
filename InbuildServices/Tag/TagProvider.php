<?php
namespace Hkm_services\Tag;

use Hkm_services\Tag\Models\TagModel;
use Hkm_code\Database\BaseBuilder;


class TagProvider implements TagProviderInterface
{
    /**
     * @var TagModel $tagModel
     */
    protected $tagModel;


    /**
     * @var BaseBuilder $tagBuilder
     */
    protected $tagBuilder;
    
    function __construct()
    {
        $model = new TagModel();
        $model::CHECK_ENGINE();
        $this->tagBuilder = $model::BUILDER();
        $this->tagModel = $model;
    }
    
    public function retrieveById($value)
    {
        $tag = $this->tagBuilder->select('*' )
                                  ->where('tag_id',$value)->get()->getFirstRow('Hkm_services\Tag\Entities\TagEntities');
        if ( ! is_null($tag))
        {
          return $tag;

        }return null;
    }

    public function retrieveBySlug($value)
    {
        $tag = $this->tagBuilder->select('*' )
                                  ->where('tag_slug',$value)->get()->getFirstRow('Hkm_services\Tag\Entities\TagEntities');
        if ( ! is_null($tag))
        {
          return $tag;

        }return null;
    }

    public function retrieveByTagname($value)
    {
        $tag = $this->tagBuilder->select('*' )
                                  ->where('tag_name',$value)->get()->getFirstRow('Hkm_services\Tag\Entities\TagEntities');
        if ( ! is_null($tag))
        {
          return $tag;

        }return null;
    }

    public function allTags()
    {
        $allTags = $this->tagModel::FIND_ALL();
        return $allTags;
    }

    public function  addTags($tagsname)
    {
        $tagNames = TaggUtility::MAKE_TAG_ARRAY($tagsname);
        $tags_id = [];
        foreach($tagNames as $tagName) {
            $tags_id[] = $this->ADD_SINGLE_TAG($tagName);
        }
        return $tags_id;
    }

    public function deleteTags($tagsname)
    {

        $tagNames = TaggUtility::MAKE_TAG_ARRAY($tagsname);

        foreach($tagNames as $tagName) {
            $this->REMOVE_SINGLE_TAG($tagName);
        }
    }

    /**
     * Adds a single tag
     *
     * @param string $tagName
     */
    private function ADD_SINGLE_TAG($tagName)
    {
        $tagName = trim($tagName);

        if(strlen($tagName) == 0) {
            return;
        }

        $tagSlug = TaggUtility::SLUG($tagName);

        $checkTag = $this->tagModel::FIND($tagSlug);
        if(!empty($checkTag)){
            if(is_array($checkTag)){
                return $checkTag[0]->tagIdentifier;
            }else{
                return $checkTag->tagIdentifier;
            }
        }

        $tagged = [
            'tag_name' => $tagName,
            'tag_slug' => $tagSlug,
            'meta_title' => ucfirst($tagName)
        ];

        $id = $this->tagModel::INSERT($tagged);

        if($id) return $id;
        return false;
    }

    /**
     * Removes a single tag
     *
     * @param $tagName string
     */
    private function REMOVE_SINGLE_TAG($tagName)
    {
        $tagName = trim($tagName);

        $tagSlug = TaggUtility::SLUG($tagName);

        return $this->tagModel::DELETE($tagSlug);
    }
}

