<?php

/**
 * This file is part of the Hkm_code 4 framework.
 *
 * (c) Hkm_code Foundation <admin@hakrichteam.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hkm_code\Debug\Toolbar\Collectors;

use Hkm_code\Vezirion\ServicesSystem;
use ReflectionException;
use ReflectionFunction;
use ReflectionMethod;

/**
 * Routes collector
 */
class Routes extends BaseCollector
{
	/**
	 * Whether this collector has data that can
	 * be displayed in the Timeline.
	 *
	 * @var boolean
	 */
	protected static $hasTimeline = false;

	/**
	 * Whether this collector needs to display
	 * content in a tab or not.
	 *
	 * @var boolean
	 */
	protected static $hasTabContent = true;

	/**
	 * The 'title' of this Collector.
	 * Used to name things in the toolbar HTML.
	 *
	 * @var string
	 */
	protected static $title = 'Routes';

	//--------------------------------------------------------------------

	/**
	 * Returns the data of this collector to be formatted in the toolbar
	 *
	 * @return array
	 * @throws ReflectionException
	 */
	public static function DISPLAY(): array
	{
		$rawRoutes = ServicesSystem::ROUTES(true);
		$router    = ServicesSystem::ROUTER(null, null, true);

		/*
		 * Matched Route
		 */
		$route = $router::GET_MATCHED_ROUTE();

		// Get our parameters
		// Closure routes
		if (is_callable($router::CONTROLLER_NAME()))
		{
			$method = new ReflectionFunction($router::COLTROLLER_NAME());
		}
		else
		{
			try
			{
				$method = new ReflectionMethod($router::CONTROLLER_NAME(), $router::METHOD_NAME());
			}
			catch (ReflectionException $e)
			{
				// If we're here, the method doesn't exist
				// and is likely calculated in _remap.
				$method = new ReflectionMethod($router::CONTROLLER_NAME(), '_remap');
			}
		}

		$rawParams = $method->getParameters();

		$params = [];
		foreach ($rawParams as $key => $param)
		{
			$params[] = [
				'name'  => $param->getName(),
				'value' => $router::PARAMS()[$key] ??
					'&lt;empty&gt;&nbsp| default: ' . var_export($param->isDefaultValueAvailable() ? $param->getDefaultValue() : null, true),
			];
		}

		$matchedRoute = [
			[
				'directory'  => $router::DIRECTORY(),
				'controller' => $router::CONTROLLER_NAME(),
				'method'     => $router::METHOD_NAME(),
				'paramCount' => count($router::PARAMS()),
				'truePCount' => count($params),
				'params'     => $params,
			],
		];

		/*
		* Defined Routes
		*/
		$routes  = [];
		$methods = [
			'get',
			'head',
			'post',
			'patch',
			'put',
			'delete',
			'options',
			'trace',
			'connect',
			'cli',
		];

		foreach ($methods as $method)
		{
			$raw = $rawRoutes::GET_ROUTES($method);

			foreach ($raw as $route => $handler)
			{
				// filter for strings, as callbacks aren't displayable
				if (is_string($handler))
				{
					$routes[] = [
						'method'  => strtoupper($method),
						'route'   => $route,
						'handler' => $handler,
					];
				}
			}
		}

		return [
			'matchedRoute' => $matchedRoute,
			'routes'       => $routes,
		];
	}

	//--------------------------------------------------------------------

	/**
	 * Returns a count of all the routes in the system.
	 *
	 * @return integer
	 */
	public static function getBadgeValue(): int
	{
		$rawRoutes = ServicesSystem::ROUTES(true);

		return count($rawRoutes::GET_ROUTES());
	}

	//--------------------------------------------------------------------

	/**
	 * Display the icon.
	 *
	 * Icon from https://icons8.com - 1em package
	 *
	 * @return string
	 */
	public static function ICON(): string
	{
		return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsMAAA7DAcdvqGQAAAFDSURBVEhL7ZRNSsNQFIUjVXSiOFEcuQIHDpzpxC0IGYeE/BEInbWlCHEDLsSiuANdhKDjgm6ggtSJ+l25ldrmmTwIgtgDh/t37r1J+16cX0dRFMtpmu5pWAkrvYjjOB7AETzStBFW+inxu3KUJMmhludQpoflS1zXban4LYqiO224h6VLTHr8Z+z8EpIHFF9gG78nDVmW7UgTHKjsCyY98QP+pcq+g8Ku2s8G8X3f3/I8b038WZTp+bO38zxfFd+I6YY6sNUvFlSDk9CRhiAI1jX1I9Cfw7GG1UB8LAuwbU0ZwQnbRDeEN5qqBxZMLtE1ti9LtbREnMIuOXnyIf5rGIb7Wq8HmlZgwYBH7ORTcKH5E4mpjeGt9fBZcHE2GCQ3Vt7oTNPNg+FXLHnSsHkw/FR+Gg2bB8Ptzrst/v6C/wrH+QB+duli6MYJdQAAAABJRU5ErkJggg==';
	}
}
