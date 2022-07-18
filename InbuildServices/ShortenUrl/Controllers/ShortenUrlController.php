<?php

namespace Hkm_services\ShortenUrl\Controllers;

use Hkm_code\Files\GD;
use Hkm_code\PluginBaseController;
use Hkm_services\ShortenUrl\File;
use Hkm_services\ShortenUrl\Models\FileModel;
use Hkm_services\ShortenUrl\FileStoreProvider;

class ShortenUrlController extends PluginBaseController
{


    public static function VIEW($shortenUrl=null)
    {
        ob_end_clean();
        ob_end_clean();
        if (!is_null($shortenUrl)) {
			
             $flSize = explode("_",$shortenUrl);
             if(count($flSize)>1){
                 $dim = (int) $flSize[count($flSize) - 1];
                 
             }


		    $result = ShortenUrl_get($flSize[0]);
            if($result === false){
                $fileStoreProvider = new FileStoreProvider();
                $file =  $fileStoreProvider->retrieveByname($flSize[0]);
                if(count($file)>0) $result = ShortenUrl_get($file[0]->getFilePath());
                else{
                  if(isset($dim)) unset($dim);
                  $file =  $fileStoreProvider->retrieveByname($shortenUrl);
                  if(count($file)>0) $result = ShortenUrl_get($file[0]->getFilePath());
                  else $result = false;
                }
            }


            
				if (is_array($result)) {
                    $re = $result[0];
                    $ty = (object) $result[1];

                    $images = ['image','image/png','image/jpeg'];
                    if(in_array($ty->type,$images)){
                        if (filter_var($re->full_url, FILTER_VALIDATE_URL)) {
                            if (isset($dim)) {
                                $thumbnailGD = new GD($re->full_url);
                                $thumbnailGD::RESIZE_PERCENT($dim);
                                $im = $thumbnailGD::GET_OLD_IMAGE();
                            }else{
                                $im = imagecreatefromjpeg($re->full_url);
                            }
                                  
                            // View the loaded image in browser using imagejpeg() function
                            header('Content-type: image/jpg');  
                            imagejpeg($im);
                            imagedestroy($im);
                           exit;

                        }else{
                            if (isset($dim)) {
                                $thumbnailGD = new GD($re->full_url);
                                $thumbnailGD::RESIZE_PERCENT($dim);
                                $im = $thumbnailGD::GET_OLD_IMAGE();
                            }else{
                                $im = @imagecreatefromjpeg($re->full_url);
                                if (!$im) {
                                    $im = @imagecreatefrompng($re->full_url);
                                }
                            }

                                  
                            // View the loaded image in browser using imagejpeg() function
                            header('Content-type: image/jpg');  
                            imagejpeg($im);
                            imagedestroy($im);
                            exit;
                        }
                    }else{
                        switch ($ty->type) {
                            case "min":
                               if (filter_var($re->full_url, FILTER_VALIDATE_URL)) {
                               
                                   header('Content-type: image/png');
                                   echo file_get_contents($re->full_url);
                                   exit;
        
                                }else{
                                   header('Content-type: image/png');
                                   echo file_get_contents($re->full_url);
                                   exit;
                                } 
                               break;
                           
                           default:
                               return self::$response::DOWNLOAD($re->full_url,null,false,$shortenUrl);
   
                               break;
                       }
                    }
                   
                }else{
                    echo "Error: File not found!";
                    exit;
                }
				
			
		}
    }

    public static function RENAME($old,$new)
    {

        if(ShortenUrl_rename($old,$new)){
            return self::$response::SET_JSON(['status'=>'ok']);
        }
        return self::$response::SET_JSON(['status'=>false]);

    }

    public static function DELETE($file)
    {

        if(ShortenUrl_delete($file)){
            return self::$response::SET_JSON(['status'=>'ok']);
        }
        return self::$response::SET_JSON(['status'=>false]);

    }
   
