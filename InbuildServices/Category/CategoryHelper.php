<?php

use Hkm_services\Category\CategoryProvider;
use Hkm_services\Category\Entities\CategoryEntities;
use Hkm_services\HkmHtml\Hkm_Html;

hkm_add_filter('on_plugin_migrations_dir',function($plugins__migrations){
    $plugins__migrations[] = __DIR__."/Migrations";
    return $plugins__migrations;
});

hkm_add_filter('display_footer',function(){return false;},10,1);

hkm_add_filter('on_migrate_fetch_locals_result',function($migrations){
return $migrations;
});

hkm_add_filter('on_extended_view_system',function($view){
    if (is_file($view)) {
        return $view;
    }

    $pluginView = __DIR__.'/Views/'.$view.".tpl.php";
    if (is_file($pluginView)) {
        echo $pluginView."<br>";
        return $pluginView;
    }
    return $view;
});

function hkm_Authpost_category(){
    $categProvider = new CategoryProvider();
    $categProvider->allCategories();

    $dt = CategoryEntities::jsonReturn();
    Hkm_Html::INIT()
		->setElement('header','script_string',['script'=>" window.CategoriesDatass = $dt;"]);
};

