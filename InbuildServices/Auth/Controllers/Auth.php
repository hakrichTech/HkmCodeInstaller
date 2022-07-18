<?php

namespace Hkm_services\Auth\Controllers;


use Hkm_code\PluginBaseController;
use Hkm_code\Vezirion\ServicesSystem;

class Auth extends PluginBaseController
{
    public static function INDEX()
	{
        if(hkm_is_loggedIn()){
            print "you alredy logged in";
            exit;
        }
        // print_r($_COOKIE);exit;

        $view = hkm_view('header',[]);

		$view .= hkm_view('Admin/auth',[]);
		
		$view .= hkm_view('footer',[]);
		return $view;

    }
    public static function RESET()
    {
        # code...
    }
    public static function LOGOUT()
    {
        
    }
}