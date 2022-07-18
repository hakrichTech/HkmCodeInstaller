<?php
namespace Hkm_services\Tag;

interface TagProviderInterface {

     /**
	 * get tag by tagname.
	 *
	 * @param  mixed  $value
	 * @return \Hkm_services\Tag\TagInterface|null
	 */
	public function retrieveByTagname($value);

     /**
	 * get Tag by identifier.
	 *
	 * @param  mixed  $value
	 * @return \Hkm_services\Tag\TagInterface|null
	 */
	public function retrieveById($value);


	/**
	 * get all tags.
	 *
	 * @return array
	 */
	public function allTags();

     /**
	 * get Tag by slug.
	 *
	 * @param  mixed  $value
	 * @return \Hkm_services\Tag\TagInterface|null
	 */
	public function retrieveBySlug($value);

    /**
	 * add Tags.
	 *
	 * @param  array|string  $tagsname
	 * @return arrat
	 */
	public function addTags($tagsname);

    /**
	 * delete Tags.
	 *
	 * @param  array|string  $tagsname
	 * 
	 */
	public function deleteTags($tagsname);
}