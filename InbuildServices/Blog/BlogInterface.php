<?php

namespace Hkm_services\Blog;

interface BlogInterface{


    /**
	 * set blog identifier.
	 *
	 * @param  mixed  $value
	 * @return \Hkm_services\Blog\BlogInterface
	 */
	public function setParentId($value);


    /**
	 * set blog Author.
	 *
	 * @param  mixed  $value
	 * @return \Hkm_services\Blog\BlogInterface
	 */
	public function setUserId($value);

    /**
	 * set blog title.
	 *
	 * @param  mixed  $value
	 * @return \Hkm_services\Blog\BlogInterface
	 */
	public function setTitle($value);


	/**
	 * set blog tags.
	 *
	 * @param  mixed  $value
	 * @return \Hkm_services\Blog\BlogInterface
	 */
	public function setTags($values);

	/**
	 * set blog categories.
	 *
	 * @param  mixed  $value
	 * @return \Hkm_services\Blog\BlogInterface
	 */
	public function setCategs($values);


	/**
	 * set blog Metas.
	 *
	 * @param  mixed  $value
	 * @return \Hkm_services\Blog\BlogInterface
	 */
	public function setMetas($values);

	/**
	 * update blog json sync
	 * 
	 * @return BlogInterface
	 */
	public function updateJsonSync();

    /**
	 * set blog meta_title.
	 *
	 * @param  mixed  $value
	 * @return \Hkm_services\Blog\BlogInterface
	 */
	public function setMetaTitle($value);

     /**
	 * set blog url.
	 *
	 * @param  mixed  $value
	 * @return \Hkm_services\Blog\BlogInterface
	 */
	public function setSlug($value);

    /**
	 * set blog summary.
	 *
	 * @param  mixed  $value
	 * @return \Hkm_services\Blog\BlogInterface
	 */
	public function setSummary($value);

    /**
	 * set blog status.
	 *
	 * @param  mixed  $value
	 * @return \Hkm_services\Blog\BlogInterface
	 */
	public function setPublished($value);


    /**
	 * set blog content.
	 *
	 * @param  mixed  $value
	 * @return \Hkm_services\Blog\BlogInterface
	 */
	public function setContent($value);




    /**
	 * get blog identifier.
	 *
	 * @return int
	 */
	public function getBlogIdentifier();


    /**
	 * get blog Author.
	 *
	 * @return int
	 */
	public function getAuthor();

	/**
	 * get blog Author.
	 *
	 * @return \Hkm_services\Auth\HkmUserInterface
	 */
	public function getAuthorObject();


	


	/**
	 * get blog tags.
	 *
	 * @return \Hkm_services\Tag\TagInterface|array
	 */
	public function tags();


	/**
	 * get blog categories.
	 *
	 * @return \Hkm_services\Category\CategoryInterface|array
	 */
	public function categories();

    /**
	 * get blog title.
	 *
	 * @return string
	 */
	public function getTitle();


    /**
	 * get blog meta_title.
	 *
	 * @return string
	 */
	public function getMetaTitle();

     /**
	 * get blog url.
	 *
	 * @return string
	 */
	public function getUrl();

    /**
	 * get blog summary.
	 *
	 * @return string
	 */
	public function getSummary();

    /**
	 * get blog status.
	 *
	 * @return string
	 */
	public function getStatus();


    /**
	 * get blog content.
	 *
	 * @return string
	 */
	public function getContent();

	/**
	 * init blog data.
	 *
	 * @var array $data
	 * 
	 * @return BlogInterface
	 */
	public function init(array $data);


}