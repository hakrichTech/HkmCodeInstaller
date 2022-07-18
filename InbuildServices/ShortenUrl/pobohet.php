<?php


function ShortenUrl_routes_addon($App_roots)
{
    $RoutesAddOn = [
        [
        'from' =>'file/(:segment)',
        'to' => 'ShortenUrlController',
        'type'=>'get',
        'app_name'=>APP_NAME,
        'uniq'=>'shortenUrl',
        'method' =>'view/$1',
        'options'=>['namespace' => "\Hkm_services\ShortenUrl\Controllers"]
        ],
        [
            'from' =>'short_rename/file/(:segment)/(:segment)',
            'to' => 'ShortenUrlController',
            'type'=>'get',
            'app_name'=>APP_NAME,
            'uniq'=>'shortenUrlRename',
            'method' =>'rename/$1/$2',
            'options'=>['namespace' => "\Hkm_services\ShortenUrl\Controllers"]
        ],
        [
            'from' =>'short_delete/file/(:segment)',
            'to' => 'ShortenUrlController',
            'type'=>'get',
            'app_name'=>APP_NAME,
            'uniq'=>'shortenUrlDelete',
            'method' =>'delete/$1',
            'options'=>['namespace' => "\Hkm_services\ShortenUrl\Controllers"]
        ],

        [
        'from' =>'upload/(:segment)',
        'to' => 'ShortenUrlController',
        'type'=>'post,put',
        'app_name'=>APP_NAME,
        'uniq'=>'file_upload',
        'method' =>'upload/$1',
        'options'=>['namespace' => "\Hkm_services\ShortenUrl\Controllers"]
        ],
        
    ];
    
    return array_merge($App_roots,$RoutesAddOn);	
}
