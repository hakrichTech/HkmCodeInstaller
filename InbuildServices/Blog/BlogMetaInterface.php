<?php

namespace Hkm_services\Blog;

interface BlogMetaInterface{


    /**
	 * set blog headerImage.
	 *
	 * @param  mixed  $value
	 * @return BlogMetaInterface
	 */
	public function setHeaderImage($value);


     /**
	 * set blog comment permission.
	 *
	 * @param  mixed  $value
	 * @return BlogMetaInterface
	 */
	public function setCommentPermission($value);

    /**
	 * set blog visibility.
	 *
	 * @param  mixed  $value
	 * @return BlogMetaInterface
	 */
	public function setVisibility($value);


    /**
	 * get blog visibility.
	 *
	 * @return string
	 */
	public function getVisibility();

    /**
	 * get blog comment permission.
	 *
	 * @return bool
	 */
	public function getCommentPermission();

    /**
	 * get blog header image
	 *
	 * @return string
	 */
	public function getHeaderImage();

    

}