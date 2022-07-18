<?php

/**
 * This file is part of the Hkm_code 4 framework.
 *
 * (c) Hkm_code Foundation <admin@Hkm_code.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hkm_code\Database;

use Hkm_code\CLI\CLI;
use Hkm_code\Exceptions\ConfigException;
use Hkm_code\Modules\LoadModules;
use Hkm_code\Vezirion\AutoloadVezirion;
use Hkm_code\Vezirion\ServicesSystem;
use RuntimeException;
use stdClass;
use SystemConfig\Plugins;
use mysqli_result;

/**
 * Class MigrationRunner
 */
class MigrationRunner
{
	/**
	 * Whether or not migrations are allowed to run.
	 *
	 * @var boolean
	 */
	protected static $enabled = false;

	/**
	 * Name of table to store meta information
	 *
	 * @var string
	 */
	public static $table;

	/**
	 * The Namespace  where migrations can be found.
	 *
	 * @var string|null
	 */
	protected static $namespace;

	/**
	 * The database Group to migrate.
	 *
	 * @var string
	 */
	protected static $group;

	/**
	 * The migration name.
	 *
	 * @var string
	 */
	protected static $name;

	/**
	 * The pattern used to locate migration file versions.
	 *
	 * @var string
	 */
	protected static $regex = '/^\d{4}[_-]?\d{2}[_-]?\d{2}[_-]?\d{6}_(\w+)$/';

	/**
	 * The main database connection. Used to store
	 * migration information in.
	 *
	 * @var BaseConnection
	 */
	protected static $db;

	/**
	 * If true, will continue instead of throwing
	 * exceptions.
	 *
	 * @var boolean
	 */
	protected static $silent = false;

	/**
	 * used to return messages for CLI.
	 *
	 * @var array
	 */
	protected static $cliMessages = [];

	/**
	 * Tracks whether we have already ensured
	 * the table exists or not.
	 *
	 * @var boolean
	 */
	public static $tableChecked = false;

	/**
	 * The full path to locate migration files.
	 *
	 * @var string
	 */
	protected static $path;

	/**
	 * The database Group filter.
	 *
	 * @var string|null
	 */
	protected static $groupFilter;

	/**
	 * Used to skip current migration.
	 *
	 * @var boolean
	 */
	protected static $groupSkip = false;
	protected static $thiss;

	/**
	 * Constructor.
	 *
	 * When passing in $db, you may pass any of the following to connect:
	 * - group name
	 * - existing connection instance
	 * - array of database configuration values
	 *
	 * @param MigrationsConfig                      $config
	 * @param ConnectionInterface|array|string|null $db
	 *
	 * @throws ConfigException
	 */
	public function __construct( $config, $db = null)
	{
		self::$thiss = $this;
		self::$enabled = $config::$enabled ?? false;
		self::$table   = $config::$table ?? 'migrations';

		// Default name space is the app namespace
		self::$namespace = APP_NAMESPACE."\\";

		// get default database group
		$config      = hkm_config('Database');
		self::$group = $config::$defaultGroup;
		unset($config);

		// If no db connection passed in, use
		// default database group.
		self::$db = hkm_db_connect($db);
	}

	//--------------------------------------------------------------------

