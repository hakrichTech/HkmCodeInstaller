<?php

/**
 * This file is part of the Hkm_code 4 framework.
 *
 * (c) Hkm_code Foundation <admin@hakrichteam.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Hkm_code File System Helpers
 */
// ------------------------------------------------------------------------

if (! function_exists('hkm_directory_map'))
{
	/**
	 * Create a Directory Map
	 *
	 * Reads the specified directory and builds an array
	 * representation of it. Sub-folders contained with the
	 * directory will be mapped as well.
	 *
	 * @param string  $sourceDir      Path to source
	 * @param integer $directoryDepth Depth of directories to traverse
	 *                                 (0 = fully recursive, 1 = current dir, etc)
	 * @param boolean $hidden         Whether to show hidden files
	 *
	 * @return array
	 */
	function hkm_directory_map(string $sourceDir, int $directoryDepth = 0, bool $hidden = false): array
	{
		try
		{
			$fp = opendir($sourceDir);

			$fileData  = [];
			$newDepth  = $directoryDepth - 1;
			$sourceDir = rtrim($sourceDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

			while (false !== ($file = readdir($fp)))
			{
				// Remove '.', '..', and hidden files [optional]
				if ($file === '.' || $file === '..' || ($hidden === false && $file[0] === '.'))
				{
					continue;
				}

				if (is_dir($sourceDir . $file))
				{
					$file .= DIRECTORY_SEPARATOR;
				}

				if (($directoryDepth < 1 || $newDepth > 0) && is_dir($sourceDir . $file))
				{
					$fileData[$file] = hkm_directory_map($sourceDir . $file, $newDepth, $hidden);
				}
				else
				{
					$fileData[] = $file;
				}
			}

			closedir($fp);
			return $fileData;
		}
		catch (Throwable $e)
		{
			return [];
		}
	}
}

// ------------------------------------------------------------------------

if (! function_exists('hkm_directory_mirror'))
{
	/**
	 * Recursively copies the files and directories of the origin directory
	 * into the target directory, i.e. "mirror" its contents.
	 *
	 * @param string  $originDir
	 * @param string  $targetDir
	 * @param boolean $overwrite Whether individual files overwrite on collision
	 *
	 * @return void
	 *
	 * @throws InvalidArgumentException
	 */
	function hkm_directory_mirror(string $originDir, string $targetDir, bool $overwrite = true): void
	{
		if (! is_dir($originDir = rtrim($originDir, '\\/')))
		{
			throw new InvalidArgumentException(sprintf('The origin directory "%s" was not found.', $originDir));
		}

		if (! is_dir($targetDir = rtrim($targetDir, '\\/')))
		{
			@mkdir($targetDir, 0755, true);
		}

		$dirLen = strlen($originDir);

		/**
		 * @var SplFileInfo $file
		 */
		foreach (new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($originDir, FilesystemIterator::SKIP_DOTS),
			RecursiveIteratorIterator::SELF_FIRST
		) as $file)
		{
			$origin = $file->getPathname();
			$target = $targetDir . substr($origin, $dirLen);

			if ($file->isDir())
			{
				mkdir($target, 0755);
			}
			elseif (! is_file($target) || ($overwrite && is_file($target)))
			{
				copy($origin, $target);
			}
		}
	}
}

// ------------------------------------------------------------------------

if (! function_exists('hkm_write_file'))
{
	/**
	 * Write File
	 *
	 * Writes data to the file specified in the path.
	 * Creates a new file if non-existent.
	 *
	 * @param string $path File path
	 * @param string $data Data to write 
	 * @param string $mode fopen() mode (default: 'w+')
	 *
	 * @return boolean
	 */
	function hkm_write_file(string $path, string $data, string $mode = 'w+'): bool
	{
		try
		{
			$fp = fopen($path, $mode);

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
		catch (Throwable $e)
		{
			return false;
		}
	}
}

// ------------------------------------------------------------------------

if (! function_exists('hkm_delete_files'))
{
	/**
	 * Delete Files
	 *
	 * Deletes all files contained in the supplied directory path.
	 * Files must be writable or owned by the system in order to be deleted.
	 * If the second parameter is set to true, any directories contained
	 * within the supplied base directory will be nuked as well.
	 *
	 * @param string  $path   File path
	 * @param boolean $delDir Whether to delete any directories found in the path
	 * @param boolean $htdocs Whether to skip deleting .htaccess and index page files
	 * @param boolean $hidden Whether to include hidden files (files beginning with a period)
	 *
	 * @return boolean
	 */
	function hkm_delete_files(string $path, bool $delDir = false, bool $htdocs = false, bool $hidden = false): bool
	{
		$path = realpath($path) ?: $path;
		$path = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

		try
		{
			foreach (new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
				RecursiveIteratorIterator::CHILD_FIRST
			) as $object)
			{
				$filename = $object->getFilename();
				if (! $hidden && $filename[0] === '.')
				{
					continue;
				}

				if (! $htdocs || ! preg_match('/^(\.htaccess|index\.(html|htm|php)|web\.config)$/i', $filename))
				{
					$isDir = $object->isDir();
					if ($isDir && $delDir)
					{
						rmdir($object->getPathname());
						continue;
					}
					if (! $isDir)
					{
						unlink($object->getPathname());
					}
				}
			}

			return true;
		}
		catch (Throwable $e)
		{
			return false;
		}
	}
}



