<?php

/**
 * This file is part of the Hkm_code 4 framework.
 *
 * (c) Hkm_code Foundation <admin@Hkm_code.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hkm_code\Filters;

use Hkm_code\HTTP\Request;
use Hkm_code\HTTP\Response;
use Hkm_code\Vezirion\ServicesSystem;

/**
 * Debug toolbar filter
 */
class DebugToolbar implements FilterInterface
{
	
	public static function BEFORE(Request $request, $arguments = null)
	{
	}

	public static function AFTER(Request $request, Response $response, $arguments = null)
	{
		// ServicesSystem::TOOLBAR()::PREPARE($request, $response);
	}

	//--------------------------------------------------------------------
}
