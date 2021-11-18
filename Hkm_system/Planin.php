<?php

use Hkm_code\HTTP\Request;
use Hkm_code\HTTP\Response;
use Laminas\Escaper\Escaper;
use TeamsMailerSystem\Mailer;
use Hkm_code\Vezirion\Services;
use Hkm_code\Cookie\CookieStore;
use Hkm_code\Vezirion\Factories;
use Hkm_code\Vezirion\FileLocator;
use Hkm_code\HTTP\RedirectResponse;
use Hkm_code\Vezirion\Settings\App;
use Hkm_code\Database\ConnectionInterface;
use TeamsMailerSystem\Validation\EmailValidation;
use Hkm_code\Files\Exceptions\FileNotFoundException;
use TeamsMailerSystem\Exception as TeamsMailerException;

// use Hkm_code\Vezirion\vezirionData\vezirionDataHelper;






function freeEmails($email)
{
	$emailPart = explode('@', $email);
	$host = explode('.', $emailPart[1])[0];
	return !in_array($host, ['gmail', 'yahoo', 'icloud', 'ymail', 'outlook']);
}
if (! function_exists('hkm_old'))
{
	/**
	 * Provides access to "hkm_old input" that was set in the session
	 * during a hkm_redirect()->withInput().
	 *
	 * @param string         $key
	 * @param null           $default
	 * @param string|boolean $escape
	 *
	 * @return mixed|null
	 */
	function hkm_old(string $key, $default = null, $escape = 'html')
	{
		// Ensure the session is loaded
		if (session_status() === PHP_SESSION_NONE && ENVIRONMENT !== 'testing')
		{
			// @codeCoverageIgnoreStart
			hkm_session();
			// @codeCoverageIgnoreEnd
		}

		$request = Services::REQUEST();

		$value = $request::GET_OLD_INPUT($key);

		// Return the default value if nothing
		// found in the hkm_old input.
		if (is_null($value))
		{
			return $default;
		}

		// If the result was serialized array or string, then unserialize it for use...
		if (is_string($value) && (strpos($value, 'a:') === 0 || strpos($value, 's:') === 0))
		{
			$value = unserialize($value);
		}

		return $escape === false ? $value : hkm_esc($value, $escape);
	}
}

if (! function_exists('hkm_slash_item'))
{
	//Unlike CI3, this function is placed here because
	//it's not a config, or part of a config.
	/**
	 * Fetch a config file item with slash appended (if not empty)
	 *
	 * @param string $item Config item name
	 *
	 * @return string|null The configuration item or NULL if
	 * the item doesn't exist
	 */
	function hkm_slash_item(string $item): ?string
	{
		$config     = hkm_config(App::class);
		$configItem = $config->{$item};

		if (! isset($configItem) || empty(trim($configItem)))
		{
			return $configItem;
		}

		return rtrim($configItem, '/') . '/';
	}
}

