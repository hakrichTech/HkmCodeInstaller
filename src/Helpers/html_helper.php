<?php

/**
 * This file is part of the Hkm_code 4 framework.
 *
 * (c) Hkm_code Foundation <admin@hakrichteam.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Hkm_code\Files\Exceptions\FileNotFoundException;


hkm_helper('url');

// ------------------------------------------------------------------------

if (! function_exists('hkm_meta'))
{
	/**
	 * Meta
	 *
	 * Generates meta 
	 *
	 * @param array   $attribute      meta attributes
	 *
	 * @return string
	 */
	function hkm_meta(array $attribute = []): string
	{
		$meta = '<meta ';

		// extract fields if needed
		if (is_array($attribute))
		{
			foreach ($attribute as $key => $value) {
				if ($value !== ''){
					$meta .= $key.'="'.$value.'"';
				}
			}
		}

		return $meta . '/>';
	}
}

// --------------------------------------------------------------------

/**
 * Hkm_code HTML Helpers
 */
if (! function_exists('ul'))
{
	/**
	 * Unordered List
	 *
	 * Generates an HTML unordered list from an single or
	 * multi-dimensional array.
	 *
	 * @param array $list
	 * @param mixed $attributes HTML attributes string, array, object
	 *
	 * @return string
	 */
	function ul(array $list, $attributes = ''): string
	{
		return hkm_list('ul', $list, $attributes);
	}
}

// ------------------------------------------------------------------------

if (! function_exists('ol'))
{
	/**
	 * Ordered List
	 *
	 * Generates an HTML ordered list from an single or multi-dimensional array.
	 *
	 * @param array $list
	 * @param mixed $attributes HTML attributes string, array, object
	 *
	 * @return string
	 */
	function ol(array $list, $attributes = ''): string
	{
		return hkm_list('ol', $list, $attributes);
	}
}

// ------------------------------------------------------------------------

if (! function_exists('hkm_list'))
{
	/**
	 * Generates the list
	 *
	 * Generates an HTML ordered list from an single or multi-dimensional array.
	 *
	 * @param string  $type
	 * @param mixed   $list
	 * @param mixed   $attributes string, array, object
	 * @param integer $depth
	 *
	 * @return string
	 */
	function hkm_list(string $type = 'ul', $list = [], $attributes = '', int $depth = 0): string
	{
		// Set the indentation based on the depth
		$out = str_repeat(' ', $depth)
				// Write the opening list tag
				. '<' . $type . hkm_stringify_attributes($attributes) . ">\n";

		// Cycle through the list elements.  If an array is
		// encountered we will recursively call hkm_list()

		static $_lasthkm_list_item = '';
		foreach ($list as $key => $val)
		{
			$_lasthkm_list_item = $key;

			$out .= str_repeat(' ', $depth + 2) . '<li>';

			if (! is_array($val))
			{
				$out .= $val;
			}
			else
			{
				$out .= $_lasthkm_list_item
						. "\n"
						. hkm_list($type, $val, '', $depth + 4)
						. str_repeat(' ', $depth + 2);
			}

			$out .= "</li>\n";
		}

		// Set the indentation for the closing tag and apply it
		return $out . str_repeat(' ', $depth) . '</' . $type . ">\n";
	}
}

// ------------------------------------------------------------------------

if (! function_exists('img'))
{
	/**
	 * Image
	 *
	 * Generates an image element
	 *
	 * @param string|array        $src        Image source URI, or array of attributes and values
	 * @param boolean             $indexPage  Whether to treat $src as a routed URI string
	 * @param string|array|object $attributes Additional HTML attributes
	 *
	 * @return string
	 */
	function img($src = '', bool $indexPage = false, $attributes = ''): string
	{
		if (! is_array($src))
		{
			$src = ['src' => $src];
		}
		if (! isset($src['src']))
		{
			$src['src'] = $attributes['src'] ?? '';
		}
		if (! isset($src['alt']))
		{
			$src['alt'] = $attributes['alt'] ?? '';
		}

		$img = '<img';

		// Check for a relative URI
		if (! preg_match('#^([a-z]+:)?//#i', $src['src']) && strpos($src['src'], 'data:') !== 0)
		{
			if ($indexPage === true)
			{
				$img .= ' src="' . hkm_site_url($src['src']) . '"';
			}
			else
			{
				$img .= ' src="' . hkm_slash_item('baseURL') . $src['src'] . '"';
			}

			unset($src['src']);
		}

		// Append any other values
		foreach ($src as $key => $value)
		{
			$img .= ' ' . $key . '="' . $value . '"';
		}

		// Prevent passing completed values to hkm_stringify_attributes
		if (is_array($attributes))
		{
			unset($attributes['alt'], $attributes['src']);
		}

		return $img . hkm_stringify_attributes($attributes) . ' />';
	}
}

