<?php
namespace Hkm_traits\AddOn;

trait SingletonTrait
{

    protected static $instance = null;

    /**
     * Get instance of class.
     *
     * @access public
     * @static
     * @return object Return the instance class or create first instance of the class.
     */
    public static function GET_INSTANCE()
    {
        return (null === static::$instance) ? static::$instance = new static : static::$instance;
    }
    public static function GET_INSTANCE_0()
	{
		if (!is_object(self::$instance)) {
			self::$instance = new self();
		}
		return self::$instance;
	}

}