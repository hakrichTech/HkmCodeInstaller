<?php

namespace Hkm_Config;

use InvalidArgumentException;


class config_system implements config_system_interface
{
    protected static $path;

	public function __construct(string $path, string $file = '.hkm_config.yaml')
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
			throw new InvalidArgumentException("The .hkm_config.yaml file is not readable: {$path}");
		}

		$vars = [];


        hkm_helper('yaml');
        $vars = hkm_env_format(self::$path);

		foreach ($vars as $name => $value) {
		  self::SET_VARIABLE($name, $value);
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

}


?>