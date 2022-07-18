<@php

function _routes_addon()
{
	global $Hkm_routesAddOn;
        $RoutesAddOn = [
            // [
            // 'from' =>'dashbord/(:segment)',
            // 'to' => 'Home',
            // 'type'=>'get',
            // 'app_name'=>APP_NAME,
            // 'uniq'=>'dashbord',
            // 'method' =>'dashbord/$1',
            // 'options'=>['filter'=>"login"]
            // ],
            
        ];

        $Hkm_routesAddOn = array_merge($Hkm_routesAddOn,$RoutesAddOn);
	
}
// Correction

hkm_add_config('_routes_addon');



