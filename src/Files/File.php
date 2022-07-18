<?php

/**
 * This file is part of the Hkm_code 4 framework.
 *
 * (c) Hkm_code Foundation <admin@hakrichteam.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hkm_code\Files;

use Hkm_code\Files\Exceptions\FileException;
use Hkm_code\Files\Exceptions\FileNotFoundException;
use Hkm_code\Vezirion\FileLocator;
use SplFileInfo;

/**
 * Wrapper for PHP's built-in SplFileInfo, with goodies.
 */
class File extends SplFileInfo
{
	/**
	 * The files size in bytes
	 *
	 * @var integer
	 */
	protected static $size;

	/**
	 * Original MimeType
	 *
	 * @var null|string
	 */
	protected static $originalMimeType = null;

	//--------------------------------------------------------------------

	/**
	 * Run our SplFileInfo constructor with an optional verification
	 * that the path is really a file.
	 *
	 * @param string  $path
	 * @param boolean $checkFile
	 */
	protected static $thiss ;
	public function __construct(string $path, bool $checkFile = false)
	{
		if ($checkFile && ! is_file($path))
		{
			throw FileNotFoundException::FOR_FILE_NOT_FOUND($path);
		}

		parent::__construct($path);
		self::$thiss = $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Retrieve the file size.
	 *
	 * Implementations SHOULD return the value stored in the "size" key of
	 * the file in the $_FILES array if available, as PHP calculates this based
	 * on the actual size transmitted.
	 *
	 * @return integer The file size in bytes
	 */
	public static function GET_SIZE()
	{
		return self::$size ?? (self::$size = self::$thiss->getSize());
	}

	/**
	 * Retrieve the file size by unit.
	 *
	 * @param string $unit
	 *
	 * @return integer|string
	 */
	public static function GET_SIZE_BY_UNIT(string $unit = 'b')
	{
		switch (strtolower($unit))
		{
			case 'kb':
				return number_format(self::GET_SIZE() / 1024, 3);
			case 'mb':
				return number_format((self::GET_SIZE() / 1024) / 1024, 3);
			case 'gb':
				return number_format(((self::GET_SIZE() / 1024) / 1024) / 1024, 3);
			default:
				return self::GET_SIZE();
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Attempts to determine the file extension based on the trusted
	 * getType() method. If the mime type is unknown, will return null.
	 *
	 * @return string|null
	 */
	public static function GUESS_EXTENSION(): ?string
	{
		$mime = FileLocator::LOCATE_FILE("SystemConfig\Mimes");
		if (is_array($mime)) {
			$mime = array_shift($mime);
		}
		$mimes =  FileLocator::GET_CLASS_NAME($mime);
		$mimes = new $mimes();
		return $mimes::GUESS_EXTENSION_FROM_TYPE(self::GET_MIME_TYPE());
	}


	
	//--------------------------------------------------------------------

	/**
	 * Retrieve the media type of the file. SHOULD not use information from
	 * the $_FILES array, but should use other methods to more accurately
	 * determine the type of file, like finfo, or mime_content_type().
	 *
	 * @return string The media type we determined it to be.
	 */
	public static function GET_MIME_TYPE(): string
	{
		if (! function_exists('finfo_open'))
		{
			// @codeCoverageIgnoreStart
			return self::$originalMimeType ?? 'application/octet-stream';
			// @codeCoverageIgnoreEnd
		}

		$finfo    = finfo_open(FILEINFO_MIME_TYPE);
		$mimeType = finfo_file($finfo, self::$thiss->getRealPath() ?: self::$thiss->__toString());
		finfo_close($finfo);
		return $mimeType;
	}

	//--------------------------------------------------------------------

	/**
	 * Generates a random names based on a simple hash and the time, with
	 * the correct file extension attached.
	 *
	 * @return string
	 */
	public static function GET_RANDOM_NAME(): string
	{
		$extension = self::$thiss->getExtension();
		$extension = empty($extension) ? '' : '.' . $extension;
		return time() . '_' . bin2hex(random_bytes(10)) . $extension;
	}

	//--------------------------------------------------------------------

	/**
	 * Moves a file to a new location.
	 *
	 * @param string      $targetPath
	 * @param string|null $name
	 * @param boolean     $overwrite
	 *
	 * @return File
	 */
	public static function MOVE(string $targetPath, string $name = null, bool $overwrite = false)
	{
		$targetPath  = rtrim($targetPath, '/') . '/';
		$name        = $name ?? self::$thiss->getBaseName();
		$destination = $overwrite ? $targetPath . $name : self::GET_DESTINATION($targetPath . $name);

		$oldName = self::$thiss->getRealPath() ?: self::$thiss->__toString();

		if (! @rename($oldName, $destination))
		{
			$error = error_get_last();
			throw FileException::FOR_UNABLE_TO_MOVE(self::$thiss->getBaseName(), $targetPath, strip_tags($error['message']));
		}
 
		@chmod($destination, 0777 & ~umask());

		return new File($destination);
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the destination path for the move operation where overwriting is not expected.
	 *
	 * First, it checks whether the delimiter is present in the filename, if it is, then it checks whether the
	 * last element is an integer as there may be cases that the delimiter may be present in the filename.
	 * For the all other cases, it appends an integer starting from zero before the file's extension.
	 *
	 * @param string  $destination
	 * @param string  $delimiter
	 * @param integer $i
	 *
	 * @return string
	 */
	public static function GET_DESTINATION(string $destination, string $delimiter = '_', int $i = 0): string
	{
		while (is_file($destination))
		{
			$info      = pathinfo($destination);
			$extension = isset($info['extension']) ? '.' . $info['extension'] : '';
			if (strpos($info['filename'], $delimiter) !== false)
			{
				$parts = explode($delimiter, $info['filename']);
				if (is_numeric(end($parts)))
				{
					$i = end($parts);
					array_pop($parts);
					$parts[]     = ++ $i;
					$destination = $info['dirname'] . '/' . implode($delimiter, $parts) . $extension;
				}
				else
				{
					$destination = $info['dirname'] . '/' . $info['filename'] . $delimiter . ++ $i . $extension;
				}
			}
			else
			{
				$destination = $info['dirname'] . '/' . $info['filename'] . $delimiter . ++ $i . $extension;
			}
		}
		return $destination;
	}

	//--------------------------------------------------------------------
}
