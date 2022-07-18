<?php

hkm_add_action('says_hello', function ($nam){
},10,1);
hkm_add_filter('check_mesg',function ($msg){
 return $msg." _ yes";
});
hkm_add_action('error_plugin_migration_header',function (){});
hkm_add_action('on_file_uploaded_done',function (){});

hkm_add_action('on_all_migrate_migrated',function (){});
hkm_add_action('error_migration_class_not_found',function ($migration,$mesage){},10,2);
hkm_add_action('error_migration_method_not_found',function ($migration,$method,$message){},10,3);
hkm_add_filter('on_migrate_fetch_locals_result',function ($migrations){return $migrations;});
hkm_add_filter('on_migrate_rollback',function ($migrations){return $migrations;});
hkm_add_filter('on_migrate_rollbacked',function ($migrations){return $migrations;});
hkm_add_filter('on_migrate_migrate',function ($migrations){return $migrations;});
hkm_add_filter('on_migrate_migrated',function ($migrations){return $migrations;});
hkm_add_filter('on_plugin_migrations_dir',function ($plugins__migrations){return $plugins__migrations;});



hkm_add_filter('on_redirect_url',function ($url){return $url;});


hkm_add_action('on_auth_login',function (){});
hkm_add_action('on_auth_logout',function (){});


// auth.attempt
// auth.login

hkm_add_filter('on_extend_roots_system',function ($roots){return $roots;});
hkm_add_filter('get_app_info',function ($app){return $app;});
hkm_add_filter('on_fetch_result_as_object',function ($row){
    $row->jsonSync();
    return $row;
});
hkm_add_filter('on_extended_view_system',function ($view){return $view;});
