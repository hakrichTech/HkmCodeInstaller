<?php
namespace Hkm_services\Auth;


interface AuthProviderInterface {

	/**
	 * Retrieve a user by their unique identifier.
	 *
	 * @param  mixed  $identifier
	 * @return \Hkm_services\Auth\HkmUserInterface|null
	 */
	public function retrieveById($identifier);

	/**
	 * Retrieve a user by by their unique identifier and "remember me" token.
	 *
	 * @param  mixed   $identifier
	 * @param  string  $token
	 * @return \Hkm_services\Auth\HkmUserInterface|null
	 */
	public function retrieveByToken($identifier, $token);


	public function validateToken($token);

	public function createToken($id = null, $payloadData=false, $request = null);

	/**
	 * Update the "remember me" token for the given user in storage.
	 *
	 * @param  \Hkm_services\Auth\HkmUserInterface  $user
	 * @param  string  $token
	 * @return void
	 */
	public function updateRememberToken(HkmUserInterface $user, $token);

	/**
	 * Retrieve a user by the given credentials.
	 *
	 * @param  array  $credentials
	 * @return \Hkm_services\Auth\HkmUserInterface|null
	 */
	public function retrieveByCredentials(array $credentials);

	/**
	 * Validate a user against the given credentials.
	 *
	 * @param  \Hkm_services\Auth\HkmUserInterface  $user
	 * @param  array  $credentials
	 * @return bool
	 */
	public function validateCredentials(HkmUserInterface $user, array $credentials);

	public function addAttemp(HkmUserInterface $user);
	public function updateOnlineStatus(HkmUserInterface $user);
	public function unvalidateToken($token);

	/**
	 * Retrieve a user by by their phone number.
	 *
	 * @param  string  $phone
	 * @return \Hkm_services\Auth\HkmUserInterface|nul|array
	 */
	public function retrieveByPhone(string $phone);

	public function sendRequest(HkmUserInterface $user,int $request, bool $update=false);
    /**
	 *
	 * @param  HkmUserInterface  $user
	 */
    public function resetPasswordRequest(HkmUserInterface $user);

	/**
	 *
	 * @param  string  $token
	 */
    public function resendPasswordRequest($token);
	public function checkRequest(HkmUserInterface $user,$request);
	public function getRequests(HkmUserInterface $user, int $request = 0);
	public function deleteRequest(string $token);

	/**
	 * Update the "remember me" token for the given user in storage.
	 *
	 * @param  \Hkm_services\Auth\HkmUserInterface $user
	 * @param  string  $value
	 * @return void
	 */
	public function setVerified(HkmUserInterface $user, $value);

	/**
	 * Update the "remember me" token for the given user in storage.
	 *
	 * @param  \Hkm_services\Auth\HkmUserInterface $user
	 * @param  array  $value
	 * @return void
	 */
	public function update(HkmUserInterface $user, $value);

}
