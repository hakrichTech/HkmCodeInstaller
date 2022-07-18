<?php

use Hkm_code\Vezirion\ServicesSystem;
use Hkm_services\Auth\AuthProvider;
use Hkm_Config\config_system;
use Hkm_services\Auth\HkmUserInterface;

global $cookie_config;
global $auth_config;
global $auth_secure_cookie;
global $emailToVerif;

/**
 * @var HkmUserInterface $current_user
 */
global $current_user;

global $userSession;

/**
 * @var AuthProvider $Authprovider
 */
global $Authprovider;

$cookie_config = hkm_config('Cookie');

$filter_login = false;

define('RESET_PASSWORD_REQUEST',100);
define('RESET_PASSWORD_CHANGE',101);
define('VERIF_EMAIL',102);
define('OTP_REQUEST_ACTION','verif_account');
define('OTP_RESEND_ACTION','resent_otp');
define('OTP_VERIFY_ACTION','verfy_otp');
define('CHANGE_PASSWORD_ACTION','nwpswd');

require_once __DIR__."/pobohet.php";

$auth_config = hkm_config('Hkm_services\Auth~Auth');


$Authprovider = new AuthProvider();

$userSession = hkm_config('App')::$sessionCookieName."_user";


hkm_add_filter('on_plugin_migrations_dir',function($plugins__migrations){
        $plugins__migrations[] = __DIR__."/Migrations";
        return $plugins__migrations;
});

hkm_add_filter('on_extend_roots_system','Auth_addon_roots');
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
		 return $pluginView;
	 }
	 return $view;
});

hkm_add_action('on_file_uploaded_done',function ($file){
	/**
	 * @var HkmUserInterface $current_user
	 */
	global $current_user;

    if (isset($_POST['update_profile'])) {
		$current_user->setAvatar($file['url'])->update();
    }
});

function Auth_base64UrlEncode($text)
{
    return str_replace(
        ['+', '/', '='],
        ['-', '_', ''],
        base64_encode($text)
    );
}

function Auth_config_private_key($id = null)
{



    $privateKeyPath = hkm_config('Hkm_services\Auth~Auth')::$privateKeyPath;
    if (!is_dir($privateKeyPath)) {
        mkdir($privateKeyPath, 0777, true);
    }
    $fileCreated = true;
    if (!is_file($privateKeyPath . "secrets.auth.yaml")) {
        hkm_helper('filesystem');
        $fileCreated = hkm_write_file(
            $privateKeyPath . "secrets.auth.yaml","# Example Environment Configuration file"
        );
    }

    if ($fileCreated) {
        $config = new config_system($privateKeyPath, 'secrets.auth.yaml');
        $config::LOAD();
        if (!is_null($id)) {
            if (isset($_ENV[md5($id)])) return $_ENV[md5($id)];
            else {
                $secret = bin2hex(random_bytes(64));
                $data = [
                    md5($id) => $secret
                ];
                hkm_helper('yaml');
                if (hkm_config_add($privateKeyPath . 'secrets.auth.yaml', $data)) return $secret;
                else return false;
            }
        } else {
            if (isset($_ENV['public_secret_key'])) return $_ENV['public_secret_key'];
            else {
                $secret = bin2hex(random_bytes(64));
                $data = [
                    'public_secret_key' => $secret
                ];
                hkm_helper('yaml');
                if (hkm_config_create($privateKeyPath . 'secrets.auth.yaml', $data)) return $secret;
                else return false;
            }
        }
    } else return false;
}


function Auth_Request($credentials, &$errors){
    $config = hkm_config('Hkm_services\Auth~Auth');

	if ( empty( $credentials )) {
		$credentials = array(); // Back-compat for plugins passing an empty string.
        
        $data = hkm_HttpPostData();
        foreach ($data as $key => $value) {
            if ($value == null || $value == "") {
                $data[$key] = " ";
            }
        }
        
        $checkValidation = hkm_validate($data,$config);
        if(!$checkValidation){
            $credentials['login'] = $data[$config::$fields['user_login']];
            
        }else{
             $errors = $checkValidation::GET_ERRORS();
			 return ;
        }
		$auth = new AuthProvider();
		$user = $auth->retrieveByEmail($credentials['login']);
		if(!is_null($user)){
			
				$auth->deleteAttemps($user);
				if($user->isVerified())$auth->setVerified($user,'false');
					
				ServicesSystem::SESSION()->set(md5('user_to_verif'),$user->getEmail());
				$em = explode('@',$user->getEmail());
				$em[0] = $em[0][0]."*********".$em[0][strlen($em[0])-1];
				$email = implode('@',$em); 
				$user = [
					'message' => "verif",
					'error' => true,
					'email' => $email,
					'render' => 'login'
				];
		}

		$user = [
			'message' => 'No user with this account!',
			'error' => true,
			'render' => 'login'
		];

		$errors = $user;
		return ;

	}
}

