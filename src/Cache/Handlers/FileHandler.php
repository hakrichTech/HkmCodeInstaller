<?php

/**
 * This file is part of the Hkm_code 4 framework.
 *
 * (c) Hkm_code Foundation <admin@hakrichteam.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hkm_code\Cache\Handlers;

use Hkm_code\Cache\Exceptions\CacheException;
use Hkm_code\Vezirion\BaseVezirion;
use Throwable;

/**
 * File system cache handler
 */
class FileHandler extends BaseHandler
{
	/**
	 * Maximum key length.
	 */
	public  const MAX_KEY_LENGTH = 255;

	/**
	 * Where to store cached files on the disk.
	 *
	 * @var string
	 */
	protected static $path;
 
	/**
	 * Mode for the stored files.
	 * Must be chmod-safe (octal).
	 *
	 * @var integer
	 *
	 * @see https://www.php.net/manual/en/function.chmod.php
	 */
	protected static $mode;

	//--------------------------------------------------------------------

	/**
	 * Constructor.
	 *
	 * @throws CacheException
	 */
	public function __construct( $config)
	{
		if (! property_exists($config, 'file'))
		{
			$config::$file = [
				'storePath' => $config::$storePath ?? WRITEPATH . 'cache',
				'mode'      => 0640,
			];
		}


		self::$path = ! empty($config::$file['storePath']) ? $config::$file['storePath'] : WRITEPATH . 'cache';
		self::$path = rtrim(self::$path, '/') . '/';

		if (! hkm_is_really_writable(self::$path))
		{
			throw CacheException::forUnableToWrite(self::$path);
		}

		self::$mode   = $config::$file['mode'] ?? 0640;
		self::$prefix = $config::$prefix;
	}

	//--------------------------------------------------------------------

	/**
	 * Takes care of any handler-specific setup that must be done.
	 */
	public static function INITIALIZE()
	{
	}

	//--------------------------------------------------------------------

	/**
	 * Attempts to fetch an item from the cache store.
	 *
	 * @param string $key Cache item name
	 *
	 * @return mixed
	 */
	public static function GET(string $key)
	{
		
		$key  = static::VALIDATE_KEY($key, self::$prefix);
		$data = self::GET_ITEM($key);

		return is_array($data) ? $data['data'] : null;
	}

	//--------------------------------------------------------------------

	/**
	 * Saves an item to the cache store.
	 *
	 * @param string  $key   Cache item name
	 * @param mixed   $value The data to save
	 * @param integer $ttl   Time To Live, in seconds (default 60)
	 *
	 * @return boolean Success or failure
	 */
	public static function SAVE(string $key, $value, int $ttl = 60)
	{
		$key = static::VALIDATE_KEY($key, self::$prefix);

		$contents = [
			'time' => time(),
			'ttl'  => $ttl,
			'data' => $value,
		];

		if (self::WRITE_FILE(self::$path . $key, serialize($contents)))
		{
			try
			{
				chmod(self::$path . $key, self::$mode);
			}
			// @codeCoverageIgnoreStart
			catch (Throwable $e)
			{
				log_message('debug', 'Failed to set mode on cache file: ' . $e->getMessage());
			}
			// @codeCoverageIgnoreEnd

			return true;
		}

		return false;
	}

	//--------------------------------------------------------------------

	/**
	 * Deletes a specific item from the cache store.
	 *
	 * @param string $key Cache item name
	 *
	 * @return boolean Success or failure
	 */
	public static function DELETE(string $key)
	{
		$key = static::VALIDATE_KEY($key, self::$prefix);

		return is_file(self::$path . $key) && unlink(self::$path . $key);
	}

	//--------------------------------------------------------------------

	/**
	 * Deletes items from the cache store matching a given pattern.
	 *
	 * @param string $pattern Cache items glob-style pattern
	 *
	 * @return integer The number of deleted items
	 */
	public static function DELETE_MATCHING(string $pattern)
	{
		$deleted = 0;

		foreach (glob(self::$path . $pattern, GLOB_NOSORT) as $filename)
		{
			if (is_file($filename) && @unlink($filename))
			{
				$deleted++;
			}
		}

		return $deleted;
	}

	//--------------------------------------------------------------------

	/**
	 * Performs atomic incrementation of a raw stored value.
	 *
	 * @param string  $key    Cache ID
	 * @param integer $offset Step/value to increase by
	 *
	 * @return boolean
	 */
	public static function INCREMENT(string $key, int $offset = 1)
	{
		$key  = static::VALIDATE_KEY($key, self::$prefix);
		$data = self::GET_ITEM($key);

		if ($data === false)
		{
			$data = [
				'data' => 0,
				'ttl'  => 60,
			];
		}
		elseif (! is_int($data['data']))
		{
			return false;
		}

		$newValue = $data['data'] + $offset;

		return self::SAVE($key, $newValue, $data['ttl']) ? $newValue : false;
	}