if (! function_exists('hkm_img_data'))
{
	/**
	 * Image (data)
	 *
	 * Generates a src-ready string from an image using the "data:" protocol
	 *
	 * @param string      $path Image source path
	 * @param string|null $mime MIME type to use, or null to guess
	 *
	 * @return string
	 */
	function hkm_img_data(string $path, string $mime = null): string
	{
		if (! is_file($path) || ! is_readable($path))
		{
			throw FileNotFoundException::FOR_FILE_NOT_FOUND($path);
		}

		// Read in file binary data
		$handle = fopen($path, 'rb');
		$data   = fread($handle, filesize($path));
		fclose($handle);

		// Encode as base64
		$data = base64_encode($data);

		// Figure out the type (Hail Mary to JPEG)
		$mime = $mime ?? hkm_config('Mimes')::GUESS_TYPE_FROM_EXTENSION(pathinfo($path, PATHINFO_EXTENSION)) ?? 'image/jpg';

		return 'data:' . $mime . ';base64,' . $data;
	}
}

// ------------------------------------------------------------------------

if (! function_exists('doctype'))
{
	/**
	 * Doctype
	 *
	 * Generates a page document type declaration
	 *
	 * Examples of valid options: html5, xhtml-11, xhtml-strict, xhtml-trans,
	 * xhtml-frame, html4-strict, html4-trans, and html4-frame.
	 * All values are saved in the doctypes config file.
	 *
	 * @param string $type The doctype to be generated
	 *
	 * @return string
	 */
	function doctype(string $type = 'html5'): string
	{
		$config   = hkm_config('DocTypes');
		$doctypes = $config::$list;
		return $doctypes[$type] ?? false;
	}
}

// ------------------------------------------------------------------------

if (! function_exists('hkm_script_string'))
{ 
	/**
	 * Script
	 *
	 * Generates link to a JS file
	 *
	 * @param mixed   $string       Script source or an array
	 *
	 * @return string
	 */
	function hkm_script_string($string = ''): string
	{
		$script = '<script>';
		$script.=$string;

		return $script . '</script>';
	}
}

// ------------------------------------------------------------------------

if (! function_exists('hkm_script_tag'))
{ 
	/**
	 * Script
	 *
	 * Generates link to a JS file
	 *
	 * @param mixed   $src       Script source or an array
	 * @param boolean $indexPage Should indexPage be added to the JS path
	 *
	 * @return string
	 */
	function hkm_script_tag($src = '', bool $indexPage = false): string
	{
		$script = '<script ';
		if (! is_array($src))
		{
			$src = ['src' => $src];
		}

		foreach ($src as $k => $v)
		{
			if ($k === 'src' && ! preg_match('#^([a-z]+:)?//#i', $v))
			{
				if ($indexPage === true)
				{
					$script .= 'src="' . hkm_site_url($v) . '" ';
				}
				else
				{
					$script .= 'src="' . hkm_slash_item('baseURL') . $v . '" ';
				}
			}
			else
			{
				$script .= $k . '="' . $v . '" ';
			}
		}

		return $script . 'type="text/javascript" ' . '></script>';
	}
}

// ------------------------------------------------------------------------

if (! function_exists('hkm_link_tag'))
{
	/**
	 * Link
	 *
	 * Generates link to a CSS file
	 *
	 * @param mixed   $href      Stylesheet href or an array
	 * @param string  $rel
	 * @param string  $type
	 * @param string  $title
	 * @param string  $media
	 * @param boolean $indexPage should indexPage be added to the CSS path.
	 * @param string  $hreflang
	 *
	 * @return string
	 */
	function hkm_link_tag($href = '', string $rel = 'stylesheet', string $type = 'text/css', string $title = '', string $media = '', bool $indexPage = false, string $hreflang = ''): string
	{
		$link = '<link ';

		// extract fields if needed
		if (is_array($href))
		{
			$rel       = $href['rel'] ?? $rel;
			$type      = $href['type'] ?? $type;
			$title     = $href['title'] ?? $title;
			$media     = $href['media'] ?? $media;
			$hreflang  = $href['hreflang'] ?? '';
			$indexPage = $href['indexPage'] ?? $indexPage;
			$href      = $href['href'] ?? '';
		}

		if (! preg_match('#^([a-z]+:)?//#i', $href))
		{
			if ($indexPage === true)
			{
				$link .= 'href="' . hkm_site_url($href) . '" ';
			}
			else
			{
				$link .= 'href="' . hkm_slash_item('baseURL') . $href . '" ';
			}
		}
		else
		{
			$link .= 'href="' . $href . '" ';
		}

		if ($hreflang !== '')
		{
			$link .= 'hreflang="' . $hreflang . '" ';
		}

		$link .= 'rel="' . $rel . '" ';

		if (! in_array($rel, ['alternate', 'canonical'], true))
		{
			$link .= 'type="' . $type . '" ';
		}

		if ($media !== '')
		{
			$link .= 'media="' . $media . '" ';
		}

		if ($title !== '')
		{
			$link .= 'title="' . $title . '" ';
		}

		return $link . '/>';
	}
}

