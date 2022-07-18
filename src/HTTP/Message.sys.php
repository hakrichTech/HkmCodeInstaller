<?php
namespace Hkm_code\HTTP;
use Hkm_traits\AddOn\MessageTrait;

class Message
{
	protected static $protocolVersion;
	protected static $thiss;

    protected static $validProtocolVersions = [
		'1.0',
		'1.1',
		'2.0',
    ];
	protected static $body;
	public function __construct()
	{
		self::$thiss = $this;
	}
	use MessageTrait;
    
    public static function GET_BODY()
	{
		return self::$body;
    }
    public static function GET_HEADERS(): array
	{
		return self::HEADERS();
    }
    public static function GET_HEADER(string $name)
	{
		return self::HEADER($name);
    }
    public static function HAS_HEADER(string $name): bool
	{
		$origName = self::GET_HEADER_NAME($name);

		return isset(self::$headers[$origName]);
    }
    public static function GET_HEADER_LINE(string $name): string
	{
		$origName = self::GET_HEADER_NAME($name);

		if (! array_key_exists($origName, self::$headers))
		{
			return '';
		}
        $v = self::$headers[$origName];
		if (is_string($v))
		{
			return $v;
		}
		if (! is_array($v))
		{
			return '';
		}

		$options = [];

		foreach ($v as $key => $value)
		{
			if (is_string($key) && ! is_array($value))
			{
				$options[] = $key . '=' . $value;
			}
			elseif (is_array($value))
			{
				$key       = key($value);
				$options[] = $key . '=' . $value[$key];
			}
			elseif (is_numeric($key))
			{
				$options[] = $value;
			}
		}

		return implode(', ', $options);
		
    }
    public static function GET_PROTOCOL_VERSION(): string
	{
		return self::$protocolVersion ?? '1.1';
	}
}