	/**
	 * Locate and run all new migrations
	 *
	 * @param string|null $group
	 *
	 * @throws ConfigException
	 * @throws RuntimeException
	 *
	 * @return boolean
	 */
	public static function LATEST(string $group = null)
	{
		if (! self::$enabled)
		{
			throw ConfigException::FOR_DISABLED_MIGRATIONS();
		}


		self::ENSURE_TABLE();


		// Set database group if not null
		if (! is_null($group))
		{
			self::$groupFilter = $group;
			self::SET_GROUP($group);
		}

		// Locate the migrations
		$migrations = self::FIND_MIGRATIONS();

		// If nothing was found then we're done
		if (empty($migrations))
		{
			return true;
		}
		
        $histories = self::GET_HISTORY((string) $group);

		// Remove any migrations already in the history
		foreach ($histories as $history)
		{
			unset($migrations[self::GET_OBJECT_UID($history)]);
		}

		// Start a new batch
		$batch = self::GET_LAST_BATCH() + 1;
		// Run each migration
		foreach ($migrations as $migration)
		{
			if (self::MIGRATE('up', $migration))
			{

				if (self::$groupSkip === true)
				{
					self::$groupSkip = false;
					continue;
				}

				self::ADD_HISTORY($migration, $batch);
			}
			// If a migration failed then try to back out what was done
			else
			{
				self::REGRESS(-1);

				$message = hkm_lang('Migrations.generalFault');

				if (self::$silent)
				{
					self::$cliMessages[] = "\t" . CLI::color($message, 'red');
					return false;
				}

				throw new RuntimeException($message);
			}
		}

		hkm_do_action('on_all_migrate_migrated',true);



		$data           = get_object_vars(self::$thiss);
		$data['method'] = 'latest';

		return true;
	}

	//--------------------------------------------------------------------

	/**
	 * Migrate down to a previous batch
	 *
	 * Calls each migration step required to get to the provided batch
	 *
	 * @param integer     $targetBatch Target batch number, or negative for a relative batch, 0 for all
	 * @param string|null $group
	 *
	 * @throws ConfigException
	 * @throws RuntimeException
	 *
	 * @return mixed Current batch number on success, FALSE on failure or no migrations are found
	 */
	public static function REGRESS(int $targetBatch = 0, string $group = null)
	{
		if (! self::$enabled)
		{
			throw ConfigException::FOR_DISABLED_MIGRATIONS();
		}

		// Set database group if not null
		if (! is_null($group))
		{
			self::SET_GROUP($group);
		}

		self::ENSURE_TABLE();

		// Get all the batches
		$batches = self::GET_BATCHES();

		// Convert a relative batch to its absolute
		if ($targetBatch < 0)
		{
			$targetBatch = $batches[count($batches) - 1 + $targetBatch] ?? 0;
		}

		// If the goal was rollback then check if it is done
		if (empty($batches) && $targetBatch === 0)
		{
			return true;
		}

		// Make sure $targetBatch is found
		if ($targetBatch !== 0 && ! in_array($targetBatch, $batches, true))
		{
			$message = hkm_lang('Migrations.batchNotFound') . $targetBatch;

			if (self::$silent)
			{
				self::$cliMessages[] = "\t" . CLI::color($message, 'red');
				return false;
			}

			throw new RuntimeException($message);
		}

		// Save the namespace to restore it after loading migrations
		$tmpNamespace = self::$namespace;

		// Get all migrations
		self::$namespace = null;
		$allMigrations   = self::FIND_MIGRATIONS();

		// Gather migrations down through each batch until reaching the target
		$migrations = [];

		while ($batch = array_pop($batches))
		{
			// Check if reached target
			if ($batch <= $targetBatch)
			{
				break;
			}

			// Get the migrations from each history
			foreach (self::GET_BATCH_HISTORY($batch, 'desc') as $history)
			{
				// Create a UID from the history to match its migration
				$uid = self::GET_OBJECT_UID($history);

				// Make sure the migration is still available
				if (! isset($allMigrations[$uid]))
				{
					$message = hkm_lang('Migrations.gap') . ' ' . $history->version;

					if (self::$silent)
					{
						self::$cliMessages[] = "\t" . CLI::color($message, 'red');
						return false;
					}

					throw new RuntimeException($message);
				}

				// Add the history and put it on the list
				$migration          = $allMigrations[$uid];
				$migration->history = $history;
				$migrations[]       = $migration;
			}
		}

		// Run each migration
		foreach ($migrations as $migration)
		{
			if (self::MIGRATE('down', $migration))
			{
				self::REMOVE_HISTORY($migration->history);
			}
			// If a migration failed then quit so as not to ruin the whole batch
			else
			{
				$message = hkm_lang('Migrations.generalFault');

				if (self::$silent)
				{
					self::$cliMessages[] = "\t" . CLI::color($message, 'red');
					return false;
				}

				throw new RuntimeException($message);
			}
		}

		$data           = get_object_vars(self::$thiss);
		$data['method'] = 'regress';

		// Restore the namespace
		self::$namespace = $tmpNamespace;

		return true;
	}

