<?php

namespace Hkm_services\WebsiteManager\Controllers;

use Hkm_code\PluginBaseController;

class WebController extends PluginBaseController
{


    public static function UPDATE()
    {
      return hkm_view('header_web',[])
             .hkm_view('update_web',[]) 
             .hkm_view('pages/site_update',[])
             .hkm_view('footer_web',[]);
    }

}