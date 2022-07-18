<?php
namespace Hkm_code\HTTP;

use RuntimeException;

/**
 * Class Hkm_code
 *
 * Represents a request from the command-line. Provides additional
 * tools to interact with that request since CLI requests are not
 * static like HTTP requests might be.
 *
 * Portions of this code were initially from the FuelPHP Framework,
 * version 1.7.x, and used here under the MIT license they were
 * originally made available under.
 *
 * http://fuelphp.com
 */
class CLIRequest extends Request
{
	/**
	 * Stores the segments of our cli "URI" command.
	 *
	 * @var array
	 */
	protected static $segments = [];

	/**
	 * Command line options and their values.
	 *
	 * @var array
	 */
	protected static $options = [];

	/**
	 * Set the expected HTTP verb
	 *
	 * @var string
	 */

	/**
	 * Constructor
	 *
	 * @param App $config
	 */
	public function __construct( $config)
	{
		if (! hkm_is_cli())
		{
			throw new RuntimeException(static::class . ' needs to run from the command line.'); // @codeCoverageIgnore
		}

		parent::__construct($config,'cli');


		// Don't terminate the script when the cli's tty goes away
		ignore_user_abort(true);

		self::PARSE_COMMAND();
        self::$thiss = $this;

	}

	public static function GET_PATH(): string
	{
		$path = implode('/', self::$segments);

		return empty($path) ? '' : $path;
	}

	
	public static function GET_OPTIONS(): array
	{
		return self::$options;
	}
 
	public static function GET_SEGMENTS(): array
	{
		return self::$segments;
	}

	public static function GET_OPTION(string $key)
	{
		return self::$options[$key] ?? null;
	}

	public static function GET_OPTION_STRING(bool $useLongOpts = false): string
	{
		if (empty(self::$options))
		{
			return '';
		}

		$out = '';

		foreach (self::$options as $name => $value)
		{
			if ($useLongOpts && mb_strlen($name) > 1)
			{
				$out .= "--{$name} ";
			}
			else
			{
				$out .= "-{$name} ";
			}

			// If there's a space, we need to group
			// so it will pass correctly.
			if (mb_strpos($value, ' ') !== false)
			{
				$out .= '"' . $value . '" ';
			}
			elseif ($value !== null)
			{
				$out .= "{$value} ";
			}
		}

		return trim($out);
	}

	
	protected static function PARSE_COMMAND()
	{
		$args = self::GET_SERVER('argv');
		array_shift($args); // Scrap index.php

		$optionValue = false;

		foreach ($args as $i => $arg)
		{
			if (mb_strpos($arg, '-') !== 0)
			{
				if ($optionValue)
				{
					$optionValue = false;
				}
				else
				{
					self::$segments[] = htmlspecialchars($arg);
				}

				continue;
			}

			$arg   = htmlspecialchars(ltrim($arg, '-'));
			$value = null;

			if (isset($args[$i + 1]) && mb_strpos($args[$i + 1], '-') !== 0)
			{
				$value       = htmlspecialchars($args[$i + 1]);
				$optionValue = true;
			}


			self::$options[$arg] = $value;

		}

	}

	//--------------------------------------------------------------------

	/**
	 * Determines if this request was made from the command line (CLI).
	 *
	 * @return boolean
	 */
	public static function IS_CLI(): bool
	{
		return hkm_is_cli();
	}

	//--------------------------------------------------------------------
}
