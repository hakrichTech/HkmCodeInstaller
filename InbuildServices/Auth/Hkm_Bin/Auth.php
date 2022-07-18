<?php
namespace Hkm_services\Auth\Hkm_Bin;


use Hkm_code\Vezirion\BaseVezirion;

class Auth extends BaseVezirion
{
 
     public static $MAX_LOGIN_ATTEMPTS_PER_HOUR = 5;
     public static $CSRF_TOKEN_SECRET = "<change me to something random>";
     public static $MAX_PASSWORD_RESET_REQUESTS_PER_DAY = 3;
     public static $PASSWORD_RESET_REQUEST_EXPIRY_TIME = 60*60; 

     public static $privateKeyPath = WRITEPATH."pv-k/";
     public static $fields = [
         'user_login'=>'username',
         'user_password'=>'password',
         'remember'=>'remember'
     ];

    public static $error = [
        'emptyOrNotString' => "No empty field is allowed!",
        'wrongPassword' => ' Invalid username, email address or incorrect password.',
        'retryAfterHour'=> "Wrong password and try again after 1 hour!",
        'accountNotVerified' =>"Your account is not verified!",
        'noAccountFound' => "No account found!",
        'maxRequest' => "You can not reset your password 3 times per day. please try again tomorrow!"
    ];

    public static $requestPasswordUser = "password";

    public static $passwordResetFields = [
        'id'=>'id',
        'error'=>[
          'failedToUpdate'=>"Failed to update password!",
          'requestExpired'=>"This reset request is expired",
          'hashNotMatch'=>"Invalid provided code!",
          'noRequest'=>"No request found",
          'tokenError'=>"Invalid CSRF token",
          "passwordRequiredMatch" => 'Password must have upper & lower letters + at least one number + at least one symbol and be 8 or more chars long',
          'passwordNotMatch'=>'Passwords do not match',
          'noHash' => "Empty Hash provided!",
          "noId"=> "No id provided!"

        ],
        'hash' => 'hash',
        'password' => 'password',
        'confirmPassword' => 'confirmPassword',
        'csrf_token'=>'csrf_token'
    ];
    public static $validationRulesFiles = [
        'paths' => []
    ];

    public static $validationRules = [
      'rules' => []
    ];

    public static $otpLength = 5;


}