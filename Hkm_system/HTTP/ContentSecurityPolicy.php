<?php

namespace Hkm_code\HTTP;


/**
 * Provides tools for working with the Content-Security-Policy header
 * to help defeat XSS attacks.
 *
 * @see http://www.w3.org/TR/CSP/
 * @see http://www.html5rocks.com/en/tutorials/security/content-security-policy/
 * @see http://content-security-policy.com/
 * @see https://www.owasp.org/index.php/Content_Security_Policy
 */
class ContentSecurityPolicy
{
	/**
	 * Used for security enforcement
	 *
	 * @var array|string
	 */
	protected static $baseURI = [];

	/**
	 * Used for security enforcement
	 *
	 * @var array|string
	 */
	protected static $childSrc = [];

	/**
	 * Used for security enforcement
	 *
	 * @var array
	 */
	protected static $connectSrc = [];

	/**
	 * Used for security enforcement
	 *
	 * @var array|string
	 */
	protected static $defaultSrc = [];

	/**
	 * Used for security enforcement
	 *
	 * @var array|string
	 */
	protected static $fontSrc = [];

	/**
	 * Used for security enforcement
	 *
	 * @var array|string
	 */
	protected static $formAction = [];

	/**
	 * Used for security enforcement
	 *
	 * @var array|string
	 */
	protected static $frameAncestors = [];

	/**
	 * Used for security enforcement
	 *
	 * @var array|string
	 */
	protected static $frameSrc = [];

	/**
	 * Used for security enforcement
	 *
	 * @var array|string
	 */
	protected static $imageSrc = [];

	/**
	 * Used for security enforcement
	 *
	 * @var array|string
	 */
	protected static $mediaSrc = [];

	/**
	 * Used for security enforcement
	 *
	 * @var array|string
	 */
	protected static $objectSrc = [];

	/**
	 * Used for security enforcement
	 *
	 * @var array|string
	 */
	protected static $pluginTypes = [];

	/**
	 * Used for security enforcement
	 *
	 * @var string
	 */
	protected static $reportURI;

	/**
	 * Used for security enforcement
	 *
	 * @var array|string
	 */
	protected static $sandbox = [];

	/**
	 * Used for security enforcement
	 *
	 * @var array|string
	 */
	protected static $scriptSrc = [];

	/**
	 * Used for security enforcement
	 *
	 * @var array|string
	 */
	protected static $styleSrc = [];

	/**
	 * Used for security enforcement
	 *
	 * @var array|string
	 */
	protected static $manifestSrc = [];

	/**
	 * Used for security enforcement
	 *
	 * @var boolean
	 */
	protected static $upgradeInsecureRequests = false;

	/**
	 * Used for security enforcement
	 *
	 * @var boolean
	 */
	protected static $reportOnly = false;

	/**
	 * Used for security enforcement
	 *
	 * @var array
	 */
	protected static $validSources = [
		'self',
		'none',
		'unsafe-inline',
		'unsafe-eval',
	];

	/**
	 * Used for security enforcement
	 *
	 * @var array
	 */
	protected static $nonces = [];

	/**
	 * An array of header info since we have
	 * to build ourself before passing to Response.
	 *
	 * @var array
	 */
	protected static $tempHeaders = [];

	/**
	 * An array of header info to build
	 * that should only be reported.
	 *
	 * @var array
	 */
	protected static $reportOnlyHeaders = [];
	protected static $thiss;

	/**
	 * Constructor.
	 *
	 * Stores our default values from the Config file.
	 *
	 * @param ContentSecurityPolicyConfig $config
	 */
	public  function __construct($config)
	{
		self::$thiss = $this;
		foreach (get_object_vars($config) as $setting => $value)
		{
			if (property_exists($this, $setting))
			{
				self::${$setting} = $value;
			}
		}
	}

	/**
	 * Compiles and sets the appropriate headers in the request.
	 *
	 * Should be called just prior to sending the response to the user agent.
	 *
	 * @param Response $response
	 *
	 * @return void
	 */
	public static  function FINALIZE(Response &$response)
	{
		self::GENERATE_NONCES($response);
		self::BUILD_HEADERS($response);
	}

