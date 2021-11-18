<?php


namespace Hkm_code\Vezirion;

use InvalidArgumentException;

/**
 * Environment-specific configuration
 */
class DotEnv
{
	
	protected static $path;

	public function __construct(string $path, string $file = '.env')
	{
		self::$path = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $file;
	}

	public static function LOAD(): bool
	{
		
		$vars = self::PARSE();

		return $vars !== null;
	}

	
	public static function PARSE(): ?array
	{
		
		// We don't want to enforce the presence of a .env file, they should be optional.
		if (! is_file(self::$path))
		{
			return null;
		}

		// Ensure the file is readable
		if (! is_readable(self::$path))
		{
			$path = self::$path;
			throw new InvalidArgumentException("The .env file is not readable: {$path}");
		}

		$vars = [];

		$lines = file(self::$path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

		

		foreach ($lines as $line)
		{
			// Is it a setting?
			if (strpos(trim($line), ';') === 0)
			{
				if (strpos($line, '=') !== false)
			    {
				

					[$name, $value] = self::NORMALIZE_VARIABLE($line);
					$vars[$name]        = $value;
					self::SET_VARIABLE($name, $value);
			    }
			}
            

			
		}

		return $vars;
	}


	protected static function SET_VARIABLE(string $name, string $value = '')
	{
		if (! getenv($name, true))
		{
			putenv("$name=$value");
		}

		if (empty($_ENV[$name]))
		{
			$_ENV[$name] = $value;
		}

		if (empty($_SERVER[$name]))
		{
			$_SERVER[$name] = $value;
		}
	}

	
	public static function NORMALIZE_VARIABLE(string $name, string $value = ''): array
	{
		// Split our compound string into its parts.
		if (strpos($name, '=') !== false)
		{
			[$name, $value] = explode('=', $name, 2);
		}


		// Sanitize the name
		$name = str_replace(['export', '\'', '"',';'], '', $name);
		$name  = trim($name);
		$value = trim($value);


		// Sanitize the value
		$value = self::SANITIZE_VALUE($value);

		$value = self::RESOLVE_NESTED_VARIABLE($value);

		return [
			$name,
			$value,
		];
	}

	
	protected static function SANITIZE_VALUE(string $value): string
	{
		if (! $value)
		{
			return $value;
		}

		// Does it begin with a quote?
		if (strpbrk($value[0], '"\'') !== false)
		{
			// value starts with a quote
			$quote        = $value[0];
			$regexPattern = sprintf(
					'/^
					%1$s          # match a quote at the start of the value
					(             # capturing sub-pattern used
								  (?:          # we do not need to capture this
								   [^%1$s\\\\] # any character other than a quote or backslash
								   |\\\\\\\\   # or two backslashes together
								   |\\\\%1$s   # or an escaped quote e.g \"
								  )*           # as many characters that match the previous rules
					)             # end of the capturing sub-pattern
					%1$s          # and the closing quote
					.*$           # and discard any string after the closing quote
					/mx', $quote
			);

			$value = preg_replace($regexPattern, '$1', $value);
			$value = str_replace("\\$quote", $quote, $value);
			$value = str_replace('\\\\', '\\', $value);
		}
		else
		{
			$parts = explode(' #', $value, 2);

			$value = trim($parts[0]);

			// Unquoted values cannot contain whitespace
			if (preg_match('/\s+/', $value) > 0)
			{
				throw new InvalidArgumentException('.env values containing spaces must be surrounded by quotes.');
			}
		}

		return $value;
	}

	//--------------------------------------------------------------------

	
	protected static function RESOLVE_NESTED_VARIABLE(string $value): string
	{
		if (strpos($value, '$') !== false)
		{
			$value = preg_replace_callback(
				'/\${([a-zA-Z0-9_\.]+)}/',
				function ($matchedPatterns) {
					$nestedVariable = self::GET_VARIABLE($matchedPatterns[1]);

					if (is_null($nestedVariable))
					{
						return $matchedPatterns[0];
					}

					return $nestedVariable;
				},
				$value
			);
		}

		return $value;
	}


	protected static function GET_VARIABLE(string $name)
	{
		switch (true)
		{
			case array_key_exists($name, $_ENV):
				return $_ENV[$name];
			case array_key_exists($name, $_SERVER):
				return $_SERVER[$name];
			default:
				$value = getenv($name);

				// switch getenv default to null
				return $value === false ? null : $value;
		}
	}

	//--------------------------------------------------------------------
}
