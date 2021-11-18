<@php

namespace {namespace};

use Hkm_code\Filters\FilterInterface;
use Hkm_code\HTTP\Request;
use Hkm_code\HTTP\Response;

class {class} implements FilterInterface
{
	/**
	 * Do whatever processing this filter needs to do.
	 * By default it should not return anything during
	 * normal execution. However, when an abnormal state
	 * is found, it should return an instance of
	 * Hkm_code\HTTP\Response. If it does, script
	 * execution will end and that Response will be
	 * sent back to the client, allowing for error pages,
	 * redirects, etc.
	 *
	 * @param Request $request
	 * @param array|null       $arguments
	 *
	 * @return mixed
	 */
	public static function BEFORE(Request $request, $arguments = null)
	{
		//
	}

	/**
	 * Allows After filters to inspect and modify the response
	 * object as needed. This method does not allow any way
	 * to stop execution of other after filters, short of
	 * throwing an Exception or Error.
	 *
	 * @param Request  $request
	 * @param Response $response
	 * @param array|null        $arguments
	 *
	 * @return mixed
	 */
	public static function AFTER(Request $request, Response $response, $arguments = null)
	{
		//
	}
}
