<?php

/**
 * This file is part of the Hkm_code 4 framework.
 *
 * (c) Hkm_code Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hkm_code\Database;

use Hkm_code\CLI\CLI;
use Faker\Factory;
use Faker\Generator;
use InvalidArgumentException;

/**
 * Class Seeder
 */
class Seeder
{
	/**
	 * The name of the database group to use.
	 *
	 * @var string
	 */
	protected static $DBGroup;

	/**
	 * Where we can find the Seed files.
	 *
	 * @var string
	 */
	protected static $seedPath;

	/**
	 * An instance of the main Database configuration
	 *
	 * @var Database
	 */
	protected static $config;

	/**
	 * Database Connection instance
	 *
	 * @var BaseConnection
	 */
	protected static $db;

	/**
	 * Database Forge instance.
	 *
	 * @var Forge
	 */
	protected static $forge;

	/**
	 * If true, will not display CLI messages.
	 *
	 * @var boolean
	 */
	protected static $silent = false;

	/**
	 * Faker Generator instance.
	 *
	 * @var Generator|null
	 */
	private static $faker;
	private static $thiss;

	/**
	 * Seeder constructor.
	 *
	 * @param Database            $config
	 * @param BaseConnection|null $db
	 */
	public function __construct( $config, BaseConnection $db = null)
	{
		self::$thiss = $this;
		self::$seedPath = $config::$filesPath ?? APPPATH . 'Database/';

		if (empty(self::$seedPath))
		{
			throw new InvalidArgumentException('Invalid filesPath set in the Database.');
		}

		self::$seedPath = rtrim(self::$seedPath, '\\/') . '/Seeds/';

		if (! is_dir(self::$seedPath))
		{
			throw new InvalidArgumentException('Unable to locate the seeds directory. Please check Database::filesPath');
		}

		self::$config = &$config;

		$db = $db ?? $config::CONNECT(self::$DBGroup);

		self::$db    = &$db;
		self::$forge = $config::FORGE(self::$DBGroup);
	}

	/**
	 * Gets the Faker Generator instance.
	 *
	 * @return Generator|null
	 */
	public static function FAKER(): ?Generator
	{
		if (self::$faker === null && class_exists(Factory::class))
		{
			self::$faker = Factory::create();
		}

		return self::$faker;
	}

	/**
	 * Loads the specified seeder and runs it.
	 *
	 * @param string $class
	 *
	 * @throws InvalidArgumentException
	 *
	 * @return void
	 */
	public static function CALL(string $class)
	{
		$class = trim($class);

		if ($class === '')
		{
			throw new InvalidArgumentException('No seeder was specified.');
		}

		if (strpos($class, '\\') === false)
		{
			$path = self::$seedPath . str_replace('.php', '', $class) . '.php';

			if (! is_file($path))
			{
				throw new InvalidArgumentException('The specified seeder is not a valid file: ' . $path);
			}

			// Assume the class has the correct namespace
			// @codeCoverageIgnoreStart
			$class = APP_NAMESPACE . '\Database\Seeds\\' . $class;

			if (! class_exists($class, false))
			{
				// require_once $path;
			}
			// @codeCoverageIgnoreEnd
		}

		/**
		 * @var Seeder
		 */
		$seeder = new $class(self::$config);
		$seeder::SET_SILENT(self::$silent)::RUN();

		unset($seeder);

		if (is_cli() && ! self::$silent)
		{
			CLI::write("Seeded: {$class}", 'green');
		}
	}

	/**
	 * Sets the location of the directory that seed files can be located in.
	 *
	 * @param string $path
	 *
	 * @return $this
	 */
	public static function SET_PATH(string $path)
	{
		self::$seedPath = rtrim($path, '\\/') . '/';

		return self::$thiss;
	}

	/**
	 * Sets the silent treatment.
	 *
	 * @param boolean $silent
	 *
	 * @return $this
	 */
	public static function SET_SILENT(bool $silent)
	{
		self::$silent = $silent;

		return self::$thiss;
	}

	/**
	 * Run the database seeds. This is where the magic happens.
	 *
	 * Child classes must implement this method and take care
	 * of inserting their data here.
	 *
	 * @return mixed
	 *
	 * @codeCoverageIgnore
	 */
	public static function RUN()
	{
	}
}