if (! function_exists('hkm_csrf_token'))
{
	/**
	 * Returns the CSRF token name.
	 * Can be used in Views when building hidden inputs manually,
	 * or used in javascript vars when using APIs.
	 *
	 * @return string
	 */
	function hkm_csrf_token(): string
	{
		return Services::security()->getTokenName();
	}
}
if (!function_exists('hkm_db_connect')) {
	/**
	 * Grabs a database connection and returns it to the user.
	 *
	 * This is a convenience wrapper for \Database::connect()
	 * and supports the same parameters. Namely:
	 *
	 * When passing in $db, you may pass any of the following to connect:
	 * - group name
	 * - existing connection instance
	 * - array of database configuration values
	 *
	 * If $getShared === false then a new connection instance will be provided,
	 * otherwise it will all calls will return the same instance.
	 *
	 * @param ConnectionInterface|array|string|null $db
	 * @param boolean                               $getShared
	 *
	 * @return BaseConnection
	 */
	function hkm_db_connect($db = null, bool $getShared = true)
	{
		$database = FileLocator::SEARCH(APP_CONFIG_NAMESPACE . '/Database')[0];
		$database = FileLocator::GET_CLASS_NAME($database);

		return $database::CONNECT($db, $getShared);
	}
}
if (!function_exists('hkm_remove_invisible_characters')) {
	/**
	 * Remove Invisible Characters
	 *
	 * This prevents sandwiching null characters
	 * between ascii characters, like Java\0script.
	 *
	 * @param string  $str
	 * @param boolean $urlEncoded
	 *
	 * @return string
	 */
	function hkm_remove_invisible_characters(string $str, bool $urlEncoded = true): string
	{
		$nonDisplayables = [];

		// every control character except newline (dec 10),
		// carriage return (dec 13) and horizontal tab (dec 09)
		if ($urlEncoded) {
			$nonDisplayables[] = '/%0[0-8bcef]/';  // url encoded 00-08, 11, 12, 14, 15
			$nonDisplayables[] = '/%1[0-9a-f]/';   // url encoded 16-31
		}

		$nonDisplayables[] = '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S';   // 00-08, 11, 12, 14-31, 127

		do {
			$str = preg_replace($nonDisplayables, '', $str, -1, $count);
		} while ($count);

		return $str;
	}
}
if (! function_exists('hkm_csrf_field'))
{
	/**
	 * Generates a hidden input field for use within manually generated forms.
	 *
	 * @param string|null $id
	 *
	 * @return string
	 */
	function hkm_csrf_field(string $id = null): string
	{
		return '<input type="hidden"' . (! empty($id) ? ' id="' . hkm_esc($id, 'attr') . '"' : '') . ' name="' . hkm_csrf_token() . '" value="' . hkm_csrf_hash() . '" />';
	}
}
if (! function_exists('hkm_csrf_hash'))
{
	/**
	 * Returns the current hash value for the CSRF protection.
	 * Can be used in Views when building hidden inputs manually,
	 * or used in javascript vars for API usage.
	 *
	 * @return string
	 */
	function hkm_csrf_hash(): string
	{
		return Services::security()->getHash();
	}
}
function TeamsMailerAPI($data)
{
	$data = (object) $data;
	$mailer = hkm_service('teams_mailer');
	if ($mailer instanceof Mailer) {

		try {
			$mailer::SET_DEBUG(0);
			$mailer::IS_SMTP();
			$mailer::$Host = 'teamsmailer.com';
			$mailer::$SMTPAuth = true;
			$mailer::$Username   = 'config@teamsmailer.com';
			$mailer::$Password   = 'config@+%pa1';
			$mailer::$SMTPSecure = Mailer::ENCRYPTION_STARTTLS;
			$mailer::$Port       = 587;
			$mailer::SET_FROM('config@teamsmailer.com', "Teamsmailer");
			$mailer::ADD_ADDRESS($data->receiverEmail, $data->receiverName);
			$mailer::ADD_REPLY_TO($data->replyEmail, $data->replyName);
			$mailer::IS_HTML(true);
			$mailer::$Subject = $data->preheader;
			$mailer::$Body    = $data->template;
			$mailer::SEND();

			$mailer::RESET();
			return true;
		} catch (TeamsMailerException $e) {
			return false;
		}
	}
}
if (!function_exists('hkm_cookies')) {
	/**
	 * Fetches the global `CookieStore` instance held by `Response`.
	 *
	 * @param Cookie[] $hkm_cookies   If `getGlobal` is false, this is passed to CookieStore's constructor
	 * @param boolean  $getGlobal If false, creates a new instance of CookieStore
	 *
	 * @return CookieStore
	 */
	function hkm_cookies(array $hkm_cookies = [], bool $getGlobal = true): CookieStore
	{
		if ($getGlobal) {
			return Services::RESPONSE()::GET_COOKIE_STORE();
		}

		return new CookieStore($hkm_cookies);
	}
}
if (! function_exists('hkm_stringify_attributes'))
{
	/**
	 * Stringify attributes for use in HTML tags.
	 *
	 * Helper function used to convert a string, array, or object
	 * of attributes to a string.
	 *
	 * @param mixed   $attributes string, array, object
	 * @param boolean $js
	 *
	 * @return string
	 */
	function hkm_stringify_attributes($attributes, bool $js = false): string
	{
		$atts = '';

		if (empty($attributes))
		{
			return $atts;
		}

		if (is_string($attributes))
		{
			return ' ' . $attributes;
		}

		$attributes = (array) $attributes;

		foreach ($attributes as $key => $val)
		{
			$atts .= ($js) ? $key . '=' . hkm_esc($val, 'js') . ',' : ' ' . $key . '="' . hkm_esc($val) . '"';
		}

		return rtrim($atts, ',');
	}
}
if (!function_exists('hkm_redirect')) {
	/**
	 * Convenience method that works with the current global $request and
	 * $router instances to redirect using named/reverse-routed routes
	 * to determine the URL to go to. If nothing is found, will treat
	 * as a traditional redirect and pass the string in, letting
	 * $response->redirect() determine the correct method and code.
	 *
	 * If more control is needed, you must use $response->redirect explicitly.
	 *
	 * @param string $route
	 *
	 * @return RedirectResponse
	 */
	function hkm_redirect(string $route = null): RedirectResponse
	{
		$response = Services::REDIRECT_RESPONSE(null, true);

		if (!empty($route)) {
			return $response::ROUTE($route);
		}

		return $response;
	}
}
if (!function_exists('hkm_session')) {
	/**
	 * A convenience method for accessing the session instance,
	 * or an item that has been set in the session.
	 *
	 * Examples:
	 *    hkm_session()->set('foo', 'bar');
	 *    $foo = hkm_session('bar');
	 *
	 * @param string $val
	 *
	 * @return Session|mixed|null
	 */
	function hkm_session(string $val = null)
	{
		$session = Services::SESSION();

		// Returning a single item?
		if (is_string($val)) {
			return $session->get($val);
		}

		return $session;
	}
}
if (!function_exists('hkm_force_https')) {
	/**
	 * Used to force a page to be accessed in via HTTPS.
	 * Uses a standard redirect, plus will set the HSTS header
	 * for modern browsers that support, which gives best
	 * protection against man-in-the-middle attacks.
	 *
	 * @see https://en.wikipedia.org/wiki/HTTP_Strict_Transport_Security
	 *
	 * @param integer           $duration How long should the SSL header be set for? (in seconds)
	 *                                    Defaults to 1 year.
	 * @param RequestInterface  $request
	 * @param ResponseInterface $response
	 *
	 * @throws HTTPException
	 */
	function hkm_force_https(int $duration = 31536000, Request $request = null, Response $response = null)
	{
		if (is_null($request)) {
			$request = Services::REQUEST(null, true);
		}
		if (is_null($response)) {
			$response = Services::RESPONSE(null, true);
		}

		if ((ENVIRONMENT !== 'testing' && (hkm_is_cli() || $request::IS_SECURE())) || (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'test')) {
			return;
		}

		// If the session status is active, we should regenerate
		// the session ID for safety sake.
		if (ENVIRONMENT !== 'testing' && session_status() === PHP_SESSION_ACTIVE) {
			// @codeCoverageIgnoreStart
			Services::SESSION(null, true)
				::REGENERATE();
		}

		// $baseURL = hkm_config('app')->baseURL;

		// if (strpos($baseURL, 'https://') === 0)
		// {
		// 	$baseURL = (string) substr($baseURL, strlen('https://'));
		// }
		// elseif (strpos($baseURL, 'http://') === 0)
		// {
		// 	$baseURL = (string) substr($baseURL, strlen('http://'));
		// }

		// $uri = URI::CREATE_URI_STRING(
		// 	'https', $baseURL, $request->uri::GET_PATH(), // Absolute URIs should use a "/" for an empty path
		// 	$request->uri::GET_QUERY(), $request->uri::GET_FRAGMENT()
		// );

		// Set an HSTS header
		// $response->setHeader('Strict-Transport-Security', 'max-age=' . $duration);
		// $response->redirect($uri);
		// $response->sendHeaders();

		if (ENVIRONMENT !== 'testing') {
			// @codeCoverageIgnoreStart
			exit();
			// @codeCoverageIgnoreEnd
		}
	}
}
if (!function_exists('hkm_config')) {
	/**
	 * More simple way of getting config instances from Factories
	 *
	 * @param string  $name
	 * @param boolean $getShared
	 *
	 * @return mixed
	 */
	function hkm_config(string $name, bool $getShared = true)
	{
		return Factories::config($name, ['getShared' => $getShared]);
	}
}
if (!function_exists('hkm_service')) {
	/**
	 * Allows cleaner access to the Services Config file.
	 * Always returns a SHARED instance of the class, so
	 * calling the function multiple times should always
	 * return the same instance.
	 *
	 * These are equal:
	 *  - $timer = hkm_service('timer')
	 *  - $timer = \Hkm_code\Vezirion\Services::timer();
	 *
	 * @param string $name
	 * @param mixed  ...$params
	 *
	 * @return mixed
	 */
	function hkm_service(string $name, ...$params)
	{
		return Services::$name(...$params);
	}
}
if (!function_exists('hkm_app_timezone')) {
	/**
	 * Returns the timezone the application has been set to display
	 * dates in. This might be different than the timezone set
	 * at the server level, as you often want to stores dates in UTC
	 * and convert them on the fly for the user.
	 *
	 * @return string
	 */
	function hkm_app_timezone(): string
	{
		$config = hkm_config("App");

		return $config::$appTimezone;
	}
}
if (!function_exists('hkm_view')) {
	/**
	 * Grabs the current RendererInterface-compatible class
	 * and tells it to render the specified view. Simply provides
	 * a convenience method that can be used in Controllers,
	 * libraries, and routed closures.
	 *
	 * NOTE: Does not provide any escaping of the data, so that must
	 * all be handled manually by the developer.
	 *
	 * @param string $name
	 * @param array  $data
	 * @param array  $options Unused - reserved for third-party extensions.
	 *
	 * @return string
	 */
	function hkm_view(string $name, array $data = [], array $options = []): string
	{
		/**
		 * @var Hkm_code\View\View $renderer
		 */
		$renderer = Services::RENDERER();

		$saveData = hkm_config(View::class)::$saveData;

		if (array_key_exists('saveData', $options)) {
			$saveData = (bool) $options['saveData'];
			unset($options['saveData']);
		}

		return $renderer::SET_DATA($data, 'raw')
			::RENDER($name, $options, $saveData);
	}
}
if (!function_exists('hkm_is_email_valid')) {
	function hkm_is_email_valid(string $value)
	{

		// $cals = new EmailValidation($value);
		// $cals::Checkvalidity($cals::CHECK_VALIDITY);

		// return $cals::is_okay() ? false : true;
	}
}
if (!function_exists('hkm_is_valid_host')) {
	/**
	 * Validate whether a string contains a valid value to use as a hostname or IP address.
	 * IPv6 addresses must include [], e.g. `[::1]`, not just `::1`.
	 *
	 * @param string $host The host name or IP address to check
	 *
	 * @return bool
	 */
	function hkm_is_valid_host($host)
	{
		//Simple syntax limits
		if (empty($host) || !is_string($host) || strlen($host) > 256 || !preg_match('/^([a-zA-Z\d.-]*|\[[a-fA-F\d:]+\])$/', $host)) {
			return false;
		}
		//Looks like a bracketed IPv6 address
		if (strlen($host) > 2 && substr($host, 0, 1) === '[' && substr($host, -1, 1) === ']') {
			return filter_var(substr($host, 1, -1), FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false;
		}
		//If removing all the dots results in a numeric string, it must be an IPv4 address.
		//Need to check this first because otherwise things like `999.0.0.0` are considered valid host names
		if (is_numeric(str_replace('.', '', $host))) {
			//Is it a valid IPv4 address?
			return filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false;
		}
		if (filter_var('http://' . $host, FILTER_VALIDATE_URL) !== false) {
			//Is it a syntactically valid hostname?
			return true;
		}

		return false;
	}
}
if (!function_exists('hkm_mb_pathinfo')) {
	/**
	 * Multi-byte-safe pathinfo replacement.
	 * Drop-in replacement for pathinfo(), but multibyte- and cross-platform-safe.
	 *
	 * @see http://www.php.net/manual/en/function.pathinfo.php#107461
	 *
	 * @param string     $path    A filename or path, does not need to exist as a file
	 * @param int|string $options Either a PATHINFO_* constant,
	 *                            or a string name to return only the specified piece
	 *
	 * @return string|array
	 */
	function hkm_mb_pathinfo($path, $options = null)
	{
		$ret = ['dirname' => '', 'basename' => '', 'extension' => '', 'filename' => ''];
		$pathinfo = [];
		if (preg_match('#^(.*?)[\\\\/]*(([^/\\\\]*?)(\.([^.\\\\/]+?)|))[\\\\/.]*$#m', $path, $pathinfo)) {
			if (array_key_exists(1, $pathinfo)) {
				$ret['dirname'] = $pathinfo[1];
			}
			if (array_key_exists(2, $pathinfo)) {
				$ret['basename'] = $pathinfo[2];
			}
			if (array_key_exists(5, $pathinfo)) {
				$ret['extension'] = $pathinfo[5];
			}
			if (array_key_exists(3, $pathinfo)) {
				$ret['filename'] = $pathinfo[3];
			}
		}
		switch ($options) {
			case PATHINFO_DIRNAME:
			case 'dirname':
				return $ret['dirname'];
			case PATHINFO_BASENAME:
			case 'basename':
				return $ret['basename'];
			case PATHINFO_EXTENSION:
			case 'extension':
				return $ret['extension'];
			case PATHINFO_FILENAME:
			case 'filename':
				return $ret['filename'];
			default:
				return $ret;
		}
	}
}
if (!function_exists('hkm_mime_type')) {
	/**
	 * Get the MIME type for a file extension.
	 *
	 * @param string $ext File extension
	 *
	 * @return string MIME type of file
	 */
	function hkm_mime_types($ext = '')
	{
		$mimes = [
			'xl' => 'application/excel',
			'js' => 'application/javascript',
			'hqx' => 'application/mac-binhex40',
			'cpt' => 'application/mac-compactpro',
			'bin' => 'application/macbinary',
			'doc' => 'application/msword',
			'word' => 'application/msword',
			'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
			'xltx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
			'potx' => 'application/vnd.openxmlformats-officedocument.presentationml.template',
			'ppsx' => 'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
			'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
			'sldx' => 'application/vnd.openxmlformats-officedocument.presentationml.slide',
			'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
			'dotx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
			'xlam' => 'application/vnd.ms-excel.addin.macroEnabled.12',
			'xlsb' => 'application/vnd.ms-excel.sheet.binary.macroEnabled.12',
			'class' => 'application/octet-stream',
			'dll' => 'application/octet-stream',
			'dms' => 'application/octet-stream',
			'exe' => 'application/octet-stream',
			'lha' => 'application/octet-stream',
			'lzh' => 'application/octet-stream',
			'psd' => 'application/octet-stream',
			'sea' => 'application/octet-stream',
			'so' => 'application/octet-stream',
			'oda' => 'application/oda',
			'pdf' => 'application/pdf',
			'ai' => 'application/postscript',
			'eps' => 'application/postscript',
			'ps' => 'application/postscript',
			'smi' => 'application/smil',
			'smil' => 'application/smil',
			'mif' => 'application/vnd.mif',
			'xls' => 'application/vnd.ms-excel',
			'ppt' => 'application/vnd.ms-powerpoint',
			'wbxml' => 'application/vnd.wap.wbxml',
			'wmlc' => 'application/vnd.wap.wmlc',
			'dcr' => 'application/x-director',
			'dir' => 'application/x-director',
			'dxr' => 'application/x-director',
			'dvi' => 'application/x-dvi',
			'gtar' => 'application/x-gtar',
			'php3' => 'application/x-httpd-php',
			'php4' => 'application/x-httpd-php',
			'php' => 'application/x-httpd-php',
			'phtml' => 'application/x-httpd-php',
			'phps' => 'application/x-httpd-php-source',
			'swf' => 'application/x-shockwave-flash',
			'sit' => 'application/x-stuffit',
			'tar' => 'application/x-tar',
			'tgz' => 'application/x-tar',
			'xht' => 'application/xhtml+xml',
			'xhtml' => 'application/xhtml+xml',
			'zip' => 'application/zip',
			'mid' => 'audio/midi',
			'midi' => 'audio/midi',
			'mp2' => 'audio/mpeg',
			'mp3' => 'audio/mpeg',
			'm4a' => 'audio/mp4',
			'mpga' => 'audio/mpeg',
			'aif' => 'audio/x-aiff',
			'aifc' => 'audio/x-aiff',
			'aiff' => 'audio/x-aiff',
			'ram' => 'audio/x-pn-realaudio',
			'rm' => 'audio/x-pn-realaudio',
			'rpm' => 'audio/x-pn-realaudio-plugin',
			'ra' => 'audio/x-realaudio',
			'wav' => 'audio/x-wav',
			'mka' => 'audio/x-matroska',
			'bmp' => 'image/bmp',
			'gif' => 'image/gif',
			'jpeg' => 'image/jpeg',
			'jpe' => 'image/jpeg',
			'jpg' => 'image/jpeg',
			'png' => 'image/png',
			'tiff' => 'image/tiff',
			'tif' => 'image/tiff',
			'webp' => 'image/webp',
			'avif' => 'image/avif',
			'heif' => 'image/heif',
			'heifs' => 'image/heif-sequence',
			'heic' => 'image/heic',
			'heics' => 'image/heic-sequence',
			'eml' => 'message/rfc822',
			'css' => 'text/css',
			'html' => 'text/html',
			'htm' => 'text/html',
			'shtml' => 'text/html',
			'log' => 'text/plain',
			'text' => 'text/plain',
			'txt' => 'text/plain',
			'rtx' => 'text/richtext',
			'rtf' => 'text/rtf',
			'vcf' => 'text/vcard',
			'vcard' => 'text/vcard',
			'ics' => 'text/calendar',
			'xml' => 'text/xml',
			'xsl' => 'text/xml',
			'wmv' => 'video/x-ms-wmv',
			'mpeg' => 'video/mpeg',
			'mpe' => 'video/mpeg',
			'mpg' => 'video/mpeg',
			'mp4' => 'video/mp4',
			'm4v' => 'video/mp4',
			'mov' => 'video/quicktime',
			'qt' => 'video/quicktime',
			'rv' => 'video/vnd.rn-realvideo',
			'avi' => 'video/x-msvideo',
			'movie' => 'video/x-sgi-movie',
			'webm' => 'video/webm',
			'mkv' => 'video/x-matroska',
		];
		$ext = strtolower($ext);
		if (array_key_exists($ext, $mimes)) {
			return $mimes[$ext];
		}

		return 'application/octet-stream';
	}
}
if (!function_exists('hkm_esc')) {
	/**
	 * Performs simple auto-escaping of data for security reasons.
	 * Might consider making this more complex at a later date.
	 *
	 * If $data is a string, then it simply escapes and returns it.
	 * If $data is an array, then it loops over it, escaping each
	 * 'value' of the key/value pairs.
	 *
	 * Valid context values: html, js, css, url, attr, raw, null
	 *
	 * @param string|array $data
	 * @param string       $context
	 * @param string       $encoding
	 *
	 * @return string|array
	 * @throws InvalidArgumentException
	 */
	function hkm_esc($data, string $context = 'html', string $encoding = null)
	{
		if (is_array($data)) {
			foreach ($data as &$value) {

				$value = hkm_esc($value, $context);
			}
		}

		if (is_string($data)) {

			$context = strtolower($context);

			// Provide a way to NOT escape data since
			// this could be called automatically by
			// the View library.
			if (empty($context) || $context === 'raw') {

				return $data;
			}

			if (!in_array($context, ['html', 'js', 'css', 'url', 'attr'], true)) {
				throw new InvalidArgumentException('Invalid escape context provided.');
			}

			$method = $context === 'attr' ? 'escapeHtmlAttr' : 'escape' . ucfirst($context);

			static $escaper;
			if (!$escaper) {
				$escaper = new Escaper($encoding);
			}

			if ($encoding && $escaper->getEncoding() !== $encoding) {
				$escaper = new Escaper($encoding);
			}

			$data = $escaper->$method($data);
		}

		return $data;
	}
}
if (!function_exists('hkm_html2text')) {

	/**
	 * Convert an HTML string into plain text.
	 * This is used by msgHTML().
	 * Note - hkm_older versions of this function used a bundled advanced converter
	 * which was removed for license reasons in #232.
	 * Example usage:
	 *
	 * ```php
	 * //Use default conversion
	 * $plain = $mail->html2text($html);
	 * //Use your own custom converter
	 * $plain = $mail->html2text($html, function($html) {
	 *     $converter = new MyHtml2text($html);
	 *     return $converter->get_text();
	 * });
	 * ```
	 *
	 * @param string        $html     The HTML text to convert
	 * @param string        $CharSet     The Character set of the text
	 * @param bool|callable $advanced Any boolean value to use the internal converter,
	 *                                or provide your own callable for custom conversion.
	 *                                *Never* pass user-supplied data into this parameter
	 *
	 * @return string
	 */
	function hkm_html2text($html, $CharSet, $advanced = false)
	{
		if (is_callable($advanced)) {
			return call_user_func($advanced, $html);
		}

		return html_entity_decode(
			trim(strip_tags(preg_replace('/<(head|title|style|script)[^>]*>.*?<\/\\1>/si', '', $html))),
			ENT_QUOTES,
			$CharSet
		);
	}
}
if (!function_exists('hkm_route_to')) {
	/**
	 * Given a controller/method string and any params,
	 * will attempt to build the relative URL to the
	 * matching route.
	 *
	 * NOTE: This requires the controller/method to
	 * have a route defined in the routes Config file.
	 *
	 * @param string $method
	 * @param mixed  ...$params
	 *
	 * @return false|string
	 */
	function hkm_route_to(string $method, ...$params)
	{
		return Services::ROUTES()::REVERSE_ROUTE($method, ...$params);
	}
}
if (!function_exists('hkm_lang')) {

	function hkm_lang(string $line, array $args = [], string $locale = null)
	{
		return Services::LANGUAGE($locale)::GET_LINE($line, $args);
	}
}
if (!function_exists('hkm_helper')) {
	/**
	 * Loads a helper file into memory. Supports namespaced helpers,
	 * both in and out of the 'helpers' directory of a namespaced directory.
	 *
	 * Will load ALL helpers of the matching name, in the following order:
	 *   1. app/Helpers
	 *   2. {namespace}/Helpers
	 *   3. system/Helpers
	 *
	 * @param  string|array $filenames
	 * @throws FileNotFoundException
	 */
	function hkm_helper($filenames)
	{
		static $loaded = [];

		// $loader = Services::locator(true);

		if (!is_array($filenames)) {
			$filenames = [$filenames];
		}

		// Store a list of all files to include...
		$includes = [];

		foreach ($filenames as $filename) {
			// Store our system and application helper
			// versions so that we can control the load ordering.
			$systemHelper  = null;
			$appHelper     = null;
			$localIncludes = [];

			if (strpos($filename, '_helper') === false) {
				$filename .= '_helper';
			}

			// Check if this helper has already been loaded
			if (in_array($filename, $loaded, true)) {
				continue;
			}


			// $path = $loader->locateFile($filename, 'Helpers');
			$path = SYSTEMPATH . "Helpers/" . $filename . '.php';

			if (!is_file($path)) {
				// throw FileNotFoundException::forFileNotFound($filename);
				print_r($filename . " not Found!");
				exit;
			}

			$includes[] = $path;
			$loaded[]   = $filename;
		}

		// Now actually include all of the files
		if (!empty($includes)) {
			foreach ($includes as $path) {
				include_once($path);
			}
		}
	}
}
if (!function_exists('plugins')) {
	/**
	 * Loads a helper file into memory. Supports namespaced helpers,
	 * both in and out of the 'helpers' directory of a namespaced directory.
	 *
	 * Will load ALL helpers of the matching name, in the following order:
	 *   2. {namespace}/Plugins
	 *   3. system/Plugins
	 *
	 * @param  string|array $filenames
	 * @throws FileNotFoundException
	 */
	function plugins($filenames)
	{
		static $loaded = [];

		$loader = Services::LOCATOR(true);

		if (!is_array($filenames)) {
			$filenames = [$filenames];
		}

		// Store a list of all files to include...
		$includes = [];

		foreach ($filenames as $filename) {
			// Store our system and application helper
			// versions so that we can control the load ordering.
			$systemPlugin  = null;
			$appPlugin     = null;
			$localIncludes = [];

			if (strpos($filename, '_plugin') === false) {
				$filename .= '_plugin';
			}

			// Check if this Plugin has already been loaded
			if (in_array($filename, $loaded, true)) {
				continue;
			}


			$paths = $loader::LOCATE_FILE('Plugin\\' . $filename);

			foreach ($paths as $path) {
				if (!is_file($path)) {
					throw FileNotFoundException::FOR_FILE_NOT_FOUND($filename);
				}

				$includes[] = $path;
				$loaded[]   = $filename;
			}
		}

		// Now actually include all of the files
		if (!empty($includes)) {
			foreach ($includes as $path) {
				include_once($path);
			}
		}
	}
}
function hkm_get_host($email, $r = true)
{
	$f = explode('@', $email)[1];
	if ($r) {
		if ($f == 'gmail.com') {
			return 'smtp.gmail.com';
		} else {
			return 'mail' . $f;
		}
	} else {
		return $f;
	}
}
if (!function_exists('hkm_is_cli')) {

	function hkm_is_cli(): bool
	{
		if (PHP_SAPI === 'cli') {
			return true;
		}

		if (defined('STDIN')) {
			return true;
		}

		if (stristr(PHP_SAPI, 'cgi') && getenv('TERM')) {
			return true;
		}

		if (!isset($_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']) && isset($_SERVER['argv']) && count($_SERVER['argv']) > 0) {
			return true;
		}

		// if source of request is from CLI, the `$_SERVER` array will not populate this key
		return !isset($_SERVER['REQUEST_METHOD']);
	}
}
if (!function_exists('hkm_log_message')) {
	/**
	 * A convenience/compatibility method for logging events through
	 * the Log system.
	 *
	 * Allowed log levels are:
	 *  - emergency
	 *  - alert
	 *  - critical
	 *  - error
	 *  - warning
	 *  - notice
	 *  - info
	 *  - debug
	 *
	 * @param string $level
	 * @param string $message
	 * @param array  $context
	 *
	 * @return mixed
	 */
	function hkm_log_message(string $level, string $message, array $context = [])
	{
		// When running tests, we want to always ensure that the
		// TestLogger is running, which provides utilities for
		// for asserting that logs were called in the test code.
		if (ENVIRONMENT === 'testing') {
			// $logger = new TestLogger(new Logger());

			// return $logger->log($level, $message, $context);
		}

		// @codeCoverageIgnoreStart
		return Services::LOGGER(true)->log($level, $message, $context);
		// @codeCoverageIgnoreEnd
	}
}
if (!function_exists('hkm_command')) {
	/**
	 * Runs a single command.
	 * Input expected in a single string as would
	 * be used on the command line itself:
	 *
	 *  > hkm_command('migrate:create SomeMigration');
	 *
	 * @param string $command
	 *
	 * @return false|string
	 */
	function hkm_command(string $command)
	{
		$runner      = hkm_service('commands');
		$regexString = '([^\s]+?)(?:\s|(?<!\\\\)"|(?<!\\\\)\'|$)';
		$regexQuoted = '(?:"([^"\\\\]*(?:\\\\.[^"\\\\]*)*)"|\'([^\'\\\\]*(?:\\\\.[^\'\\\\]*)*)\')';

		$args   = [];
		$length = strlen($command);
		$cursor = 0;

		/**
		 * Adopted from Symfony's `StringInput::tokenize()` with few changes.
		 *
		 * @see https://github.com/symfony/symfony/blob/master/src/Symfony/Component/Console/Input/StringInput.php
		 */
		while ($cursor < $length) {
			if (preg_match('/\s+/A', $command, $match, 0, $cursor)) {
				// nothing to do
			} elseif (preg_match('/' . $regexQuoted . '/A', $command, $match, 0, $cursor)) {
				$args[] = stripcslashes(substr($match[0], 1, strlen($match[0]) - 2));
			} elseif (preg_match('/' . $regexString . '/A', $command, $match, 0, $cursor)) {
				$args[] = stripcslashes($match[1]);
			} else {
				// @codeCoverageIgnoreStart
				throw new InvalidArgumentException(sprintf('Unable to parse input near "... %s ...".', substr($command, $cursor, 10)));
				// @codeCoverageIgnoreEnd
			}

			$cursor += strlen($match[0]);
		}

		$command     = array_shift($args);
		$params      = [];
		$optionValue = false;

		foreach ($args as $i => $arg) {
			if (mb_strpos($arg, '-') !== 0) {
				if ($optionValue) {
					// if this was an option value, it was already
					// included in the previous iteration
					$optionValue = false;
				} else {
					// add to segments if not starting with '-'
					// and not an option value
					$params[] = $arg;
				}

				continue;
			}

			$arg   = ltrim($arg, '-');
			$value = null;

			if (isset($args[$i + 1]) && mb_strpos($args[$i + 1], '-') !== 0) {
				$value       = $args[$i + 1];
				$optionValue = true;
			}

			$params[$arg] = $value;
		}

		ob_start();
		$runner::RUN($command, $params);

		return ob_get_clean();
	}
}
if (!function_exists('hkm_pobohet')) {
	function hkm_pobohet($array)
	{
		if (is_array($array)) {

			$dr = [
				'type' => $array[1],
				'method' => !empty($array[4]) ? trim($array[4]) : "*def*"
			];

			$dr['url'] = isset($array[0]) && !empty($array[0]) ? strpos($array[0], "/") >= 0 ? ltrim($array[0], '/') : trim($array[0], '/') : "-";

			if (empty($array[2])) {
				if (empty($array[3])) {
					$dr['controler'] = "default";
					return $dr;
				} else {
					$dr['controler'] = "+default+\\" . trim($array[3], '\\');
					return $dr;
				}
			} else {
				if (empty($array[3])) {
					$dr['controler'] = rtrim("\\" . trim($array[2], '\\') . "\\-default-", "\\");
					return $dr;
				} else {
					$dr['controler'] = rtrim("\\" . trim($array[2], '\\') . "\\" . trim($array[3], '\\'), "\\");
					return $dr;
				}
			}
		}
	}
}
if (!function_exists('hkm_array_string')) {
	function hkm_array_string($value)
	{
		if (is_array($value)) {
			$cv = "";
			foreach ($value as $key => $valu) $cv .= $valu . ",";
			return $cv;
		} else return $value;
	}
}
if (!function_exists('hkm_count_values')) {
	function hkm_count_values($value)
	{
		if (is_array($value)) {
			return strlen(join(",", array_values($value)) . join(",", array_keys($value)));
		} else return strlen($value);
	}
}
if (!function_exists('hkm_setPad')) {
	/**
	 * Pads our string out so that all titles are the same length to nicely line up descriptions.
	 *
	 * @param string  $item
	 * @param integer $max
	 * @param integer $extra  How many extra spaces to add at the end
	 * @param integer $indent
	 *
	 * @return string
	 */
	function hkm_setPad(string $item, int $max, int $extra = 2, int $indent = 0): string
	{
		$max += $extra + $indent;

		return str_pad(str_repeat(' ', $indent) . $item, $max);
	}
}
function hkm_isAssoc(array $arr)
{
	if (array() === $arr) return false;
	return array_keys($arr) !== range(0, count($arr) - 1);
}
if (!function_exists('hkm_XMLSanitizeValue')) {
	function hkm_XMLSanitizeValue($value)
	{
		if (is_array($value)) {
			if (hkm_isAssoc($value)) {
				$v = '';
				foreach ($value as $key => $va) {
					$v .= $key . "=" . $va . "'-,-'";
				}
				return rtrim($v, "'-,-'");
			} else return join("'-,-'", $value);
		} else return $value;
	}
}
if (!function_exists('hkm_XMLSanitizeArray')) {
	function hkm_XMLSanitizeArray($value)
	{
		if (!is_array($value)) {
			if (stripos($value, "'-,-'") !== false && stripos($value, "'-,-'") > 0) {
				$v = explode("'-,-'", $value);
				$ar = [];
				foreach ($v as $key) {
					if (stripos($key, "=") !== false && stripos($key, "=") > 0) {
						$d = explode("=", $key);
						$ar[$d[0]] = $d[1];
					}
				}
				if (count($ar) > 0) return $ar;
				else return $v;
			} else return $value;
		} else return array_map("hkm_XMLSanitizeArray", $value);
	}
}
/**
 * Find the last character boundary prior to $maxLength in a utf-8
 * quoted-printable encoded string.
 * Original written by Colin Brown.
 *
 * @param string $encodedText utf-8 QP text
 * @param int    $maxLength   Find the last character boundary prior to this length
 *
 * @return int
 */
function hkm_utf8CharBoundary($encodedText, $maxLength)
{
	$foundSplitPos = false;
	$lookBack = 3;
	while (!$foundSplitPos) {
		$lastChunk = substr($encodedText, $maxLength - $lookBack, $lookBack);
		$encodedCharPos = strpos($lastChunk, '=');
		if (false !== $encodedCharPos) {
			//Found start of encoded character byte within $lookBack block.
			//Check the encoded byte value (the 2 chars after the '=')
			$hex = substr($encodedText, $maxLength - $lookBack + $encodedCharPos + 1, 2);
			$dec = hexdec($hex);
			if ($dec < 128) {
				//Single byte character.
				//If the encoded char was found at pos 0, it will fit
				//otherwise reduce maxLength to start of the encoded char
				if ($encodedCharPos > 0) {
					$maxLength -= $lookBack - $encodedCharPos;
				}
				$foundSplitPos = true;
			} elseif ($dec >= 192) {
				//First byte of a multi byte character
				//Reduce maxLength to split at start of character
				$maxLength -= $lookBack - $encodedCharPos;
				$foundSplitPos = true;
			} elseif ($dec < 192) {
				//Middle byte of a multi byte character, look further back
				$lookBack += 3;
			}
		} else {
			//No encoded character found
			$foundSplitPos = true;
		}
	}

	return $maxLength;
}
/**
 * Format a header line.
 *
 * @param string     $name
 * @param string|int $value
 *
 * @return string
 */
function hkm_headerLine($name, $value, $LE)
{
	return $name . ': ' . $value . $LE;
}
/**
 * Return an RFC 822 formatted date.
 *
 * @return string
 */
function hkm_rfcDate()
{
	//Set the time zone to whatever the default is to avoid 500 errors
	//Will default to UTC if it's not set properly in php.ini
	date_default_timezone_set(@date_default_timezone_get());

	return date('D, j M Y H:i:s O');
}
/**
 * Strip newlines to prevent header injection.
 *
 * @param string $str
 *
 * @return string
 */
function hkm_secureHeader($str)
{
	return trim(str_replace(["\r", "\n"], '', $str));
}
/**
 * Remove trailing breaks from a string.
 *
 * @param string $text
 *
 * @return string The text to remove breaks from
 */
function hkm_stripTrailingWSP($text)
{
	return rtrim($text, " \r\n\t");
}
/**
 * If a string contains any "special" characters, double-quote the name,
 * and escape any double quotes with a backslash.
 *
 * @param string $str
 *
 * @return string
 *
 * @see RFC822 3.4.1
 */
function hkm_quotedString($str)
{
	if (preg_match('/[ ()<>@,;:"\/\[\]?=]/', $str)) {
		//If the string contains any of these chars, it must be double-quoted
		//and any double quotes must be escaped with a backslash
		return '"' . str_replace('"', '\\"', $str) . '"';
	}

	//Return the string untouched, it doesn't need quoting
	return $str;
}
/**
 * Tells whether IDNs (Internationalized Domain Names) are supported or not. This requires the
 * `intl` and `mbstring` PHP extensions.
 *
 * @return bool `true` if required functions for IDN support are present
 */
function hkm_idnSupported()
{
	return function_exists('idn_to_ascii') && function_exists('mb_convert_encoding');
}
/**
 * Create a unique ID to use for boundaries.
 *
 * @return string
 */
function HKM_GENERATE_ID()
{
	$len = 32; //32 bytes = 256 bits
	$bytes = '';
	if (function_exists('random_bytes')) {
		try {
			$bytes = random_bytes($len);
		} catch (\Exception $e) {
			//Do nothing
		}
	} elseif (function_exists('openssl_random_pseudo_bytes')) {
		/** @noinspection CryptographicallySecureRandomnessInspection */
		$bytes = openssl_random_pseudo_bytes($len);
	}
	if ($bytes === '') {
		//We failed to produce a proper random string, so make do.
		//Use a hash to force the length to the same as the other methods
		$bytes = hash('sha256', uniqid((string) mt_rand(), true), true);
	}

	//We don't care about messing up base64 format here, just want a random string
	return str_replace(['=', '+', '/'], '', base64_encode(hash('sha256', $bytes, true)));
}
/**
 * Fix CVE-2016-10033 and CVE-2016-10045 by disallowing potentially unsafe shell characters.
 * Note that escapeshellarg and escapeshellcmd are inadequate for our purposes, especially on Windows.
 *
 * @see https://github.com/PHPMailer/PHPMailer/issues/924 CVE-2016-10045 bug report
 *
 * @param string $string The string to be validated
 *
 * @return bool
 */
function hkm_isShellSafe($string)
{
	//Future-proof
	if (
		escapeshellcmd($string) !== $string
		|| !in_array(escapeshellarg($string), ["'$string'", "\"$string\""])
	) {
		return false;
	}

	$length = strlen($string);

	for ($i = 0; $i < $length; ++$i) {
		$c = $string[$i];

		//All other characters have a special meaning in at least one common shell, including = and +.
		//Full stop (.) has a special meaning in cmd.exe, but its impact should be negligible here.
		//Note that this does permit non-Latin alphanumeric characters based on the current locale.
		if (!ctype_alnum($c) && strpos('@_-.', $c) === false) {
			return false;
		}
	}

	return true;
}
/**
 * Calculate an MD5 HMAC hash.
 * Works like hash_HMAC('md5', $data, $key)
 * in case that function is not available.
 *
 * @param string $data The data to hash
 * @param string $key  The key to hash with
 *
 * @return string
 */
function hkm_hmac($data, $key)
{
	if (function_exists('hash_hmac')) {
		return hash_hmac('md5', $data, $key);
	}

	//The following borrowed from
	//http://php.net/manual/en/function.mhash.php#27225

	//RFC 2104 HMAC implementation for php.
	//Creates an md5 HMAC.
	//Eliminates the need to install mhash to compute a HMAC
	//by Lance Rushing

	$bytelen = 64; //byte length for md5
	if (strlen($key) > $bytelen) {
		$key = pack('H*', md5($key));
	}
	$key = str_pad($key, $bytelen, chr(0x00));
	$ipad = str_pad('', $bytelen, chr(0x36));
	$opad = str_pad('', $bytelen, chr(0x5c));
	$k_ipad = $key ^ $ipad;
	$k_opad = $key ^ $opad;

	return md5($k_opad . pack('H*', md5($k_ipad . $data)));
}
if (!function_exists('hkm_fetch_Dir')) {
	function hkm_fetch_Dir($x, $WithfFiles = false, $third_party = false)
	{
		$array = [];
		if (is_dir($x)) {
			$files = scandir($x);
			foreach ($files as $file) {
				if ($file != '.' && $file != "..") {
					if ($third_party) {
						if (is_dir($x . "/" . $file)) {
							$array[$file . '\\'] = $x . "/" . $file;
						}
					} else {
						if ($WithfFiles) {
							if (is_dir($x . "/" . $file)) {
								$array = array_merge(hkm_fetch_Dir($x . "/" . $file, $WithfFiles), $array);
							} else {
								$array[] = $x . "/" . $file;
							}
						} else {
							if (is_dir($x . "/" . $file)) {
								$array[] = $x . "/" . $file;
								$array = array_merge(hkm_fetch_Dir($x . "/" . $file), $array);
							}
						}
					}
				}
			}
		}
		return $array;
	}
}

$files = hkm_fetch_Dir(__DIR__."/Planins/",true);
foreach ($files as $value) {
	include $value;
}


