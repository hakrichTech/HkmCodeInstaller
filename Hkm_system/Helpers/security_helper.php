<?php

/**
 * This file is part of the Hkm_code 4 framework.
 *
 * (c) Hkm_code Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Hkm_code\Vezirion\Services;

/**
 * Hkm_code Security Helpers
 */

if (! function_exists('sanitize_filename'))
{
	/**
	 * Sanitize a filename to use in a URI.
	 *
	 * @param string $filename
	 *
	 * @return string
	 */
	function sanitize_filename(string $filename): string
	{
		return Services::SECURITY()->sanitizeFilename($filename);
	}
}

//--------------------------------------------------------------------

if (! function_exists('strip_image_tags'))
{
	/**
	 * Strip Image Tags
	 *
	 * @param string $str
	 *
	 * @return string
	 */
	function strip_image_tags(string $str): string
	{
		return preg_replace([
			'#<img[\s/]+.*?src\s*=\s*(["\'])([^\\1]+?)\\1.*?\>#i',
			'#<img[\s/]+.*?src\s*=\s*?(([^\s"\'=<>`]+)).*?\>#i',
		], '\\2', $str
		);
	}
}

//--------------------------------------------------------------------

if (! function_exists('encode_php_tags'))
{
	/**
	 * Convert PHP tags to entities
	 *
	 * @param string $str
	 *
	 * @return string
	 */
	function encode_php_tags(string $str): string
	{
		return str_replace(['<?', '?>'], ['&lt;?', '?&gt;'], $str);
	}
}

//--------------------------------------------------------------------