function Auth_LogIn( $credentials, $secure_cookie, &$errors) {
    $config = hkm_config('Hkm_services\Auth~Auth');

	if ( empty( $credentials )) {
		$credentials = array(); // Back-compat for plugins passing an empty string.
        
        $data = hkm_HttpPostData();
        foreach ($data as $key => $value) {
            if ($value == null || $value == "") {
                $data[$key] = " ";
            }
        }
        
        $checkValidation = hkm_validate($data,$config);
        if(!$checkValidation){
            $credentials['login'] = $data[$config::$fields['user_login']];
            $credentials['password'] = $data[$config::$fields['user_password']];
            $credentials['remember'] = $data[$config::$fields['remember']];
            
        }else{
             $errors = $checkValidation::GET_ERRORS();
			 return ;
        }
        
        

	}

	if ( ! empty( $credentials['remember'] ) ) {
		if ($credentials['remember']=="off") {
			$credentials['remember'] = false;
		}else{
	    	$credentials['remember'] = true;
		}
	} else {
		$credentials['remember'] = false;
	}
 
	
	if ( '' === $secure_cookie ) {
		$secure_cookie = hkm_is_ssl();
	}

	$secure_cookie = hkm_apply_filters( 'secure_signon_cookie', $secure_cookie, $credentials );


	$user = Auth_authenticate( $credentials['login']??'', $credentials['password']??'',$credentials['remember']);
	

	
    if ($user instanceof HkmUserInterface) {
		return $user;
	}

	if ( isset($user['error'])) {
		$errors = $user;
		return;
	}

	return $user;
}



 /**
  * @return HkmUserInterface|null
  */
function Auth_get_user($username):HkmUserInterface
{
	global $Authprovider;
	return $Authprovider->retrieveByUsernameOrEmail($username);
}

/**
 * @return array|null
 */
function Auth_get_user_by_phone($phone):array
{
	global $Authprovider;
	$array = $Authprovider->retrieveByPhone($phone);
    return $array;
}

function hkm_is_loggedIn()
{
	$auth = ServicesSystem::AUTHBASIC();
	return $auth->check();
}

if ( ! function_exists( 'Auth_authenticate' ) ) :
	
	/**
	 * @return HkmUserInterface|null|array
	 */
	function Auth_authenticate( $username, $password , $remember = false)
	 {
		$username = Auth_sanitize_user( $username );
		$password = trim( $password );
        global $auth_config, $Authprovider,$current_user;


		$user = Auth_authenticate_cookie(null, $username, $password );

		if ( null == $user ) {

			
			if (empty($username) && empty($password)) {

					ServicesSystem::SESSION()->put('_hkm_previous_url',(string) ServicesSystem::REQUEST()::GET_URI());
					ServicesSystem::SESSION()->save();
					hkm_redirect()::TO('/login')::PRETEND(false)::SEND();
			}

			$user = $Authprovider->retrieveByUsernameOrEmail($username);
			if (is_null($user)) {
				$user = [
					'message' => hkm_config('Hkm_services\Auth~Auth')::$error['wrongPassword'],
					'error' => true,
					'render' => 'login'
				];
			}
			else{
				$x = (int) $user->getAttemptNumber();
				

				if ($x < $auth_config::$MAX_LOGIN_ATTEMPTS_PER_HOUR) {
	
					$isCorrect = $Authprovider->validateCredentials($user,['password'=>$password]);
					if($isCorrect){
						$Authprovider->deleteAttemps($user);
						if($user->isVerified()){
							ServicesSystem::AUTHBASIC()->login($user,$remember);
							$current_user = $user;
						}
						else{
							ServicesSystem::SESSION()->set(md5('user_to_verif'),$user->getEmail());
							$em = explode('@',$user->getEmail());
							$em[0] = $em[0][0]."*********".$em[0][strlen($em[0])-1];
							$email = implode('@',$em); 
							$user = [
								'message' => "verif",
								'error' => true,
								'email' => $email,
								'render' => 'login'
							];
						}

					}else {
						$Authprovider->addAttemp($user);
						$user = [
							'message' => hkm_config('Hkm_services\Auth~Auth')::$error['wrongPassword'],
							'error' => true,
							'render' => 'login'
						];
					}
				}else{
					$user = [
						'message' => $auth_config::$error['retryAfterHour'],
						'error' => true,
						'render' => 'login'
					  ];

				}
			}

			}

		return $user;
	}
