<?php
namespace Hkm_traits\AddOn;

use Hkm_code\HTTP\Header;

/**
 * 
 */
trait MessageTrait
{
	protected static $headers = [];
    protected static $headerMap = [];
    
    public static function SET_BODY($data): self
	{
		self::$body = $data;


		return self::$thiss;
    }
    public static function APPEND_BODY($data): self
	{
		self::$body .= (string) $data;

		return self::$thiss;
    }
    public static function POPULATE_HEADERS(): void
	{
		$contentType = $_SERVER['CONTENT_TYPE'] ?? getenv('CONTENT_TYPE');
		if (! empty($contentType))
		{
			self::SET_HEADER('Content-Type', $contentType);
		} 
		unset($contentType);

		foreach (array_keys($_SERVER) as $key)
		{
			if (sscanf($key, 'HTTP_%s', $header) === 1)
			{
				// take SOME_HEADER and turn it into Some-Header
				$header = str_replace('_', ' ', strtolower($header));
				$header = str_replace(' ', '-', ucwords($header));

				self::SET_HEADER($header, $_SERVER[$key]);

				// Add us to the header map so we can find them case-insensitively
				self::$headerMap[strtolower($header)] = $header;
			}

		}
    }
    
    public static function HEADERS(): array
	{
		// If no headers are defined, but the user is
		// requesting it, then it's likely they want
		// it to be populated so do that...

		if (empty(self::$headers))
		{
			self::POPULATE_HEADERS();
		}


		return self::$headers;
    }
    
    public static function HEADER($name)
	{
		$origName = self::GET_HEADER_NAME($name);

		return self::$headers[$origName] ?? null;
    }
    public static function SET_HEADER(string $name, $value): self
	{
		$origName = self::GET_HEADER_NAME($name);

		if (isset(self::$headers[$origName]) && is_array(self::$headers[$origName]))
		{
			if (! is_array($value))
			{
				$value = [$value];
			}

			foreach ($value as $v)
			{
				self::APPEND_HEADER($origName, $v);
			}
		}
		else
		{
			
			self::$headers[$origName]               = $value;
			self::$headerMap[strtolower($origName)] = $origName;
		}

		return self::$thiss;
    }
    public static function REMOVE_HEADER(string $name): self
	{
		$origName = self::GET_HEADER_NAME($name);

		unset(self::$headers[$origName]);
		unset(self::$headerMap[strtolower($name)]);

		return self::$thiss;
    }
    public static function APPEND_HEADER(string $name, ?string $value): self
	{
		$origName = self::GET_HEADER_NAME($name);

		if(array_key_exists($origName, self::$headers)){
			


			if ($value === null)
			{
				self::$headers[$origName]='';
			}
	
			if (! is_array(self::$headers[$origName]))
			{
				self::$headers[$origName] = [self::$headers[$origName]];
			}
	
			if (! in_array($value, self::$headers[$origName], true))
			{
				self::$headers[$origName][] = $value;
			}


		}else{

			self::SET_HEADER($name, $value);

		}
			

		return self::$thiss;
    }
    
    public static function PREPEND_HEADER(string $name, string $value): self
	{
		$origName = self::GET_HEADER_NAME($name);

		if ($value === null)
		{
			self::$headers[$origName]='';
		}

		if (! is_array(self::$headers[$origName]))
		{
			self::$headers[$origName] = [self::$headers[$origName]];
		}

		array_unshift(self::$headers[$origName], $value);

		return self::$thiss;
    }
    protected static function GET_HEADER_NAME(string $name): string
	{
		return self::$headerMap[strtolower($name)] ?? $name;
	}
	public static function SET_PROTOCOL_VERSION(string $version)
	{
		if (! is_numeric($version))
		{
			$version = substr($version, strpos($version, '/') + 1);
		}

		// Make sure that version is in the correct format
		$version = number_format((float) $version, 1);

		if (! in_array($version, self::$validProtocolVersions, true))
		{
              d($version);
			// throw HTTPException::forInvalidHTTPProtocol(implode(', ', self::$validProtocolVersions));
		}

		self::$protocolVersion = $version;

	}

    
}
