<?php

function Admin_addon_roots($App_roots)
{
    $AdminRoutesAddOn = [
        [
        'from' =>'admin/(:segment)',
        'to' => 'Admin',
        'type'=>'get',
        'app_name'=>APP_NAME,
        'uniq'=>'admins',
        'method' =>'Dashboard/$1',
        'options'=>['namespace' => "\Hkm_services\Admin\Controllers","filter"=>"login"]
        ]
        

    ];
    return array_merge($App_roots,$AdminRoutesAddOn);
}