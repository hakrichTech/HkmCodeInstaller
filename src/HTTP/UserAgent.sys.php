<?php
namespace Hkm_code\HTTP;

use Hkm_code\Vezirion\BaseVezirion;

/**
 * Abstraction for an HTTP user agent
 */
class UserAgent
{
	/**
	 * Current user-agent
	 *
	 * @var string
	 */
	protected static $agent = '';

	/**
	 * Flag for if the user-agent belongs to a browser
	 *
	 * @var boolean
	 */
	protected static $isBrowser = false;

	/**
	 * Flag for if the user-agent is a robot
	 *
	 * @var boolean
	 */
	protected static $isRobot = false;

	/**
	 * Flag for if the user-agent is a mobile browser
	 *
	 * @var boolean
	 */
	protected static $isMobile = false;

	/**
	 * Holds the config file contents.
	 *
	 * @var UserAgents
	 */
	protected static $config;

	/**
	 * Current user-agent platform
	 *
	 * @var string
	 */
	protected static $platform = '';

	/**
	 * Current user-agent browser
	 *
	 * @var string
	 */
	protected static $browser = '';

	/**
	 * Current user-agent version
	 *
	 * @var string
	 */
	protected static $version = '';

	/**
	 * Current user-agent mobile name
	 *
	 * @var string
	 */
	protected static $mobile = '';

	/**
	 * Current user-agent robot name
	 *
	 * @var string
	 */
	protected static $robot = '';

	/**
	 * HTTP Referer
	 *
	 * @var mixed
	 */
	protected static $referrer;

	//--------------------------------------------------------------------