	/**
	 * If TRUE, nothing will be restricted. Instead all violations will
	 * be reported to the reportURI for monitoring. This is useful when
	 * you are just starting to implement the policy, and will help
	 * determine what errors need to be addressed before you turn on
	 * all filtering.
	 *
	 * @param boolean $value
	 *
	 * @return $this
	 */
	public static  function REPORT_ONLY(bool $value = true)
	{
		self::$reportOnly = $value;

		return self::$thiss;
	}

	/**
	 * Adds a new base_uri value. Can be either a URI class or a simple string.
	 *
	 * base_uri restricts the URLs that can appear in a page’s <base> element.
	 *
	 * @see http://www.w3.org/TR/CSP/#directive-base-uri
	 *
	 * @param string|array $uri
	 * @param boolean|null $explicitReporting
	 *
	 * @return $this
	 */
	public static  function ADD_BASE_URI($uri, bool $explicitReporting = null)
	{
		self::ADD_OPTION($uri, 'baseURI', $explicitReporting ?? self::$reportOnly);

		return self::$thiss;
	}

	/**
	 * Adds a new valid endpoint for a form's action. Can be either
	 * a URI class or a simple string.
	 *
	 * child-src lists the URLs for workers and embedded frame contents.
	 * For example: child-src https://youtube.com would enable embedding
	 * videos from YouTube but not from other origins.
	 *
	 * @see http://www.w3.org/TR/CSP/#directive-child-src
	 *
	 * @param string|array $uri
	 * @param boolean|null $explicitReporting
	 *
	 * @return $this
	 */
	public static  function ADD_CHILD_SRC($uri, bool $explicitReporting = null)
	{
		self::ADD_OPTION($uri, 'childSrc', $explicitReporting ?? self::$reportOnly);

		return self::$thiss;
	}

	/**
	 * Adds a new valid endpoint for a form's action. Can be either
	 * a URI class or a simple string.
	 *
	 * connect-src limits the origins to which you can connect
	 * (via XHR, WebSockets, and EventSource).
	 *
	 * @see http://www.w3.org/TR/CSP/#directive-connect-src
	 *
	 * @param string|array $uri
	 * @param boolean|null $explicitReporting
	 *
	 * @return $this
	 */
	public static  function ADD_CONNECT_SRC($uri, bool $explicitReporting = null)
	{
		self::ADD_OPTION($uri, 'connectSrc', $explicitReporting ?? self::$reportOnly);

		return self::$thiss;
	}

	/**
	 * Adds a new valid endpoint for a form's action. Can be either
	 * a URI class or a simple string.
	 *
	 * default_src is the URI that is used for many of the settings when
	 * no other source has been set.
	 *
	 * @see http://www.w3.org/TR/CSP/#directive-default-src
	 *
	 * @param string|array $uri
	 * @param boolean|null $explicitReporting
	 *
	 * @return $this
	 */
	public static  function SET_DEFULT_SRC($uri, bool $explicitReporting = null)
	{
		self::$defaultSrc = [(string) $uri => $explicitReporting ?? self::$reportOnly];

		return self::$thiss;
	}

	/**
	 * Adds a new valid endpoint for a form's action. Can be either
	 * a URI class or a simple string.
	 *
	 * font-src specifies the origins that can serve web fonts.
	 *
	 * @see http://www.w3.org/TR/CSP/#directive-font-src
	 *
	 * @param string|array $uri
	 * @param boolean|null $explicitReporting
	 *
	 * @return $this
	 */
	public static  function ADD_FONT_SRC($uri, bool $explicitReporting = null)
	{
		self::ADD_OPTION($uri, 'fontSrc', $explicitReporting ?? self::$reportOnly);

		return self::$thiss;
	}

	/**
	 * Adds a new valid endpoint for a form's action. Can be either
	 * a URI class or a simple string.
	 *
	 * @see http://www.w3.org/TR/CSP/#directive-form-action
	 *
	 * @param string|array $uri
	 * @param boolean|null $explicitReporting
	 *
	 * @return $this
	 */
	public static  function ADD_FORM_ACTION($uri, bool $explicitReporting = null)
	{
		self::ADD_OPTION($uri, 'formAction', $explicitReporting ?? self::$reportOnly);

		return self::$thiss;
	}

