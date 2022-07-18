<?php


function Web_routes_addon($App_roots)
{
    $RoutesAddOn = [
        [
        'from' =>'site',
        'to' => 'WebController',
        'type'=>'get',
        'app_name'=>APP_NAME,
        'uniq'=>'update1',
        'method' =>'update',
        'options'=>['namespace' => "\Hkm_services\WebsiteManager\Controllers"]
        ],
        
    ];
    
    return array_merge($App_roots,$RoutesAddOn);	
}
