<?php
namespace Hkm_services\Tag;

interface TagInterface{

    /**
	 * set Tag identifier.
	 *
	 * @param  mixed  $value
	 * @return \Hkm_services\Tag\TagInterface
	 */
	public function setTagId($value);

    /**
	 * set Tag name.
	 *
	 * @param  mixed  $value
	 * @return \Hkm_services\Tag\TagInterface
	 */
	public function setTagName($value);

    /**
	 * set Tag slug.
	 *
	 * @param  mixed  $value
	 * @return \Hkm_services\Tag\TagInterface
	 */
	public function setTagSlug($value);


    /**
	 * set tag meta_title.
	 *
	 * @param  mixed  $value
	 * @return \Hkm_services\Tag\TagInterface
	 */
	public function setMetaTitle($value);


	/**
	 * syncs.
	 *
	 * @return \Hkm_services\Tag\TagInterface
	 */
	public function sync();


     /**
	 * get tag url.
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
	 * get tag name.
	 *
	 * @return string
	 */
	public function getName();


    /**
	 * get tag meta_title.
	 *
	 * @return string
	 */
	public function getMetaTitle();

     /**
	 * get tag identifier.
	 *
	 * @return string
	 */
	public function getTagIdentifier();




}