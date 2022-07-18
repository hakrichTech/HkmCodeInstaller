
    public static function {name}( bool $getShared = true)
    {
        if ($getShared)
		{
			return self::GET_SHARED_INSTANCE('{name}');
		}

		return new {class}();
    }
    // {addon Service}
