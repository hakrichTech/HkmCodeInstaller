<?php


namespace Hkm_code\Modules;

use Hkm_code\Exceptions\File\FileNotFoundException;

class LoadModules extends Modules
{
    private static $modules = array();
    private static $systemModules = array();
    public static $systemFiles = array();
    protected static $nmspaces = array();
    protected static $nmspaces2 = array();
    public static $Files = array();

    public static $namspaces = ['system'=>[],'app'=>[]];


    public static function GET_SYSTEM_MODULES()
    {


        $modul = require_once SYSTEMROOTPATH."vendor/composer/autoload_psr4.php";
        $files = [];
        if (is_file(SYSTEMROOTPATH."vendor/composer/autoload_files.php")) {
            $files = require_once SYSTEMROOTPATH."vendor/composer/autoload_files.php";
        }
        $namespaces = require_once SYSTEMROOTPATH."vendor/composer/autoload_namespaces.php";
        self::$nmspaces = array_merge(self::$nmspaces,$namespaces);
        self::$systemModules = array_merge(self::$systemModules,$modul,$namespaces);
        self::$systemFiles = array_merge(self::$systemFiles,$files);
        foreach (self::$systemModules as $key => $value) {
            self::$nmspaces2[] = array(count(explode("\\",$key))=>$key);
            self::$namspaces['system'][$key] = $value;

        } 


    }

    public static function APP_NAMESPACE($refNumber)
    { 
        $modul = array(); 
        if (is_file(CREATE_PATH.$refNumber."/vendor/composer/autoload_psr4.php")) {
            $modul = require_once CREATE_PATH.$refNumber."/vendor/composer/autoload_psr4.php"; 
         }
        return $modul;

    }

    public static function GET_SYSTEM_1($refNumber)
    {
        
        $modul = array(); 
        $namespaces = array(); 
        $dif = []; 
        

        if (is_file(CREATE_PATH.$refNumber."/vendor/composer/autoload_psr4.php")) {
           $modul = require_once CREATE_PATH.$refNumber."/vendor/composer/autoload_psr4.php"; 
           $namespaces = require_once CREATE_PATH.$refNumber."/vendor/composer/autoload_namespaces.php";
           $dif =[];
           if (!empty($namespace)) { 
            @$dif = array_diff($namespace,self::$nmspaces);
           }
           self::$nmspaces = array_merge(self::$nmspaces,$dif);


        }
        if(is_file( CREATE_PATH.$refNumber."/vendor/composer/autoload_files.php")){
            $files = [];
            if (is_file(CREATE_PATH.$refNumber."vendor/composer/autoload_files.php")) {
                $files = require_once CREATE_PATH.$refNumber."/vendor/composer/autoload_files.php";
            }
           self::$Files = $files;

        } 

        if (is_array($modul)) { 
          @$dif = array_diff($modul,$namespaces,self::$systemModules);
        }
        if (count($dif)>0) {
            foreach ($dif as $key => $value) {
                self::$nmspaces2[] = array(count(explode("\\",$key))=>$key);
                self::$namspaces['app'][$key] = $value;
    
            }
        }else self::$namspaces['app'] = array_merge($modul,$namespaces);
        
        self::$systemModules = array_merge(self::$systemModules,$modul,$namespaces);
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
        $f = false;
        foreach (self::$nmspaces as $key => $value) {
            $cl = ltrim($class,"\\");
            
                $f = \stripos($cl,$key);
                if($f !== false){
                    if (is_file($value[0]."/".implode("/",explode("\\",$class)).".php")) {
                        self::$modules[$key]=$value[0]."/".implode("/",explode("\\",$class)).".php";
                        $f = true;
                        break;
                    }else{
                      if (is_file($value[0]."/".implode("/",explode("\\",$class)).".sys.php")) {
                        self::$modules[$key]=$value[0]."/".implode("/",explode("\\",$class)).".sys.php";
                        $f = true;
                        break;
                      }
                        
                    }
                }
            
        }
        if(!$f){

            $st= self::$nmspaces2;
            $f = array_column($st,4);

            $f[]= 'Laminas\\Escaper\\';
            $file = false;
            foreach ($f as $value) {
                
                $nms = explode($value,$class);
                if(count($nms)>1){
                  $nms[0] = self::$systemModules[$value][0];
                  $file = str_replace("\\","/",implode("/",$nms));
                 
                };
            }
            if($file){
                if (is_file($file.".php")) {
                    require_once $file.".php";  
                }else{
                    if (is_file($file.".sys.php")) {
                       require_once $file.".sys.php";   
                    }else {

                        throw FileNotFoundException::FOR_FILE_NOT_FOUND($file.".php");
                        
                    }
                }
            }else{
                die("2No such file or Directory: ".$class);

            }
            
            
         }
    }

    public static function Load_modules(string $ref) 
    {
        $ref = explode(" ",$ref);
        self::GET_SYSTEM_MODULES();
        if ($ref[0]!="HkmCode") {
           self::GET_SYSTEM_1(implode(" ",$ref));
        }
        self::LENGTH_PSR_4();

       
    }

    public static function Loader($class)
    {
        
        $fetch = explode("\\", $class);
        foreach (self::$systemFiles as $key => $value) {
            require_once $value;
        }

        foreach (self::$Files as $key => $value) {
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
                    }else {

                        throw FileNotFoundException::FOR_FILE_NOT_FOUND($file.".php");
                        
                    }
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
                     }else {
                            throw FileNotFoundException::FOR_FILE_NOT_FOUND($file.".php");
                          
                     }
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
