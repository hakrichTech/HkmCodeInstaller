<?php

/**
 * This file is part of the Application 4 framework.
 *
 * (c) Application Foundation <admin@Application.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hkm_code\Debug\Toolbar\Collectors;

use Hkm_code\Application;
use Hkm_code\Vezirion\ServicesSystem;

/**
 * Debug toolbar configuration
 */
class Config
{
	/**
	 * Return toolbar config values as an array.
	 *
	 * @return array
	 */
	public static function DISPLAY(): array
	{
		$config = hkm_config("App");

		return [
			'HKM_Version'   => Application::HKM_VERSION,
			'phpVersion'  => phpversion(),
			'phpSAPI'     => php_sapi_name(),
			'environment' => ENVIRONMENT,
			'baseURL'     => $config::$baseURL,
			'timezone'    => app_timezone(),
			'locale'      => ServicesSystem::REQUEST()::GET_LOCALE(),
			'cspEnabled'  => $config::$CSPEnabled,
		];
	}
}
