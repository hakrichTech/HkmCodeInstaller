<?php

/**
 * This file is part of the Hkm_code 4 framework.
 *
 * (c) Hkm_code Foundation <admin@Hkm_code.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hkm_code\HTTP\Files;

use Hkm_code\Files\File;
use Hkm_code\Exceptions\HTTP\HTTPException;
use Exception;
use Hkm_code\Files\GD;
use Hkm_code\Vezirion\FileLocator;
use InvalidArgumentException;
use RuntimeException;

/**
 * Value object representing a single file uploaded through an
 * HTTP request. Used by the IncomingRequest class to
 * provide files.
 *
 * Typically, implementors will extend the SplFileInfo class.
 */
class UploadedFile extends File implements UploadedFileInterface
{
	/**
	 * The path to the temporary file.
	 *
	 * @var string
	 */
	protected static $path;

	/**
	 * The original filename as provided by the client.
	 *
	 * @var string
	 */
	protected static $originalName;

	/**
	 * The original filename as provided by the client.
	 *
	 * @var string
	 */
	protected static $exten;

	/**
	 * The filename given to a file during a move.
	 *
	 * @var string
	 */
	protected static $name;
	protected static $minature;
	protected static $dims1;
	protected static $dims2;

	/**
	 * The type of file as provided by PHP
	 *
	 * @var string
	 */
	protected static $originalMimeType;

	/**
	 * The error constant of the upload
	 * (one of PHP's UPLOADERRXXX constants)
	 *
	 * @var integer
	 */
	protected static $error;

	/**
	 * Whether the file has been moved already or not.
	 *
	 * @var boolean
	 */
	protected static $hasMoved = false;

	//--------------------------------------------------------------------

	/**
	 * Accepts the file information as would be filled in from the $_FILES array.
	 *
	 * @param string  $path         The temporary location of the uploaded file.
	 * @param string  $originalName The client-provided filename.
	 * @param string  $mimeType     The type of file as provided by PHP
	 * @param integer $size         The size of the file, in bytes
	 * @param integer $error        The error constant of the upload (one of PHP's UPLOADERRXXX constants)
	 */
	public function __construct(string $path, string $originalName, string $mimeType = null, int $size = null, int $error = null)
	{
		self::$path             = $path;
		self::$name             = $originalName;
		self::$originalName     = $originalName;
		self::$originalMimeType = $mimeType;
		self::$size             = $size;
		self::$error            = $error;

		parent::__construct($path, false);

		self::$exten = self::GET_CLIENT_EXTENSION();
	}

	//--------------------------------------------------------------------

	/**
	 * Move the uploaded file to a new location.
	 *
	 * $targetPath may be an absolute path, or a relative path. If it is a
	 * relative path, resolution should be the same as used by PHP's rename()
	 * function.
	 *
	 * The original file MUST be removed on completion.
	 *
	 * If this method is called more than once, any subsequent calls MUST raise
	 * an exception.
	 *
	 * When used in an SAPI environment where $_FILES is populated, when writing
	 * files via moveTo(), is_uploaded_file() and move_uploaded_file() SHOULD be
	 * used to ensure permissions and upload status are verified correctly.
	 *
	 * If you wish to move to a stream, use getStream(), as SAPI operations
	 * cannot guarantee writing to stream destinations.
	 *
	 * @see http://php.net/is_uploaded_file
	 * @see http://php.net/move_uploaded_file
	 *
	 * @param string  $targetPath Path to which to move the uploaded file.
	 * @param string  $name       the name to rename the file to.
	 * @param boolean $overwrite  State for indicating whether to overwrite the previously generated file with the same
	 *                            name or not.
	 *
	 * @return boolean
	 *
	 * @throws InvalidArgumentException if the $path specified is invalid.
	 * @throws RuntimeException on any error during the move operation.
	 * @throws RuntimeException on the second or subsequent call to the method.
	 */
	public static function MOVE(string $targetPath, string $name = null, bool $overwrite = false)
	{
		$targetPath = rtrim($targetPath, '/') . '/';
		$targetPath = self::SET_PATH($targetPath); //set the target path

		if (self::$hasMoved)
		{
			throw HTTPException::FOR_ALREADY_MOVED();
		}

		if (! self::IS_VALID())
		{
			throw HTTPException::FOR_INVALID_FILE();
		}

		$name        = is_null($name) ? self::GET_NAME() : $name;
		$destination = $overwrite ? $targetPath . $name : self::GET_DESTINATION($targetPath . $name);

		try
		{
			move_uploaded_file(self::$path, $destination);
		}
		catch (Exception $e)
		{
			$error   = error_get_last();
			$message = isset($error['message']) ? strip_tags($error['message']) : '';
			throw HTTPException::FOR_MOVE_FAILED(basename(self::$path), $targetPath, $message);
		}

		@chmod($targetPath, 0777 & ~umask());

		// Success, so store our new information
		self::$path     = $targetPath;
		self::$name     = basename($destination);
		self::$hasMoved = true;
		$imgs = ['jpg','jpeg','png'];

		if( in_array(strtolower(self::$exten),$imgs)){
			$thumbnailGD = new GD($destination);
			self::$dims1 = $thumbnailGD::GET_CURRENT_DIMENSIONS();
			$thumbnailGD::RESIZE_PERCENT(50);
			self::$dims2 = $thumbnailGD::GET_CURRENT_DIMENSIONS();
            $thumbnailGD::SAVE($destination.'.min.png', 'png');
			self::$minature = $destination.'.min.png';

		}

		return true;
	}
	public static function THUMBNAIL()
	{
	  return self::$minature;
	}


