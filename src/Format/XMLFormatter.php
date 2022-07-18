<?php

/**
 * This file is part of the Hkm_code 4 framework.
 *
 * (c) Hkm_code Foundation <admin@hakrichteam.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hkm_code\Format;

use Hkm_code\Format\Exceptions\FormatException;
use SimpleXMLElement;

/**
 * XML data formatter
 */
class XMLFormatter implements FormatterInterface
{
	/**
	 * Takes the given data and formats it.
	 *
	 * @param mixed $data
	 *
	 * @return string|boolean (XML string | false)
	 */
	public static function FORMAT($data)
	{
		$config = hkm_config('Format');

		// SimpleXML is installed but default
		// but best to check, and then provide a fallback.
		if (! extension_loaded('simplexml'))
		{
			// never thrown in travis-ci
			// @codeCoverageIgnoreStart
			throw FormatException::FOR_MISSING_EXTENSION();
			// @codeCoverageIgnoreEnd
		}

		$options = $config::$formatterOptions['application/xml'] ?? 0;
		$output  = new SimpleXMLElement('<?xml version="1.0"?><response></response>', $options);

		self::ARRAY_TO_XML((array) $data, $output);

		return $output->asXML();
	}

	/**
	 * A recursive method to convert an array into a valid XML string.
	 *
	 * Written by CodexWorld. Received permission by email on Nov 24, 2016 to use this code.
	 *
	 * @see http://www.codexworld.com/convert-array-to-xml-in-php/
	 *
	 * @param array            $data
	 * @param SimpleXMLElement $output
	 */
	protected static function ARRAY_TO_XML(array $data, &$output)
	{
		foreach ($data as $key => $value)
		{
			$key = self::NORMALIZE_XML_TAG($key);

			if (is_array($value))
			{
				$subnode = $output->addChild("$key");
				self::ARRAY_TO_XML($value, $subnode);
			}
			else
			{
				$output->addChild("$key", htmlspecialchars("$value"));
			}
		}
	}

	/**
	 * Normalizes tags into the allowed by W3C.
	 * Regex adopted from this StackOverflow answer.
	 *
	 * @param string|integer $key
	 *
	 * @return string
	 *
	 * @see https://stackoverflow.com/questions/60001029/invalid-characters-in-xml-tag-name
	 */
	protected static function NORMALIZE_XML_TAG($key)
	{
		$startChar = 'A-Z_a-z' .
			'\\x{C0}-\\x{D6}\\x{D8}-\\x{F6}\\x{F8}-\\x{2FF}\\x{370}-\\x{37D}' .
			'\\x{37F}-\\x{1FFF}\\x{200C}-\\x{200D}\\x{2070}-\\x{218F}' .
			'\\x{2C00}-\\x{2FEF}\\x{3001}-\\x{D7FF}\\x{F900}-\\x{FDCF}' .
			'\\x{FDF0}-\\x{FFFD}\\x{10000}-\\x{EFFFF}';
		$validName = $startChar . '\\.\\d\\x{B7}\\x{300}-\\x{36F}\\x{203F}-\\x{2040}';

		$key = trim($key);
		$key = preg_replace("/[^{$validName}-]+/u", '', $key);
		$key = preg_replace("/^[^{$startChar}]+/u", 'item$0', $key);

		return preg_replace('/^(xml).*/iu', 'item$0', $key); // XML is a reserved starting word
	}
}
