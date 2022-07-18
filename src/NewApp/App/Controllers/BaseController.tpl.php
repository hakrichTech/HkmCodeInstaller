<@php

namespace App\Controllers;

use Hkm_code\Controller;
use Hkm_code\HTTP\CLIRequest;
use Hkm_code\HTTP\IncomingRequest;
use Hkm_code\HTTP\Request;
use Hkm_code\HTTP\Response;
use Psr\Log\LoggerInterface;

/** 
 * Class BaseController
 *
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 * Extend this class in any new controllers:
 *     class Home extends BaseController
 *
 * For security be sure to declare any new methods as protected or private.
 */

class BaseController extends Controller
{
	/**
	 * Instance of the main Request object.
	 *
	 * @var IncomingRequest|CLIRequest
	 */
	protected static $request;

	/**
	 * An array of helpers to be loaded automatically upon
	 * class instantiation. These helpers will be available
	 * to all other controllers that extend BaseController.
	 *
	 * @var array
	 */
	protected static $helpers = [];

	/**
	 * Constructor.
	 *
	 * @param Request  $request
	 * @param Response $response
	 * @param LoggerInterface   $logger
	 */
	public static function INIT_CONTROLLER(Request $request, Response $response, LoggerInterface $logger)
	{
		// Do Not Edit This Line
		parent::INIT_CONTROLLER($request, $response, $logger);

		//--------------------------------------------------------------------
		// Preload any models, libraries, etc, here.
		//--------------------------------------------------------------------
		// E.g.: $this->session = \Config\Services::session();
	}
}