	//--------------------------------------------------------------------

	/**
	 * Migrate a single file regardless of order or batches.
	 * Method "up" or "down" determined by presence in history.
	 * NOTE: This is not recommended and provided mostly for testing.
	 *
	 * @param string      $path  Full path to a valid migration file
	 * @param string      $path  Namespace of the target migration
	 * @param string|null $group
	 */
	public static function FORCE(string $path, string $namespace, string $group = null)
	{
		if (! self::$enabled)
		{
			throw ConfigException::FOR_DISABLED_MIGRATIONS();
		}

		self::ENSURE_TABLE();

		// Set database group if not null
		if (! is_null($group))
		{
			self::$groupFilter = $group;
			self::SET_GROUP($group);
		}

		// Create and validate the migration
		$migration = self::MIGRATION_FROM_FILE($path, $namespace);
		if (empty($migration))
		{
			$message = hkm_lang('Migrations.notFound');

			if (self::$silent)
			{
				self::$cliMessages[] = "\t" . CLI::color($message, 'red');
				return false;
			}
			throw new RuntimeException($message);
		}

		// Check the history for a match
		$method = 'up';
		self::SET_NAMESPACE($migration->namespace);
		foreach (self::GET_HISTORY(self::$group) as $history)
		{
			if (self::GET_OBJECT_UID($history) === $migration->uid)
			{
				$method             = 'down';
				$migration->history = $history;
				break;
			}
		}

		// up
		if ($method === 'up')
		{
			// Start a new batch
			$batch = self::GET_LAST_BATCH() + 1;

			if (self::MIGRATE('up', $migration) && self::$groupSkip === false)
			{
				self::ADD_HISTORY($migration, $batch);
				return true;
			}

			self::$groupSkip = false;
		}

		// down
		elseif (self::MIGRATE('down', $migration))
		{
			self::REMOVE_HISTORY($migration->history);
			return true;
		}

		// If it came this far the migration failed
		$message = hkm_lang('Migrations.generalFault');

		if (self::$silent)
		{
			self::$cliMessages[] = "\t" . CLI::color($message, 'red');
			return false;
		}
		throw new RuntimeException($message);
	}

	//--------------------------------------------------------------------

	/**
	 * Retrieves list of available migration scripts
	 *
	 * @return array    List of all located migrations by their UID
	 */
	public static function FIND_MIGRATIONS(): array
	{
		// If a namespace is set then use it, otherwise load all namespaces from the autoloader
		$namespaces = self::$namespace ? [self::$namespace] : array_keys(array_merge(AutoloadVezirion::$class,AutoloadVezirion::$classmap['system']));
        
		$namespaces[] = '____plugins____migration_files';
		// Collect the migrations to run by their sortable UID
		$migrations = [];
		foreach ($namespaces as $namespace)
		{
			
			$migs = self::FIND_NAMESPACE_MIGRATIONS($namespace);
			foreach ($migs as $migration)
			{
				$migrations[$migration->uid] = $migration;
			}
			
			
		}

		$migrations = hkm_apply_filters('on_migrate_fetch_locals_result',$migrations);

		// Sort migrations ascending by their UID (version)
		ksort($migrations);
 
		return $migrations;
	}

	//--------------------------------------------------------------------

