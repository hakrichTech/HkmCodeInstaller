<?php

/*
 | --------------------------------------------------------------------
 | App Namespace
 | --------------------------------------------------------------------
 |
 | This defines the default Namespace that is used throughout
 | hakrichteam to refer to the Application directory. Change
 | this constant to change the namespace that all application
 | classes should use.
 |
 | NOTE: changing this will require manually modifying the
 | existing namespaces of App\* namespaced-classes.
 */
defined('APP_NAMESPACE') || define('APP_NAMESPACE', 'App');

defined('APP_NAME') || define('APP_NAME', 'System');

/*
 | --------------------------------------------------------------------------
 | Composer Path
 | --------------------------------------------------------------------------
 |
 | The path that Composer's autoload file is expected to live. By default,
 | the vendor folder is in the Root directory, but you can customize that here.
 */
defined('COMPOSER_PATH') || define('COMPOSER_PATH', ROOTPATH . 'vendor/autoload.php');

/*
 |--------------------------------------------------------------------------
 | Timing Constants
 |--------------------------------------------------------------------------
 |
 | Provide simple ways to work with the myriad of PHP functions that
 | require information to be in seconds.
 */
defined('SECOND') || define('SECOND', 1);
defined('MINUTE') || define('MINUTE', 60);
defined('HOUR')   || define('HOUR', 3600);
defined('DAY')    || define('DAY', 86400);
defined('WEEK')   || define('WEEK', 604800);
defined('MONTH')  || define('MONTH', 2592000);
defined('YEAR')   || define('YEAR', 31536000);
defined('DECADE') || define('DECADE', 315360000);

define( 'MINUTE_IN_SECONDS', MINUTE );
define( 'HOUR_IN_SECONDS', 60 * MINUTE_IN_SECONDS );
define( 'DAY_IN_SECONDS', 24 * HOUR_IN_SECONDS );
define( 'WEEK_IN_SECONDS', 7 * DAY_IN_SECONDS );
define( 'MONTH_IN_SECONDS', 30 * DAY_IN_SECONDS );
define( 'YEAR_IN_SECONDS', 365 * DAY_IN_SECONDS );

/*
 | --------------------------------------------------------------------------
 | Exit Status Codes
 | --------------------------------------------------------------------------
 |
 | Used to indicate the conditions under which the script is exit()ing.
 | While there is no universal standard for error codes, there are some
 | broad conventions.  Three such conventions are mentioned below, for
 | those who wish to make use of them.  The hakrichteam defaults were
 | chosen for the least overlap with these conventions, while still
 | leaving room for others to be defined in future versions and user
 | applications.
 |
 | The three main conventions used for determining exit status codes
 | are as follows:
 |
 |    Standard C/C++ Hkm_code (stdlibc):
 |       http://www.gnu.org/software/libc/manual/html_node/Exit-Status.html
 |       (This link also contains other GNU-specific conventions)
 |    BSD sysexits.h:
 |       http://www.gsp.com/cgi-bin/man.cgi?section=3&topic=sysexits
 |    Bash scripting:
 |       http://tldp.org/LDP/abs/html/exitcodes.html
 |
 */
defined('EXIT_SUCCESS')        || define('EXIT_SUCCESS', 0); // no errors
defined('EXIT_ERROR')          || define('EXIT_ERROR', 1); // generic error
defined('EXIT_CONFIG')         || define('EXIT_CONFIG', 3); // configuration error
defined('EXIT_UNKNOWN_FILE')   || define('EXIT_UNKNOWN_FILE', 4); // file not found
defined('EXIT_UNKNOWN_CLASS')  || define('EXIT_UNKNOWN_CLASS', 5); // unknown class
defined('EXIT_UNKNOWN_METHOD') || define('EXIT_UNKNOWN_METHOD', 6); // unknown class member
defined('EXIT_USER_INPUT')     || define('EXIT_USER_INPUT', 7); // invalid user input
defined('EXIT_DATABASE')       || define('EXIT_DATABASE', 8); // database error
defined('EXIT__AUTO_MIN')      || define('EXIT__AUTO_MIN', 9); // lowest automatically-assigned error code
defined('EXIT__AUTO_MAX')      || define('EXIT__AUTO_MAX', 125); // highest automatically-assigned error code


define('AUTH_KEY',         'OiF5os+w[HJFy$DKhQv%Pe#V,M<e2#B?i:4%zHIzNp-o7R<DyI<z&Ea/Wz{l)8TN');
define('SECURE_AUTH_KEY',  'Q v#4dK& rC8@=u#|0ettCM)CLAWm36_6v|!8Vm3Up{ayLI {PBfj%Z7Kfc+pbq8');
define('LOGGED_IN_KEY',    '?+Q<=F$q0GXMF0YEMWs2z|@av h/%G-pUzY7>zL+g8UFAZu(IACs ?$ u?rb|H+V');
define('NONCE_KEY',        'bk-bqsM6M_X7W?vI-Jba,Xjz+&[TAaXS%y}AKxLx=7f5UbJpW:Z(h:;X&/{ .rv+');
define('AUTH_SALT',        'x<wi)|F?z;oS!i8`EEX@y9|:=~c@e%G7 _6O:fiYjxi=+my+${78<[,s( QO5P?P');
define('SECURE_AUTH_SALT', '2H~Q1$[@)UBOJ~{Ba684-A|W=!vN]:c%rP}M^SfXz+_0+;^?O|~MK,uQGz!lodZF');
define('LOGGED_IN_SALT',   'w>%/x0kZL5{k]qnM1/`85w41]B2.E|b}[,-).>D5J[BGNh+&iy0ZGrRU?uTobpiU');


define('NONCE_SALT',       'X:@_wa|u(CpFs!7-BD|z`5+;09<E*W?(p{Js|K2}d-3*pivbNdDXV(]:6K.-S!U,');
define('SECRET_KEY',       '&P39M/9Hcl]4*<^]v@eV=t0j:*Cr| (*,FSUFb7@wOSbIHcSvA<A&F:bIAp?=Az');
define('SECRET_SALT',       'P7*@2Kw|V?N4NCd >*59G4+`R+TN$NUKrxcV6O<1]PwVmF:@PbyOQx+53TsWRAq!');



define( 'KB_IN_BYTES', 1024 );
define( 'MB_IN_BYTES', 1024 * KB_IN_BYTES );
define( 'GB_IN_BYTES', 1024 * MB_IN_BYTES );
define( 'TB_IN_BYTES', 1024 * GB_IN_BYTES );

if ( ! defined( 'HKM_START_TIMESTAMP' ) ) {
    define( 'HKM_START_TIMESTAMP', microtime( true ) );
}

if ( ! defined( 'HKM_DEBUG_DISPLAY' ) ) {
    define( 'HKM_DEBUG_DISPLAY', false );
}


// Add define( 'HKM_DEBUG_LOG', true ); to enable error logging to wp-content/debug.log.
if ( ! defined( 'HKM_DEBUG_LOG' ) ) {
    define( 'HKM_DEBUG_LOG', false );
}

if ( ! defined( 'HKM_CACHE' ) ) {
    define( 'HKM_CACHE', false );
}

