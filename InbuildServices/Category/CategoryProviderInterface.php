<?php
namespace Hkm_services\Category;

interface CategoryProviderInterface {

     /**
	 * get tag by tagname.
	 *
	 * @param  mixed  $value
	 * @return \Hkm_services\Category\CategoryInterface|null
	 */
	public function retrieveByCategoryName($value);

     /**
	 * get Category by identifier.
	 *
	 * @param  mixed  $value
	 * @return \Hkm_services\Category\CategoryInterface|null
	 */
	public function retrieveById($value);


	/**
	 * get all categories.
	 *
	 * @return array
	 */
	public function allCategories();

     /**
	 * get Category by slug.
	 *
	 * @param  mixed  $value
	 * @return \Hkm_services\Category\CategoryInterface|null
	 */
	public function retrieveBySlug($value);

    /**
	 * add Categorys.
	 *
	 * @param  array|string  $categoriesname
	 * @return arrat
	 */
	public function addCategories($categoriesname);

    /**
	 * delete Categorys.
	 *
	 * @param  array|string  $categoriesname
	 * 
	 */
	public function deleteCategories($categoriesname);
}