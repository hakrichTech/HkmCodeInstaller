<?php
namespace Hkm_services\ShortenUrl;


interface FileStoreProviderInterface {

	/**
	 * Retrieve a user by their unique identifier.
	 *
	 * @param  mixed  $identifier
	 * @return \Hkm_services\ShortenUrl\FileInterface|null
	 */
	public function retrieveById($identifier);

	
	/**
	 * Retrieve a file by their name.
	 *
	 * @param  string  $name
	 * @return \Hkm_services\ShortenUrl\FileInterface|nul|array
	 */
	public function retrieveByname(string $name);



}