// ------------------------------------------------------------------------

if (! function_exists('video'))
{
	/**
	 * Video
	 *
	 * Generates a video element to embed videos. The video element can
	 * contain one or more video sources
	 *
	 * @param mixed   $src                Either a source string or an array of sources
	 * @param string  $unsupportedMessage The message to display if the media tag is not supported by the browser
	 * @param string  $attributes         HTML attributes
	 * @param array   $tracks
	 * @param boolean $indexPage
	 *
	 * @return string
	 */
	function video($src, string $unsupportedMessage = '', string $attributes = '', array $tracks = [], bool $indexPage = false): string
	{
		if (is_array($src))
		{
			return hkm_media('video', $src, $unsupportedMessage, $attributes, $tracks);
		}

		$video = '<video';

		if (hkm_has_protocol($src))
		{
			$video .= ' src="' . $src . '"';
		}
		elseif ($indexPage === true)
		{
			$video .= ' src="' . hkm_site_url($src) . '"';
		}
		else
		{
			$video .= ' src="' . hkm_slash_item('baseURL') . $src . '"';
		}

		if ($attributes !== '')
		{
			$video .= ' ' . $attributes;
		}

		$video .= ">\n";

		if (! empty($tracks))
		{
			foreach ($tracks as $track)
			{
				$video .= hkm_space_indent() . $track . "\n";
			}
		}

		if (! empty($unsupportedMessage))
		{
			$video .= hkm_space_indent()
					. $unsupportedMessage
					. "\n";
		}

		return $video . "</video>\n";
	}
}

	// ------------------------------------------------------------------------

if (! function_exists('audio'))
{
	/**
	 * Audio
	 *
	 * Generates an audio element to embed sounds
	 *
	 * @param mixed   $src                Either a source string or an array of sources
	 * @param string  $unsupportedMessage The message to display if the media tag is not supported by the browser.
	 * @param string  $attributes         HTML attributes
	 * @param array   $tracks
	 * @param boolean $indexPage
	 *
	 * @return string
	 */
	function audio($src, string $unsupportedMessage = '', string $attributes = '', array $tracks = [], bool $indexPage = false): string
	{
		if (is_array($src))
		{
			return hkm_media('audio', $src, $unsupportedMessage, $attributes, $tracks);
		}

		$audio = '<audio';

		if (hkm_has_protocol($src))
		{
			$audio .= ' src="' . $src . '"';
		}
		elseif ($indexPage === true)
		{
			$audio .= ' src="' . hkm_site_url($src) . '"';
		}
		else
		{
			$audio .= ' src="' . hkm_slash_item('baseURL') . $src . '"';
		}

		if ($attributes !== '')
		{
			$audio .= ' ' . $attributes;
		}

		$audio .= '>';

		if (! empty($tracks))
		{
			foreach ($tracks as $track)
			{
				$audio .= "\n" . hkm_space_indent() . $track;
			}
		}

		if (! empty($unsupportedMessage))
		{
			$audio .= "\n" . hkm_space_indent() . $unsupportedMessage . "\n";
		}

		return $audio . "</audio>\n";
	}
}

	// ------------------------------------------------------------------------

if (! function_exists('hkm_media'))
{
	/**
	 * Generate media based tag
	 *
	 * @param string $name
	 * @param array  $types
	 * @param string $unsupportedMessage The message to display if the media tag is not supported by the browser.
	 * @param string $attributes
	 * @param array  $tracks
	 *
	 * @return string
	 */
	function hkm_media(string $name, array $types = [], string $unsupportedMessage = '', string $attributes = '', array $tracks = []): string
	{
		$media = '<' . $name;

		if (empty($attributes))
		{
			$media .= '>';
		}
		else
		{
			$media .= ' ' . $attributes . '>';
		}

		$media .= "\n";

		foreach ($types as $option)
		{
			$media .= hkm_space_indent() . $option . "\n";
		}

		if (! empty($tracks))
		{
			foreach ($tracks as $track)
			{
				$media .= hkm_space_indent() . $track . "\n";
			}
		}

		if (! empty($unsupportedMessage))
		{
			$media .= hkm_space_indent() . $unsupportedMessage . "\n";
		}

		return $media . ('</' . $name . ">\n");
	}
}

	// ------------------------------------------------------------------------