	/**
	 * Adds a new resource that should allow embedding the resource using
	 * <frame>, <iframe>, <object>, <embed>, or <applet>
	 *
	 * @see http://www.w3.org/TR/CSP/#directive-frame-ancestors
	 *
	 * @param string|array $uri
	 * @param boolean|null $explicitReporting
	 *
	 * @return $this
	 */
	public static  function ADD_FRAME_ANCESTOR($uri, bool $explicitReporting = null)
	{
		self::ADD_OPTION($uri, 'frameAncestors', $explicitReporting ?? self::$reportOnly);

		return self::$thiss;
	}

	/**
	 * Adds a new valid endpoint for valid frame sources. Can be either
	 * a URI class or a simple string.
	 *
	 * @see http://www.w3.org/TR/CSP/#directive-frame-src
	 *
	 * @param string|array $uri
	 * @param boolean|null $explicitReporting
	 *
	 * @return $this
	 */
	public static  function ADD_FRAME_SRC($uri, bool $explicitReporting = null)
	{
		self::ADD_OPTION($uri, 'frameSrc', $explicitReporting ?? self::$reportOnly);

		return self::$thiss;
	}

	//--------------------------------------------------------------------

	/**
	 * Adds a new valid endpoint for valid image sources. Can be either
	 * a URI class or a simple string.
	 *
	 * @see http://www.w3.org/TR/CSP/#directive-img-src
	 *
	 * @param string|array $uri
	 * @param boolean|null $explicitReporting
	 *
	 * @return $this
	 */
	public static  function ADD_IMAGE_SRC($uri, bool $explicitReporting = null)
	{
		self::ADD_OPTION($uri, 'imageSrc', $explicitReporting ?? self::$reportOnly);

		return self::$thiss;
	}

	/**
	 * Adds a new valid endpoint for valid video and audio. Can be either
	 * a URI class or a simple string.
	 *
	 * @see http://www.w3.org/TR/CSP/#directive-media-src
	 *
	 * @param string|array $uri
	 * @param boolean|null $explicitReporting
	 *
	 * @return $this
	 */
	public static  function ADD_MEDIA_SRC($uri, bool $explicitReporting = null)
	{
		self::ADD_OPTION($uri, 'mediaSrc', $explicitReporting ?? self::$reportOnly);

		return self::$thiss;
	}

	/**
	 * Adds a new valid endpoint for manifest sources. Can be either
	 * a URI class or simple string.
	 *
	 * @see https://www.w3.org/TR/CSP/#directive-manifest-src
	 *
	 * @param string|array $uri
	 * @param boolean|null $explicitReporting
	 *
	 * @return $this
	 */
	public static  function ADD_MANIFEST_SRC($uri, bool $explicitReporting = null)
	{
		self::ADD_OPTION($uri, 'manifestSrc', $explicitReporting ?? self::$reportOnly);

		return self::$thiss;
	}

	/**
	 * Adds a new valid endpoint for Flash and other plugin sources. Can be either
	 * a URI class or a simple string.
	 *
	 * @see http://www.w3.org/TR/CSP/#directive-object-src
	 *
	 * @param string|array $uri
	 * @param boolean|null $explicitReporting
	 *
	 * @return $this
	 */
	public static  function ADD_OBJECT_SRC($uri, bool $explicitReporting = null)
	{
		self::ADD_OPTION($uri, 'objectSrc', $explicitReporting ?? self::$reportOnly);

		return self::$thiss;
	}

	/**
	 * Limits the types of plugins that can be used. Can be either
	 * a URI class or a simple string.
	 *
	 * @see http://www.w3.org/TR/CSP/#directive-plugin-types
	 *
	 * @param string|array $mime              One or more plugin mime types, separate by spaces
	 * @param boolean|null $explicitReporting
	 *
	 * @return $this
	 */
	public static  function ADD_PLUGIN_TYPE($mime, bool $explicitReporting = null)
	{
		self::ADD_OPTION($mime, 'pluginTypes', $explicitReporting ?? self::$reportOnly);

		return self::$thiss;
	}

