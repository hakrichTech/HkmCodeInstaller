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

/**
 * Interface QueryInterface
 *
 * Represents a single statement that can be executed against the database.
 * Statements are platform-specific and can handle binding of binds.
 */
interface QueryInterface
{
	/**
	 * Sets the raw query string to use for this statement.
	 *
	 * @param string  $sql
	 * @param mixed   $binds
	 * @param boolean $setEscape
	 *
	 * @return mixed
	 */
	public static function SET_QUERY(string $sql, $binds = null, bool $setEscape = true);

	//--------------------------------------------------------------------

	/**
	 * Returns the final, processed query string after binding, etal
	 * has been performed.
	 *
	 * @return mixed
	 */
	public static function GET_QUERY();

	//--------------------------------------------------------------------

	/**
	 * Records the execution time of the statement using microtime(true)
	 * for it's start and end values. If no end value is present, will
	 * use the current time to determine total duration.
	 *
	 * @param float $start
	 * @param float $end
	 *
	 * @return mixed
	 */
	public static function SET_DURATION(float $start, float $end = null);

	//--------------------------------------------------------------------

	/**
	 * Returns the duration of this query during execution, or null if
	 * the query has not been executed yet.
	 *
	 * @param integer $decimals The accuracy of the returned time.
	 *
	 * @return string
	 */
	public static function GET_DURATION(int $decimals = 6): string;

	//--------------------------------------------------------------------

	/**
	 * Stores the error description that happened for this query.
	 *
	 * @param integer $code
	 * @param string  $error
	 */
	public static function SET_ERROR(int $code, string $error);

	//--------------------------------------------------------------------

	/**
	 * Reports whether this statement created an error not.
	 *
	 * @return boolean
	 */
	public static function HAS_ERROR(): bool;

	//--------------------------------------------------------------------

	/**
	 * Returns the error code created while executing this statement.
	 *
	 * @return integer
	 */
	public static function GET_ERROR_CODE(): int;

	//--------------------------------------------------------------------

	/**
	 * Returns the error message created while executing this statement.
	 *
	 * @return string
	 */
	public static function GET_ERROR_MESSAGE(): string;

	//--------------------------------------------------------------------

	/**
	 * Determines if the statement is a write-type query or not.
	 *
	 * @return boolean
	 */
	public static function IS_WRITE_TYPE(): bool;

	//--------------------------------------------------------------------

	/**
	 * Swaps out one table prefix for a new one.
	 *
	 * @param string $orig
	 * @param string $swap
	 *
	 * @return mixed
	 */
	public static function SWAP_PREFIX(string $orig, string $swap);

	//--------------------------------------------------------------------
}
