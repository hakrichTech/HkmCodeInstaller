<?php

/**
 * This file is part of the Hkm_code 4 framework.
 *
 * (c) Hkm_code Foundation <admin@Hkm_code.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hkm_code\HTTP;

use Hkm_code\Exceptions\DownloadException;
use Hkm_code\Files\File;

/**
 * HTTP response when a download is requested.
 */
class DownloadResponse extends Response
{
	/**
	 * Download file name
	 *
	 * @var string
	 */
	private static $filename;

	/**
	 * Download for file
	 *
	 * @var File|null
	 */
	private static $file;

	/**
	 * mime set flag
	 *
	 * @var boolean
	 */
	private static $setMime;

	/**
	 * Download for binary
	 *
	 * @var string|null
	 */
	private static $binary;

	/**
	 * Download charset
	 *
	 * @var string
	 */
	private static $charset = 'UTF-8';

	/**
	 * Download reason
	 *
	 * @var string
	 */
	protected static $reason = 'OK';

	/**
	 * The current status code for this response.
	 *
	 * @var integer
	 */
	protected static $statusCode = 200;

	/**
	 * Constructor.
	 *
	 * @param string  $filename
	 * @param boolean $setMime
	 */
	public function __construct(string $filename, bool $setMime)
	{
		parent::__construct(hkm_config('App'));

		self::$filename = $filename;
		self::$setMime  = $setMime;

		// Make sure the content type is either specified or detected
		self::REMOVE_HEADER('Content-Type');
	}
 
	/**
	 * set download for binary string.
	 *
	 * @param string $binary
	 */
	public static function SET_BINARY(string $binary)
	{
		if (self::$file !== null)
		{
			throw DownloadException::FOR_CANNOT_SET_BINARY();
		}

		self::$binary = $binary;
	}

	/**
	 * set download for file.
	 *
	 * @param string $filepath
	 */
	public static function SET_FILE_PATH(string $filepath)
	{
		if (self::$binary !== null)
		{
			throw DownloadException::FOR_CONNOT_SET_FILE_PATH($filepath);
		}

		self::$file = new File($filepath, true);
	}

	/**
	 * set name for the download.
	 *
	 * @param string $filename
	 *
	 * @return $this
	 */
	public static function SET_FILE_NAME(string $filename)
	{
		self::$filename = $filename;
		return self::$thiss;
	}

	/**
	 * get content length.
	 *
	 * @return integer
	 */
	public static function GET_CONTENT_LENGTH() : int
	{
		if (is_string(self::$binary))
		{
			return strlen(self::$binary);
		}

		if (self::$file instanceof File)
		{
			return self::$file->getSize();
		}

		return 0;
	}

	/**
	 * Set content type by guessing mime type from file extension
	 */
	private static function SET_CONTENT_TYPE_BY_MIME_TYPE()
	{
		$mime    = null;
		$charset = '';

		if (self::$setMime === true && ($lastDotPosition = strrpos(self::$filename, '.')) !== false)
		{
			$mime    = hkm_config('Mimes')::GUESS_TYPE_FROM_EXTENTENSION(substr(self::$filename, $lastDotPosition + 1));
			$charset = self::$charset;
		}

		if (! is_string($mime))
		{
			// Set the default MIME type to send
			$mime    = 'application/octet-stream';
			$charset = '';
		}

		self::SET_CONTENT_TYPE($mime, $charset);
	}

	/**
	 * get download filename.
	 *
	 * @return string
	 */
	private static function GET_DOWNLOAD_FILE_NAME(): string
	{
		$filename  = self::$filename;
		$x         = explode('.', self::$filename);
		$extension = end($x);

		/* It was reported that browsers on Android 2.1 (and possibly older as well)
		 * need to have the filename extension upper-cased in order to be able to
		 * download it.
		 *
		 * Reference: http://digiblog.de/2011/04/19/android-and-the-download-file-headers/
		 */
		// @todo: depend super global
		if (count($x) !== 1 && isset($_SERVER['HTTP_USER_AGENT'])
				&& preg_match('/Android\s(1|2\.[01])/', $_SERVER['HTTP_USER_AGENT']))
		{
			$x[count($x) - 1] = strtoupper($extension);
			$filename         = implode('.', $x);
		}

		return $filename;
	}

