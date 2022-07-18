<?php

use Hkm_code\Database\BaseBuilder;
use Hkm_services\Auth\HkmUserInterface;
use Hkm_services\WebsiteManager\HkmServer;
use Hkm_services\WebsiteManager\Models\InformationSchemaModel;
use Hkm_services\WebsiteManager\Models\DefaultInformationSchemaModel;

global $cookie_config;
global $auth_secure_cookie;
global $emailToVerif;
global $current_user;
global $userSession;
global $filter_login;
global $is_HKM_SERVER;

require_once __DIR__."/pobohet.php";

hkm_add_filter('on_extend_roots_system','Web_routes_addon');



$is_HKM_SERVER = true;
hkm_add_filter('on_plugin_migrations_dir',function($plugins__migrations){
    if (defined('SYSTEM')) $plugins__migrations[] = __DIR__."/Migrations";
    return $plugins__migrations;
});
hkm_add_filter('get_app_info',function($app){
    if (is_int($app)) {
        $HkmServer = new HkmServer([]);
        $app = $HkmServer::website_by_app_id($app)::get_app();
        return (object) $app;

    }
    return $app;
});

hkm_add_filter('on_extended_view_system',function($view){
    if (is_file($view)) {
        return $view;
     }
    $pluginView = __DIR__.'/Views/'.$view.".tpl.php";
    
     if (is_file($pluginView)) {
         return $pluginView;
     }
     return $view;
    });


function Hkm_server_initialise($app):HkmServer
{
    return new HkmServer($app);
}

function Hkm_server_check_column(string $column, string $table_name,string $database_name = 'Hkm_Server')
{
    /**
     * @var BaseBuilder $builder;
     */
    $mo = new InformationSchemaModel();
    $mo::CHECK_ENGINE();
    $builder = $mo::BUILDER();
    $columns = $builder->select('column_name as columns')
            ->where('table_schema',$database_name)
            ->where('table_name',$table_name)
            ->get()->getResultArray();
            array_shift($columns);
    
    return in_array($column,$columns['columns']);

}


function Hkm_default_check_column(string $column, string $table_name,string $database_name = 'Hkm_Server')
{
    /**
     * @var BaseBuilder $builder;
     */
    $mo = new DefaultInformationSchemaModel();
    $mo::CHECK_ENGINE();
    $builder = $mo::BUILDER();
    $columns = $builder->select('column_name as columns')
            ->where('table_schema',$database_name)
            ->where('table_name',$table_name)
            ->get()->getResultArray();
    $found = false;
    foreach ($columns as $value) {
        if($value['columns'] == $column){
          $found = true;
          break;
        }
    }
    return $found;

}

function Admin_initialise_login(HkmUserInterface &$user)
{

    $HkmServer = new HkmServer([]);
    global $currentAppDatabase,$engine;
    
    if ($engine != ".") {
        
        $admin = $HkmServer::set_database_group('system')::get_admin_data($user->getEmail(),null);
        if($admin) {
            $user->setApp([$HkmServer::get_app(),$admin['appId']]);
            $currentAppDatabase = $HkmServer::get_database();
            $user->setAdmin(true); 
            $user->setPermission(is_array($admin['role'])?$admin['role']:[]);


        }else{
            $user->setAdmin(false);   
        }
    }

}

