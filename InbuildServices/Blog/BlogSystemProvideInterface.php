<?php
namespace Hkm_services\Blog;

use Hkm_services\Tag\TagInterface;
use Hkm_services\Category\CategoryInterface;

interface BlogSystemProvideInterface{

    /**
	 * Retrieve a blog by their unique identifier.
	 *
	 * @param  mixed  $identifier
	 * @return \Hkm_services\Blog\BlogInterface|null
	 */
	public function retrieveById($identifier);

    /**
	 * Retrieve a blog by their Title.
	 *
	 * @param  string  $title
	 * @return array|null
	 */
	public function retrieveBySearch($title);


    /**
	 * Retrieve blogs by their category.
	 *
	 * @param  CategoryInterface  $categ
	 * @return array|null
	 */
	public function retrieveByCategory(CategoryInterface $categ);

     /**
	 * Retrieve blogs by their tag.
	 *
	 * @param  TagInterface  $tag
	 * @return array|null
	 */
	public function retrieveByTag(TagInterface $tag);

    /**
	 * Get published blogs.
	 *
	 * @return array|null
	 */
	public function Published();


    /**
	 * Get unpublished blogs.
	 *
	 * @return array|null
	 */
	public function unPublished();



}


