<?php
namespace Hkm_traits\AddOn;
use Closure;

/**
 * 
 */
trait PobohetCollectionTrait
{
    protected static function SET_CONSTRAINT(string $placeholder)
	{
		self::$defaultPlaceholder = $placeholder;
    }
    public static function SET_NAMESPACE(string $value)
	{
		self::$defaultNamespace = ltrim(str_replace('APP_NAMESPACE',APP_NAMESPACE,$value),"\\");
    }
    public static function GET_404_OVERRIDE()
	{
		return self::$override404;
    }
    public static function SET_DEFAULT_CONSTRAINT(string $placeholder)
	{
		if (array_key_exists($placeholder, self::$placeholders))
		{
			self::$defaultPlaceholder = $placeholder;
		}

		return self::$thiss;
	}
	public static function GET_DEFAULT_NAMESPACE()
	{
		return self::$defaultNamespace;
	}
	public static function GET_ROUTES_OPTIONS(string $from = null, string $verb = null): array
	{
		$options = self::LOAD_ROUTES_OPTIONS($verb);

		return $from ? $options[$from] ?? [] : $options;
	}
	public static function IS_FILTERED(string $search, string $verb = null): bool
	{
		$options = self::LOAD_ROUTES_OPTIONS($verb);

		return isset($options[$search]['filter']);
	}
    public static function SHOULD_AUTO_ROUTE(): bool
	{
		return self::$autoRoute;
	}
	public static function SHOUD_TRANSLATE_URI_DASHES(): bool
	{
		return self::$translateURIDashes;
	}
	public static function GET_DEFAULT_CONTROLLER(): string
	{
		return self::$defaultController;
    }
    public static function GET_DEFAULT_METHOD(): string
	{
		return self::$defaultMethod;
	}
	public static function GET(string $from, $to, array $options = null)
	{
		self::CREATE('get', $from, $to, $options);

		return self::$thiss;
	}
	public static function POST(string $from, $to, array $options = null)
	{
		self::CREATE('post', $from, $to, $options);

		return self::$thiss;
	}
	public static function PUT(string $from, $to, array $options = null)
	{
		self::CREATE('put', $from, $to, $options);

		return self::$thiss;
	}
	public static function DELETE(string $from, $to, array $options = null)
	{
		self::CREATE('delete', $from, $to, $options);

		return self::$thiss;
	}
	public static function HEAD(string $from, $to, array $options = null)
	{
		self::CREATE('head', $from, $to, $options);

		return self::$thiss;
	}
	public static function PATCH(string $from, $to, array $options = null)
	{
		self::CREATE('patch', $from, $to, $options);

		return self::$thiss;
	}
	public static function OPTIONS(string $from, $to, array $options = null)
	{
		self::CREATE('options', $from, $to, $options);

		return self::$thiss;
	}
	public static function CLI(string $from, $to, array $options = null)
	{
		self::CREATE('cli', $from, $to, $options);

		return self::$thiss;
	}
    public static function GET_HTTP_VERB(): string
	{
		return self::$HTTPVerb;
	}
	public static function SET_HTTP_VERB(string $verb)
	{
		self::$HTTPVerb = $verb;

		return self::$thiss;
    }
    public static function ENVIRONMENT(string $env, Closure $callback)
	{
		if (ENVIRONMENT === $env)
		{
			$callback(self::$thiss);
		}

		return self::$thiss;
    }
    public static function SET_PRIORITIZE(bool $enabled = true)
	{
		self::$prioritize = $enabled;

		return self::$thiss;
	}
    public static function ADD_PLACEHOLDER($placeholder, string $pattern = null)
	{
		if (! is_array($placeholder))
		{
			$placeholder = [$placeholder => $pattern];
		}

		self::$placeholders = array_merge(self::$placeholders, $placeholder);

		return self::$thiss;
	}
    public static function SET_POBOHETS(array $value)
	{
		$arr = [];
		foreach ($value as $key => $valu) $arr[$key] = [];
		self::$routes = $arr;
	}
	public static function SET_HTTPMETHODS(array $value)
	{
		self::$defaultHTTPMethods = $value;
	}
    
    public static function SET_CONTROLLER(string $value)
	{
		self::$defaultController = htmlspecialchars($value);

		return self::$thiss;
	}
	public static function SET_PLACEHOLDERS($value = [])
	{
		if (is_array($value)) self::$placeholders = $value;
		else{
		 self::$placeholders = [];
		 hkm_log_message('info','Place holder value is not an Array');
		}
		
	}
    public static function SET_METHOD(string $value)
	{
		self::$defaultMethod = htmlspecialchars($value);

		return self::$thiss;
    }
    public static function SET_TRANSLATED_URI_DASH(bool $value)
	{
		self::$translateURIDashes = $value;

		return self::$thiss;
    }
    public static function SET_AUTO_ROUTE(bool $value)
	{
		self::$autoRoute = $value;

		return self::$thiss;
    }
    public static function SET_404_OVERRIDE($callable = null)
	{
		self::$override404 = $callable;

		return self::$thiss;
    }
}
