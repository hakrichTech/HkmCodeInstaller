<?php
namespace Hkm_code\HTTP;

class Header 
{
	protected static $name;
	protected static $value;
	protected static $thiss;
    public function __construct(string $name, $value = null)
	{
		self::$name = $name;
        self::SET_VALUE($value);
        self::$thiss = $this;
    }
    public static function GET_NAME(): string
	{
		return self::$name;
    }
    public static function SET_NAME(string $name)
	{
		self::$name = $name;

		return self::$thiss;
    }

	public static function GET_VALUE()
	{
		return self::$value;
	}
    
    public static function SET_VALUE($value = null)
	{
		self::$value = $value ?? '';

		return self::$thiss;
    }
    public static function APPEND_VALUE($value = null)
	{
		if ($value === null)
		{
			return self::$thiss;
		}

		if (! is_array(self::$value))
		{
			self::$value = [self::$value];
		}

		if (! in_array($value, self::$value, true))
		{
			self::$value[] = $value;
		}

		return self::$thiss;
    }
    public static function PREPEND_VALUE($value = null)
	{
		if ($value === null)
		{
			return self::$thiss;
		}

		if (! is_array(self::$value))
		{
			self::$value = [self::$value];
		}

		array_unshift(self::$value, $value);

		return self::$thiss;
    }
    public static function GET_VALUE_LINE(): string
	{
		if (is_string(self::$value))
		{
			return self::$value;
		}
		if (! is_array(self::$value))
		{
			return '';
		}

		$options = [];

		foreach (self::$value as $key => $value)
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
    public function __toString(): string
	{
		return self::$name . ': ' . self::GET_VALUE_LINE();
	}
}
