<?php

/**
 * This file is part of the Hkm_code 4 framework.
 *
 * (c) Hkm_code Foundation <admin@hakrichteam.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hkm_code\Debug;

use Hkm_code\API\ResponseTrait;
use Hkm_code\Exceptions\PageNotFoundException;
use Hkm_code\HTTP\IncomingRequest;
use Hkm_code\HTTP\Response;
use function error_reporting;
use ErrorException;
use Throwable;

/**
 * Exceptions manager
 */
class Exceptions
{
	use ResponseTrait;

	/**
	 * Nesting level of the output buffering mechanism
	 *
	 * @var integer
	 */
	public static $ob_level;

	/**
	 * The path to the directory containing the
	 * cli and html error view directories.
	 *
	 * @var string
	 */
	protected static $viewPath;

	/**
	 * Config for debug exceptions.
	 *
	 * @var ExceptionsConfig
	 */
	protected static $config;

	/**
	 * The incoming request.
	 *
	 * @var IncomingRequest
	 */
	protected static $request;

	/**
	 * The outgoing response.
	 *
	 * @var Response
	 */
	protected static $response;
	protected static $thiss;

	//--------------------------------------------------------------------

	/**
	 * Constructor.
	 *
	 * @param  $config
	 * @param IncomingRequest  $request
	 * @param Response         $response
	 */
	public  function __construct( $config, IncomingRequest $request, Response $response)
	{
		self::$ob_level = ob_get_level();

		self::$viewPath = rtrim($config::$errorViewPath, '\\/ ') . DIRECTORY_SEPARATOR;

		self::$config = $config;

		self::$request  = $request;
		self::$response = $response;
		self::$thiss = $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Responsible for registering the error, exception and shutdown
	 * handling of our application.
	 */
	public static function INITIALIZE()
	{
		//Set the Exception Handler
		set_exception_handler([self::$thiss, 'EXCEPTION_HANDLER']);

		// Set the Error Handler
		set_error_handler([self::$thiss, 'errorHandler']);

		// Set the handler for shutdown to catch Parse errors
		// Do we need this in PHP7?
		register_shutdown_function([self::$thiss, 'shutdownHandler']);
	}

	//--------------------------------------------------------------------

	/**
	 * Catches any uncaught errors and exceptions, including most Fatal errors
	 * (Yay PHP7!). Will log the error, display it if display_errors is on,
	 * and fire an event that allows custom actions to be taken at this point.
	 *
	 * @param Throwable $exception
	 *
	 * @codeCoverageIgnore
	 */
	public static function EXCEPTION_HANDLER(Throwable $exception)
	{
		[
			$statusCode,
			$exitCode,
		] = self::DETERMINE_CODES($exception);

		// Log it
		if (self::$config::$log === true && ! in_array($statusCode, self::$config::$ignoreCodes, true))
		{
			log_message('critical', $exception->getMessage() . "\n{trace}", [
							'trace' => $exception->getTraceAsString(),
						]);
		}

		if (! hkm_is_cli())
		{
			self::$response::SET_STATUS_CODE($statusCode);
			$PRO = self::$request::GET_PROTOCOL_VERSION();
			$STATUS = self::$response::GET_STATUS_CODE();
			$REASON = self::$response::GET_REASON();
			$header = "HTTP/{$PRO} {$STATUS} {$REASON}";
			header($header, true, $statusCode);

			if (strpos(self::$request::GET_HEADER_LINE('accept'), 'text/html') === false)
			{
				self::$respond(ENVIRONMENT === 'development' ? self::COLLECT_VARS($exception, $statusCode) : '', $statusCode)::SEND();

				exit($exitCode);
			}
		}

		self::RENDER($exception, $statusCode);

		exit($exitCode);
	}

	//--------------------------------------------------------------------

	/**
	 * Even in PHP7, some errors make it through to the errorHandler, so
	 * convert these to Exceptions and let the exception handler log it and
	 * display it.
	 *
	 * This seems to be primarily when a user triggers it with trigger_error().
	 *
	 * @param integer      $severity
	 * @param string       $message
	 * @param string|null  $file
	 * @param integer|null $line
	 *
	 * @throws ErrorException
	 */
	public static function ERROR_HANDLER(int $severity, string $message, string $file = null, int $line = null)
	{
		if (! (error_reporting() & $severity))
		{
			return;
		}

		// Convert it to an exception and pass it along.
		throw new ErrorException($message, 0, $severity, $file, $line);
	}

	//--------------------------------------------------------------------

	/**
	 * Checks to see if any errors have happened during shutdown that
	 * need to be caught and handle them.
	 */
	public static function SHUTDOWN_HANDLER()
	{
		$error = error_get_last();

		// If we've got an error that hasn't been displayed, then convert
		// it to an Exception and use the Exception handler to display it
		// to the user.
		// Fatal Error?
		if (! is_null($error) && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE], true))
		{
			self::EXCEPTION_HANDLER(new ErrorException($error['message'], $error['type'], 0, $error['file'], $error['line']));
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Determines the view to display based on the exception thrown,
	 * whether an HTTP or CLI request, etc.
	 *
	 * @param Throwable $exception
	 * @param string    $templatePath
	 *
	 * @return string       The path and filename of the view file to use
	 */
	protected static function DETERMINE_VIEW(Throwable $exception, string $templatePath): string
	{
		// Production environments should have a custom exception file.
		$view         = 'production.php';
		$templatePath = rtrim($templatePath, '\\/ ') . DIRECTORY_SEPARATOR;

		if (str_ireplace(['off', 'none', 'no', 'false', 'null'], '', ini_get('display_errors')))
		{
			$view = 'error_exception.php';
		}

		// 404 Errors
		if ($exception instanceof PageNotFoundException)
		{
			return 'error_404.php';
		}

		// Allow for custom views based upon the status code
		if (is_file($templatePath . 'error_' . $exception->getCode() . '.php'))
		{
			return 'error_' . $exception->getCode() . '.php';
		}

		return $view;
	}

	//--------------------------------------------------------------------

	/**
	 * Given an exception and status code will display the error to the client.
	 *
	 * @param Throwable $exception
	 * @param integer   $statusCode
	 */
	protected static function RENDER(Throwable $exception, int $statusCode)
	{
		// Determine possible directories of error views
		$path    = self::$viewPath;
		$altPath = rtrim((config("Path"))::$viewDirectory, '\\/ ') . DIRECTORY_SEPARATOR . 'errors' . DIRECTORY_SEPARATOR;

		$path    .= (is_cli() ? 'cli' : 'html') . DIRECTORY_SEPARATOR;
		$altPath .= (is_cli() ? 'cli' : 'html') . DIRECTORY_SEPARATOR;

		// Determine the views
		$view    = self::DETERMINE_VIEW($exception, $path);
		$altView = self::DETERMINE_VIEW($exception, $altPath);

		// Check if the view exists
		if (is_file($path . $view))
		{
			$viewFile = $path . $view;
		}
		elseif (is_file($altPath . $altView))
		{
			$viewFile = $altPath . $altView;
		}

		// Prepare the vars
		$vars = self::COLLECT_VARS($exception, $statusCode);
		extract($vars);

		// RENDER it
		if (ob_get_level() > self::$ob_level + 1)
		{
			ob_end_clean();
		}

		ob_start();
		include $viewFile; // @phpstan-ignore-line
		$buffer = ob_get_contents();
		ob_end_clean();
		echo $buffer;
	}

	//--------------------------------------------------------------------

	/**
	 * Gathers the variables that will be made available to the view.
	 *
	 * @param Throwable $exception
	 * @param integer   $statusCode
	 *
	 * @return array
	 */
	protected static function COLLECT_VARS(Throwable $exception, int $statusCode): array
	{
		$trace = $exception->getTrace();
		if (! empty(self::$config::$sensitiveDataInTrace))
		{
			self::MASK_SENSITIVE_DATA($trace, self::$config::$sensitiveDataInTrace);
		}

		return [
			'title'   => get_class($exception),
			'type'    => get_class($exception),
			'code'    => $statusCode,
			'message' => $exception->getMessage() ?? '(null)',
			'file'    => $exception->getFile(),
			'line'    => $exception->getLine(),
			'trace'   => $trace,
		];
	}

	/**
	 * Mask sensitive data in the trace.
	 *
	 * @param array|object $trace
	 * @param array        $keysToMask
	 * @param string       $path
	 */
	protected static function MASK_SENSITIVE_DATA(&$trace, array $keysToMask, string $path = '') 
	{
		foreach ($keysToMask as $keyToMask) 
		{
			$explode = explode('/', $keyToMask);
			$index   = end($explode);

			if (strpos(strrev($path . '/' . $index), strrev($keyToMask)) === 0)
			{
				if (is_array($trace) && array_key_exists($index, $trace)) 
				{
					$trace[$index] = '******************';
				} 
				elseif (is_object($trace) && property_exists($trace, $index) && isset($trace::$$index)) 
				{
					$trace::$$index = '******************';
				}
			}
		}

		if (! is_iterable($trace) && is_object($trace)) 
		{
			$trace = get_object_vars($trace);
		}

		if (is_iterable($trace)) 
		{
			foreach ($trace as $pathKey => $subarray) 
			{
				self::MASK_SENSITIVE_DATA($subarray, $keysToMask, $path . '/' . $pathKey);
			}
		}
	}

	/**
	 * Determines the HTTP status code and the exit status code for this request.
	 *
	 * @param Throwable $exception
	 *
	 * @return array
	 */
	protected static function DETERMINE_CODES(Throwable $exception): array
	{
		$statusCode = abs($exception::getCode());

		if ($statusCode < 100 || $statusCode > 599)
		{
			$exitStatus = $statusCode + EXIT__AUTO_MIN; // 9 is EXIT__AUTO_MIN
			if ($exitStatus > EXIT__AUTO_MAX) // 125 is EXIT__AUTO_MAX
			{
				$exitStatus = EXIT_ERROR; // EXIT_ERROR
			}
			$statusCode = 500;
		}
		else
		{
			$exitStatus = 1; // EXIT_ERROR
		}

		return [
			$statusCode ?: 500,
			$exitStatus,
		];
	}

	//--------------------------------------------------------------------
	//--------------------------------------------------------------------
	// Display Methods
	//--------------------------------------------------------------------

	/**
	 * Clean Path
	 *
	 * This makes nicer looking paths for the error output.
	 *
	 * @param string $file
	 *
	 * @return string
	 */
	public static function CLEAN_PATH(string $file): string
	{
		switch (true)
		{
			case strpos($file, APPPATH) === 0:
				$file = 'APPPATH' . DIRECTORY_SEPARATOR . substr($file, strlen(APPPATH));
				break;
			case strpos($file, SYSTEMPATH) === 0:
				$file = 'SYSTEMPATH' . DIRECTORY_SEPARATOR . substr($file, strlen(SYSTEMPATH));
				break;
			case strpos($file, FCPATH) === 0:
				$file = 'FCPATH' . DIRECTORY_SEPARATOR . substr($file, strlen(FCPATH));
				break;
		}

		return $file;
	}

	//--------------------------------------------------------------------

	/**
	 * Describes memory usage in real-world units. Intended for use
	 * with memory_get_usage, etc.
	 *
	 * @param integer $bytes
	 *
	 * @return string
	 */
	public static  function DESCRIBE_MEMORY(int $bytes): string
	{
		if ($bytes < 1024)
		{
			return $bytes . 'B';
		}
		if ($bytes < 1048576)
		{
			return round($bytes / 1024, 2) . 'KB';
		}

		return round($bytes / 1048576, 2) . 'MB';
	}

	//--------------------------------------------------------------------

	/**
	 * Creates a syntax-highlighted version of a PHP file.
	 *
	 * @param string  $file
	 * @param integer $lineNumber
	 * @param integer $lines
	 *
	 * @return boolean|string
	 */
	public static function HIGHLIGHT_FILE(string $file, int $lineNumber, int $lines = 15)
	{
		if (empty($file) || ! is_readable($file))
		{
			return false;
		}

		// Set our highlight colors:
		if (function_exists('ini_set'))
		{
			ini_set('highlight.comment', '#767a7e; font-style: italic');
			ini_set('highlight.default', '#c7c7c7');
			ini_set('highlight.html', '#06B');
			ini_set('highlight.keyword', '#f1ce61;');
			ini_set('highlight.string', '#869d6a');
		}

		try
		{
			$source = file_get_contents($file);
		}
		catch (Throwable $e)
		{
			return false;
		}

		$source = str_replace(["\r\n", "\r"], "\n", $source);
		$source = explode("\n", highlight_string($source, true));
		$source = str_replace('<br />', "\n", $source[1]);

		$source = explode("\n", str_replace("\r\n", "\n", $source));

		// Get just the part to show
		$start = $lineNumber - (int) round($lines / 2);
		$start = $start < 0 ? 0 : $start;

		// Get just the lines we need to display, while keeping line numbers...
		$source = array_splice($source, $start, $lines, true); // @phpstan-ignore-line

		// Used to format the line number in the source
		$format = '% ' . strlen(sprintf('%s', $start + $lines)) . 'd';

		$out = '';
		// Because the highlighting may have an uneven number
		// of open and close span tags on one line, we need
		// to ensure we can close them all to get the lines
		// showing correctly.
		$spans = 1;

		foreach ($source as $n => $row)
		{
			$spans += substr_count($row, '<span') - substr_count($row, '</span');

			$row = str_replace(["\r", "\n"], ['', ''], $row);

			if (($n + $start + 1) === $lineNumber)
			{
				preg_match_all('#<[^>]+>#', $row, $tags);
				$out .= sprintf("<span class='line highlight'><span class='number'>{$format}</span> %s\n</span>%s", $n + $start + 1, strip_tags($row), implode('', $tags[0])
				);
			}
			else
			{
				$out .= sprintf('<span class="line"><span class="number">' . $format . '</span> %s', $n + $start + 1, $row) . "\n";
			}
		}

		if ($spans > 0)
		{
			$out .= str_repeat('</span>', $spans);
		}

		return '<pre><code>' . $out . '</code></pre>';
	}

	//--------------------------------------------------------------------
}
