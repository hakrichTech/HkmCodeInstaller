<?php

/**
 * This file is part of the Hkm_code 4 framework.
 *
 * (c) Hkm_code Foundation <admin@Hkm_code.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hkm_code\CommandsInstaller\Server;

use Hkm_code\CLI\BaseCommand;
use Hkm_code\CLI\CLI;
use Hkm_code\Vezirion\ServicesSystem;

/**
 * Launch the PHP development server
 *
 * Not testable, as it throws phpunit for a loop :-/
 *
 * @codeCoverageIgnore
 */
class Serve extends BaseCommand
{
	/**
	 * Minimum PHP version
	 *
	 * @var string
	 */
	protected $minPHPVersion = '7.3';

	/**
	 * Group
	 *
	 * @var string
	 */
	protected $group = 'Hkm_code';

	/**
	 * Name
	 *
	 * @var string
	 */
	protected $name = 'serve';

	/**
	 * Description
	 *
	 * @var string
	 */
	protected $description = 'Launches the Hkm_code PHP-Development Server.';

	/**
	 * Usage
	 *
	 * @var string
	 */
	protected $usage = 'serve';

	/**
	 * Arguments
	 *
	 * @var array
	 */
	protected $arguments = [];

	/**
	 * The current port offset.
	 *
	 * @var integer
	 */
	protected $portOffset = 0;

	/**
	 * The max number of ports to attempt to serve from
	 *
	 * @var integer
	 */
	protected $tries = 10;

	/**
	 * Options
	 *
	 * @var array
	 */
	protected $options = [
		'--php'  => 'The PHP Binary [default: "PHP_BINARY"]',
		'--host' => 'The HTTP Host [default: "localhost"]',
		'--port' => 'The HTTP Host Port [default: "8080"]',
	];

	/**
	 * Run the server
	 *
	 * @param array $params Parameters
	 *
	 * @return void
	 */
	public function run(array $params)
	{
		// Collect any user-supplied options and apply them.
		$php  = escapeshellarg(CLI::getOption('php') ?? PHP_BINARY);
		$host = CLI::getOption('host') ?? 'localhost';
		
		$port = (int) (CLI::getOption('port') ?? 8080) + $this->portOffset;

		// Get the party started.
		CLI::write('Hkm_code development server started on http://' . $host . ':' . $port, 'green');
		CLI::write('Press Control-C to stop.');

		// Set the Front Controller path as Document Root.
		$docroot = escapeshellarg(FCPATH);

		// Mimic Apache's mod_rewrite functionality with user settings.
		$rewrite = escapeshellarg(__DIR__ . '/rewrite.php');

		// Call PHP's built-in webserver, making sure to set our
		// base path to the public folder, and to use the rewrite file
		// to ensure our environment is set and it simulates basic mod_rewrite.
		passthru($php . ' -S ' . $host . ':' . $port . ' -t ' . $docroot . ' ' . $rewrite, $status);

		if ($status && $this->portOffset < $this->tries)
		{
			$this->portOffset++;

			$this->run($params);
		}
	}
}
