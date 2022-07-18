<?php


namespace Hkm_code\Filters;

use Hkm_code\HTTP\Request;
use Hkm_code\HTTP\Response;
use Hkm_code\Security\Exceptions\SecurityException;
use Hkm_code\Vezirion\ServicesSystem;


class CSRF implements FilterInterface
{
	
	public static function BEFORE(Request $request, $arguments = null)
	{
		if ($request::IS_CLI())
		{
			return;
		}else{
			$security = ServicesSystem::SECURITY();

			try
			{
				$security::VERIFY($request);
			}
			catch (SecurityException $e)
			{
				if ($security::SHOULD_REDIRECT() && ! $request::IS_AJAX())
				{
					return hkm_redirect()::BACK()::WITH('error', $e->getMessage());
				}
	
				throw $e;
			}
		}

		
	}

	//--------------------------------------------------------------------
	
	public static function AFTER(Request $request, Response $response, $arguments = null)
	{
	}

	//--------------------------------------------------------------------
}
