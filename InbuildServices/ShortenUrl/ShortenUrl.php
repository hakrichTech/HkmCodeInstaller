<?php

use Hkm_services\ShortenUrl\FileStoreProvider;
use Hkm_services\ShortenUrl\Models\ShortenUrl;
use Hkm_services\ShortenUrl\Models\ShortenUrlType;
use Hkm_services\ShortenUrl\Models\FileModel as ModelsFileModel;
use Hkm_services\HkmHtml\Hkm_Html;
use Hkm_services\ShortenUrl\File;

require_once __DIR__."/pobohet.php";

hkm_add_filter('on_plugin_migrations_dir',function($plugins__migrations){
    $plugins__migrations[] = __DIR__."/Migrations";
    return $plugins__migrations;
});

hkm_add_filter('on_extend_roots_system','ShortenUrl_routes_addon');



    function ShortenUrl_insert($orginalUrl,$local=true)
    {
       
        if(!empty($orginalUrl)){
            $model = new ShortenUrl();
            $model::CHECK_ENGINE();
                        $a = true;
                        $ran_url = false;
            if ($local) {     
                while ($a) {
                    $ran_url = substr(md5(microtime()), rand(0, 26), 20);
                    $result = $model::FIND($ran_url);
                    if (is_null($result)) {
                        $id = $model::INSERT(['shorten_url'=>$ran_url,'full_url'=>$orginalUrl]);
                        $a = false;
                    }else $a = true;
                }

                if (!$a) {
                    return $ran_url;
                }else return false;
            }else{
                if (filter_var($orginalUrl, FILTER_VALIDATE_URL)) {
                    while ($a) {
                        $ran_url = substr(md5(microtime()), rand(0, 26), 20);
                        $result = $model::FIND($ran_url);
                        if (is_null($result)) {
                            $id = $model::INSERT(['shorten_url'=>$ran_url,'full_url'=>$orginalUrl]);
                            $a = false;
                        }else $a = true;
                    }
    
                    if (!$a) {
                        return $ran_url;
                    }else return false;
                }else return false;
            }
            
        }else return false;
    }

    function ShortenUrl_is_exists($orginalUrl){
        if(!empty($orginalUrl)){
            $model = new ShortenUrl();
            $model::CHECK_ENGINE();
            $f = $model::BUILDER()->where('full_url',$orginalUrl)->get()->getResult('array');
            if (empty($f)) return false;
            else true;
        }
        return true;
    }

    function chech_file_name($file_name)
    {
        $fileStoreProvider = new FileStoreProvider();
        $files = count($fileStoreProvider->retrieveByname($file_name));
        if($files>0) return true;
        else return false;
    }
    
    function ShortenUrl_get($ran_url)
    {
       
        if(!empty($ran_url)){
                $model = new ShortenUrl();
                $model::CHECK_ENGINE();
                $result = $model::FIND($ran_url);
                if (is_null($result)){
                  if (APP_NAME == 'XproV2') {
                      $mdl = new ModelsFileModel();
                      $mdl::CHECK_ENGINE();

                      $file = $mdl::FIND($ran_url);
                      if(empty($file)) return false;
                      return ShortenUrl_get($file['path']);
                  }
                 return false;

                }
                else {
                    $model = new ShortenUrlType();
                    $model::CHECK_ENGINE();
                    $type = $model::FIND($result->id);
                    if(empty($type)) return false;
                    return [$result,$type];
                }
        }else return false;
    }


  

    function ShortenUrl_update($orginalUrl,$shorten_url,$hidden_url)
    {
        if(!empty($shorten_url)){
            if(preg_match("/\//i", $shorten_url)){
                $explodeURL = explode('/', $shorten_url);
                $shortURL = end($explodeURL);

                if($shortURL != ""){
                    $model = new ShortenUrl();
                    $model::CHECK_ENGINE();
                    $result = $model::FIND($shortURL);

                    if (is_null($result)){
                        try {
                            $model::UPDATE($hidden_url,['shorten_url'=>$shortURL,'full_url'=>$orginalUrl]);
                            return "success";

                        } catch (\Throwable $th) {
                           return "Error - Failed to update link!";
                        }

                    }else return "The short url that you've entered already exist. Please enter another one!";

                }else return "Required - You have to enter short url!";
                
            }else return "Invalid URL - You can't edit domain name!";
            
        }else  return  "Error- You have to enter short url!";
        
    } 

    function ShortenUrl_rename($old,$new)
    {
        $fileStoreProvider = new FileStoreProvider();
        $file =  $fileStoreProvider->retrieveByname($new);
        if(count($file)>0){
            return false;
        }
        if (ShortenUrl_get($old)) {
            if (APP_NAME == 'XproV2') {
                $mdl = new ModelsFileModel();
                $file = $mdl::UPDATE($old,[
                    'name' => $new
                ]);
                return $file;
            }
        }
        return false;
    }
    function ShortenUrl_delete($file)
    {
        if ($fileOl = ShortenUrl_get($file)) {
            if (APP_NAME == 'XproV2') {
                $model = new ShortenUrlType();
                $model::CHECK_ENGINE();
                $r = $model::DELETE($fileOl[0]->id);

                $modelF = new ShortenUrl();
                $modelF::CHECK_ENGINE();
                $z = $modelF::DELETE($fileOl[0]->shorten_url);

                $mdl = new ModelsFileModel();
                $mdl::CHECK_ENGINE();

                $f = $mdl::DELETE($file);
                if ($r&&$z&&$f) {
                    return true;
                }
                return false;
            }
        }
        return false;
    }

    function ShortenUrl_select_by_type($type,$select=null)
    {
        $model = new ShortenUrl();
        $model::CHECK_ENGINE();
        $builder = $model::BUILDER();
        $builder->select($select??'*');
        return $builder->join('ShortenUrlType', "ShortenUrl.id = ShortenUrlType.shorten_url_id AND ShortenUrlType.type ='".$type."'")
        ->get()->getResult('array');
    }

    function ShortenUrl_generator($type, callable $callback,$local=true,$disp=false){
          $urls = $callback();
          $urlsWithDisp = [];
          if (!is_array($urls)) $urls = [$urls];
          foreach ($urls as $url) {
              if ($disp) {
                if (!ShortenUrl_is_exists($url[0])) {
                    $urlsWithDisp[]= [ShortenUrl_insert($url[0],$type,$local),$url[1]];      
                }
              }else{
                if (!ShortenUrl_is_exists($url)) {
                    $urlsWithDisp[]= ShortenUrl_insert($url,$type,$local);      
                }
              }
              
          }
          return $urlsWithDisp;
    }


     function hkm_Authpost_filestore()
    {
        $fileStore = new FileStoreProvider();
        $fileStore->retrieveAll();
		$dat = File::jsonReturn();
		$datLatest = File::jsonReturnLatest();
		$fileTypes = File::jsonFileTypes();
		$dates = File::jsonFilesDates();
		Hkm_Html::INIT()
		->setElement('header','script_string',['script'=>" window.SecondFileStoreDatass = $dat;"])
		->setElement('header','script_string',['script'=>" window.SecondFileStoreDataLatest = $datLatest;"])
		->setElement('header','script_string',['script'=>" window.FileStoreFileTypes = $fileTypes;"])
		->setElement('header','script_string',['script'=>" window.FileStoreFileDates = $dates;"])
		->setElement('footer','script',['src'=>"/assets/js/filestore.modal.plugin.js",'indexPage'=>true])
		->setElement('footer','script',['src'=>"/assets/js/authpost.module.js",'indexPage'=>true]);
    }
