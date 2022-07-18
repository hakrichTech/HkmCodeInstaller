<?php

use Hkm_Config\YAMLParse\hkm_src\YAMLEmit;
use Hkm_Config\YAMLParse\hkm_src\YAMLParse;

function hkm_update_yaml($new_value)
{
    $key_ = [];
    if (is_array($new_value)) {
    foreach ($new_value as $key => $value) {
         $f = hkm_update_yaml($value);
         if(is_array($f)){
             foreach ($f as $valu) {
                 $key_[]=str_replace(".","_@_",$key).".".$valu;
             }
         }else $key_[]=str_replace(".","_@_",$key)."=".$f;
        
    }
    return $key_;

    }else{
        return str_replace(".","_@_",$new_value??"");
    }
}


function hkm_rewrite_update($array)
{ 
    $arr = [];
    if (is_array($array)) {
        foreach ($array as $value) {
            
            $d = explode(".",$value);
            $va = $d[count($d)-1];
            unset($d[count($d)-1]);
            $r = explode("=",$va);
            $c ="";
            $rtg="{";
             
            foreach ($d as $key) {
             $rtg .="\"".str_replace("_@_",".",$key)."\":{";
            $c .= "}";
            }
            $arr=array_merge_recursive(json_decode($rtg."\"".str_replace("_@_",".",$r[0])."\":\"".str_replace("_@_",".",$r[1])."\"}".$c,true)??[],$arr);

        }
        
    }
    
    

    return $arr;
}

function hkm_env_format($source,$mode = YAMLParse::YAML_FILE)
{
    $yaml = new YAMLParse();
    $settings = [];
    $data = hkm_update_yaml($yaml::GET($source,$mode));
    if (is_array($data)) {
        foreach ($data as $setting) {
            @list($name,$value) = explode('=',$setting);
            $settings[$name] = str_replace("_@_",".",$value)??null;
        }
    }

    return $settings;

}


function hkm_config_create($source,$data)
{
   $yaml = new YAMLEmit($source);
   return $yaml::CREATE($data)::WRITE();
}

function hkm_config_add($source,$data)
{
   $yaml = new YAMLEmit($source);
   return $yaml::ADD($data)::WRITE();
}