	/**
	 * Constructor
	 *
	 * Sets the User Agent and runs the compilation routine
	 *
	 * @param null|UserAgents $config
	 */
	public function __construct(BaseVezirion $config = null)
	{
		$this::$config = $config ?? hkm_config('UserAgents');

		if (isset($_SERVER['HTTP_USER_AGENT']))
		{
			$this::$agent = trim($_SERVER['HTTP_USER_AGENT']);
			self::COMPILE_DATA();
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Is Browser
	 *
	 * @param string $key
	 *
	 * @return boolean
	 */
	public static function IS_BROWSER(string $key = null): bool
	{
		if (! self::$isBrowser)
		{
			return false;
		}

		// No need to be specific, it's a browser
		if ($key === null)
		{
			return true;
		}

		// Check for a specific browser
		return (isset(self::$config::$browsers[$key]) && self::$browser === self::$config::$browsers[$key]);
	}

	//--------------------------------------------------------------------

	/**
	 * Is Robot
	 *
	 * @param string $key
	 *
	 * @return boolean
	 */
	public static function IS_ROBOT(string $key = null): bool
	{
		if (! self::$isRobot)
		{
			return false;
		}

		// No need to be specific, it's a robot
		if ($key === null)
		{
			return true;
		}

		// Check for a specific robot
		return (isset(self::$config::$robots[$key]) && self::$robot === self::$config::$robots[$key]);
	}

	//--------------------------------------------------------------------

	/**
	 * Is Mobile
	 *
	 * @param string $key
	 *
	 * @return boolean
	 */
	public static function IS_MOBILE(string $key = null): bool
	{
		if (! self::$isMobile)
		{
			return false;
		}

		// No need to be specific, it's a mobile
		if ($key === null)
		{
			return true;
		}

		// Check for a specific robot
		return (isset(self::$config::$mobiles[$key]) && self::$mobile === self::$config::$mobiles[$key]);
	}

	//--------------------------------------------------------------------

	/**
	 * Is this a referral from another site?
	 *
	 * @return boolean
	 */
	public static function IS_REFERRAL(): bool
	{
		if (! isset(self::$referrer))
		{
			if (empty($_SERVER['HTTP_REFERER']))
			{
				self::$referrer = false;
			}
			else
			{
				$refererHost = @parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST);
				$ownHost     = parse_url(\hkm_base_url(), PHP_URL_HOST);

				self::$referrer = ($refererHost && $refererHost !== $ownHost);
			}
		}

		return self::$referrer;
	}

	//--------------------------------------------------------------------

	/**
	 * Agent String
	 *
	 * @return string
	 */
	public static function GET_AGENT_STRING(): string
	{
		return self::$agent;
	}

	//--------------------------------------------------------------------

	/**
	 * Get Platform
	 *
	 * @return string
	 */
	public static function GET_PLATFORM(): string
	{
		return self::$platform;
	}

	//--------------------------------------------------------------------

	/**
	 * Get Browser Name
	 *
	 * @return string
	 */
	public static function GET_BROWSER(): string
	{
		return self::$browser;
	}

	//--------------------------------------------------------------------

	/**
	 * Get the Browser Version
	 *
	 * @return string
	 */
	public static function GET_VERSION(): string
	{
		return self::$version;
	}

	//--------------------------------------------------------------------

	/**
	 * Get The Robot Name
	 *
	 * @return string
	 */
	public static function GET_ROBOT(): string
	{
		return self::$robot;
	}

	//--------------------------------------------------------------------

	/**
	 * Get the Mobile Device
	 *
	 * @return string
	 */
	public static function GET_MOBILE(): string
	{
		return self::$mobile;
	}

	//--------------------------------------------------------------------

	/**
	 * Get the referrer
	 *
	 * @return string
	 */
	public static function GET_REFERRER(): string
	{
		return empty($_SERVER['HTTP_REFERER']) ? '' : trim($_SERVER['HTTP_REFERER']);
	}

	//--------------------------------------------------------------------

	/**
	 * Parse a custom user-agent string
	 *
	 * @param string $string
	 *
	 * @return void
	 */
	public static function PARSE(string $string)
	{
		// Reset values
		self::$isBrowser = false;
		self::$isRobot   = false;
		self::$isMobile  = false;
		self::$browser   = '';
		self::$version   = '';
		self::$mobile    = '';
		self::$robot     = '';

		// Set the new user-agent string and parse it, unless empty
		self::$agent = $string;

		if (! empty($string))
		{
			self::COMPILE_DATA();
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Compile the User Agent Data
	 *
	 * @return void
	 */
	protected static function COMPILE_DATA()
	{
		self::SET_PLATFORM();

		foreach (['set_Robot', 'set_Browser', 'set_Mobile'] as $function)
		{
			$method = strtoupper($function);
			if (self::$method() === true)
			{
				break;
			}
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Set the Platform
	 *
	 * @return boolean
	 */
	protected static function SET_PLATFORM(): bool
	{
		if (is_array(self::$config::$platforms) && self::$config::$platforms)
		{
			foreach (self::$config::$platforms as $key => $val)
			{
				if (preg_match('|' . preg_quote($key) . '|i', self::$agent))
				{
					self::$platform = $val;

					return true;
				}
			}
		}

		self::$platform = 'Unknown Platform';

		return false;
	}

	//--------------------------------------------------------------------

	/**
	 * Set the Browser
	 *
	 * @return boolean
	 */
	protected static function SET_BROWSER(): bool
	{
		if (is_array(self::$config::$browsers) && self::$config::$browsers)
		{
			foreach (self::$config::$browsers as $key => $val)
			{
				if (preg_match('|' . $key . '.*?([0-9\.]+)|i', self::$agent, $match))
				{
					self::$isBrowser = true;
					self::$version   = $match[1];
					self::$browser   = $val;
					self::SET_MOBILE();

					return true;
				}
			}
		}

		return false;
	}

	//--------------------------------------------------------------------

	/**
	 * Set the Robot
	 *
	 * @return boolean
	 */
	protected static function SET_ROBOT(): bool
	{
		if (is_array(self::$config::$robots) && self::$config::$robots)
		{
			foreach (self::$config::$robots as $key => $val)
			{
				if (preg_match('|' . preg_quote($key) . '|i', self::$agent))
				{
					self::$isRobot = true;
					self::$robot   = $val;
					self::SET_MOBILE();

					return true;
				}
			}
		}

		return false;
	}

	//--------------------------------------------------------------------

	/**
	 * Set the Mobile Device
	 *
	 * @return boolean
	 */
	protected static function SET_MOBILE(): bool
	{
		if (is_array(self::$config::$mobiles) && self::$config::$mobiles)
		{
			foreach (self::$config::$mobiles as $key => $val)
			{
				if (false !== (stripos(self::$agent, $key)))
				{
					self::$isMobile = true;
					self::$mobile   = $val;

					return true;
				}
			}
		}

		return false;
	}

	//--------------------------------------------------------------------

	/**
	 * Outputs the original Agent String when cast as a string.
	 *
	 * @return string
	 */
	public function __toString(): string
	{
		return self::GET_AGENT_STRING();
	}
}
