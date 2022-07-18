<?php
use Hkm_code\Vezirion\ServicesSystem;
use Hkm_code\Cache\CacheInterface;

if (!function_exists('hkm_cache')) {
	/**
	 * A convenience method that provides access to the Cache
	 * object. If no parameter is provided, will return the object,
	 * otherwise, will attempt to return the cached value.
	 *
	 * Examples:
	 *    hkm_cache()::SAVE('foo', 'bar');
	 *    $foo = hkm_cache('bar');
	 *
	 * @param string|null $key
	 *
	 * @return CacheInterface|mixed
	 */
	function hkm_cache(string $key = null)
	{
		$cache = ServicesSystem::CACHE();

		// No params - return cache object
		if (is_null($key)) {
			return $cache;
		}
		// Still here? Retrieve the value.
		return $cache::GET($key);
	}
}


function hkm_cache_add( $key, $data, $group = '',$expire = 0 ) {

	return hkm_cache()::SAVE( $group."_".$key, $data, (int) $expire);
	
}

function hkm_cache_close() {
	return true;
}

function hkm_cache_decr( $key, $group = '' ,$offset = 1 ) {
	return hkm_cache()::DECREMENT($group."_".$key, $offset );
}

function hkm_cache_delete( $key, $group = '') {
	return hkm_cache()::DELETE( $group."_".$key);
}

function hkm_cache_flush() {

	return hkm_cache()::CLEAN();
}

function hkm_cache_get( $key, $group = '' ) {
	
	return hkm_cache($group."_".$key);
}

function hkm_cache_get_multiple( $keys, $group = '') {
	$values = [];

	foreach ($keys as $key ) {
		$values[$key] = hkm_cache($group."_".$key);
	}

	return $values;
}

function hkm_cache_incr( $key, $group = '',$offset = 1 ) {
	return hkm_cache()::INCREMENT( $group."_".$key, $offset );
}

function hkm_cache_init() {
	$GLOBALS['hkm_object_cache'] = ServicesSystem::CACHE();
}

function hkm_cache_set( $key, $data, $group = '',$expire = 0 ) {
	return hkm_cache()::SAVE( $group."_".$key, $data, (int) $expire );

}

function hkm_cache_replace( $key, $data,$group = '', $expire = 0 ) {
	return hkm_cache()::SAVE( $group."_".$key, $data, (int) $expire );
}

function wp_cache_reset() {
	return hkm_cache()::CLEAN();
}

function _get_non_cached_ids( $object_ids, $cache_key ) {
	$non_cached_ids = array();
	$cache_values   = hkm_cache_get_multiple( $object_ids, $cache_key );

	foreach ( $cache_values as $id => $value ) {
		if ( ! $value ) {
			$non_cached_ids[] = (int) $id;
		}
	}

	return $non_cached_ids;
}