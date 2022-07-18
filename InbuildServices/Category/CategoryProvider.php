<?php
namespace Hkm_services\Category;

use Hkm_services\Category\Models\CategoryModel;
use Hkm_code\Database\BaseBuilder;


class CategoryProvider implements CategoryProviderInterface
{
    /**
     * @var CategoryModel $categoryModel
     */
    protected $categoryModel;


    /**
     * @var BaseBuilder $categoryBuilder
     */
    protected $categoryBuilder;
    
    function __construct()
    {
        $model = new CategoryModel();
        $model::CHECK_ENGINE();
        $this->categoryBuilder = $model::BUILDER();
        $this->categoryModel = $model;
    }
    
    public function retrieveById($value)
    {
        $category = $this->categoryBuilder->select('*' )
                                  ->where('category_id',$value)->get()->getFirstRow('Hkm_services\Category\Entities\CategoryEntities');
        if ( ! is_null($category))
        {
          return $category;

        }return null;
    }

    public function retrieveBySlug($value)
    {
        $category = $this->categoryBuilder->select('*' )
                                  ->where('slug',$value)->get()->getFirstRow('Hkm_services\Category\Entities\CategoryEntities');
        if ( ! is_null($category))
        {
          return $category;

        }return null;
    }

    public function retrieveByCategoryName($value)
    {
        $category = $this->categoryBuilder->select('*' )
                                  ->where('name',$value)->get()->getFirstRow('Hkm_services\Category\Entities\CategoryEntities');
        if ( ! is_null($category))
        {
          return $category;

        }return null;
    }

    public function allCategories()
    {
        $allCategories = $this->categoryModel::FIND_ALL();
        return $allCategories; 
    }

    public function  addCategories($categoriesname)
    {
        $categoryNames = CategoryUtility::MAKE_CATEG_ARRAY($categoriesname);
        $categorys_id = [];
        foreach($categoryNames as $categoryName) {
            $categorys_id[] = $this->ADD_SINGLE_TAG($categoryName);
        }
        return $categorys_id;
    }

    public function deleteCategories($categoriesname)
    {

        $categoryNames = CategoryUtility::MAKE_CATEG_ARRAY($categoriesname);

        foreach($categoryNames as $categoryName) {
            $this->REMOVE_SINGLE_TAG($categoryName);
        }
    }

    /**
     * Adds a single category
     *
     * @param string $categoryName
     */
    private function ADD_SINGLE_TAG($categoryName)
    {
        $categoryName = trim($categoryName);

        if(strlen($categoryName) == 0) {
            return;
        }

        $categorySlug = CategoryUtility::SLUG($categoryName);

        $checkCategory = $this->categoryModel::FIND($categorySlug);
        if(!empty($checkCategory)){
           if(hkm_isAssoc($checkCategory)){
            return $checkCategory['category_id'];
           }else{
            return $checkCategory[0]['category_id'];
           }
        }

        $categoryged = [
            'name' => $categoryName,
            'slug' => $categorySlug,
            'meta_title' => ucfirst($categoryName)
        ];

        $id = $this->categoryModel::INSERT($categoryged);

        if($id) return $id;
        return false;
    }

    /**
     * Removes a single category
     *
     * @param $categoryName string
     */
    private function REMOVE_SINGLE_TAG($categoryName)
    {
        $categoryName = trim($categoryName);

        $categorySlug = CategoryUtility::SLUG($categoryName);

        return $this->categoryModel::DELETE($categorySlug);
    }
}

