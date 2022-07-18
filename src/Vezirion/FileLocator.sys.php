<?php


namespace Hkm_code\Vezirion;

use Hkm_code\Modules\LoadModules;

class FileLocator extends AutoloadVezirion
{
	
	
	public static function LOCATE_FILE(string $fileOrginal, string $folder = null, string $ext = 'php') 
	{
		$file = static::ENSURE_EXT($fileOrginal, $ext);

		// Clears the folder name if it is at the beginning of the filename
		if (! empty($folder) && strpos($file, $folder) === 0)
		{
			$file = substr($file, strlen($folder . '/'));
		}
		
		// Is not namespaced? Try the application folder.
		if (strpos($file, '\\') === false)
		{
			$f = static::LEGACY_LOCATE($file, $folder);
			if ($f) {
			
				return $f;

			}else{
				$filed = static::SEARCH(($folder??"")."/".$file);
                return $filed;
			}

		}

		// Standardize slashes to handle nested directories.
		$file = strtr($fileOrginal, '/', '\\');

        return static::SEARCH($file);

		// Namespaces always comes with arrays of paths
		

	}

	/**
	 * Examines a file and returns the fully qualified domain name.
	 *
	 * @param string $file
	 *
	 * @return string
	 */
	public static function GET_CLASS_NAME(string $file) : string
	{
		$php       = file_get_contents($file);
		$tokens    = token_get_all($php);
		$dlm       = false;
		$namespace = '';
		$className = '';

		foreach ($tokens as $i => $token)
		{
			if ($i < 2)
			{
				continue;
			}

			if ((isset($tokens[$i - 2][1]) && ($tokens[$i - 2][1] === 'phpnamespace' || $tokens[$i - 2][1] === 'namespace')) || ($dlm && $tokens[$i - 1][0] === T_NS_SEPARATOR && $token[0] === T_STRING))
			{
				if (! $dlm)
				{
					$namespace = 0;
				}
				if (isset($token[1]))
				{
					$namespace = $namespace ? $namespace . '\\' . $token[1] : $token[1];
					$dlm       = true;
				}
			}
			elseif ($dlm && ($token[0] !== T_NS_SEPARATOR) && ($token[0] !== T_STRING))
			{
				$dlm = false;
			}

			if (($tokens[$i - 2][0] === T_CLASS || (isset($tokens[$i - 2][1]) && $tokens[$i - 2][1] === 'phpclass'))
				&& $tokens[$i - 1][0] === T_WHITESPACE
				&& $token[0] === T_STRING)
			{
				$className = $token[1];
				break;
			}
		}

		if (empty($className))
		{
			return '';
		}

		return $namespace . '\\' . $className;
	}

	/**
	 * Searches through all of the defined namespaces looking for a file.
	 * Returns an array of all found locations for the defined file.
	 *
	 * Example:
	 *
	 *  $locator->search('Config/Routes.php');
	 *  // Assuming PSR4 namespaces include foo and bar, might return:
	 *  [
	 *      'app/Modules/foo/Config/Routes.php',
	 *      'app/Modules/bar/Config/Routes.php',
	 *  ]
	 *
	 * @param string  $path
	 * @param string  $ext
	 * @param boolean $prioritizeApp
	 *
	 * @return array
	 */
	public static function SEARCH(string $path, string $ext = 'php', bool $prioritizeApp = true): array
	{
		$path1 = static::ENSURE_EXT(trim($path,"/"), $ext);


		$foundPaths = [];
		$appPaths   = [];

		foreach (static::GET_NAMESPACES() as $namespace)
		{   
			
			if (isset($namespace['path']) && isset($namespace['prefix']))
			{

				$file = str_replace("\\","/",$namespace['path'] . str_replace($namespace['prefix'],"",$path1));
			
				if(is_file($file)){
					$fullPath = $file;
					$fullPath = realpath($fullPath) ?: $fullPath;
	
					if ($prioritizeApp)
					{
						$foundPaths[] = $fullPath;
					}
					elseif (strpos($fullPath, APPPATH) === 0)
					{
						$appPaths[] = $fullPath;
					}
					else
					{
						$foundPaths[] = $fullPath;
					}
				}else{
		         $path2 = static::ENSURE_EXT($path, "sys.".$ext);
				 $file = str_replace("\\","/",$namespace['path'] . ltrim($path2,$namespace['prefix']));
					if(is_file($file)){
						$fullPath = $file;
						$fullPath = realpath($fullPath) ?: $fullPath;
		
						if ($prioritizeApp)
						{
							$foundPaths[] = $fullPath;
						}
						elseif (strpos($fullPath, APPPATH) === 0)
						{
							$appPaths[] = $fullPath;
						}
						else
						{
							$foundPaths[] = $fullPath;
						}
					}

				}
				
			}
		}

		if (! $prioritizeApp && ! empty($appPaths))
		{
			$foundPaths = array_merge($foundPaths, $appPaths);
		}

		// Remove any duplicates
		$foundPaths = array_unique($foundPaths);

		return $foundPaths;
	}

