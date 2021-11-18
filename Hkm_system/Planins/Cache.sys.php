<?php
use Hkm_code\Vezirion\Services;

if (!function_exists('hkm_cache')) {
	/**
	 * A convenience method that provides access to the Cache
	 * object. If no parameter is provided, will return the object,
	 * otherwise, will attempt to return the cached value.
	 *
	 * Examples:
	 *    hkm_cache()->save('foo', 'bar');
	 *    $foo = hkm_cache('bar');
	 *
	 * @param string|null $key
	 *
	 * @return CacheInterface|mixed
	 */
	function hkm_cache(string $key = null)
	{
		$cache = Services::CACHE();

		// No params - return cache object
		if (is_null($key)) {
			return $cache;
		}

		// Still here? Retrieve the value.
		return $cache::GET($key);
	}
}