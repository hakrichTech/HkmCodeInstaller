<?php


function Blog_routes_addon($App_roots)
{
    $BlogRoutesAddOn = [
        [
        'from' =>'hkm/blog/(:segment)/(:segment)',
        'to' => 'BlogController',
        'type'=>'get',
        'app_name'=>APP_NAME,
        'uniq'=>'hkm/blog/',
        'method' =>'view_blog/$1/$2',
        'options'=>['namespace' => "\Hkm_services\Blog\Controllers"]
        ],
        [
            'from' =>'api/v1/blog/(:segment)',
            'to' => 'BlogApi',
            'type'=>'get',
            'app_name'=>APP_NAME,
            'uniq'=>'api/blog/',
            'method' =>'api/$1',
            'options'=>['namespace' => "\Hkm_services\Blog\Controllers"]   
        ]
        ,
        [
            'from' =>'blog/create',
            'to' => 'BlogController',
            'type'=>'post',
            'app_name'=>APP_NAME,
            'uniq'=>'blog_create',
            'method' =>'create_blog',
            'options'=>['namespace' => "\Hkm_services\Blog\Controllers"]   
        ]
    ];

    return array_merge($App_roots,$BlogRoutesAddOn);	
}
