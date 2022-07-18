<?php
namespace Hkm_code\HTTP;

use Hkm_traits\AddOn\RequestTrait;

class Request extends Message
{
	use RequestTrait;
    
	protected static $proxyIPs;
	protected static $method;
	protected static $uri;
	protected static $thiss;
	protected static $ip;

    public function __construct($config = null,$method = "")
	{
		/**
		 * @deprecated $this->proxyIps property will be removed in the future
		 */
		parent::__construct();
		self::$proxyIPs = $config::$proxyIPs;
		if (empty(self::$method))
		{
			$method = empty($method)?self::GET_SERVER('REQUEST_METHOD') ?? 'GET':$method;
		}
		
		self::$ip = self::GET_SERVER('REMOTE_ADDR')??0;

		if (empty(self::$uri))
		{
			self::$uri = new URI();
		}
		self::$method = $method;
        self::$thiss = $this;
    } 

	/**
	 * Test to see if a request contains the HTTP_X_REQUESTED_WITH header.
	 *
	 * @return boolean
	 */
	public static function IS_AJAX(): bool
	{
		return self::HAS_HEADER('X-Requested-With') && strtolower(self::HEADER('X-Requested-With')) === 'xmlhttprequest';
	}

	

	/**
	 * Attempts to detect if the current connection is secure through
	 * a few different methods.
	 *
	 * @return boolean
	 */
	public static function IS_SECURE(): bool
	{
		if (! empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off')
		{
			return true;
		}

		if (self::HAS_HEADER('X-Forwarded-Proto') && self::HEADER('X-Forwarded-Proto')::GET_VALUE() === 'https')
		{
			return true;
		}
		return self::HAS_HEADER('Front-End-Https') && ! empty(self::HEADER('Front-End-Https')::GET_VALUE()) && strtolower(self::HEADER('Front-End-Https')::GET_VALUE()) !== 'off';
	}
	 /**
     * Returns the user.
     *
     * @return string|null
     */
    public static function GET_USER()
    {
		if(self::HAS_HEADER('PHP_AUTH_USER')) return self::HEADER('PHP_AUTH_USER')::GET_VALUE();
		return null;

    }

    /**
     * Returns the password.
     *
     * @return string|null
     */
    public static function GET_PASSWORD()
    {
		if(self::HAS_HEADER('PHP_AUTH_PW')) return self::HEADER('PHP_AUTH_PW')::GET_VALUE();
		return null;
    }

    /**
     * Gets the user info.
     *
     * @return string A user name and, optionally, scheme-specific information about how to gain authorization to access the server
     */
    public static function GET_USER_INFO()
    {
        $userinfo = self::GET_USER();

        $pass = self::GET_PASSWORD();
        if ('' != $pass) {
            $userinfo .= ":$pass";
        }

        return $userinfo;
    }
    //--------------------------------------------------------------------

	/**
	 * Determines if this request was made from the command line (CLI).
	 *
	 * @return boolean
	 */
	public static function IS_CLI(): bool
	{
		return hkm_is_cli();
	}
    public static function IS_VALID_IP(string $ip = null, string $which = null): bool
	{
		// return (new FormatRules())::VALID_IP($ip, $which);
		return true;
    }
    
    public static function GET_METHOD(bool $upper = false): string
	{
		return ($upper) ? strtoupper(self::$method) : strtolower(self::$method);
    }
    public static function SET_METHOD(string $method)
	{
		self::$method = $method;

		return self::$thiss;
    }

	
    public static function WITH_METHOD($method)
	{
		$request = clone self::$thiss;

		$request::$method = $method;

		return $request;
    }
    public static function GET_URI()
	{
		return self::$uri;
	}
}