	/**
	 * Retrieves a list of available migration scripts for one namespace
	 *
	 * @param string $namespace The namespace to search for migrations
	 *
	 * @return array    List of unsorted migrations from the namespace
	 */
	public static function FIND_NAMESPACE_MIGRATIONS(string $namespace): array
	{
		$migrations = [];
		$locator    = ServicesSystem::LOCATOR(true);
		$dirsPluginsMigration = [];
		if ($namespace == '____plugins____migration_files') {
			
			$dirsPluginsMigration = hkm_apply_filters('on_plugin_migrations_dir',$dirsPluginsMigration);

		}


		// If self::$path contains a valid directory use it.
		if (! empty(self::$path))
		{

			hkm_helper('filesystem');
			$dir   = rtrim(self::$path, DIRECTORY_SEPARATOR) . '/';
			$files = hkm_get_filenames($dir, true);
		}
		// Otherwise use FileLocator to search files in the subdirectory of the namespace
		else
		{
			if (empty($dirsPluginsMigration)) {
				$files = $locator::LIST_NAMESPACE_FILES($namespace, 'Database/Migrations/');
			}
			else{
				hkm_helper('filesystem');
				$f_plgins = [];
				foreach ($dirsPluginsMigration as $dir) {
					$dir   = rtrim($dir, DIRECTORY_SEPARATOR) . '/';
					$files_plugins = hkm_get_filenames($dir, true);
					$f_plgins = array_merge($f_plgins,$files_plugins);
				}


				foreach ($f_plgins as $plugins) {
					$default_headers = array(
						'Name'        => 'Migrate Name',
						'MigrateURI'   => 'Migrate URI',
						'Version'     => 'Version',
						'Description' => 'Description',
						'Author'      => 'Author',
						'AuthorURI'   => 'Author URI',
						'Class'  => 'Migrate class',
						'Namespace'  => 'Migrate Namespace',
					); 
	                $plugin_data = hkm_get_file_data( $plugins, $default_headers, 'plugin_migation' );
				    $migration = new stdClass();
                    $classname = $locator::GET_CLASS_NAME($plugins);
					$nm = explode("\\",$classname);
					unset($nm[count($nm) -1]);
					// Get migration version number
					$migration->version   = self::GET_MIGRATION_NUMBER($plugin_data['Version']);
					$migration->name      = $plugin_data['Name'];
					$migration->path      = $plugins;
					$migration->class     = $classname;
					$migration->namespace = empty($plugin_data['Namespace'])?implode("\\",$nm):$plugin_data['Namespace'];
					$migration->uid       = self::GET_OBJECT_UID($migration);

					if (empty($plugin_data['Class'])) {
						hkm_do_action('error_plugin_migration_header',$migration);
					}else{
						$migrations[]=$migration;
					}
				}

				return $migrations;

			}
			

		}

         

		// Load all *_*.php files in the migrations path
		// We can't use glob if we want it to be testable....
		foreach ($files as $file)
		{
			// Clean up the file path
			$file = empty(self::$path) ? $file : self::$path . str_replace(self::$path, '', $file);

			// Create the migration object from the file and save it
			if ($migration = self::MIGRATION_FROM_FILE($file, $namespace))
			{
				$migrations[] = $migration;
			}
		}

		return $migrations;
	}

	//--------------------------------------------------------------------

	/**
	 * Create a migration object from a file path.
	 *
	 * @param string $path The path to the file
	 * @param string $path The namespace of the target migration
	 *
	 * @return object|false    Returns the migration object, or false on failure
	 */
	protected static function MIGRATION_FROM_FILE(string $path, string $namespace)
	{
		if (substr($path, -4) !== '.php')
		{
			return false;
		}

		// Remove the extension
		$name = basename($path, '.php');

		// Filter out non-migration files
		if (! preg_match(self::$regex, $name))
		{
			return false;
		}

		$locator = ServicesSystem::LOCATOR(true);

		// Create migration object using stdClass
		$migration = new stdClass();

		// Get migration version number
		$migration->version   = self::GET_MIGRATION_NUMBER($name);
		$migration->name      = self::GET_MIGRATION_NAME($name);
		$migration->path      = $path;
		$migration->class     = $locator::GET_CLASS_NAME($path);
		$migration->namespace = $namespace;
		$migration->uid       = self::GET_OBJECT_UID($migration);


		return $migration;
	}

