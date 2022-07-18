<?php



namespace Hkm_code\Language;

use MessageFormatter;

class Language
{
	
	protected static $language = [];

	/**
	 * The current language/locale to work with.
	 *
	 * @var string
	 */
	protected static $locale;

	/**
	 * Boolean value whether the intl
	 * libraries exist on the system.
	 *
	 * @var boolean
	 */
	protected static $intlSupport = false;

	/**
	 * Stores filenames that have been
	 * loaded so that we don't load them again.
	 *
	 * @var array
	 */
	protected static $loadedFiles = [];
	protected static $thiss;
	
	
	//--------------------------------------------------------------------

	public function __construct(string $locale)
	{
		self::$locale = $locale;

		if (class_exists('MessageFormatter'))
		{
			self::$intlSupport = true;
		}
		self::$thiss = $this;
	}

	
	public static function SET_LOCAL(string $locale = null)
	{
		if (! is_null($locale))
		{
			self::$locale = $locale;
		}

		return self::$thiss;
	}

	public static function GET_LOCAL(): string
	{
		return self::$locale;
	}


	public static function GET_LINE(string $line, array $args = [])
	{
		// if no file is given, just parse the line
		if (strpos($line, '.') === false)
		{
			return self::FORMAT_MESSAGE($line, $args);
		}

		// Parse out the file name and the actual alias.
		// Will load the language file and strings.
		[$file, $parsedLine] = self::PARSE_LINE($line, self::$locale);

		$output = self::GET_TRANSLATION_OUTPUT(self::$locale, $file, $parsedLine);

		if ($output === null && strpos(self::$locale, '-'))
		{
			[$locale] = explode('-', self::$locale, 2);

			[$file, $parsedLine] = self::PARSE_LINE($line, $locale);

			$output = self::GET_TRANSLATION_OUTPUT($locale, $file, $parsedLine);
		}

		// if still not found, try English
		if ($output === null)
		{
			[$file, $parsedLine] = self::PARSE_LINE($line, 'en');

			$output = self::GET_TRANSLATION_OUTPUT('en', $file, $parsedLine);
		}

		$output = $output ?? $line;

		return self::FORMAT_MESSAGE($output, $args);
	}

	//--------------------------------------------------------------------

	/**
	 * @return array|string|null
	 */
	protected static function GET_TRANSLATION_OUTPUT(string $locale, string $file, string $parsedLine)
	{
		$output = self::$language[$locale][$file][$parsedLine] ?? null;
		if ($output !== null)
		{
			return $output;
		}

		foreach (explode('.', $parsedLine) as $row)
		{
			if (! isset($current))
			{
				$current = self::$language[$locale][$file] ?? null;
			}

			$output = $current[$row] ?? null;
			if (is_array($output))
			{
				$current = $output;
			}
		}

		if ($output !== null)
		{
			return $output;
		}

		$row = current(explode('.', $parsedLine));
		$key = substr($parsedLine, strlen($row) + 1);

		return self::$language[$locale][$file][$row][$key] ?? null;
	}

	/**
	 * Parses the language string which should include the
	 * filename as the first segment (separated by period).
	 *
	 * @param string $line
	 * @param string $locale
	 *
	 * @return array
	 */
	protected static function PARSE_LINE(string $line, string $locale): array
	{
		$file = substr($line, 0, strpos($line, '.'));
		$line = substr($line, strlen($file) + 1);

		if (! isset(self::$language[$locale][$file]) || ! array_key_exists($line, self::$language[$locale][$file]))
		{
			self::LOAD($file, $locale);
		}

		return [
			$file,
			$line,
		];
	}

	//--------------------------------------------------------------------

	/**
	 * Advanced message formatting.
	 *
	 * @param string|array $message Message.
	 * @param array	       $args    Arguments.
	 *
	 * @return string|array Returns formatted message.
	 */
	protected static function FORMAT_MESSAGE($message, array $args = [])
	{
		if (! self::$intlSupport || $args === [])
		{
			return $message;
		}

		if (is_array($message))
		{
			foreach ($message as $index => $value)
			{
				$message[$index] = self::FORMAT_MESSAGE($value, $args);
			}

			return $message;
		}

		return MessageFormatter::formatMessage(self::$locale, $message, $args);
	}

	//--------------------------------------------------------------------

	/**
	 * Loads a language file in the current locale. If $return is true,
	 * will return the file's contents, otherwise will merge with
	 * the existing language lines.
	 *
	 * @param string  $file
	 * @param string  $locale
	 * @param boolean $return
	 *
	 * @return void|array
	 */
	protected static function LOAD(string $file, string $locale, bool $return = false)
	{
		if (! array_key_exists($locale, self::$loadedFiles))
		{
			self::$loadedFiles[$locale] = [];
		}

		if (in_array($file, self::$loadedFiles[$locale], true))
		{
			// Don't load it more than once.
			return [];
		}

		if (! array_key_exists($locale, self::$language))
		{
			self::$language[$locale] = [];
		}

		if (! array_key_exists($file, self::$language[$locale]))
		{
			self::$language[$locale][$file] = [];
		}

		$path = "Language/{$locale}/{$file}.php";

		$lang = self::REQUIRE_FILE($path);

		if ($return)
		{
			return $lang;
		}

		self::$loadedFiles[$locale][] = $file;

		// Merge our string
		self::$language[$locale][$file] = $lang;
	}

	//--------------------------------------------------------------------

	/**
	 * A simple method for including files that can be
	 * overridden during testing.
	 *
	 * @param string $path
	 *
	 * @return array
	 */
	protected static function REQUIRE_FILE(string $path): array
	{
		// $files   = ServicesSystem::LOCATOR()::SEARCH($path, 'php', false);
		$files   = SYSTEMPATH.$path;

		$strings = [];
		
		if (is_array($files)) {
			foreach ($files as $file)
			{
				// On some OS's we were seeing failures
				// on this command returning boolean instead
				// of array during testing, so we've removed
				// the require_once for now.
				if (is_file($file))
				{
					$strings[] = require $file;
				}
			}
		}else{
			if (is_file($files))
			{
				$strings[] = require $files;
			}
		}
		

		if (isset($strings[1]))
		{
			$strings = array_replace_recursive(...$strings);
		}
		elseif (isset($strings[0]))
		{
			$strings = $strings[0];
		}

		return $strings;
	}

	//--------------------------------------------------------------------
}
