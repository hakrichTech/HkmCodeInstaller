<@php

namespace {namespace};

use Hkm_Config\Hkm_Bin\Services;

class RegesterAsService extends Services
{
    public $helpers = [];
    public $name = "{name}";

    public static function {name}( bool $getShared = true)
    {
        if ($getShared)
		{
			return self::GET_SHARED_INSTANCE('{name}');
		}

		return new {class}();
    }
    // {addon Service}
    // do not edit or modify the line above
}
