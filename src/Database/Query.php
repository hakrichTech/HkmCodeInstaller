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

use phpDocumentor\Reflection\Types\This;

/**
 * Query builder
 */
class Query implements QueryInterface
{
	/**
	 * The query string, as provided by the user.
	 *
	 * @var string
	 */
	protected static $originalQueryString;

	/**
	 * The final query string after binding, etc.
	 *
	 * @var string
	 */
	protected static $finalQueryString = "";

	/**
	 * The binds and their values used for binding.
	 *
	 * @var array
	 */
	protected static $binds = [];

	/**
	 * Bind marker
	 *
	 * Character used to identify values in a prepared statement.
	 *
	 * @var string
	 */
	protected static $bindMarker = '?';

	/**
	 * The start time in seconds with microseconds
	 * for when this query was executed.
	 *
	 * @var string|float
	 */
	protected static $startTime;

	/**
	 * The end time in seconds with microseconds
	 * for when this query was executed.
	 *
	 * @var float
	 */
	protected static $endTime;

	/**
	 * The error code, if any.
	 *
	 * @var integer
	 */
	protected static $errorCode;

	/**
	 * The error message, if any.
	 *
	 * @var string
	 */
	protected static $errorString;

	/**
	 * Pointer to database connection.
	 * Mainly for escaping features.
	 *
	 * @var ConnectionInterface
	 */
	public static $db;
	public static $thiss;

	/**
	 * BaseQuery constructor.
	 *
	 * @param ConnectionInterface $db
	 */
	public  function __construct(ConnectionInterface $db)
	{
		
		self::$db = $db;
		self::$thiss = $this;
	}

	/**
	 * Sets the raw query string to use for this statement.
	 *
	 * @param string  $sql
	 * @param mixed   $binds
	 * @param boolean $setEscape
	 *
	 * @return $this
	 */
	public static function SET_QUERY(string $sql, $binds = null, bool $setEscape = true)
	{
		self::$originalQueryString = $sql;

		if (! is_null($binds))
		{

			if (! is_array($binds))
			{
				$binds = [$binds];
			}

			if ($setEscape)
			{
				array_walk($binds, function (&$item) {
					$item = [
						$item,
						true,
					];
				});
			}
			self::$binds = $binds;
		}

		return self::$thiss;
	}

	/**
	 * Will store the variables to bind into the query later.
	 *
	 * @param array   $binds
	 * @param boolean $setEscape
	 *
	 * @return $this
	 */
	public static function SET_BINDS(array $binds, bool $setEscape = true)
	{
		if ($setEscape)
		{
			array_walk($binds, function (&$item) {
				$item = [
					$item,
					true,
				];
			});
		}

		self::$binds = $binds;

		return self::$thiss;
	}

	/**
	 * Returns the final, processed query string after binding, etal
	 * has been performed.
	 *
	 * @return string
	 */
	public static function GET_QUERY(): string
	{
		if (empty(self::$finalQueryString))
		{
			self::$finalQueryString = self::$originalQueryString;
		}

		self::COMPILE_BINDS();
		return self::$finalQueryString;
	}

	/**
	 * Records the execution time of the statement using microtime(true)
	 * for it's start and end values. If no end value is present, will
	 * use the current time to determine total duration.
	 *
	 * @param float $start
	 * @param float $end
	 *
	 * @return $this
	 */
	public static function SET_DURATION(float $start, float $end = null)
	{
		self::$startTime = $start;

		if (is_null($end))
		{
			$end = microtime(true);
		}

		self::$endTime = $end;

		return self::$thiss;
	}

	/**
	 * Returns the start time in seconds with microseconds.
	 *
	 * @param boolean $returnRaw
	 * @param integer $decimals
	 *
	 * @return string|float
	 */
	public static function GET_START_TIME(bool $returnRaw = false, int $decimals = 6)
	{
		if ($returnRaw)
		{
			return self::$startTime;
		}

		return number_format(self::$startTime, $decimals);
	}

	/**
	 * Returns the duration of this query during execution, or null if
	 * the query has not been executed yet.
	 *
	 * @param integer $decimals The accuracy of the returned time.
	 *
	 * @return string
	 */
	public static function GET_DURATION(int $decimals = 6): string
	{
		return number_format((self::$endTime - self::$startTime), $decimals);
	}

	/**
	 * Stores the error description that happened for this query.
	 *
	 * @param integer $code
	 * @param string  $error
	 *
	 * @return $this
	 */
	public static function SET_ERROR(int $code, string $error)
	{
		self::$errorCode   = $code;
		self::$errorString = $error;

		return self::$thiss;
	}

	/**
	 * Reports whether this statement created an error not.
	 *
	 * @return boolean
	 */
	public static function HAS_ERROR(): bool
	{
		return ! empty(self::$errorString);
	}

	/**
	 * Returns the error code created while executing this statement.
	 *
	 * @return integer
	 */
	public static function GET_ERROR_CODE(): int
	{
		return self::$errorCode;
	}

	/**
	 * Returns the error message created while executing this statement.
	 *
	 * @return string
	 */
	public static function GET_ERROR_MESSAGE(): string
	{
		return self::$errorString;
	}

	/**
	 * Determines if the statement is a write-type query or not.
	 *
	 * @return boolean
	 */
	public static function IS_WRITE_TYPE(): bool
	{
		return self::$db::IS_WRITE_TYPE(self::$originalQueryString);
	}

	/**
	 * Swaps out one table prefix for a new one.
	 *
	 * @param string $orig
	 * @param string $swap
	 *
	 * @return $this
	 */
	public static function SWAP_PREFIX(string $orig, string $swap)
	{
		$sql = empty(self::$finalQueryString) ? self::$originalQueryString : self::$finalQueryString;

		self::$finalQueryString = preg_replace('/(\W)' . $orig . '(\S+?)/', '\\1' . $swap . '\\2', $sql);

		return self::$thiss;
	}

