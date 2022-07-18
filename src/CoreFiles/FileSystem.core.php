<?php
use Hkm_code\Vezirion\FileLocator;



if (!function_exists('hkm_is_really_writable')) {
    /**
     * Tests for file writability
     *
     * is_writable() returns TRUE on Windows servers when you really can't write to
     * the file, based on the read-only attribute. is_writable() is also unreliable
     * on Unix servers if safe_mode is on.
     *
     * @link https://bugs.php.net/bug.php?id=54709
     *
     * @param string $file
     *
     * @return boolean
     *
     * @throws             Exception
     * @codeCoverageIgnore Not practical to test, as travis runs on linux
     */
    function hkm_is_really_writable(string $file): bool
    {
        // If we're on a Unix server with safe_mode off we call is_writable
        if (DIRECTORY_SEPARATOR === '/' || !ini_get('safe_mode')) {
            return is_writable($file);
        }

        /* For Windows servers and safe_mode "on" installations we'll actually
		 * write a file then read it. Bah...
		 */
        if (is_dir($file)) {
            $file = rtrim($file, '/') . '/' . bin2hex(random_bytes(16));
            if (($fp = @fopen($file, 'ab')) === false) {
                return false;
            }

            fclose($fp);
            @chmod($file, 0777);
            @unlink($file);

            return true;
        }

        if (!is_file($file) || ($fp = @fopen($file, 'ab')) === false) {
            return false;
        }

        fclose($fp);

        return true;
    }
}

if (!function_exists('hkm_arraging_folder_name')) {
    function hkm_arraging_folder_name(string $name)
    {
        return UCfirst(str_replace(' ', '_', trim(strtolower($name))));
    }
}

if (!function_exists('hkm_read_env_file')) {
    function hkm_read_env_file($file)
    {

        $AR = array();
        $database = file_get_contents($file);
        $data = explode("\n", $database);
        foreach ($data as $key) {
            if ($key != "") {
                @list($m, $value, $phone) = explode("=", $key);
                $AR[$m] = $value;
            }
        }
        return $AR;
    }
}

if (!function_exists('hkm_clean_path')) {
	/**
	 * A convenience method to clean paths for
	 * a nicer looking output. Useful for exception
	 * handling, error logging, etc.
	 *
	 * @param string $path
	 *
	 * @return string
	 */
	function hkm_clean_path(string $path): string
	{
		// Resolve relative paths
		$path = realpath($path) ?: $path;
		$cleanPath = '';
		switch (true) {
			case strpos($path, SYSTEMPATH) === 0:
				$cleanPath = 'SYSTEMPATH' . DIRECTORY_SEPARATOR . substr($path, strlen(SYSTEMPATH));
			case strpos($path, SYSTEMROOTPATH) === 0:
				$cleanPath = 'SYSTEMROOTPATH' . DIRECTORY_SEPARATOR . substr($path, strlen(SYSTEMROOTPATH));
			case strpos($path, APPPATH) === 0:
				$cleanPath = 'APPPATH' . DIRECTORY_SEPARATOR . substr($path, strlen(APPPATH));
			case strpos($path, BUILDPATH) === 0:
				$cleanPath = 'BUILDPATH' . DIRECTORY_SEPARATOR . substr($path, strlen(BUILDPATH));
			case strpos($path, WRITEPATH) === 0:
				$cleanPath = 'WRITEPATH' . DIRECTORY_SEPARATOR . substr($path, strlen(WRITEPATH));
			case strpos($path, ROOTPATH) === 0:
				$cleanPath = 'ROOTPATH' . DIRECTORY_SEPARATOR . substr($path, strlen(ROOTPATH));
			    break;
			default:
			  $cleanPath = $path;
		}
		return $cleanPath;
	}
}


if (!function_exists('hkm_findQualifiedNameFromPath')) {
	function hkm_findQualifiedNameFromPath($dir)
	{
		$d = FileLocator::LIST_FILES($dir);
		$f = [];
		foreach ($d as $file) {
			if (strpos($file, '.php')) {
                if (strpos($file, '.tpl.php')==false) {
                $classname = FileLocator::GET_CLASS_NAME($file);
				$f[]=$classname;
				
                }
                
			}
		}

		return $f;
	}
}
 
/**
 * Map a file name to a MIME type.
 * Defaults to 'application/octet-stream', i.e.. arbitrary binary data.
 *
 * @param string $filename A file name or full path, does not need to exist as a file
 *
 * @return string
 */
function hkm_filename_to_type($filename)
{
	//In case the path is a URL, strip any query string before getting extension
	$qpos = strpos($filename, '?');
	if (false !== $qpos) {
		$filename = substr($filename, 0, $qpos);
	}
	$ext = hkm_mb_pathinfo($filename, PATHINFO_EXTENSION);

	return _mime_types($ext);
}
 
/**
 * Check whether a file path is safe, accessible, and readable.
 *
 * @param string $path A relative or absolute path to a file
 *
 * @return bool
 */
function hkm_fileIsAccessible($path)
{
	if (!isPermittedPath($path)) {
		return false;
	}
	$readable = file_exists($path);
	//If not a UNC path (expected to start with \\), check read permission, see #2069
	if (strpos($path, '\\\\') !== 0) {
		$readable = $readable && is_readable($path);
	}
	return  $readable;
}

/**
 * Check whether a file path is of a permitted type.
 * Used to reject URLs and phar files from functions that access local file paths,
 * such as addAttachment.
 *
 * @param string $path A relative or absolute path to a file
 *
 * @return bool
 */
function hkm_isPermittedPath($path)
{
	//Matches scheme definition from https://tools.ietf.org/html/rfc3986#section-3.1
	return !preg_match('#^[a-z][a-z\d+.-]*://#i', $path);
}

function hkm_get_file($filesLang, $code)
{
	$file = '';
	$found = false;
	foreach ($filesLang as $value) {
		$ptss = explode('/', $value);
		$ct = count($ptss);
		$fn = $ptss[$ct - 1];
		if ('phpmailer.lang-' . $code . '.php' == $fn) {
			$file = $fn;
			$found = true;
			break;
		}
		continue;
	}
	return [$found, $file];
}