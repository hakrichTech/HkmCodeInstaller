<?php
namespace Hkm_services\Category;

interface CategoryInterface{

    /**
	 * set Category identifier.
	 *
	 * @param  mixed  $value
	 * @return \Hkm_services\Category\CategoryInterface
	 */
	public function setCategoryId($value);

    /**
	 * set Category name.
	 *
	 * @param  mixed  $value
	 * @return \Hkm_services\Category\CategoryInterface
	 */
	public function setName($value);

    /**
	 * set Category slug.
	 *
	 * @param  mixed  $value
	 * @return \Hkm_services\Category\CategoryInterface
	 */
	public function setSlug($value);


    /**
	 * set category meta_title.
	 *
	 * @param  mixed  $value
	 * @return \Hkm_services\Category\CategoryInterface
	 */
	public function setMetaTitle($value);


	/**
	 * syncs.
	 *
	 * @return \Hkm_services\Category\CategoryInterface
	 */
	public function sync();


     /**
	 * get category url.
	 *
	 * @return string
	 */
	public function getUrl();

	/**
	 * get blogs.
	 *
	 * @return array
	 */
	public function blogs();

    /**
	 * get category name.
	 *
	 * @return string
	 */
	public function getName();


    /**
	 * get category meta_title.
	 *
	 * @return string
	 */
	public function getMetaTitle();

     /**
	 * get category identifier.
	 *
	 * @return string
	 */
	public function getCategoryIdentifier();




}