// --------------------------------------------------------------------

if (! function_exists('hkm_get_dir_file_info'))
{
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
	 * @param boolean $recursion    Internal variable to determine recursion status - do not use in calls
	 *
	 * @return array
	 */
	function hkm_get_dir_file_info(string $sourceDir, bool $topLevelOnly = true, bool $recursion = false): array
	{
		static $fileData = [];
		$relativePath    = $sourceDir;

		try
		{
			$fp = opendir($sourceDir); {
				// reset the array and make sure $source_dir has a trailing slash on the initial call
			if ($recursion === false)
				{
				$fileData  = [];
				$sourceDir = rtrim(realpath($sourceDir), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
			}

				// Used to be foreach (scandir($source_dir, 1) as $file), but scandir() is simply not as fast
			while (false !== ($file = readdir($fp)))
				{
				if (is_dir($sourceDir . $file) && $file[0] !== '.' && $topLevelOnly === false)
				{
					hkm_get_dir_file_info($sourceDir . $file . DIRECTORY_SEPARATOR, $topLevelOnly, true);
				}
				elseif ($file[0] !== '.')
				{
					$fileData[$file]                  = hkm_get_file_info($sourceDir . $file);
					$fileData[$file]['relative_path'] = $relativePath;
				}
			}

				closedir($fp);
				return $fileData;
			}
		}
		catch (Throwable $fe)
		{
			return [];
		}
	}
}

// --------------------------------------------------------------------

if (! function_exists('hkm_get_file_info'))
{
	/**
	 * Get File Info
	 *
	 * Given a file and path, returns the name, path, size, date modified
	 * Second parameter allows you to explicitly declare what information you want returned
	 * Options are: name, server_path, size, date, readable, writable, executable, fileperms
	 * Returns false if the file cannot be found.
	 *
	 * @param string $file           Path to file
	 * @param mixed  $returnedValues Array or comma separated string of information returned
	 *
	 * @return array|null
	 */
	function hkm_get_file_info(string $file, $returnedValues = ['name', 'server_path', 'size', 'date'])
	{
		if (! is_file($file))
		{
			return null;
		}

		$fileInfo = [];

		if (is_string($returnedValues))
		{
			$returnedValues = explode(',', $returnedValues);
		}

		foreach ($returnedValues as $key)
		{
			switch ($key) {
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
					$fileInfo['writable'] = hkm_is_really_writable($file);
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

function hkm_get_file_data_in_array($file,$escap = "\n",$size = 0)
{
	// We don't need to write to the file, so just open for reading.
   
	$fp = fopen( $file, 'r' );

	if ( $fp ) {
		// Pull only the first 8 KB of the file in.
		$file_data = fread( $fp, $size?$size :8 * KB_IN_BYTES );

		// PHP will close file handle, but we are good citizens.
		fclose( $fp );
	} else {
		$file_data = '';
	}

	// Make sure we catch CR-only line endings.
	$file_data = str_replace( "\r", "\n", $file_data );
	return explode($escap,$file_data);
}
/**
 * Retrieve metadata from a file.
 *
 * Searches for metadata in the first 8 KB of a file, such as a plugin or theme.
 * Each piece of metadata must be on its own line. Fields can not span multiple
 * lines, the value will get cut at the end of the first line.
 *
 * If the file data is not within that first 8 KB, then the author should correct
 * their plugin file and move the data headers to the top.
 *
 *
 * @param string $file            Absolute path to the file.
 * @param array  $default_headers List of headers, in the format `array( 'HeaderKey' => 'Header Name' )`.
 * @param string $context         Optional. If specified adds filter hook {@see 'extra_$context_headers'}.
 *                                Default empty.
 * @param int $size            The size of bit to read. By default is 8 KB.
 * @return string[] Array of file header values keyed by header name.
 */
function hkm_get_file_data( $file, $default_headers, $context = '', $size = 0 ) {
	// We don't need to write to the file, so just open for reading.
   
	$fp = fopen( $file, 'r' );

	if ( $fp ) {
		// Pull only the first 8 KB of the file in.
		$file_data = fread( $fp, $size?$size :8 * KB_IN_BYTES );

		// PHP will close file handle, but we are good citizens.
		fclose( $fp );
	} else {
		$file_data = '';
	}

	// Make sure we catch CR-only line endings.
	$file_data = str_replace( "\r", "\n", $file_data );

	/**
	 * Filters extra file headers by context.
	 *
	 * The dynamic portion of the hook name, `$context`, refers to
	 * the context where extra headers might be loaded.
	 *
	 *
	 * @param array $extra_context_headers Empty array by default.
	 */
	$extra_headers = $context ? hkm_apply_filters( "extra_{$context}_headers", array() ) : array();
	if ( $extra_headers ) {
		$extra_headers = array_combine( $extra_headers, $extra_headers ); // Keys equal values.
		$all_headers   = array_merge( $extra_headers, (array) $default_headers );
	} else {
		$all_headers = $default_headers;
	}


	foreach ( $all_headers as $field => $regex ) {
		if ( preg_match( '/^(?:[ \t]*<\?php)?[ \t\/*#@]*' . preg_quote( $regex, '/' ) . ':(.*)$/mi', $file_data, $match ) && $match[1] ) {
			$all_headers[ $field ] = _hkm_cleanup_header_comment( $match[1] );
		} else {
			$all_headers[ $field ] = '';
		}
	}


	return $all_headers;
}

/**
 * Strip close comment and close php tags from file headers used by WP.
 *
 * @access private
 *
 * @see https://core.trac.wordpress.org/ticket/8497
 *
 * @param string $str Header comment to clean up.
 * @return string
 */
function _hkm_cleanup_header_comment( $str ) {
	return trim( preg_replace( '/\s*(?:\*\/|\?>).*/', '', $str ) );
}
// --------------------------------------------------------------------

if (! function_exists('hkm_symbolic_permissions'))
{
	/**
	 * Symbolic Permissions
	 *
	 * Takes a numeric value representing a file's permissions and returns
	 * standard symbolic notation representing that value
	 *
	 * @param  integer $perms Permissions
	 * @return string
	 */
	function hkm_symbolic_permissions(int $perms): string
	{
		if (($perms & 0xC000) === 0xC000)
		{
			$symbolic = 's'; // Socket
		}
		elseif (($perms & 0xA000) === 0xA000)
		{
			$symbolic = 'l'; // Symbolic Link
		}
		elseif (($perms & 0x8000) === 0x8000)
		{
			$symbolic = '-'; // Regular
		}
		elseif (($perms & 0x6000) === 0x6000)
		{
			$symbolic = 'b'; // Block special
		}
		elseif (($perms & 0x4000) === 0x4000)
		{
			$symbolic = 'd'; // Directory
		}
		elseif (($perms & 0x2000) === 0x2000)
		{
			$symbolic = 'c'; // Character special
		}
		elseif (($perms & 0x1000) === 0x1000)
		{
			$symbolic = 'p'; // FIFO pipe
		}
		else
		{
			$symbolic = 'u'; // Unknown
		}

		// Owner
		$symbolic .= (($perms & 0x0100) ? 'r' : '-')
				. (($perms & 0x0080) ? 'w' : '-')
				. (($perms & 0x0040) ? (($perms & 0x0800) ? 's' : 'x') : (($perms & 0x0800) ? 'S' : '-'));

		// Group
		$symbolic .= (($perms & 0x0020) ? 'r' : '-')
				. (($perms & 0x0010) ? 'w' : '-')
				. (($perms & 0x0008) ? (($perms & 0x0400) ? 's' : 'x') : (($perms & 0x0400) ? 'S' : '-'));

		// World
		$symbolic .= (($perms & 0x0004) ? 'r' : '-')
				. (($perms & 0x0002) ? 'w' : '-')
				. (($perms & 0x0001) ? (($perms & 0x0200) ? 't' : 'x') : (($perms & 0x0200) ? 'T' : '-'));

		return $symbolic;
	}
}

// --------------------------------------------------------------------

if (! function_exists('hkm_octal_permissions'))
{
	/**
	 * Octal Permissions
	 *
	 * Takes a numeric value representing a file's permissions and returns
	 * a three character string representing the file's octal permissions
	 *
	 * @param  integer $perms Permissions
	 * @return string
	 */
	function hkm_octal_permissions(int $perms): string
	{
		return substr(sprintf('%o', $perms), -3);
	}
}

// ------------------------------------------------------------------------

if (! function_exists('hkm_same_file'))
{
	/**
	 * Checks if two files both exist and have identical hashes
	 *
	 * @param string $file1
	 * @param string $file2
	 *
	 * @return boolean  Same or not
	 */
	function hkm_same_file(string $file1, string $file2): bool
	{
		return is_file($file1) && is_file($file2) && md5_file($file1) === md5_file($file2);
	}
}

// ------------------------------------------------------------------------

if (! function_exists('hkm_set_realpath'))
{
	/**
	 * Set Realpath
	 *
	 * @param string  $path
	 * @param boolean $checkExistence Checks to see if the path exists
	 *
	 * @return string
	 */
	function hkm_set_realpath(string $path, bool $checkExistence = false): string
	{
		// Security check to make sure the path is NOT a URL. No remote file inclusion!
		if (preg_match('#^(http:\/\/|https:\/\/|www\.|ftp)#i', $path) || filter_var($path, FILTER_VALIDATE_IP) === $path)
		{
			throw new InvalidArgumentException('The path you submitted must be a local server path, not a URL');
		}

		// Resolve the path
		if (realpath($path) !== false)
		{
			$path = realpath($path);
		}
		elseif ($checkExistence && ! is_dir($path) && ! is_file($path))
		{
			throw new InvalidArgumentException('Not a valid path: ' . $path);
		}

		// Add a trailing slash, if this is a directory
		return is_dir($path) ? rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR : $path;
	}
}

function hkm_is_writable( $path ) {
	if ( 'WIN' === strtoupper( substr( PHP_OS, 0, 3 ) ) ) {
		return win_is_writable( $path );
	} else {
		return @is_writable( $path );
	}
}

function win_is_writable( $path ) {
	if ( '/' === $path[ strlen( $path ) - 1 ] ) {
		// If it looks like a directory, check a random file within the directory.
		return win_is_writable( $path . uniqid( mt_rand() ) . '.tmp' );
	} elseif ( is_dir( $path ) ) {
		// If it's a directory (and not a file), check a random file within the directory.
		return win_is_writable( $path . '/' . uniqid( mt_rand() ) . '.tmp' );
	}

	// Check tmp file for read/write capabilities.
	$should_delete_tmp_file = ! file_exists( $path );

	$f = @fopen( $path, 'a' );
	if ( false === $f ) {
		return false;
	}
	fclose( $f );

	if ( $should_delete_tmp_file ) {
		unlink( $path );
	}

	return true;
}

function rename_file(string $oldname,string $newname, bool $move = true) : bool
{
	if($move){
		if (rename($oldname, $newname)) return true;
		else return false;
	}else{
     $old = explode("/",$oldname);
	 $new = explode("/",$newname);
	 $old[count($old) -1] = $new[count($new) -1];
	 $newname = implode("/",$old);
	 if (rename($oldname, $newname)) return true;
		else return false;
	}
}

/**
 * Usage rename_files('pages/*.md', '.md', '.html');
 */

function rename_files(string $pattern, string $search, string $replace) : array
{
	$paths = glob($pattern);

	$results = [];

	foreach ($paths as $path) {
		// check if the pathname is a file
		if (!is_file($path)) {
		    $results[$path] = false;
			continue;
		}
		// get the dir and filename
		$dirname = dirname($path);
		$filename = basename($path);

		// replace $search by $replace in the filename
		$new_path = $dirname . '/' . str_replace($search, $replace, $filename);

		// check if the new file exists
		if (file_exists($new_path)) {
		    $results[$path] = false;
			continue;
		}

		// rename the file
		$results[$path] = rename_file($path, $new_path);
	}
	return $results;
}