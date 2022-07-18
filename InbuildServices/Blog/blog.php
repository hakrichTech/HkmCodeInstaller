<?php

use Hkm_services\Blog\BlogSystemProvider;
use Hkm_services\Blog\Entities\BlogEntities;
use Hkm_services\HkmHtml\Hkm_Html;

require_once __DIR__."/pobohet.php";

hkm_add_filter('on_plugin_migrations_dir',function($plugins__migrations){
    $plugins__migrations[] = __DIR__."/Migrations";
    return $plugins__migrations;
});

hkm_add_filter('on_extend_roots_system','Blog_routes_addon');

// hkm_add_filter('display_footer',function(){return false;},10,1);

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
// /**
//  * @var HkmUserInterface $current_user
//  */
// global $current_user;

// if (isset($_POST['update_profile'])) {
//     $current_user->setAvatar($file['url'])->update();
// }
});


function hkm_Authpost_blogs()
{
   $blogSystem = new BlogSystemProvider();
   $DATAS = $blogSystem->allBlogs();
   $blogs = BlogEntities::jsonReturn();

   Hkm_Html::INIT()
		->setElement('header','script_string',['script'=>" window.BlogsDatass = $blogs;"]);

   return $DATAS;
}
