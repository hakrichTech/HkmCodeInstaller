<?php
namespace Hkm_code\HTTP;
use Hkm_code\HTTP\Message;
use Hkm_code\Cookie\Cookie;
use Hkm_code\Cookie\CookieStore;
use Hkm_traits\AddOn\ResponseTrait;
use Hkm_code\HTTP\ContentSecurityPolicy;
use Hkm_code\Exceptions\HTTP\HTTPException;
use Hkm_code\Cookie\Exceptions\CookieException;

class Response extends Message
{

    protected static $statusCodes = [
		// 1xx: Informational
		100 => 'Continue',
		101 => 'Switching Protocols',
		102 => 'Processing', // http://www.iana.org/go/rfc2518
		103 => 'Early Hints', // http://www.ietf.org/rfc/rfc8297.txt
		// 2xx: Success
		200 => 'OK',
		201 => 'Created',
		202 => 'Accepted',
		203 => 'Non-Authoritative Information', // 1.1
		204 => 'No Content',
		205 => 'Reset Content',
		206 => 'Partial Content',
		207 => 'Multi-Status', // http://www.iana.org/go/rfc4918
		208 => 'Already Reported', // http://www.iana.org/go/rfc5842
		226 => 'IM Used', // 1.1; http://www.ietf.org/rfc/rfc3229.txt
		// 3xx: Redirection
		300 => 'Multiple Choices',
		301 => 'Moved Permanently',
		302 => 'Found', // Formerly 'Moved Temporarily'
		303 => 'See Other', // 1.1
		304 => 'Not Modified',
		305 => 'Use Proxy', // 1.1
		306 => 'Switch Proxy', // No longer used
		307 => 'Temporary Redirect', // 1.1
		308 => 'Permanent Redirect', // 1.1; Experimental; http://www.ietf.org/rfc/rfc7238.txt
		// 4xx: Client error
		400 => 'Bad Request',
		401 => 'Unauthorized',
		402 => 'Payment Required',
		403 => 'Forbidden',
		404 => 'Not Found',
		405 => 'Method Not Allowed',
		406 => 'Not Acceptable',
		407 => 'Proxy Authentication Required',
		408 => 'Request Timeout',
		409 => 'Conflict',
		410 => 'Gone',
		411 => 'Length Required',
		412 => 'Precondition Failed',
		413 => 'Request Entity Too Large',
		414 => 'Request-URI Too Long',
		415 => 'Unsupported Media Type',
		416 => 'Requested Range Not Satisfiable',
		417 => 'Expectation Failed', 
		418 => "I'm a teapot", // April's Fools joke; http://www.ietf.org/rfc/rfc2324.txt
		// 419 (Authentication Timeout) is a non-standard status code with unknown origin
		421 => 'Misdirected Request', // http://www.iana.org/go/rfc7540 Section 9.1.2
		422 => 'Unprocessable Entity', // http://www.iana.org/go/rfc4918
		423 => 'Locked', // http://www.iana.org/go/rfc4918
		424 => 'Failed Dependency', // http://www.iana.org/go/rfc4918
		425 => 'Too Early', // https://datatracker.ietf.org/doc/draft-ietf-httpbis-replay/
		426 => 'Upgrade Required',
		428 => 'Precondition Required', // 1.1; http://www.ietf.org/rfc/rfc6585.txt
		429 => 'Too Many Requests', // 1.1; http://www.ietf.org/rfc/rfc6585.txt
		431 => 'Request Header Fields Too Large', // 1.1; http://www.ietf.org/rfc/rfc6585.txt
		451 => 'Unavailable For Legal Reasons', // http://tools.ietf.org/html/rfc7725
		499 => 'Client Closed Request', // http://lxr.nginx.org/source/src/http/ngx_http_request.h#0133
		// 5xx: Server error
		500 => 'Internal Server Error',
		501 => 'Not Implemented',
		502 => 'Bad Gateway',
		503 => 'Service Unavailable',
		504 => 'Gateway Timeout',
		505 => 'HTTP Version Not Supported',
		506 => 'Variant Also Negotiates', // 1.1; http://www.ietf.org/rfc/rfc2295.txt
		507 => 'Insufficient Storage', // http://www.iana.org/go/rfc4918
		508 => 'Loop Detected', // http://www.iana.org/go/rfc5842
		510 => 'Not Extended', // http://www.ietf.org/rfc/rfc2774.txt
		511 => 'Network Authentication Required', // http://www.ietf.org/rfc/rfc6585.txt
		599 => 'Network Connect Timeout Error', // https://httpstatuses.com/599
    ];
    protected static $reason = '';
    protected static $statusCode = 200;
	protected static $pretend = false;
	protected static $thiss;
	use ResponseTrait;
	public static function SET_STATUS_CODE(int $code, string $reason = '')
	{
		// Valid range?
		if ($code < 100 || $code > 599)
		{
			throw HTTPException::FOR_INVALID_STATUS_CODE($code);
		}

		// Unknown and no message?
		if (! array_key_exists($code, static::$statusCodes) && empty($reason))
		{
            throw HTTPException::FOR_UNKNOWN_STATUS_CODE($code);
            
		}

		self::$statusCode = $code;

		self::$reason = ! empty($reason) ? $reason : static::$statusCodes[$code];

		return self::$thiss;
    }
	public function __construct($config)
	{
		// Default to a non-caching page.
		// Also ensures that a Cache-control header exists.
		self::NO_CACHE();
        self::$thiss = $this;
		// We need CSP object even if not enabled to avoid calls to non existing methods
		self::$CSP = new ContentSecurityPolicy(hkm_config('ContentSecurityPolicy')); 

		self::$CSPEnabled = $config::$CSPEnabled;

		//---------------------------------------------------------------------
		// DEPRECATED COOKIE MANAGEMENT
		//---------------------------------------------------------------------
		self::$cookiePrefix   = $config::$cookiePrefix;
		self::$cookieDomain   = $config::$cookieDomain;
		self::$cookiePath     = $config::$cookiePath;
		self::$cookieSecure   = $config::$cookieSecure;
		self::$cookieHTTPOnly = $config::$cookieHTTPOnly;
		// self::$cookieSameSite = $config::$cookieSameSite ?? Cookie::SAMESITE_LAX;

		$config::$cookieSameSite = $config::$cookieSameSite ?? Cookie::SAMESITE_LAX;

		if (! in_array(strtolower($config::$cookieSameSite ?: Cookie::SAMESITE_LAX), Cookie::ALLOWED_SAMESITE_VALUES, true))
		{
			throw CookieException::forInvalidSameSite($config::$cookieSameSite);
		}

		self::$cookieStore = new CookieStore([]);
		Cookie::setDefaults(hkm_config('Cookie') ?? [
			// @todo Remove this fallback when deprecated `App` members are removed
			'prefix'   => $config::$cookiePrefix,
			'path'     => $config::$cookiePath,
			'domain'   => $config::$cookieDomain,
			'secure'   => $config::$cookieSecure,
			'httponly' => $config::$cookieHTTPOnly,
			'samesite' => $config::$cookieSameSite ?? Cookie::SAMESITE_LAX,
		]);

		// Default to an HTML Content-Type. Devs can override if needed.
		self::SET_CONTENT_TYPE('text/html');
	}
    
    public static function PRETEND(bool $pretend = true)
	{

		self::$pretend = $pretend;
		return self::$thiss;
    }
    public static function GET_STATUS_CODE(): int
	{
		if (empty(self::$statusCode))
		{
			d("FOR_MISSING_RESPONSE_STATUS");
			// throw HTTPException::FOR_MISSING_RESPONSE_STATUS();
		}

		return self::$statusCode;
    }
    public static function GET_REASON(): string
	{
		return self::GET_REASON_PHRASE();
    }
    public static function GET_REASON_PHRASE()
	{
		if (self::$reason === '')
		{
			return ! empty(self::$statusCode) ? static::$statusCodes[self::$statusCode] : '';
		}

		return self::$reason;
	}
    
}