if (! function_exists('source'))
{
	/**
	 * Source
	 *
	 * Generates a source element that specifies multiple media resources
	 * for either audio or video element
	 *
	 * @param string  $src        The path of the media resource
	 * @param string  $type       The MIME-type of the resource with optional codecs parameters
	 * @param string  $attributes HTML attributes
	 * @param boolean $indexPage
	 *
	 * @return string
	 */
	function source(string $src, string $type = 'unknown', string $attributes = '', bool $indexPage = false): string
	{
		if (! hkm_has_protocol($src))
		{
			$src = $indexPage === true ? hkm_site_url($src) : hkm_slash_item('baseURL') . $src;
		}

		$source = '<source src="' . $src
				. '" type="' . $type . '"';

		if (! empty($attributes))
		{
			$source .= ' ' . $attributes;
		}

		return $source . ' />';
	}
}

	// ------------------------------------------------------------------------

if (! function_exists('track'))
{
	/**
	 * Track
	 *
	 * Generates a track element to specify timed tracks. The tracks are
	 * formatted in WebVTT format.
	 *
	 * @param string $src         The path of the .VTT file
	 * @param string $kind
	 * @param string $srcLanguage
	 * @param string $label
	 *
	 * @return string
	 */
	function track(string $src, string $kind, string $srcLanguage, string $label): string
	{
		return '<track src="' . $src
				. '" kind="' . $kind
				. '" srclang="' . $srcLanguage
				. '" label="' . $label
				. '" />';
	}
}

	// ------------------------------------------------------------------------

if (! function_exists('object'))
{
	/**
	 * Object
	 *
	 * Generates an object element that represents the media
	 * as either image or a resource plugin such as audio, video,
	 * Java applets, ActiveX, PDF and Flash
	 *
	 * @param string  $data       A resource URL
	 * @param string  $type       Content-type of the resource
	 * @param string  $attributes HTML attributes
	 * @param array   $params
	 * @param boolean $indexPage
	 *
	 * @return string
	 */
	function object(string $data, string $type = 'unknown', string $attributes = '', array $params = [], bool $indexPage = false): string
	{
		if (! hkm_has_protocol($data))
		{
			$data = $indexPage === true ? hkm_site_url($data) : hkm_slash_item('baseURL') . $data;
		}

		$object = '<object data="' . $data . '" '
				. $attributes . '>';

		if (! empty($params))
		{
			$object .= "\n";
		}

		foreach ($params as $param)
		{
			$object .= hkm_space_indent() . $param . "\n";
		}

		return $object . "</object>\n";
	}
}

	// ------------------------------------------------------------------------

if (! function_exists('param'))
{
	/**
	 * Param
	 *
	 * Generates a param element that defines parameters
	 * for the object element.
	 *
	 * @param string $name       The name of the parameter
	 * @param string $value      The value of the parameter
	 * @param string $type       The MIME-type
	 * @param string $attributes HTML attributes
	 *
	 * @return string
	 */
	function param(string $name, string $value, string $type = 'ref', string $attributes = ''): string
	{
		return '<param name="' . $name
				. '" type="' . $type
				. '" value="' . $value
				. '" ' . $attributes . ' />';
	}
}

	// ------------------------------------------------------------------------

if (! function_exists('embed'))
{
	/**
	 * Embed
	 *
	 * Generates an embed element
	 *
	 * @param string  $src        The path of the resource to embed
	 * @param string  $type       MIME-type
	 * @param string  $attributes HTML attributes
	 * @param boolean $indexPage
	 *
	 * @return string
	 */
	function embed(string $src, string $type = 'unknown', string $attributes = '', bool $indexPage = false): string
	{
		if (! hkm_has_protocol($src))
		{
			$src = $indexPage === true ? hkm_site_url($src) : hkm_slash_item('baseURL') . $src;
		}

		return '<embed src="' . $src
				. '" type="' . $type . '" '
				. $attributes . " />\n";
	}
}

// ------------------------------------------------------------------------

if (! function_exists('hkm_has_protocol'))
{
	/**
	 * Test the protocol of a URI.
	 *
	 * @param string $url
	 *
	 * @return false|integer
	 */
	function hkm_has_protocol(string $url)
	{
		return preg_match('#^([a-z]+:)?//#i', $url);
	}
}

// ------------------------------------------------------------------------

if (! function_exists('hkm_space_indent'))
{
	/**
	 * Provide space indenting.
	 *
	 * @param integer $depth
	 *
	 * @return string
	 */
	function hkm_space_indent(int $depth = 2): string
	{
		return str_repeat(' ', $depth);
	}
}

// ------------------------------------------------------------------------