    public static function UPLOAD($type)
    {
        if (hkm_is_loggedIn()) {
            $fileStore = new FileStoreProvider();

            switch ($type) {
                case 'image':
                    $validationRule = [
                        'upload' => [
                            'label' => 'Image File',
                            'rules' => 'uploaded[upload]'
                                . '|is_image[upload]'
                                . '|mime_in[upload,image/jpg,image/jpeg,image/gif,image/png,image/webp]'
                                . '|max_size[upload,10000]',
                                // . '|max_dims[file,1024,768]',
                                'errors' => [
                                    'uploaded' => 'Please check the file. It does not appear to be uploaded file.',
                                    'is_image' => 'Please check the file. It does not appear to be an image.',
                                    'mime_in' => 'Please check the file. It does not appear to be an image.',
                                    'max_size' => 'The file is too big in size to upload on the server.',
                                ]
                        ],
                    ];
    
                    if(!hkm_validate_v3($validationRule,[],$validator)){
                        $errors = $validator::GET_ERRORS();
                        $er = " ";
    
                        foreach ($errors['upload'] as $key => $value) {
                            $er .=$value."\n";
                        }
    
                        return self::$response::SET_JSON(["uploaded"=>0,
                                                  "error"=>["message"=>$er]]);
                    }else{
                        $images = $fileStore->upload('image','upload');
                        if (isset($images['url'])) {
                            $response = [
                                "uploaded"=> 1,
                                "fileName" => "File Uploaded",
                                "url" => "/file/".$images['url']
                                ];
                                $response['file']=$images['fileid'];
                                hkm_do_action('on_file_uploaded_done',$response);
    
                                return self::$response::SET_JSON($response);
                        }else {
                            $response = [
                                "uploaded"=>0,
                                "error"=>[
                                    "message"=>"Error uploading a file from the server!"
                                   ]
                                ];
                            return self::$response::SET_JSON($response);
                        }
                    }
                    break;
                case 'files':
                    $validationRule = [
                        'upload' => [
                            'label' => 'File',
                            'rules' => 'uploaded[upload]'
                                . '|max_size[upload,10000]',
                                // . '|max_dims[file,1024,768]',
                                'errors' => [
                                    'uploaded' => 'Please check the file. It does not appear to be uploaded file.',
                                    'max_size' => 'The file is too big in size to upload on the server.',
                                ]
                        ],
                    ];
                    if(!hkm_validate_v3($validationRule,[],$validator)){
                        $errors = $validator::GET_ERRORS();
                        $er = " ";
    
                        if(is_array($errors['upload'])){
                            foreach ($errors['upload'] as $key => $value) {
                                $er .=$value."\n";
                            }
                        }else $er =  $errors['upload'];
                        
    
                        return self::$response::SET_JSON(["uploaded"=>0,
                                                  "error"=>["message"=>$er]]);
                    }else{
                        $ty = "unknown";
                        if(isset($_POST['fileType'])) $ty = $_POST['fileType'];
                        $file = $fileStore->upload($ty,'upload');
                        if (isset($file['url'])) {
                            $response = [
                                "uploaded"=> 1,
                                "fileName" => "File Uploaded",
                                "filereturned" => File::arrayReturn()
                                ];
                                $response['file']=$file['fileid'];
                                hkm_do_action('on_file_uploaded_done',$response);
    
                                return self::$response::SET_JSON($response);
                        }else {
                            $response = [
                                "uploaded"=>0,
                                "error"=>[
                                    "message"=>"Error uploading a file from the server!"
                                   ]
                                ];
                            return self::$response::SET_JSON($response);
                        }
                    }
                    break;
                default:
                return self::$response::SET_JSON(["uploaded"=>0,
                            "error"=>["message"=>"Type of file error!"]]);
                    break;
            }

            
        }else{

			return self::$response::SET_JSON(["uploaded"=>0,
                                              "error"=>["message"=>"Token error!"],
                                              "file"=>$_FILES
                                            ]);
        }
    }

}