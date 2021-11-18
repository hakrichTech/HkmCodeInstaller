<?php

use Hkm_code\Database\ConnectionInterface;
use Hkm_code\Vezirion\Factories;

if (!function_exists('hkm_model')) {
	/**
	 * More simple way of getting model instances from Factories
	 *
	 * @param string                   $name
	 * @param boolean                  $getShared
	 * @param ConnectionInterface|null $conn
	 *
	 * @return mixed
	 */
	function hkm_model(string $name, bool $getShared = true, ConnectionInterface &$conn = null)
	{
		return Factories::MODELS($name, ['getShared' => $getShared], $conn);
	}
}