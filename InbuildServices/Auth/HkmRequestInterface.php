<?php 

namespace Hkm_services\Auth;


interface HkmRequestInterface {

	/**
	 * Get the unique identifier for the user.
	 *
	 * @return HkmUserInterface
	 */
	public function getUser();

	/**
	 *
	 * @return int
	 */
	public function getRequest();

	/**
	 *
	 * @return string
	 */
	public function getHash();

	
	public function delete();

	/**
	 *
	 * @return string
	 */
	public function getToken();

	/**
	 *
	 * @return bool
	 */
	public function isRequestValid();

	/**
	 *
	 * @return mixed
	 */
	public function getPayload();

}
