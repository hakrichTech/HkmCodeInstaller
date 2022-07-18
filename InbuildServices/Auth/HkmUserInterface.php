<?php 
namespace Hkm_services\Auth;


interface HkmUserInterface {

	/**
	 * Get the unique identifier for the user.
	 *
	 * @return mixed
	 */
	public function getAuthIdentifier();

	/**
	 * Get the password for the user.
	 *
	 * @return string
	 */
	public function getAuthPassword();

	/**
	 * Get the email for the user.
	 *
	 * @return string
	 */
	public function getEmail();

	/**
	 * Set the profile .
	 *
	 * @param  string  $value
	 * @return HkmUserInterface
	 */
	public function setEmail(string $value);

		/**
	 * Check if user is verified.
	 *
	 * @return bool
	 */
	public function isVerified();

	/**
	 * Get the attempt number of login.
	 *
	 * @return bool
	 */
	public function getAttemptNumber();

	/**
	 * Get the token value for the "remember me" session.
	 *
	 * @return string
	 */
	public function getRememberToken();

	/**
	 * Set the token value for the "remember me" session.
	 *
	 * @param  string  $value
	 * @return HkmUserInterface
	 */
	public function setRememberToken($value);



	/**
	 * Init new User object.
	 *
	 * @param  mixed  $value
	 * @return HkmUserInterface
	 */
	public function init($value);

	/**
	 * Set the username .
	 *
	 * @param  string  $username
	 * @return HkmUserInterface
	 */
	public function setUsername(string $username);


	/**
	 * Set the profile .
	 *
	 * @param  string  $value
	 * @return HkmUserInterface
	 */
	public function setAvatar(string $value);

	/**
	 * get the profile .
	 *
	 * @return string
	 */
	public function getProfile();


	public function update();

	/**
	 * Get the username .
	 *
	 * @return  string
	 */
	public function getUsername();

	/**
	 * Get the userFullname .
	 *
	 * @return  string
	 */
	public function getFullname();

	/**
	 * Set the profile .
	 *
	 * @param  string  $value
	 * @return HkmUserInterface
	 */
	public function setUserfullname(string $value);


	/**
	 * Set the profile .
	 *
	 * @param  string  $value
	 * @return HkmUserInterface
	 */
	public function setGender(string $value);


	/**
	 * Set the address .
	 *
	 * @param  HkmUserAddressInterface  $value
	 * @return HkmUserInterface
	 */
	public function setAddress(HkmUserAddressInterface $value);


	/**
	 * Get the address .
	 *
	 * @return HkmUserAddressInterface
	 */
	public function getAddress();


	/**
	 * get the Gender .
	 *
	 * @return string
	 */
	public function getGender();

	/**
	 * Get the birthdate .
	 *
	 * @return  string
	 */
	public function getBirthdate();


	/**
	 * Set the profile .
	 *
	 * @param  string  $value
	 * @return HkmUserInterface
	 */
	public function setBirthdate(string $value);

	/**
	 * Get the phone .
	 *
	 * @return  string
	 */
	public function getPhone();

	/**
	 * Set the profile .
	 *
	 * @param  string  $value
	 * @return HkmUserInterface
	 */
	public function setPhone(string $value);

	/**
	 * Set the username .
	 *
	 * @param  string  $value
	 * @return HkmUserInterface
	 */
	public function setPassword(string $value);

	/**
	 * Set the if is admin .
	 *
	 * @param  bool  $value
	 * @return HkmUserInterface
	 */
	public function setAdmin(bool $value);

	/**
	 * Set the if is app .
	 *
	 * @param  array  $value
	 * @return HkmUserInterface
	 */
	public function setApp(array $value);

	/**
	 * Set the if is adminPermission .
	 *
	 * @param  array  $value
	 * @return void
	 */
	public function setPermission(array $value);


	/**
	 * Get the app id .
	 *
	 * @return int|string
	 */
	public function getAppIndentifier();

	/**
	 * Check if is admin .
	 *
	 * @return bool
	 */
	public function isAdmin();

	/**
	 * Get all adminPermission .
	 *
	 * @return array
	 */
	public function getPermissions();


	/**
	 * Get app  .
	 *
	 * @return string
	 */
	public function getApp();

	/**
	 * Check if he has permission .
	 *
	 * @param  int $permission
	 * @return bool
	 */
	public function hasPermission(int $permission);
	/**
	 * Get the column name for the "remember me" token.
	 *
	 * @return string
	 */
	public function getRememberTokenName();

}
