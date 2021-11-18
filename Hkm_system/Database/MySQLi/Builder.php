<?php

/**
 * This file is part of the Hkm_code 4 framework.
 *
 * (c) Hkm_code Foundation <admin@Hkm_code.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hkm_code\Database\MySQLi;

use Hkm_code\Database\BaseBuilder;

/**
 * Builder for MySQLi
 */
class Builder extends BaseBuilder
{
	/**
	 * Identifier escape character
	 *
	 * @var string
	 */
	protected $escapeChar = '`';

	/**
	 * Specifies which sql statements
	 * support the ignore option.
	 *
	 * @var array
	 */
	protected $supportedIgnoreStatements = [
		'update' => 'IGNORE',
		'insert' => 'IGNORE',
		'delete' => 'IGNORE',
	];

	/**
	 * FROM tables
	 *
	 * Groups tables in FROM clauses if needed, so there is no confusion
	 * about operator precedence.
	 *
	 * Note: This is only used (and overridden) by MySQL.
	 *
	 * @return string
	 */
	protected function _fromTables(): string
	{
		if (! empty($this->QBJoin) && count($this->QBFrom) > 1)
		{
			return '(' . implode(', ', $this->QBFrom) . ')';
		}

		return implode(', ', $this->QBFrom);
	}
}
