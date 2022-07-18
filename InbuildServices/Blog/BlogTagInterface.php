<?php

namespace Hkm_services\Blog;

interface BlogTagInterface{


    /**
	 * set blog identifier.
	 *
	 * @param  mixed  $value
	 * @return \Hkm_services\Blog\BlogTagInterface
	 */
	public function setPostId($value);


    /**
	 * set blog Author.
	 *
	 * @param  mixed  $value
	 * @return \Hkm_services\Blog\BlogTagInterface
	 */
	public function setTagId($value);

    

    /**
	 * get blog identifier.
	 *
	 * @return \Hkm_services\Blog\BlogInterface
	 */
	public function getBlog();


    /**
	 * get tag identifier.
	 *
	 * @return \Hkm_services\Tag\TagInterface
	 */
	public function getTag();

    
	// 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
}