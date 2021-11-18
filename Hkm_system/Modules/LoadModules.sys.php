<?php


namespace Hkm_code\Modules;

class LoadModules extends Modules
{
    private static $modules = array();
    private static $systemModules = array();
    private static $systemFiles = array();
    public static $namspaces = ['system'=>[],'app'=>[]];


    public static function GET_SYSTEM_MODULES()
    {


        $modul = require_once SYSTEMROOTPATH."vendor/composer/autoload_psr4.php";
        $files = require_once SYSTEMROOTPATH."vendor/composer/autoload_files.php";
        self::$systemModules = array_merge(self::$systemModules,$modul);
        self::$systemFiles = array_merge(self::$systemFiles,$files);
        foreach (self::$systemModules as $key => $value) {
            self::$namspaces['system'][$key] = $value;

        }


    }

    public static function APP_NAMESPACE($refNumber)
    {
        $modul = array(); 
        if (is_file(SYSTEMROOTPATH."../".$refNumber."/vendor/composer/autoload_psr4.php")) {
            $modul = require_once SYSTEMROOTPATH."../".$refNumber."/vendor/composer/autoload_psr4.php"; 
         }
        return $modul;

    }

    public static function GET_SYSTEM_1($refNumber)
    {
        $modul = array(); 
        $dif = []; 
        

        if (is_file(SYSTEMROOTPATH."../".$refNumber."/vendor/composer/autoload_psr4.php")) {
           $modul = require_once SYSTEMROOTPATH."../".$refNumber."/vendor/composer/autoload_psr4.php"; 
        }
        if(is_file( SYSTEMROOTPATH."../".$refNumber."/vendor/composer/autoload_files.php")){
            $files = require_once SYSTEMROOTPATH."../".$refNumber."/vendor/composer/autoload_files.php";
            $dif = array_diff($files,self::$systemFiles);
           self::$systemFiles = array_merge(self::$systemFiles,$dif);

        } 

        if (is_array($modul)) { 
          @$dif = array_diff($modul,self::$systemModules);
        }
        if (count($dif)>0) {
            foreach ($dif as $key => $value) {
                self::$namspaces['app'][$key] = $value;
    
            }
        }else self::$namspaces['app'] = $modul;
        
        self::$systemModules = array_merge(self::$systemModules,$modul);
    }



    public static function LENGTH_PSR_4()
    {
        foreach (self::$systemModules as $key => $value) {
            $data[$key] = strlen($key);

        }

       (array) self::$modulesLength_psr_4 = $data;
    }
   
    
    public static function CHECK_LENGTH($class)
    {
        foreach (self::$modulesLength_psr_4 as $key => $value) {
            $cl = $class;
            if($cl[0]=="\\"){
             $cl = substr($class,1);
            }
            if (strlen($cl) >= $value) {
                $f = \stripos($cl,$key);
                if($f !== false){
                    if (is_file(self::$systemModules[$key][0]."/".implode("/",explode("\\",substr($cl,$value))).".php")) {
                        self::$modules[$key]=self::$systemModules[$key][0]."/".implode("/",explode("\\",substr($cl,$value))).".php";
                    }else{
                      if (is_file(self::$systemModules[$key][0]."/".implode("/",explode("\\",substr($cl,$value))).".sys.php")) {
                        self::$modules[$key]=self::$systemModules[$key][0]."/".implode("/",explode("\\",substr($cl,$value))).".sys.php";
                      }else{
                          die("2No such file or Directory: ".self::$systemModules[$key][0]."/".implode("/",explode("\\",substr($cl,$value))));
                      }
                        
                    }
                }
            }
            
        }
    }

    public static function Load_modules(string $ref) 
    {
        self::GET_SYSTEM_MODULES();
        if ($ref!="SystemV2.0.1") {
           self::GET_SYSTEM_1($ref);   
        }
        self::LENGTH_PSR_4();

       
    }

    public static function Loader($class)
    {
        $fetch = explode("\\", $class);
        foreach (self::$systemFiles as $key => $value) {
            
            require_once $value;
        }
        if (strlen($fetch[0]) > 2) {
            if (array_key_exists($fetch[0]."\\",self::$systemModules)) {
                $fetch[0] = self::$systemModules[$fetch[0]."\\"][0];
                $file = implode("/",$fetch);
                if (is_file($file.".php")) {
                    require_once $file.".php";  
                }else{
                    if (is_file($file.".sys.php")) {
                       require_once $file.".sys.php";   
                    }else die('No such file : '.$file.".php");
                }
                
            }else{
                self::CHECK_LENGTH($class);
            }
        }else
        {
            if (array_key_exists($fetch[1]."\\",self::$systemModules)) {
                $fetch[1] = self::$systemModules[$fetch[1]."\\"][0];
                $file = implode("/",$fetch);
                if (is_file($file.".php")) {
                    require_once $file.".php";  
                }else{
                    if (is_file($file.".sys.php")) {
                        require_once $file.".sys.php";   
                     }else die('No such file or directory: '.$file);
                }
            }else{
                self::CHECK_LENGTH($class);
            }
        }
        
        if (count(self::$modules)) {
            foreach (self::$modules as $key => $value) {
                require_once $value;
            }
        }
        
    }

}