	/**
	 * Specifies a URL where a browser will send reports when a content
	 * security policy is violated. Can be either a URI class or a simple string.
	 *
	 * @see http://www.w3.org/TR/CSP/#directive-report-uri
	 *
	 * @param string $uri
	 *
	 * @return $this
	 */
	public static  function SET_REPORT_URI(string $uri)
	{
		self::$reportURI = $uri;

		return self::$thiss;
	}

	/**
	 * specifies an HTML sandbox policy that the user agent applies to
	 * the protected static resource.
	 *
	 * @see http://www.w3.org/TR/CSP/#directive-sandbox
	 *
	 * @param string|array $flags             An array of sandbox flags that can be added to the directive.
	 * @param boolean|null $explicitReporting
	 *
	 * @return $this
	 */
	public static  function ADD_SANDBOX($flags, bool $explicitReporting = null)
	{
		self::ADD_OPTION($flags, 'sandbox', $explicitReporting ?? self::$reportOnly);
		return self::$thiss;
	}

	/**
	 * Adds a new valid endpoint for javascript file sources. Can be either
	 * a URI class or a simple string.
	 *
	 * @see http://www.w3.org/TR/CSP/#directive-connect-src
	 *
	 * @param string|array $uri
	 * @param boolean|null $explicitReporting
	 *
	 * @return $this
	 */
	public static  function ADD_SCRIPT_SRC($uri, bool $explicitReporting = null)
	{
		self::ADD_OPTION($uri, 'scriptSrc', $explicitReporting ?? self::$reportOnly);

		return self::$thiss;
	}

	/**
	 * Adds a new valid endpoint for CSS file sources. Can be either
	 * a URI class or a simple string.
	 *
	 * @see http://www.w3.org/TR/CSP/#directive-connect-src
	 *
	 * @param string|array $uri
	 * @param boolean|null $explicitReporting
	 *
	 * @return $this
	 */
	public static  function ADD_STYLE_SRC($uri, bool $explicitReporting = null)
	{
		self::ADD_OPTION($uri, 'styleSrc', $explicitReporting ?? self::$reportOnly);

		return self::$thiss;
	}

	/**
	 * Sets whether the user agents should rewrite URL schemes, changing
	 * HTTP to HTTPS.
	 *
	 * @param boolean $value
	 *
	 * @return $this
	 */
	public static  function UPGRADE_INSECURE_REQUESTS(bool $value = true)
	{
		self::$upgradeInsecureRequests = $value;

		return self::$thiss;
	}

	//-------------------------------------------------------------------------
	// Utility
	//-------------------------------------------------------------------------

	/**
	 * DRY method to add an string or array to a class property.
	 *
	 * @param string|array $options
	 * @param string       $target
	 * @param boolean|null $explicitReporting
	 *
	 * @return void
	 */
	protected static function ADD_OPTION($options, string $target, bool $explicitReporting = null)
	{
		// Ensure we have an array to work with...
		if (is_string(self::${$target}))
		{
			self::${$target} = [self::${$target}];
		}

		if (is_array($options))
		{
			foreach ($options as $opt)
			{
				self::${$target}[$opt] = $explicitReporting ?? self::$reportOnly;
			}
		}
		else
		{
			self::${$target}[$options] = $explicitReporting ?? self::$reportOnly;
		}
	}

	/**
	 * Scans the body of the request message and replaces any nonce
	 * placeholders with actual nonces, that we'll then add to our
	 * headers.
	 *
	 * @param Response $response
	 *
	 * @return void
	 */
	protected static function GENERATE_NONCES(Response &$response)
	{
		$body = $response::GET_BODY();

		if (empty($body))
		{
			return;
		}

		if (! is_array(self::$styleSrc))
		{
			self::$styleSrc = [self::$styleSrc];
		}

		if (! is_array(self::$scriptSrc))
		{
			self::$scriptSrc = [self::$scriptSrc];
		}

		// Replace style placeholders with nonces
		$body = preg_replace_callback('/{csp-style-nonce}/', function ($matches) {
			$nonce = bin2hex(random_bytes(12));

			self::$styleSrc[] = 'nonce-' . $nonce;

			return "nonce=\"{$nonce}\"";
		}, $body);

		// Replace script placeholders with nonces
		$body = preg_replace_callback('/{csp-script-nonce}/', function ($matches) {
			$nonce = bin2hex(random_bytes(12));

			self::$scriptSrc[] = 'nonce-' . $nonce;

			return "nonce=\"{$nonce}\"";
		}, $body);

		$response::SET_BODY($body);
	}

