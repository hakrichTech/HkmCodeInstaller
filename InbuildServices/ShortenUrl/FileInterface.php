<?php 
namespace Hkm_services\ShortenUrl;


interface FileInterface {

	/**
	 * Get the unique identifier for the user.
	 *
	 * @return mixed
	 */
	public function getFileIdentifier();

	/**
	 * Get the password for the user.
	 *
	 * @return string
	 */
	public function getFilename();

	/**
	 * Get the email for the user.
	 *
	 * @return string
	 */
	public function getFileSize();

	public function setId($value);

	/**
	 * Set the path .
	 *
	 * @param  string  $value
	 * @return FileInterface
	 */
	public function setPath(string $value);


	/**
	 * Get the file path.
	 *
	 * @return string
	 */
	public function getFilePath();

	/**
	 * Set the filename .
	 *
	 * @param  string  $value
	 * @return FileInterface
	 */
	public function setName(string $value);


	/**
	 * Set the file size .
	 *
	 * @param  string  $value
	 * @return FileInterface
	 */
	public function setSize(string $value);

	/**
	 * Set the file type .
	 *
	 * @param  string  $value
	 * @return FileInterface
	 */
	public function setType(string $value);

	/**
	 * Get the file type.
	 *
	 * @return string
	 */
	public function getType();

	/**
	 * Set the file download .
	 *
	 * @param  string  $value
	 * @return FileInterface
	 */
	public function setDownload(string $value);

	/**
	 * Get the file Download.
	 *
	 * @return string
	 */
	public function getDownload();

	/**
	 * Set the file view .
	 *
	 * @param  string  $value
	 * @return FileInterface
	 */
	public function setViewFile(string $value);

	/**
	 * Get the file View.
	 *
	 * @return string
	 */
	public function getViews();

	/**
	 * Set the file original dimensions .
	 *
	 * @param  string  $value
	 * @return FileInterface
	 */
	public function setOriginalDimensions(string $value);

	/**
	 * Set the file Thumbnail dimensions .
	 *
	 * @param  string  $value
	 * @return FileInterface
	 */
	public function setThumbnailDimensions(string $value);
	
	/**
	 * Get the file Thumbnail dimensions.
	 *
	 * @return string
	 */
	public function getThumbnailDimensions();

	/**
	 * Get the file Original dimensions.
	 *
	 * @return string
	 */
	public function getOriginalDimensions();




}
