<?php

use Hkm_services\Auth\AuthProvider;
use Hkm_services\Auth\HkmUserInterface;

/**
 * @var HkmUserInterface $current_user
 */
global $current_user;
global $admin_test;


/**
 * @var AuthProvider $Authprovider
 */
global $Authprovider;

require_once __DIR__."/pobohet.php";


$admin_test = "hello";

$Authprovider = new AuthProvider();


hkm_add_filter('on_plugin_migrations_dir',function($plugins__migrations){
    $plugins__migrations[] = __DIR__."/Migrations";
    return $plugins__migrations;
});

hkm_add_filter('on_extend_roots_system','Admin_addon_roots');

hkm_add_filter('on_migrate_fetch_locals_result',function($migrations){
return $migrations;
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

hkm_add_action('on_file_uploaded_done',function ($file){
/**
 * @var HkmUserInterface $current_user
 */
global $current_user;

// if (isset($_POST['update_profile'])) {
//     $current_user->setAvatar($file['url'])->update();
// }

});