	//--------------------------------------------------------------------

	/**
	 * Performs atomic decrementation of a raw stored value.
	 *
	 * @param string  $key    Cache ID
	 * @param integer $offset Step/value to increase by
	 *
	 * @return boolean
	 */
	public static function DECREMENT(string $key, int $offset = 1)
	{
		$key  = static::VALIDATE_KEY($key, self::$prefix);
		$data = self::GET_ITEM($key);

		if ($data === false)
		{
			$data = [
				'data' => 0,
				'ttl'  => 60,
			];
		}
		elseif (! is_int($data['data']))
		{
			return false;
		}

		$newValue = $data['data'] - $offset;

		return self::SAVE($key, $newValue, $data['ttl']) ? $newValue : false;
	}

	//--------------------------------------------------------------------

	/**
	 * Will delete all items in the entire cache.
	 *
	 * @return boolean Success or failure
	 */
	public static function CLEAN()
	{
		return self::DELETE_FILES(self::$path, false, true);
	}

	//--------------------------------------------------------------------

	/**
	 * Returns information on the entire cache.
	 *
	 * The information returned and the structure of the data
	 * varies depending on the handler.
	 *
	 * @return array|false
	 */
	public static function GET_CACHE_INFO()
	{
		return self::GET_DIR_FILE_INFO(self::$path);
	}

	//--------------------------------------------------------------------

	/**
	 * Returns detailed information about the specific item in the cache.
	 *
	 * @param string $key Cache item name.
	 *
	 * @return array|false|null
	 *   Returns null if the item does not exist, otherwise array<string, mixed>
	 *   with at least the 'expire' key for absolute epoch expiry (or null).
	 *   Some handlers may return false when an item does not exist, which is deprecated.
	 */
	public static function GET_METADATA(string $key)
	{
		$key = static::VALIDATE_KEY($key, self::$prefix);

		if (false === $data = self::GET_ITEM($key))
		{
			return false; // This will return null in a future release
		}

		return [
			'expire' => $data['time'] + $data['ttl'],
			'mtime'  => filemtime(self::$path . $key),
			'data'   => $data['data'],
		];
	}

	//--------------------------------------------------------------------

	/**
	 * Determines if the driver is supported on this system.
	 *
	 * @return boolean
	 */
	public static function IS_SUPPORTED(): bool
	{
		return is_writable(self::$path);
	}

	//--------------------------------------------------------------------

	/**
	 * Does the heavy lifting of actually retrieving the file and
	 * verifying it's age.
	 *
	 * @param string $filename
	 *
	 * @return boolean|mixed
	 */
	protected static function GET_ITEM(string $filename)
	{
		if (! is_file(self::$path . $filename))
		{
			return false;
		}

		$data = @unserialize(file_get_contents(self::$path . $filename));
		if (! is_array($data) || ! isset($data['ttl']))
		{
			return false;
		}

		// @phpstan-ignore-next-line
		if ($data['ttl'] > 0 && time() > $data['time'] + $data['ttl'])
		{
			// If the file is still there then try to remove it
			if (is_file(self::$path . $filename))
			{
				@unlink(self::$path . $filename);
			}

			return false;
		}

		return $data;
	}

	//--------------------------------------------------------------------
	//--------------------------------------------------------------------
	// SUPPORT METHODS FOR FILES
	//--------------------------------------------------------------------

	/**
	 * Writes a file to disk, or returns false if not successful.
	 *
	 * @param string $path
	 * @param string $data
	 * @param string $mode
	 *
	 * @return boolean
	 */
	protected static function WRITE_FILE($path, $data, $mode = 'wb')
	{
		if (($fp = @fopen($path, $mode)) === false)
		{
			return false;
		}

		flock($fp, LOCK_EX);

		for ($result = $written = 0, $length = strlen($data); $written < $length; $written += $result)
		{
			if (($result = fwrite($fp, substr($data, $written))) === false)
			{
				break;
			}
		}

		flock($fp, LOCK_UN);
		fclose($fp);

		return is_int($result);
	}

	//--------------------------------------------------------------------