	/**
	 * Returns the original SQL that was passed into the system.
	 *
	 * @return string
	 */
	public static function GET_ORIGINAL_QUERY(): string
	{
		return self::$originalQueryString;
	}

	/**
	 * Escapes and inserts any binds into the finalQueryString object.
	 *
	 * @return void
	 *
	 * @see https://regex101.com/r/EUEhay/4
	 */
	protected static function COMPILE_BINDS()
	{

		

		$sql = self::$originalQueryString;
		


		$hasNamedBinds = preg_match('/:((?!=).+):/', $sql) === 1;

		if (empty(self::$binds)
			|| empty(self::$bindMarker)
			|| (! $hasNamedBinds && strpos($sql, self::$bindMarker) === false)
		)
		{
		     self::$finalQueryString = $sql;
			
			return;
		}

		if (! is_array(self::$binds))
		{
			$binds     = [self::$binds];
			$bindCount = 1;
		}
		else
		{
			$binds     = self::$binds;
			$bindCount = count($binds);
		}

		// Reverse the binds so that duplicate named binds
		// will be processed prior to the original binds.
		if (! is_numeric(key(array_slice($binds, 0, 1))))
		{
			$binds = array_reverse($binds);
		}

		// We'll need marker length later
		$ml = strlen(self::$bindMarker);


		$sql = $hasNamedBinds ? self::MATCH_NAMED_BINDS($sql, $binds) : self::MATCH_SIMPLE_BINDS($sql, $binds, $bindCount, $ml);
		

		self::$finalQueryString = $sql;
	}

	/**
	 * Match bindings
	 *
	 * @param  string $sql
	 * @param  array  $binds
	 * @return string
	 */
	protected static function MATCH_NAMED_BINDS(string $sql, array $binds): string
	{
		$replacers = [];

		foreach ($binds as $placeholder => $value)
		{
			// $value[1] contains the boolean whether should be escaped or not
			$escapedValue = $value[1] ? self::$db::ESCAPE($value[0]) : $value[0];

			// In order to correctly handle backlashes in saved strings
			// we will need to preg_quote, so remove the wrapping escape characters
			// otherwise it will get escaped.
			if (is_array($value[0]))
			{
				$escapedValue = '(' . implode(',', $escapedValue) . ')';
			}

			$replacers[":{$placeholder}:"] = $escapedValue;
		}

		return strtr($sql, $replacers);
	}

	/**
	 * Match bindings
	 *
	 * @param  string  $sql
	 * @param  array   $binds
	 * @param  integer $bindCount
	 * @param  integer $ml
	 * @return string
	 */
	protected static function MATCH_SIMPLE_BINDS(string $sql, array $binds, int $bindCount, int $ml): string
	{
		// Make sure not to replace a chunk inside a string that happens to match the bind marker
		if ($c = preg_match_all("/'[^']*'/", $sql, $matches))
		{
			$c = preg_match_all('/' . preg_quote(self::$bindMarker, '/') . '/i', str_replace($matches[0], str_replace(self::$bindMarker, str_repeat(' ', $ml), $matches[0]), $sql, $c), $matches, PREG_OFFSET_CAPTURE);

			// Bind values' count must match the count of markers in the query
			if ($bindCount !== $c)
			{
				return $sql;
			}
		}
		// Number of binds must match bindMarkers in the string.
		elseif (($c = preg_match_all('/' . preg_quote(self::$bindMarker, '/') . '/i', $sql, $matches, PREG_OFFSET_CAPTURE)) !== $bindCount)
		{
			return $sql;
		}

		do
		{
			$c--;
			$escapedValue = $binds[$c][1] ? self::$db::ESCAPE($binds[$c][0]) : $binds[$c][0];
			if (is_array($escapedValue))
			{
				$escapedValue = '(' . implode(',', $escapedValue) . ')';
			}
			$sql = substr_replace($sql, $escapedValue, $matches[0][$c][1], $ml);
		}
		while ($c !== 0);

		return $sql;
	}

	/**
	 * Returns string to display in debug toolbar
	 *
	 * @return string
	 */
	public static function DEBUG_TOOLBAR_DISPLAY(): string
	{
		// Key words we want bolded
		static $highlight = [
			'SELECT',
			'DISTINCT',
			'FROM',
			'WHERE',
			'AND',
			'LEFT&nbsp;JOIN',
			'RIGHT&nbsp;JOIN',
			'JOIN',
			'ORDER&nbsp;BY',
			'GROUP&nbsp;BY',
			'LIMIT',
			'INSERT',
			'INTO',
			'VALUES',
			'UPDATE',
			'OR&nbsp;',
			'HAVING',
			'OFFSET',
			'NOT&nbsp;IN',
			'IN',
			'LIKE',
			'NOT&nbsp;LIKE',
			'COUNT',
			'MAX',
			'MIN',
			'ON',
			'AS',
			'AVG',
			'SUM',
			'(',
			')',
		];

		if (empty(self::$finalQueryString))
		{
			self::COMPILE_BINDS(); // @codeCoverageIgnore
		}

		$sql = self::$finalQueryString;

		foreach ($highlight as $term)
		{
			$sql = str_replace($term, '<strong>' . $term . '</strong>', $sql);
		}

		return $sql;
	}

	public static function RESET()
	{
		self::$finalQueryString="";
		self::$originalQueryString="";
		self::$binds=[];
		return self::$thiss;
	}

	/**
	 * Return text representation of the query
	 *
	 * @return string
	 */
	public  function __toString(): string
	{
		return self::GET_QUERY();
	}
}
