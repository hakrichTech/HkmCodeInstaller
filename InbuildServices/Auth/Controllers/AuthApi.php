<?php

namespace Hkm_services\Auth\Controllers;

use Hkm_code\PluginBaseController;
use Hkm_code\Vezirion\ServicesSystem;
use Hkm_services\Auth\AuthProvider;
use Hkm_services\Auth\HkmRequestInterface;

class AuthApi extends PluginBaseController
{
    public static function INDEX()
	{
        if (hkm_doing_ajax()) {
            $action = self::$request::GET_GET_POST('act');
            $body = [];
            switch ($action) {
                case 'authentification':
                    // Array ( [username] => ddd@ff.com [password] => ddd [remember] => on ) 
                    $error = false;
                    Auth_LogIn([],false,$error);

                    if ($error) {
                        $body = $error;
                    }else{
                        $url = ServicesSystem::SESSION()->get('_hkm_previous_url');
                        $url = hkm_apply_filters('on_hkm_login_url_check',$url);
                        if($url == self::$data['url']."login" || $url == self::$data['url']."login2") $url = self::$data['url'];
                        $body = [
                            'error'=>false,
                            'url'=>$url??self::$data['url']
                        ];

                    }    
                    
                    break;
                case "verify_email":
                    $userEmail = ServicesSystem::SESSION()->get(md5('user_to_verif'));
                    
                    if (!empty($userEmail)) {
                        $auth = new AuthProvider();

                        $user =$auth->retrieveByEmail($userEmail);
                        $code = self::$request::GET_GET_POST('code');

                        if ($code) {
                           if($auth->checkRequest($user,VERIF_EMAIL)){
                                $curentCode = ServicesSystem::SESSION()->get(md5('user_to_verif_code'));
                                if ($code == $curentCode) {
                                    $request = $auth->getRequests($user,VERIF_EMAIL);
                                    if ($request instanceof HkmRequestInterface) {
                                        $token = $request->getToken();
                                        $auth->unvalidateToken($token);
                                        $request->delete();
                                        $auth->setVerified($user,'true');
                                        ServicesSystem::AUTHBASIC()->login($user,true);
                                        ServicesSystem::SESSION()->forget(md5('user_to_verif'));
                                        ServicesSystem::SESSION()->forget(md5('user_to_verif_code'));
                                        $url = ServicesSystem::SESSION()->get('_hkm_previous_url');
                                        $url = hkm_apply_filters('on_hkm_login_url_check',$url);
                                        if($url == self::$data['url']."login" || $url == self::$data['url']."login2") $url = self::$data['url'];
                                        $body = [
                                            'error'=>false,
                                            'url'=>$url
                                        ];
                                    }else{
                                        $body = [
                                            'error' => true,
                                            'message' => "notRequest"
                                            ];
                                    }
                                }else {
                                    $body = [
                                    'error' => true,
                                    'message' => "codeNot"
                                    ];
                                }
                           }else{
                              $body = [
                                'error' => true,
                                'message' => "none"
                                ];
                           }
                           
                           
                        }else{
                            if(is_null($auth->checkRequest($user,VERIF_EMAIL))){
                                $data = $auth->sendRequest($user,VERIF_EMAIL);
                                if (is_array($data)) {
                                    if(isset($data['code'])){
                                    ServicesSystem::SESSION()->set(md5('user_to_verif_code'),$data['code']);
                                    $body = [
                                        'error' => false
                                    ];
                                    }else{
                                    $body = $data;
                                    }
                                
                                }
                           }else{
                            $data = $auth->sendRequest($user,VERIF_EMAIL,true);
                            if (is_array($data)) {
                                if(isset($data['code'])){
                                ServicesSystem::SESSION()->set(md5('user_to_verif_code'),$data['code']);
                                $body = [
                                    'error' => false
                                ];
                                }else{
                                $body = $data;
                                }
                            
                            }
                           }
                        }
                       
                       
                    }
                    break;
                case 'reset_password_request':

					$error = false;
                    Auth_LogIn([],false,$error);

                    if ($error) {
                        $body = $error;
                    }else{
                        $body = [
                            'error'=>false,
                            'url'=>self::$data['url']."login"
                        ];

                    }  

                    break;
                
                default:
                    # code...
                    break;
            }
            
            if (hkm_is_json_request()) {

                
                return self::$response::SET_JSON($body);
            }else{
                return self::$response::SET_JSON($body);
            }
        }

    }


    public static function AUTH($action)
    {
        $body =[''];
        switch ($action) {
            case 'update':
                Auth_update($body);
                break;
            
            default:
                # code...
                break;
        }


        if (hkm_is_json_request()) {
            return self::$response::SET_JSON($body);
        }else{
            return self::$response::SET_JSON($body);
        }
    }
}