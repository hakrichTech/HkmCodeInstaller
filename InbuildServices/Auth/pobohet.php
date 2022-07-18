<?php

function Auth_addon_roots($App_roots)
{
    $AuthRoutesAddOn = [
        [
        'from' =>'reset_password',
        'to' => 'Auth',
        'type'=>'get',
        'app_name'=>APP_NAME,
        'uniq'=>'reset_password',
        'method' =>'reset',
        'options'=>['namespace' => "\Hkm_services\Auth\Controllers"]
        ],
        [
            'from' =>'login',
            'to' => 'Auth',
            'type'=>'get,post',
            'app_name'=>APP_NAME,
            'uniq'=>'login',
            'method' =>'index',
            'options'=>['namespace' => "\Hkm_services\Auth\Controllers"]
        ],
        [
            'from' =>'ajax/login',
            'to' => 'AuthApi',
            'type'=>'post',
            'app_name'=>APP_NAME,
            'uniq'=>'login',
            'method' =>'index',
            'options'=>['namespace' => "\Hkm_services\Auth\Controllers"]
        ],
        [
            'from' =>'ajax/auth/(:segment)',
            'to' => 'AuthApi',
            'type'=>'post',
            'app_name'=>APP_NAME,
            'uniq'=>'auth',
            'method' =>'auth/$1',
            'options'=>['namespace' => "\Hkm_services\Auth\Controllers"]
        ],
        [
            'from' =>'login2',
            'to' => 'AuthUpdate',
            'type'=>'get,post',
            'app_name'=>APP_NAME,
            'uniq'=>'login',
            'method' =>'index',
            'options'=>['namespace' => "\Hkm_services\Auth\Controllers"]
        ],
        [
            'from' =>'regester',
            'to' => 'Auth',
            'type'=>'get,post',
            'app_name'=>APP_NAME,
            'uniq'=>'signUp',
            'method' =>'SignUp',
            'options'=>['namespace' => "\Hkm_services\Auth\Controllers"]
        ],
        [
            'from' =>'check_log',
            'to' => 'Auth',
            'type'=>'post',
            'app_name'=>APP_NAME,
            'uniq'=>'checkLog',
            'method' =>'Check',
            'options'=>['namespace' => "\Hkm_services\Auth\Controllers"]
        ],
        [
            'from' =>'fb_check',
            'to' => 'Auth',
            'type'=>'get',
            'app_name'=>APP_NAME,
            'uniq'=>'fbc',
            'method' =>'fb',
            'options'=>['namespace' => "\Hkm_services\Auth\Controllers"]
        ],
        [
            'from' =>'gl_check',
            'to' => 'Auth',
            'type'=>'get',
            'app_name'=>APP_NAME,
            'uniq'=>'glc',
            'method' =>'google',
            'options'=>['namespace' => "\Hkm_services\Auth\Controllers"]
        ],
        [
            'from' =>'save_google',
            'to' => 'Auth',
            'type'=>'post',
            'app_name'=>APP_NAME,
            'uniq'=>'sglc',
            'method' =>'google_save',
            'options'=>['namespace' => "\Hkm_services\Auth\Controllers"]
        ],
        

    ];
    return array_merge($App_roots,$AuthRoutesAddOn);
}