	/**
	 * get Content-Disposition Header string.
	 *
	 * @return string
	 */
	private static function GET_CONTENT_DISPOSITION() : string
	{
		$downloadFilename = self::GET_DOWNLOAD_FILE_NAME();

		$utf8Filename = $downloadFilename;

		if (strtoupper(self::$charset) !== 'UTF-8')
		{
			$utf8Filename = mb_convert_encoding($downloadFilename, 'UTF-8', self::$charset);
		}

		$result = sprintf('attachment; filename="%s"', $downloadFilename);

		if ($utf8Filename)
		{
			$result .= '; filename*=UTF-8\'\'' . rawurlencode($utf8Filename);
		}

		return $result;
	}

	//--------------------------------------------------------------------

	/**
	 * Disallows status changing.
	 *
	 * @param integer $code
	 * @param string  $reason
	 *
	 * @throws DownloadException
	 */
	public static function SET_STATUS_CODE(int $code, string $reason = '')
	{
		throw DownloadException::FOR_CANNOT_SET_STATUS_CODE($code, $reason);
	}

	//--------------------------------------------------------------------
	/**
	 * Sets the Content Type header for this response with the mime type
	 * and, optionally, the charset.
	 *
	 * @param string $mime
	 * @param string $charset
	 *
	 * @return ResponseInterface
	 */
	public static function SET_CONTENT_TYPE(string $mime, string $charset = 'UTF-8')
	{
		parent::SET_CONTENT_TYPE($mime, $charset);

		if ($charset !== '')
		{
			self::$charset = $charset;
		}

		return self::$thiss;
	}

	/**
	 * Sets the appropriate headers to ensure this response
	 * is not cached by the browsers.
	 */
	public static function NO_CACHE(): self
	{
		self::REMOVE_HEADER('Cache-control');

		self::SET_HEADER('Cache-control', ['private static', 'no-transform', 'no-store', 'must-revalidate']);

		return self::$thiss;
	}

	//--------------------------------------------------------------------

	/**
	 * Disables cache configuration.
	 *
	 * @param array $options
	 *
	 * @throws DownloadException
	 */
	public static function SET_CACHE(array $options = [])
	{
		throw DownloadException::FOR_CANNOT_SET_CACHE();
	}

	//--------------------------------------------------------------------
	// Output Methods
	//--------------------------------------------------------------------

	/**
	 * {@inheritDoc}
	 *
	 * @todo Do downloads need CSP or Cookies? Compare with ResponseTrait::send()
	 */
	public static function SEND()
	{
		self::BUILD_HEADERS();
		self::SEND_HEADERS();
		self::SEND_BODY();

		return self::$thiss;
	}

	/**
	 * set header for file download.
	 */
	public static function BUILD_HEADERS()
	{
		if (! self::HAS_HEADER('Content-Type'))
		{
			self::SET_CONTENT_TYPE_BY_MIME_TYPE();
		}

		self::SET_HEADER('Content-Disposition', self::GET_CONTENT_DISPOSITION());
		self::SET_HEADER('Expires-Disposition', '0');
		self::SET_HEADER('Content-Transfer-Encoding', 'binary');
		self::SET_HEADER('Content-Length', (string) self::GET_CONTENT_LENGTH());
		self::NO_CACHE();
	}

	/**
	 * output download file text.
	 *
	 * @throws DownloadException
	 *
	 * @return DownloadResponse
	 */
	public static function SEND_BODY()
	{
		if (self::$binary !== null)
		{
			return self::SEND_BODY_BY_BINARY();
		}

		if (self::$file !== null)
		{
			return self::SEND_BODY_BY_FILE_PATH();
		}
 
		throw DownloadException::FOR_NOT_FOUND_DOWNLOAD_SOURCE();
	}

	/**
	 * output download text by file.
	 *
	 * @return DownloadResponse
	 */
	private static function SEND_BODY_BY_FILE_PATH()
	{
		$splFileObject = self::$file->openFile('rb');

		// Flush 1MB chunks of data
		while (! $splFileObject->eof() && ($data = $splFileObject->fread(1048576)) !== false)
		{
			echo $data;
		}

		return self::$thiss;
	}

	/**
	 * output download text by binary
	 *
	 * @return DownloadResponse
	 */
	private static function SEND_BODY_BY_BINARY()
	{
		echo self::$binary;

		return self::$thiss;
	}
}