	public static function ORIGINAL_FILE_DIMENSIONS()
	{
		return implode('x',self::$dims1);
	}
	public static function THUMBNAIL_FILE_DIMENSIONS()
	{
		return implode('x',self::$dims2);
	}

	/**
	 * create file target path if
	 * the set path does not exist
	 *
	 * @param string $path
	 *
	 * @return string The path set or created.
	 */
	protected static function SET_PATH(string $path): string
	{
		if (! is_dir($path))
		{
			mkdir($path, 0777, true);
			//create the index.html file
			if (! is_file($path . 'index.html'))
			{
				$file = fopen($path . 'index.html', 'x+');
				fclose($file);
			}
		}
		return $path;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns whether the file has been moved or not. If it has,
	 * the move() method will not work and certain properties, like
	 * the tempName, will no longer be available.
	 *
	 * @return boolean
	 */
	public static function HAS_MOVED(): bool
	{
		return self::$hasMoved;
	}

	//--------------------------------------------------------------------

	/**
	 * Retrieve the error associated with the uploaded file.
	 *
	 * The return value MUST be one of PHP's UPLOAD_ERR_XXX constants.
	 *
	 * If the file was uploaded successfully, this method MUST return
	 * UPLOAD_ERR_OK.
	 *
	 * Implementations SHOULD return the value stored in the "error" key of
	 * the file in the $_FILES array.
	 *
	 * @see    http://php.net/manual/en/features.file-upload.errors.php
	 * @return integer One of PHP's UPLOAD_ERR_XXX constants.
	 */
	public static function GET_ERROR(): int
	{
		return self::$error ?? UPLOAD_ERR_OK;
	}

	//--------------------------------------------------------------------

	/**
	 * Get error string
	 *
	 * @return string
	 */
	public static function GET_ERROR_STRING(): string
	{
		$errors = [
			UPLOAD_ERR_OK         => hkm_lang('HTTP.uploadErrOk'),
			UPLOAD_ERR_INI_SIZE   => hkm_lang('HTTP.uploadErrIniSize'),
			UPLOAD_ERR_FORM_SIZE  => hkm_lang('HTTP.uploadErrFormSize'),
			UPLOAD_ERR_PARTIAL    => hkm_lang('HTTP.uploadErrPartial'),
			UPLOAD_ERR_NO_FILE    => hkm_lang('HTTP.uploadErrNoFile'),
			UPLOAD_ERR_CANT_WRITE => hkm_lang('HTTP.uploadErrCantWrite'),
			UPLOAD_ERR_NO_TMP_DIR => hkm_lang('HTTP.uploadErrNoTmpDir'),
			UPLOAD_ERR_EXTENSION  => hkm_lang('HTTP.uploadErrExtension'),
		];

		$error = self::$error ?? UPLOAD_ERR_OK;

		return sprintf($errors[$error] ?? hkm_lang('HTTP.uploadErrUnknown'), self::GET_NAME());
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the mime type as provided by the client.
	 * This is NOT a trusted value.
	 * For a trusted versioself::GET_MIME_TYPE() instead.
	 *
	 * @return string The media type sent by the client or null if none was provided.
	 */
	public static function GET_CLIENT_MIME_TYPE(): string
	{
		return self::$originalMimeType;
	}

	//--------------------------------------------------------------------

	/**
	 * Retrieve the filename. This will typically be the filename sent
	 * by the client, and should not be trusted. If the file has been
	 * moved, this will return the final name of the moved file.
	 *
	 * @return string The filename sent by the client or null if none was provided.
	 */
	public static function GET_NAME(): string
	{
		return self::$name;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the name of the file as provided by the client during upload.
	 *
	 * @return string
	 */
	public static function GET_CLIENT_NAME(): string
	{
		return self::$originalName;
	}

	//--------------------------------------------------------------------

	/**
	 * Gets the temporary filename where the file was uploaded to.
	 *
	 * @return string
	 */
	public static function GET_TEMP_NAME(): string
	{
		return self::$path;
	}

	//--------------------------------------------------------------------

	/**
	 * Overrides SPLFileInfo's to work with uploaded files, since
	 * the temp file that's been uploaded doesn't have an extension.
	 *
	 * This method tries to guess the extension from the files mime
	 * type but will return the clientExtension if it fails to do so.
	 *
	 * This method will always return a more or less helpfull extension
	 * but might be insecure if the mime type is not machted. Consider
	 * using GEUSS_EXTENSION for a more safe version.
	 */
	public static function GET_EXTENSION(): string
	{
		return self::GEUSS_EXTENSION() ?: self::GET_CLIENT_EXTENSION();
	}

	/**
	 * Attempts to determine the best file extension from the file's
	 * mime type. In contrast to GET_EXTENSION, this method will return
	 * an empty string if it fails to determine an extension instead of
	 * falling back to the unsecure clientExtension.
	 *
	 * @return string
	 */
	public static function GEUSS_EXTENSION(): string
	{
		$mime = FileLocator::LOCATE_FILE("SystemConfig\Mimes");
		if (is_array($mime)) {
			$mime = array_shift($mime);
		}
		$mimes =  FileLocator::GET_CLASS_NAME($mime);
		$mimes = new $mimes();
		return $mimes::GUESS_EXTENSION_FROM_TYPE(self::GET_MIME_TYPE(), self::GET_CLIENT_EXTENSION()) ?? '';
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the original file extension, based on the file name that
	 * was uploaded. This is NOT a trusted source.
	 * For a trusted version, use GEUSS_EXTENSION() instead.
	 *
	 * @return string
	 */
	public static function GET_CLIENT_EXTENSION(): string
	{
		return pathinfo(self::$originalName, PATHINFO_EXTENSION) ?? '';
	}

	//--------------------------------------------------------------------

	/**
	 * Returns whether the file was uploaded successfully, based on whether
	 * it was uploaded via HTTP and has no errors.
	 *
	 * @return boolean
	 */
	public static function IS_VALID(): bool
	{
		return is_uploaded_file(self::$path) && self::$error === UPLOAD_ERR_OK;
	}

	/**
	 * Save the uploaded file to a new location.
	 *
	 * By default, upload files are saved in writable/uploads directory. The YYYYMMDD folder
	 * and random file name will be created.
	 *
	 * @param  string $folderName the folder name to writable/uploads directory.
	 * @param  string $fileName   the name to rename the file to.
	 * @return string file full path
	 */
	public static function STORE(string $folderName = null, string $fileName = null): string
	{
		$folderName = rtrim($folderName ?? date('Ymd'), '/') . '/';
		$fileName   = $fileName ?? self::GET_RANDOM_NAME();

		// Move the uploaded file to a new location.
		self::MOVE(WRITEPATH . 'uploads/' . $folderName, $fileName);

		return $folderName . self::$name;
	}

	//--------------------------------------------------------------------
}
 