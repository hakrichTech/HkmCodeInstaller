<?php

/**
 * This file is part of the Hkm_code 4 framework.
 *
 * (c) Hkm_code Foundation <admin@Hkm_code.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hkm_code\Validation;

use Hkm_code\HTTP\Request;

/**
 * Expected behavior of a validator
 */
interface ValidationInterface
{
	/**
	 * Runs the validation process, returning true/false determining whether
	 * or not validation was successful.
	 *
	 * @param array  $data  The array of data to validate.
	 * @param string $group The pre-defined group of rules to apply.
	 *
	 * @return boolean
	 */
	public static function RUN(array $data = null, string $group = null): bool;

	//--------------------------------------------------------------------

	/**
	 * Check; runs the validation process, returning true or false
	 * determining whether or not validation was successful.
	 *
	 * @param mixed    $value  Value to validation.
	 * @param string   $rule   Rule.
	 * @param string[] $errors Errors.
	 *
	 * @return boolean True if valid, else false.
	 */
	public static function CHECK($value, string $rule, array $errors = []): bool;

	//--------------------------------------------------------------------

	/**
	 * Takes a Request object and grabs the input data to use from its
	 * array values.
	 *
	 * @param Request $request
	 *
	 * @return ValidationInterface
	 */
	public static function WITH_REQUEST(Request $request): ValidationInterface;

	//--------------------------------------------------------------------
	// Rules
	//--------------------------------------------------------------------

	/**
	 * Stores the rules that should be used to validate the items.
	 *
	 * @param array $rules
	 * @param array $messages
	 *
	 * @return ValidationInterface
	 */
	public static function SET_RULES(array $rules, array $messages = []): ValidationInterface;

	//--------------------------------------------------------------------

	/**
	 * Checks to see if the rule for key $field has been set or not.
	 *
	 * @param string $field
	 *
	 * @return boolean
	 */
	public static function HAS_RULE(string $field): bool;

	//--------------------------------------------------------------------
	//--------------------------------------------------------------------
	// Errors
	//--------------------------------------------------------------------

	/**
	 * Returns the error for a specified $field (or empty string if not set).
	 *
	 * @param string $field
	 *
	 * @return string
	 */
	public static function GET_ERROR(string $field): string;

	//--------------------------------------------------------------------

	/**
	 * Returns the array of errors that were encountered during
	 * a run() call. The array should be in the following format:
	 *
	 *    [
	 *        'field1' => 'error message',
	 *        'field2' => 'error message',
	 *    ]
	 *
	 * @return array<string,string>
	 */
	public static function GET_ERRORS(): array;

	//--------------------------------------------------------------------

	/**
	 * Sets the error for a specific field. Used by custom validation methods.
	 *
	 * @param string $alias
	 * @param string $error
	 *
	 * @return ValidationInterface
	 */
	public static function SET_ERROR(string $alias, string $error): ValidationInterface;

	//--------------------------------------------------------------------
	// Misc
	//--------------------------------------------------------------------

	/**
	 * Resets the class to a blank slate. Should be called whenever
	 * you need to process more than one array.
	 *
	 * @return ValidationInterface
	 */
	public static function RESET(): ValidationInterface;

	//--------------------------------------------------------------------
}
