<?php

/**
 * This file is part of the Hkm_code 4 framework.
 *
 * (c) Hkm_code Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hkm_code\Database\SQLSRV;

use Hkm_code\Database\BaseUtils;
use Hkm_code\Database\Exceptions\DatabaseException;

/**
 * Utils for SQLSRV
 */
class Utils extends BaseUtils
{
	/**
	 * List databases statement
	 *
	 * @var string
	 */
	protected $listDatabases = 'EXEC sp_helpdb'; // Can also be: EXEC sp_databases

	/**
	 * OPTIMIZE TABLE statement
	 *
	 * @var string
	 */
	protected $optimizeTable = 'ALTER INDEX all ON %s REORGANIZE';

	//--------------------------------------------------------------------

	/**
	 * Platform dependent version of the backup function.
	 *
	 * @param array|null $prefs
	 *
	 * @return mixed
	 */
	public function _backup(array $prefs = null)
	{
		throw new DatabaseException('Unsupported feature of the database platform you are using.');
	}
}