	/**
	 * Based on the current state of the elements, will add the appropriate
	 * Content-Security-Policy and Content-Security-Policy-Report-Only headers
	 * with their values to the response object.
	 *
	 * @param Response $response
	 *
	 * @return void
	 */
	protected static function BUILD_HEADERS(Response &$response)
	{
		/**
		 * Ensure both headers are available and arrays...
		 *
		 * @var Response $response
		 */
		$response::SET_HEADER('Content-Security-Policy', []);
		$response::SET_HEADER('Content-Security-Policy-Report-Only', []);

		$directives = [
			'base-uri'        => 'baseURI',
			'child-src'       => 'childSrc',
			'connect-src'     => 'connectSrc',
			'default-src'     => 'defaultSrc',
			'font-src'        => 'fontSrc',
			'form-action'     => 'formAction',
			'frame-ancestors' => 'frameAncestors',
			'frame-src'       => 'frameSrc',
			'img-src'         => 'imageSrc',
			'media-src'       => 'mediaSrc',
			'object-src'      => 'objectSrc',
			'plugin-types'    => 'pluginTypes',
			'script-src'      => 'scriptSrc',
			'style-src'       => 'styleSrc',
			'manifest-src'    => 'manifestSrc',
			'sandbox'         => 'sandbox',
			'report-uri'      => 'reportURI',
		];

		// inject default base & default URIs if needed
		if (empty(self::$baseURI))
		{
			self::$baseURI = 'self';
		}

		if (empty(self::$defaultSrc))
		{
			self::$defaultSrc = 'self';
		}

		foreach ($directives as $name => $property)
		{
			if (! empty(self::${$property}))
			{
				self::ADD_TO_HEADER($name, self::${$property});
			}
		}

		// Compile our own header strings here since if we just
		// append it to the response, it will be joined with
		// commas, not semi-colons as we need.
		if (! empty(self::$tempHeaders))
		{
			$header = '';

			foreach (self::$tempHeaders as $name => $value)
			{
				$header .= " {$name} {$value};";
			}

			// add token only if needed
			if (self::$upgradeInsecureRequests)
			{
				$header .= ' upgrade-insecure-requests;';
			}

			$response::APPEND_HEADER('Content-Security-Policy', $header);
		}

		if (! empty(self::$reportOnlyHeaders))
		{
			$header = '';

			foreach (self::$reportOnlyHeaders as $name => $value)
			{
				$header .= " {$name} {$value};";
			}

			$response::APPEND_HEADER('Content-Security-Policy-Report-Only', $header);
		}

		self::$tempHeaders       = [];
		self::$reportOnlyHeaders = [];
	}

	/**
	 * Adds a directive and it's options to the appropriate header. The $values
	 * array might have options that are geared toward either the regular or the
	 * reportOnly header, since it's viable to have both simultaneously.
	 *
	 * @param string            $name
	 * @param array|string|null $values
	 *
	 * @return void
	 */
	protected static function ADD_TO_HEADER(string $name, $values = null)
	{
		if (is_string($values))
		{
			$values = [$values => 0];
		}

		$sources       = [];
		$reportSources = [];

		foreach ($values as $value => $reportOnly)
		{
			if (is_numeric($value) && is_string($reportOnly) && ! empty($reportOnly))
			{
				$value      = $reportOnly;
				$reportOnly = 0;
			}

			if ($reportOnly === true)
			{
				$reportSources[] = in_array($value, self::$validSources, true) ? "'{$value}'" : $value;
			}
			elseif (strpos($value, 'nonce-') === 0)
			{
				$sources[] = "'{$value}'";
			}
			else
				{
					$sources[] = in_array($value, self::$validSources, true) ? "'{$value}'" : $value;
			}
		}

		if (! empty($sources))
		{
			self::$tempHeaders[$name] = implode(' ', $sources);
		}

		if (! empty($reportSources))
		{
			self::$reportOnlyHeaders[$name] = implode(' ', $reportSources);
		}
	}
}