	/**
	 * Ensures a extension is at the end of a filename
	 *
	 * @param string $path
	 * @param string $ext
	 *
	 * @return string
	 */
	protected static function ENSURE_EXT(string $path, string $ext): string
	{
		if ($ext)
		{
			$ext = '.' . $ext;

			if (substr($path, -strlen($ext)) !== $ext)
			{
				$path .= $ext;
			}
		}

		return $path;
	}
	

	/**
	 * Return the namespace mappings we know about.
	 *
	 * @return array|string
	 */
	protected static function GET_NAMESPACES(string $prefix = null)
	{
		$namespaces = [];

		// Save system for last
		$system = [];
		if (is_null($prefix)) {
			if (APP_NAMESPACE!="App") unset(self::$coreClass['App\\']);
			foreach (self::$coreClass as $prefix => $paths) foreach ($paths as $path) $system[] = ['prefix' => $prefix,'path'   => rtrim($path, '\\/') . DIRECTORY_SEPARATOR,];
			foreach (self::$class as $prefix => $paths)  foreach ($paths as $path)$namespaces[] = ['prefix' => $prefix,'path'   => rtrim($path, '\\/') . DIRECTORY_SEPARATOR,];
			
			$namespaces = array_merge($namespaces,$system);
			return $namespaces;
		}else {
			if (APP_NAMESPACE!="App") unset(self::$coreClass['App\\']);
			
			if (isset(self::$coreClass[$prefix])) return self::$coreClass[$prefix];
			if (isset(self::$class[$prefix])) return self::$class[$prefix];
			
		}
		return ;
		
	}

	/**
	 * Find the qualified name of a file according to
	 * the namespace of the first matched namespace path.
	 *
	 * @param string $path
	 *
	 * @return string|false The qualified name or false if the path is not found
	 */
	public static function FIND_QUALIFIED_NAME_FROM_PATH(string $path)
	{
		$path = realpath($path) ?: $path;

		if (! is_file($path))
		{
			return false;
		}

		foreach (static::GET_NAMESPACES() as $namespace)
		{
			$namespace['path'] = realpath($namespace['path']) ?: $namespace['path'];

			if (empty($namespace['path']))
			{
				continue;
			}

			if (mb_strpos($path, $namespace['path']) === 0)
			{
				$className = '\\' . $namespace['prefix'] . '\\' .
						ltrim(str_replace('/', '\\', mb_substr(
							$path, mb_strlen($namespace['path']))
						), '\\');

				// Remove the file extension (.php)
				$className = mb_substr($className, 0, -4);

				// Check if this exists
				if (class_exists($className))
				{
					return $className;
				}
			}
		}

		return false;
	}

	/**
	 * Scans the defined namespaces, returning a list of all files
	 * that are contained within the subpath specified by $path.
	 *
	 * @param string $path
	 *
	 * @return array
	 */
	public static function LIST_FILES(string $path): array
	{
		if (empty($path))
		{
			return [];
		}

		$files = [];
		hkm_helper('filesystem');

		foreach (static::GET_NAMESPACES() as $namespace)
		{
			$fullPath = $namespace['path'] . ltrim($path,$namespace['prefix']);
			$fullPath = realpath($fullPath) ?: $fullPath;

			if (! is_dir($fullPath))
			{
				continue;
			}
 
			$tempFiles = hkm_get_filenames($fullPath, true);

			if (! empty($tempFiles))
			{
				$files = array_merge($files, $tempFiles);
			}
		}

		return $files;
	}

	/**
	 * Scans the provided namespace, returning a list of all files
	 * that are contained within the subpath specified by $path.
	 *
	 * @param string $prefix
	 * @param string $path
	 *
	 * @return array
	 */
	public static function LIST_NAMESPACE_FILES(string $prefix, string $path): array
	{
		if (empty($path) || empty($prefix))
		{
			return [];
		}

		$files = [];
		hkm_helper('filesystem');

	

		// autoloader->getNamespace($prefix) returns an array of paths for that namespace
		if (is_array(static::GET_NAMESPACES($prefix))) {
			foreach (static::GET_NAMESPACES($prefix) as $namespacePath)
			{
				$fullPath = rtrim($namespacePath, '/') . '/' . $path;
				$fullPath = realpath($fullPath) ?: $fullPath;


				if (! is_dir($fullPath))
				{
					continue;
				}

				$tempFiles = hkm_get_filenames($fullPath, true);

				if (! empty($tempFiles))
				{
					$files = array_merge($files, $tempFiles);
				}
			}
		}
		

		return $files;
	}

	/**
	 * Checks the app folder to see if the file can be found.
	 * Only for use with filenames that DO NOT include namespacing.
	 *
	 * @param string      $file
	 * @param string|null $folder
	 *
	 * @return string|false The path to the file, or false if not found.
	 */
	protected static function LEGACY_LOCATE(string $file, string $folder = null)
	{
		$path = APPPATH . (empty($folder) ? $file : $folder . '/' . $file);
		$path = realpath($path) ?: $path;

		if (is_file($path))
		{
			return $path;
		}

		return false;
	}
}