endif;

function Auth_authenticate_cookie( $user, $username, $password) {
	global $current_user;

	if ( $user instanceof HkmUserInterface ){
		$current_user = $user;
		return $user;
	}
	if ( empty( $username ) && empty( $password ) )$user = ServicesSystem::AUTHBASIC()->user();
    
	if(!is_null($user)) $current_user = $user;
	return $user;
}

if ( ! function_exists( 'Auth_is_user_logged_in' ) ) :
	function Auth_is_user_logged_in()
	{
		return  hkm_is_loggedIn();
	}
endif;


function Auth_update(&$return)
{
	if (hkm_is_loggedIn()) {
		
		global $current_user;
        
		$current_user->setUsername($_POST['username'])
		             ->setBirthdate($_POST['birthDate'])
					 ->setEmail($_POST['email'])
					 ->setUserfullname($_POST['fullname'])
					 ->setPhone($_POST['phone'])
					 ->setGender($_POST['gender']??'')
					 ->update();
		$current_user->getAddress()
		             ->setPostalcode($_POST['postalCode'])
					 ->setState($_POST['state'])
					 ->setAddress($_POST['address'])
					 ->setCountry($_POST['country'])
					 ->update();
		$return['error'] = false;
	}else{
		$return['error'] = true;
		$return['message'] = 'Session expired!';
	}
}

function Auth_sanitize_user( $username, $strict = false ) {
	// $raw_username = $username;

	$username     = hkm_strip_all_tags( $username );
	$username     = hkm_remove_accents( $username );
	// Kill octets.
	$username = preg_replace( '|%([a-fA-F0-9][a-fA-F0-9])|', '', $username );
	// Kill entities.
	$username = preg_replace( '/&.+?;/', '', $username );

	// If strict, reduce to ASCII for max portability.
	if ( $strict ) {
		$username = preg_replace( '|[^a-z0-9 _.\-@]|i', '', $username );
	}

	$username = trim( $username );
	// Consolidate contiguous whitespace.
	$username = preg_replace( '|\s+|', ' ', $username );

	/**
	 * Filters a sanitized username string.
	 *
	 *
	 * @param string $username     Sanitized username.
	 * @param string $raw_username The username prior to sanitization.
	 * @param bool   $strict       Whether to limit the sanitization to specific characters.
	 */
	
	// return hkm_apply_runtime_filters( 'sanitize_user', $username, $raw_username, $strict );
    return $username;
}



function Hkm_Authpost_user(array &$returnedArray= [])
{
	/**
		 * @var HkmUserInterface $current_user
		 */
		global $current_user;
		ServicesSystem::AUTHBASIC();
		
		if(!empty($returnedArray)){
			$email = $current_user->getEmail();
			$phone = $current_user->getPhone();
			$username = $current_user->getUsername();
			$fullname = $current_user->getFullname();
			$name =empty($fullname)? ucfirst($username):ucfirst($username)." - ".ucfirst($fullname);
			$avatar = $current_user->getProfile();
			$avatar = empty($avatar)?"/assets/img/avatarM.png":$avatar;
			$birthDate = $current_user->getBirthdate();
			$gender = $current_user->getGender();
			$gender = empty($gender)?"none":$gender;
	
			$phone = empty($phone)?'Not set':$phone;
	
			$returnedArray['name'] = $name;
			$returnedArray['email'] = $email;
			$returnedArray['username'] = $username;
			$returnedArray['fullname'] = $fullname;
			$returnedArray['avatar'] = $avatar;
			$returnedArray['phone'] = $phone;
			$returnedArray['gender'] = $gender;
			$returnedArray['birthDate'] = $birthDate;
	
			$address = $current_user->getAddress();
			$returnedArray['address'] = $address->getAddress();
			$returnedArray['state'] = $address->getState();
			$returnedArray['postalcode'] = $address->getPostalcode();
			$returnedArray['country'] = $address->getCountry();
		    $returnedArray['logged_user'] = $current_user;

		}

		


}