	//--------------------------------------------------------------------

	/**
	 * Set namespace.
	 * Allows other scripts to modify on the fly as needed.
	 *
	 * @param string $namespace or null for "all"
	 *
	 * @return MigrationRunner
	 */
	public static function SET_NAMESPACE(?string $namespace)
	{
		self::$namespace = $namespace;

		return self::$thiss;
	}

	//--------------------------------------------------------------------

	/**
	 * Set database Group.
	 * Allows other scripts to modify on the fly as needed.
	 *
	 * @param string $group
	 *
	 * @return MigrationRunner
	 */
	public static function SET_GROUP(string $group)
	{
		self::$group = $group;

		return self::$thiss;
	}

	//--------------------------------------------------------------------

	/**
	 * Set migration Name.
	 *
	 * @param string $name
	 *
	 * @return MigrationRunner
	 */
	public static function SET_NAME(string $name)
	{
		self::$name = $name;

		return self::$thiss;
	}

	//--------------------------------------------------------------------

	/**
	 * If $silent == true, then will not throw exceptions and will
	 * attempt to continue gracefully.
	 *
	 * @param boolean $silent
	 *
	 * @return MigrationRunner
	 */
	public static function SET_SILENT(bool $silent)
	{
		self::$silent = $silent;

		return self::$thiss;
	}

	//--------------------------------------------------------------------

	/**
	 * Extracts the migration number from a filename
	 *
	 * @param string $migration
	 *
	 * @return string    Numeric portion of a migration filename
	 */
	protected static function GET_MIGRATION_NUMBER(string $migration): string
	{
		preg_match('/^\d{4}[_-]?\d{2}[_-]?\d{2}[_-]?\d{6}/', $migration, $matches);

		return count($matches) ? $matches[0] : '0';
	}

	//--------------------------------------------------------------------

	/**
	 * Extracts the migration class name from a filename
	 *
	 * @param string $migration
	 *
	 * @return string    text portion of a migration filename
	 */
	protected static function GET_MIGRATION_NAME(string $migration): string
	{
		$parts = explode('_', $migration);
		array_shift($parts);

		return implode('_', $parts);
	}

	//--------------------------------------------------------------------

	/**
	 * Uses the non-repeatable portions of a migration or history
	 * to create a sortable unique key
	 *
	 * @param object $object migration or $history
	 *
	 * @return string
	 */
	public static function GET_OBJECT_UID($object): string
	{
		$cl = explode('\\',$object->class);
		if (count($cl)>1) {
			$class = $cl[count($cl)-1];
		}else{
			$class = $cl[0];
		}
		// print_r($object->version);exit;
		return preg_replace('/[^0-9]/', '', $object->version) . $class;
	}

	//--------------------------------------------------------------------

	/**
	 * Retrieves messages formatted for CLI output
	 *
	 * @return array    Current migration version
	 */
	public static function GET_CLI_MESSAGES(): array
	{
		return self::$cliMessages;
	}

	//--------------------------------------------------------------------

	/**
	 * Clears any CLI messages.
	 *
	 * @return MigrationRunner
	 */
	public static function CLEAR_CLI_MESSAGES()
	{
		self::$cliMessages = [];

		return self::$thiss;
	}

	//--------------------------------------------------------------------

