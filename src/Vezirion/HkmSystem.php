<?php
use Hkm_code\Modules\LoadModules;

return function () {


$_ENV['PROJECT']= __APP__DIR__;
$modules = new LoadModules();
$modules::Load_modules(__APP__DIR__);

spl_autoload_register(array($modules, 'Loader'), true, true);
// spl_autoload_unregister(array($modules, 'Loader'));
return $modules::$namspaces;

};

?>