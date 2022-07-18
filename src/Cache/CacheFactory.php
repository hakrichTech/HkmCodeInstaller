<?php

/**
 * This file is part of the Hkm_code 4 framework.
 *
 * (c) Hkm_code Foundation <admin@hakrichteam.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hkm_code\Cache;

use Hkm_code\Cache\Exceptions\CacheException;
use Hkm_code\Exceptions\CriticalError;

/**
 * Class Cache
 *
 * A factory for loading the desired
 */
class CacheFactory
{
	/**
	 * Attempts to create the desired cache handler, based upon the
	 *
	 * @param        $config
	 * @param string|null $handler
	 * @param string|null $backup
	 *
	 * @return CacheInterface
	 */
	public static function GET_HANDLER( $config, string $handler = null, string $backup = null)
	{
		if (! isset($config::$validHandlers) || ! is_array($config::$validHandlers))
		{
			throw CacheException::forInvalidHandlers();
		}


		if (! isset($config::$handler) || ! isset($config::$backupHandler))
		{
			throw CacheException::forNoBackup();
		}

		$handler = ! empty($handler) ? $handler : $config::$handler;
		$backup  = ! empty($backup) ? $backup : $config::$backupHandler;

		if (! array_key_exists($handler, $config::$validHandlers) || ! array_key_exists($backup, $config::$validHandlers))
		{
			throw CacheException::forHandlerNotFound();
		}

		// Get an instance of our handler.
		$adapter = new $config::$validHandlers[$handler]($config);

		if (! $adapter::IS_SUPPORTED())
		{
			$adapter = new $config::$validHandlers[$backup]($config);

			if (! $adapter::IS_SUPPORTED())
			{
				// Log stuff here, don't throw exception. No need to raise a fuss.
				// Fall back to the dummy adapter.
				$adapter = new $config::$validHandlers['dummy']();
			}
		}

		// If $adapter::initialization throws a CriticalError exception, we will attempt to
		// use the $backup handler, if that also fails, we resort to the dummy handler.
		try
		{
			$adapter::INITIALIZE();
		}
		catch (CriticalError $e)
		{
			// log the fact that an exception occurred as well what handler we are resorting to
			hkm_log_message('critical', $e->getMessage() . ' Resorting to using ' . $backup . ' handler.');

			// get the next best cache handler (or dummy if the $backup also fails)
			$adapter = self::GET_HANDLER($config, $backup, 'dummy');
		}

		return $adapter;
	}

	//--------------------------------------------------------------------
}
