<?php
namespace Hkm_services\Auth;


interface HkmUserAddressInterface {

	/**
	 * Get the unique identifier for the user.
	 *
	 * @return mixed
	 */
	public function getAuthIdentifier();

	/**
	 * Get the unique identifier for the address.
	 *
	 * @return mixed
	 */
	public function getAddressIdentifier();
	

	

	/**
	 * Get the Country for the user.
	 *
	 * @return string
	 */
	public function getCountry();

	/**
	 * Set the Country .
	 *
	 * @param  string  $value
	 * @return HkmUserAddressInterface
	 */
	public function setCountry(string $value);

	/**
	 * Set the user_id .
	 *
	 * @param  string  $value
	 * @return HkmUserAddressInterface
	 */
	public function setUser_id(string $value);

	/**
	 * Get the state.
	 *
	 * @return bool
	 */
	public function getState();

	/**
	 * Get the Postalcode.
	 *
	 * @return string
	 */
	public function getPostalcode();

	/**
	 * Set postalcode.
	 *
	 * @param  string  $value
	 * @return HkmUserAddressInterface
	 */
	public function setPostalcode($value);

	/**
	 * Set the State .
	 *
	 * @param  string  $value
	 * @return HkmUserAddressInterface
	 */
	public function setState(string $value);


	/**
	 * Set the address .
	 *
	 * @param  string  $value
	 * @return HkmUserAddressInterface
	 */
	public function setAddress(string $value);

	/**
	 * get the Address .
	 *
	 * @return string
	 */
	public function getAddress();


	public function update();


}
