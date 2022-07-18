<?php
namespace Hkm_services\Auth;



abstract class Authorize
{

    public static $thiss;
    public static $config;

    public function __construct($config)
    {
        self::$thiss = $this;
        self::$config = $config;

    }

    
    abstract public static function AUTHORIZE($user, $request): bool;
}
