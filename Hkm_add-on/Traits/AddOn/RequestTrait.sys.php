<?php
namespace Hkm_traits\AddOn;

use Hkm_code\Validation\FormatRules;

/**
 * 
 */
trait RequestTrait
{
    protected static $ipAddress = ''; 
    protected static $globals = [];
    public static function GET_IP_ADDRESS(): string
	{
		if (self::$ipAddress)
		{
			return self::$ipAddress;
		}

		$ipValidator = [
			new FormatRules(),
			'valid_ip',
		];

		/**
		 * @deprecated self::$proxyIPs property will be removed in the future
		 */
		$proxyIPs = self::$proxyIPs ?? hkm_config('App')::$proxyIPs;
		if (! empty($proxyIPs) && ! is_array($proxyIPs))
		{
			$proxyIPs = explode(',', str_replace(' ', '', $proxyIPs));
		}

		self::$ipAddress = self::GET_SERVER('REMOTE_ADDR');

		if ($proxyIPs)
		{
			foreach (['HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP', 'HTTP_X_CLIENT_IP', 'HTTP_X_CLUSTER_CLIENT_IP'] as $header)
			{
				if (($spoof = self::GET_SERVER($header)) !== null)
				{
					// Some proxies typically list the whole chain of IP
					// addresses through which the client has reached us.
					// e.g. client_ip, proxy_ip1, proxy_ip2, etc.
					// sscanf($spoof, '%[^,]', $spoof);

					if (! $ipValidator($spoof))
					{
						$spoof = null;
					}
					else
					{
						break;
					}
				}
			}

			if ($spoof)
			{
				foreach ($proxyIPs as $proxyIP)
				{
					// Check if we have an IP address or a subnet
					if (strpos($proxyIP, '/') === false)
					{
						// An IP address (and not a subnet) is specified.
						// We can compare right away.
						if ($proxyIP === self::$ipAddress)
						{
							self::$ipAddress = $spoof;
							break;
						}

						continue;
					}

					// We have a subnet ... now the heavy lifting begins
					if (! isset($separator))
					{
						$separator = $ipValidator(self::$ipAddress, 'ipv6') ? ':' : '.';
					}

					// If the proxy entry doesn't match the IP protocol - skip it
					if (strpos($proxyIP, $separator) === false)
					{
						continue;
					}

					// Convert the REMOTE_ADDR IP address to binary, if needed
					if (! isset($ip, $sprintf))
					{
						if ($separator === ':')
						{
							// Make sure we're have the "full" IPv6 format
							$ip = explode(':', str_replace('::', str_repeat(':', 9 - substr_count(self::$ipAddress, ':')), self::$ipAddress));

							for ($j = 0; $j < 8; $j ++)
							{
								$ip[$j] = intval($ip[$j], 16);
							}

							$sprintf = '%016b%016b%016b%016b%016b%016b%016b%016b';
						}
						else
						{
							$ip      = explode('.', self::$ipAddress);
							$sprintf = '%08b%08b%08b%08b';
						}

						$ip = vsprintf($sprintf, $ip);
					}

					// Split the netmask length off the network address
					sscanf($proxyIP, '%[^/]/%d', $netaddr, $masklen);

					// Again, an IPv6 address is most likely in a compressed form
					if ($separator === ':')
					{
						$netaddr = explode(':', str_replace('::', str_repeat(':', 9 - substr_count($netaddr, ':')), $netaddr));
						for ($i = 0; $i < 8; $i++)
						{
							$netaddr[$i] = intval($netaddr[$i], 16);
						}
					}
					else
					{
						$netaddr = explode('.', $netaddr);
					}

					// Convert to binary and finally compare
					if (strncmp($ip, vsprintf($sprintf, $netaddr), $masklen) === 0)
					{
						self::$ipAddress = $spoof;
						break;
					}
				}
			}
		}

		if (! $ipValidator(self::$ipAddress))
		{
			return self::$ipAddress = '0.0.0.0';
		}

		return empty(self::$ipAddress) ? '' : self::$ipAddress;
    }
    public static function GET_SERVER($index = null, $filter = null, $flags = null)
	{
		return self::FETCH_GLOBAL('server', $index, $filter, $flags);
    }
    public static function GET_ENV($index = null, $filter = null, $flags = null)
	{
		return self::FETCH_GLOBAL('env', $index, $filter, $flags);
    }
    public static function SET_GLOBAL(string $method, $value)
	{
		self::$globals[$method] = $value;

		return self::$thiss;
    }
    public static function FETCH_GLOBAL(string $method, $index = null, ?int $filter = null, $flags = null)
	{
		$method = strtolower($method);

		if (! isset(self::$globals[$method]))
		{
			self::POPULATE_GLOBALS($method);
		}

		// Null filters cause null values to return.
		$filter = $filter ?? FILTER_DEFAULT;
		$flags  = is_array($flags) ? $flags : (is_numeric($flags) ? (int) $flags : 0);

		// Return all values when $index is null
		if (is_null($index))
		{
			$values = [];
			foreach (self::$globals[$method] as $key => $value)
			{
				$values[$key] = is_array($value)
					? self::FETCH_GLOBAL($method, $key, $filter, $flags)
					: filter_var($value, $filter, $flags);
			}

			return $values;
		}

		// allow fetching multiple keys at once
		if (is_array($index))
		{
			$output = [];

			foreach ($index as $key)
			{
				$output[$key] = self::FETCH_GLOBAL($method, $key, $filter, $flags);
			}

			return $output;
		}

		// Does the index contain array notation?
		if (($count = preg_match_all('/(?:^[^\[]+)|\[[^]]*\]/', $index, $matches)) > 1)
		{
			$value = self::$globals[$method];
			for ($i = 0; $i < $count; $i++)
			{
				$key = trim($matches[0][$i], '[]');

				if ($key === '') // Empty notation will return the value as array
				{
					break;
				}

				if (isset($value[$key]))
				{
					$value = $value[$key];
				}
				else
				{
					return null;
				}
			}
		}

		if (! isset($value))
		{
			$value = self::$globals[$method][$index] ?? null;
		}

		// @phpstan-ignore-next-line
		if (is_array($value)
			&& ($filter !== FILTER_DEFAULT
				|| ((is_numeric($flags) && $flags !== 0)
					|| is_array($flags) && count($flags) > 0
				)
			)
		)
		{
			// Iterate over array and append filter and flags
			array_walk_recursive($value, function (&$val) use ($filter, $flags) {
				$val = filter_var($val, $filter, $flags);
			});

			return $value;
		}

		// Cannot filter these types of data automatically...
		if (is_array($value) || is_object($value) || is_null($value))
		{
			return $value;
		}

		return filter_var($value, $filter, $flags);
    }
    protected static function POPULATE_GLOBALS(string $method)
	{
		if (! isset(self::$globals[$method]))
		{
			self::$globals[$method] = [];
		}

		// Don't populate ENV as it might contain
		// sensitive data that we don't want to get logged.
		switch($method)
		{
			case 'get':
				self::$globals['get'] = $_GET;
				break;
			case 'post':
				self::$globals['post'] = $_POST;
				break;
			case 'request':
				self::$globals['request'] = $_REQUEST;
				break;
			case 'cookie':
				self::$globals['cookie'] = $_COOKIE;
				break;
			case 'server':
				self::$globals['server'] = $_SERVER;
				break;
		}
	}

}
