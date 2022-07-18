<?php

namespace Hkm_services\Blog;

interface BlogCategInterface{


    /**
	 * set blog identifier.
	 *
	 * @param  mixed  $value
	 * @return \Hkm_services\Blog\BlogCategInterface
	 */
	public function setPostId($value);


    /**
	 * set blog Author.
	 *
	 * @param  mixed  $value
	 * @return \Hkm_services\Blog\BlogCategInterface
	 */
	public function setCategoryId($value);

    

    /**
	 * get blog identifier.
	 *
	 * @return \Hkm_services\Blog\BlogInterface
	 */
	public function getBlog();


    /**
	 * get Categ identifier.
	 *
	 * @return \Hkm_services\Category\CategoryInterface
	 */
	public function getCateg();

    
	// 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
}