	/**
	 * Delete Files
	 *
	 * Deletes all files contained in the supplied directory path.
	 * Files must be writable or owned by the system in order to be deleted.
	 * If the second parameter is set to TRUE, any directories contained
	 * within the supplied base directory will be nuked as well.
	 *
	 * @param string  $path   File path
	 * @param boolean $delDir Whether to delete any directories found in the path
	 * @param boolean $htdocs Whether to skip deleting .htaccess and index page files
	 * @param integer $_level Current directory depth level (default: 0; internal use only)
	 *
	 * @return boolean
	 */
	protected static function DELETE_FILES(string $path, bool $delDir = false, bool $htdocs = false, int $_level = 0): bool
	{
		// Trim the trailing slash
		$path = rtrim($path, '/\\');

		if (! $currentDir = @opendir($path))
		{
			return false;
		}

		while (false !== ($filename = @readdir($currentDir)))
		{
			if ($filename !== '.' && $filename !== '..')
			{
				if (is_dir($path . DIRECTORY_SEPARATOR . $filename) && $filename[0] !== '.')
				{
					self::DELETE_FILES($path . DIRECTORY_SEPARATOR . $filename, $delDir, $htdocs, $_level + 1);
				}
				elseif ($htdocs !== true || ! preg_match('/^(\.htaccess|index\.(html|htm|php)|web\.config)$/i', $filename))
				{
					@unlink($path . DIRECTORY_SEPARATOR . $filename);
				}
			}
		}

		closedir($currentDir);

		return ($delDir === true && $_level > 0) ? @rmdir($path) : true;
	}

	//--------------------------------------------------------------------

	/**
	 * Get Directory File Information
	 *
	 * Reads the specified directory and builds an array containing the filenames,
	 * filesize, dates, and permissions
	 *
	 * Any sub-folders contained within the specified path are read as well.
	 *
	 * @param string  $sourceDir    Path to source
	 * @param boolean $topLevelOnly Look only at the top level directory specified?
	 * @param boolean $_recursion   Internal variable to determine recursion status - do not use in calls
	 *
	 * @return array|false
	 */
	protected static function GET_DIR_FILE_INFO(string $sourceDir, bool $topLevelOnly = true, bool $_recursion = false)
	{
		static $_filedata = [];
		$relativePath     = $sourceDir;

		if ($fp = @opendir($sourceDir))
		{
			// reset the array and make sure $source_dir has a trailing slash on the initial call
			if ($_recursion === false)
			{
				$_filedata = [];
				$sourceDir = rtrim(realpath($sourceDir) ?: $sourceDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
			}

			// Used to be foreach (scandir($source_dir, 1) as $file), but scandir() is simply not as fast
			while (false !== ($file = readdir($fp)))
			{
				if (is_dir($sourceDir . $file) && $file[0] !== '.' && $topLevelOnly === false)
				{
					self::GET_DIR_FILE_INFO($sourceDir . $file . DIRECTORY_SEPARATOR, $topLevelOnly, true);
				}
				elseif ($file[0] !== '.')
				{
					$_filedata[$file]                  = self::GET_FILE_INFO($sourceDir . $file);
					$_filedata[$file]['relative_path'] = $relativePath;
				}
			}

			closedir($fp);

			return $_filedata;
		}

		return false;
	}

	//--------------------------------------------------------------------

	/**
	 * Get File Info
	 *
	 * Given a file and path, returns the name, path, size, date modified
	 * Second parameter allows you to explicitly declare what information you want returned
	 * Options are: name, server_path, size, date, readable, writable, executable, fileperms
	 * Returns FALSE if the file cannot be found.
	 *
	 * @param string $file           Path to file
	 * @param mixed  $returnedValues Array or comma separated string of information returned
	 *
	 * @return array|false
	 */
	protected static function GET_FILE_INFO(string $file, $returnedValues = ['name', 'server_path', 'size', 'date'])
	{
		if (! is_file($file))
		{
			return false;
		}

		if (is_string($returnedValues))
		{
			$returnedValues = explode(',', $returnedValues);
		}

		$fileInfo = [];

		foreach ($returnedValues as $key)
		{
			switch ($key)
			{
				case 'name':
					$fileInfo['name'] = basename($file);
					break;
				case 'server_path':
					$fileInfo['server_path'] = $file;
					break;
				case 'size':
					$fileInfo['size'] = filesize($file);
					break;
				case 'date':
					$fileInfo['date'] = filemtime($file);
					break;
				case 'readable':
					$fileInfo['readable'] = is_readable($file);
					break;
				case 'writable':
					$fileInfo['writable'] = is_writable($file);
					break;
				case 'executable':
					$fileInfo['executable'] = is_executable($file);
					break;
				case 'fileperms':
					$fileInfo['fileperms'] = fileperms($file);
					break;
			}
		}

		return $fileInfo;
	}
}