	/**
	 * Truncates the history table.
	 *
	 * @return void
	 */
	public static function CLEAR_HISTORY()
	{
		if (self::$db::TABLE_EXISTS(self::$table))
		{
			self::$db::TABLE(self::$table)->truncate();
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Add a history to the table.
	 *
	 * @param object  $migration
	 * @param integer $batch
	 *
	 * @return void
	 */
	protected static function ADD_HISTORY($migration, int $batch)
	{
	
		self::$db::TABLE(self::$table)->insert([
			'version'   => $migration->version,
			'class'     => $migration->class,
			'groupe'     => self::$group,
			'namespace' => $migration->namespace,
			'time'      => time(),
			'batch'     => $batch,
		],null);

		if (hkm_is_cli())
		{
			self::$cliMessages[] = sprintf(
				"\t%s(%s) %s_%s",
				CLI::color(hkm_lang('Migrations.added'), 'yellow'),
				$migration->namespace,
				$migration->version,
				$migration->class
			);
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Removes a single history
	 *
	 * @param object $history
	 *
	 * @return void
	 */
	protected static function REMOVE_HISTORY($history)
	{
		self::$db::TABLE(self::$table)->where('id', $history->id)->delete();

		if (hkm_is_cli())
		{
			self::$cliMessages[] = sprintf(
				"\t%s(%s) %s_%s",
				CLI::color(hkm_lang('Migrations.removed'), 'yellow'),
				$history->namespace,
				$history->version,
				$history->class
			);
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Grabs the full migration history from the database for a group
	 *
	 * @param string $group
	 *
	 * @return array
	 */
	public static function GET_HISTORY(string $group = 'default'): array
	{
		self::$thiss::ENSURE_TABLE();


		$builder = self::$thiss::$db::TABLE(self::$thiss::$table);
		// If group was specified then use it
		if (! empty($group))
		{
			$builder->where('groupe', $group);
		}

		// // If a namespace was specified then use it
		if (self::$namespace)
		{
			$builder->where('namespace', self::$namespace);
		}

		$query = $builder->orderBy('id', 'ASC')->get();
		$return = ! empty($query) ? $query->getResultObject() : [];

		return $return;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the migration history for a single batch.
	 *
	 * @param integer $batch
	 *
	 * @return array
	 */
	public static function GET_BATCH_HISTORY(int $batch, $order = 'asc'): array
	{
		self::$thiss::ENSURE_TABLE();

		$query = self::$db::TABLE(self::$table)
						  ->where('batch', $batch)
						  ->orderBy('id', $order)
						  ->get();

		return ! empty($query) ? $query->getResultObject() : [];
	}

	//--------------------------------------------------------------------

	/**
	 * Returns all the batches from the database history in order
	 *
	 * @return array
	 */
	public static function GET_BATCHES(): array
	{
		self::ENSURE_TABLE();

		$batches = self::$db::TABLE(self::$table)
						  ->select('batch')
						  ->distinct()
						  ->orderBy('batch', 'asc')
						  ->get()
						  ->getResultArray();

		return array_map('intval', array_column($batches, 'batch'));
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the value of the last batch in the database.
	 *
	 * @return integer
	 */
	public static function GET_LAST_BATCH(): int
	{
		self::ENSURE_TABLE();

		$batch = self::$db::TABLE(self::$table)
						  ->selectMax('batch')
						  ->get()
						  ->getResultObject();
		$batch = is_array($batch) && count($batch)
			? end($batch)->batch
			: 0;

		return (int) $batch;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the version number of the first migration for a batch.
	 * Mostly just for tests.
	 *
	 * @param integer $batch
	 *
	 * @return string
	 */
	public static function GET_BATCH_START(int $batch): string
	{
		// Convert a relative batch to its absolute
		if ($batch < 0)
		{
			$batches = self::GET_BATCHES();
			$batch   = $batches[count($batches) - 1] ?? 0;
		}

		$migration = self::$db::TABLE(self::$table)
			->where('batch', $batch)
			->orderBy('id', 'asc')
			->limit(1)
			->get()
			->getResultObject();

		return count($migration) ? $migration[0]->version : '0';
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the version number of the last migration for a batch.
	 * Mostly just for tests.
	 *
	 * @param integer $batch
	 *
	 * @return string
	 */
	public static function GET_BATCH_END(int $batch): string
	{
		// Convert a relative batch to its absolute
		if ($batch < 0)
		{
			$batches = self::GET_BATCHES();
			$batch   = $batches[count($batches) - 1] ?? 0;
		}

		$migration = self::$db::TABLE(self::$table)
			  ->where('batch', $batch)
			  ->orderBy('id', 'desc')
			  ->limit(1)
			  ->get()
			  ->getResultObject();

		return count($migration) ? $migration[0]->version : 0;
	}

	//--------------------------------------------------------------------

	/**
	 * Ensures that we have created our migrations table
	 * in the database.
	 */
	public static function ENSURE_TABLE()
	{
		if (self::$tableChecked || self::$db::TABLE_EXISTS(self::$table))
		{
			return;
		}


		$forge = Vezirion::FORGE(self::$db);

		$forge->addField([
			'id'        => [
				'type'           => 'BIGINT',
				'constraint'     => 20,
				'unsigned'       => true,
				'auto_increment' => true,
			],
			'version'   => [
				'type'       => 'VARCHAR',
				'constraint' => 255,
				'null'       => false,
			],
			'class'     => [
				'type'       => 'VARCHAR',
				'constraint' => 255,
				'null'       => false,
			],
			'groupe'     => [
				'type'       => 'VARCHAR',
				'constraint' => 255,
				'null'       => false,
			],
			'namespace' => [
				'type'       => 'VARCHAR',
				'constraint' => 255,
				'null'       => false,
			],
			'time'      => [
				'type'       => 'INT',
				'constraint' => 11,
				'null'       => false,
			],
			'batch'     => [
				'type'       => 'INT',
				'constraint' => 11,
				'unsigned'   => true,
				'null'       => false,
			],
		]);

		$forge->addPrimaryKey('id');
		$forge->createTable(self::$table, true);
	  
		self::$tableChecked = true;
	}

	/**
	 * Handles the actual running of a migration.
	 *
	 * @param string $direction "up" or "down"
	 * @param object $migration The migration to run
	 *
	 * @return boolean
	 */
	protected static function MIGRATE($direction, $migration): bool
	{
		include_once $migration->path;



		if ($direction == 'down') {
			$migration = hkm_apply_filters('on_migrate_rollback',$migration);
		}else{
			$migration = hkm_apply_filters('on_migrate_migrate',$migration);

		}
        $direction = strtoupper($direction);
		$class = $migration->class;
		self::SET_NAME($migration->name);

		// Validate the migration file structure
		if (! class_exists($class, false))
		{
			$message = sprintf(hkm_lang('Migrations.classNotFound'), $class);

			hkm_do_action('error_migration_class_not_found',$migration,$message);

			if (self::$silent)
			{
				self::$cliMessages[] = "\t" . CLI::color($message, 'red');
				return false;
			}
			throw new RuntimeException($message);
		}

		// Initialize migration
		$instance = new $class();
		// Determine DBGroup to use
		$group = $instance::GET_DB_GROUP() ?? hkm_config('Database')::$defaultGroup;

		// Skip tests db group when not running in testing environment
		if (ENVIRONMENT !== 'testing' && $group === 'tests' && self::$groupFilter !== 'tests')
		{
			// @codeCoverageIgnoreStart
			self::$groupSkip = true;
			return true;
			// @codeCoverageIgnoreEnd
		}

		// Skip migration if group filtering was set
		if ($direction === 'up' && ! is_null(self::$groupFilter) && self::$groupFilter !== $group)
		{
			self::$groupSkip = true;
			return true;
		}

		self::SET_GROUP($group);

		if (! is_callable([$instance, $direction]))
		{
			$message = sprintf(hkm_lang('Migrations.missingMethod'), $direction);
			hkm_do_action('error_migration_method_not_found',$migration,$direction,$message);


			if (self::$silent)
			{
				self::$cliMessages[] = "\t" . CLI::color($message, 'red');
				return false;
			}
			throw new RuntimeException($message);
		}

		$instance::{$direction}();

		if (strtolower($direction) == 'down') {
			$migration = hkm_apply_filters('on_migrate_rollbacked',$migration);
		}else{
			$migration = hkm_apply_filters('on_migrate_migrated',$migration);

		}

		return true;
	}
}
