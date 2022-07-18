<?php

namespace Hkm_code;

use Hkm_code\HTTP\Exceptions\HTTPException;
use Hkm_code\HTTP\Request;
use Hkm_code\HTTP\Response;
use Hkm_code\Validation\Exceptions\ValidationException;
use Hkm_code\Validation\Validation;
use Hkm_code\Vezirion\ServicesSystem;
use Psr\Log\LoggerInterface;

/**
 * Class Controller
 */
class Controller
{
	/**
	 * Helpers that will be automatically loaded on class instantiation.
	 *
	 * @var array
	 */
	protected static $helpers = [];

	/**
	 * Instance of the main Request object.
	 *
	 * @var Request
	 */
	protected static $request;

	/**
	 * Instance of the main response object.
	 *
	 * @var Response
	 */
	protected static $response;

	/**
	 * Instance of logger to use.
	 *
	 * @var LoggerInterface
	 */
	protected static $logger;

	/**
	 * Should enforce HTTPS access for all methods in this controller.
	 *
	 * @var integer Number of seconds to set HSTS header
	 */
	protected static $forceHTTPS = 0;

	/**
	 * Once validation has been run, will hold the Validation instance.
	 *
	 * @var Validation
	 */
	protected static $validator;

	protected static $data = [];

	//--------------------------------------------------------------------

	/**
	 * Constructor.
	 *
	 * @param Request  $request
	 * @param Response $response
	 * @param LoggerInterface   $logger
	 *
	 * @throws HTTPException
	 */

	 public static $thiss;

	 public function __construct()
	 {
		 self::$thiss = $this;
	 }
	public static function INIT_CONTROLLER(Request $request, Response $response, LoggerInterface $logger)
	{
		self::$thiss::$request  = $request;
		self::$thiss::$response = $response;
		self::$thiss::$logger   = $logger;

		self::$thiss::$data = [
			'url'=>hkm_config('App',false)::$baseURL
		];
		

		if (self::$forceHTTPS > 0)
		{
			self::FORCE_HTTPS(self::$forceHTTPS);
		}

	}

	//--------------------------------------------------------------------

	/**
	 * A convenience method to use when you need to ensure that a single
	 * method is reached only via HTTPS. If it isn't, then a redirect
	 * will happen back to this method and HSTS header will be sent
	 * to have modern browsers transform requests automatically.
	 *
	 * @param integer $duration The number of seconds this link should be
	 *                          considered secure for. Only with HSTS header.
	 *                          Default value is 1 year.
	 *
	 * @throws HTTPException
	 */
	protected static function FORCE_HTTPS(int $duration = 31536000)
	{
		// hkm_force_https($duration, self::$request, self::$response);
	}

	//--------------------------------------------------------------------

	/**
	 * Provides a simple way to tie into the main Hkm_code class and
	 * tell it how long to cache the current page for.
	 *
	 * @param integer $time
	 */
	protected static function CACHE_PAGE(int $time)
	{
		Application::CACHE($time);
	}

	
	//--------------------------------------------------------------------

	/**
	 * A shortcut to performing validation on input data. If validation
	 * is not successful, a $errors property will be set on this class.
	 *
	 * @param array|string $rules
	 * @param array        $messages An array of custom error messages
	 *
	 * @return boolean
	 */
	protected static function VALIDATE($rules, array $messages = []): bool
	{
		self::$validator = ServicesSystem::VALIDATION();

		// If you replace the $rules array with the name of the group
		if (is_string($rules))
		{
			$validation = hkm_config('Validation');

			// If the rule wasn't found in the \Validation, we
			// should throw an exception so the developer can find it.
			if (! isset($validation::$rules))
			{
				throw ValidationException::FOR_RULE_NOT_FOUND($rules);
			}

			// If no error message is defined, use the error message in the Validation file
			if (! $messages)
			{
				$errorName = $rules . '_errors';
				$messages  = $validation::$errorName ?? [];
			}

			$rules = $validation::$rules;
		}

		return self::$validator::WITH_REQUEST(self::$request)::SET_RULES($rules, $messages)::RUN();
	}
}
 