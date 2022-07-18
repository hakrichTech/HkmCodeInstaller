<?php
namespace Hkm_services\WebsiteManager;
use Hkm_code\CLI\CLI;
use Hkm_services\WebsiteManager\Models\NewAppModel;
use Hkm_services\WebsiteManager\Models\AdminRolesModel;
use Hkm_services\WebsiteManager\Models\AdminsManagerModel;
use Hkm_services\WebsiteManager\Models\PluginsModel;

/**
 *
 */

 class HkmServer 
{
  public static $newApp = [];
  private static $thiss;
  private static $DBGroup;
  private static $is_cli;
  private static $app_id;
  private static $admin_id;
  private static $appData;
  private static $lang = "";
  public static $errorData = [];
  public static $error = false;
  private static $requiredData = ['newApp_url'=>'CLI.generator.className.AppUrl','newApp_databaseName'=>'CLI.generator.className.AppDatabase','newApp_databaseUser'=>'CLI.generator.className.AppDatabaseUser','newApp_databasePass'=>'CLI.generator.className.AppDatabasePass','newApp_Admin'=>'CLI.generator.className.AppUser','newApp_Admin_password'=>'CLI.generator.className.AppUserPass'];
       
  
  
  function __construct(array $new_app)
  {
    self::$is_cli = hkm_is_cli();
    self::$newApp = $new_app;
    self::$thiss = $this;
  }

  public static function set_database_group($group)
  {
    self::$DBGroup = $group;
    return self::$thiss;
  }

  public static function get_app()
  {
    return self::$appData;
  }
  public static function get_database()
  {
    if (!self::$error) {
      return [
        'hostname'=> 'localhost',
        "database"=> trim(self::$appData['appDatabase']),
        "username" => trim(self::$appData['appDbUser']),
        "password" => trim(self::$appData['appPassword']),
        'DBDriver' => 'MySQLi',
        'DBPrefix' => '',
        'pConnect' => false,
        'DBDebug'  => (ENVIRONMENT !== 'production'),
        'charset'  => 'utf8',
        'DBCollat' => 'utf8_general_ci',
        'swapPre'  => '',
        'encrypt'  => false,
        'compress' => false,
        'strictOn' => false,
        'failover' => [],
        'port'     => 3306,
      ];
    }else return [];
   
  }

  public static function set_language($lang)
  {
    self::$lang = $lang;
    return self::$thiss;
  }

  public static function new_app_url()
  {
    if (self::$is_cli) {
      self::$newApp['newApp_url'] = Cli::prompt(hkm_lang(self::$requiredData['newApp_url'],[],self::$lang ?self::$lang:"en"),null,
      "required|valid_url");

      CLI::newLine();
      CLI::write("[-] Generate Application start...!", 'green');
      CLI::newLine(0);
    }

    return self::$thiss;
  }


  public static function new_service_dir()
  {
    if (self::$is_cli) {
      self::$newApp['service_dir'] = self::$newApp['newApp_path'];

      CLI::write("[-] Generate Service file...!", 'green');
      CLI::newLine(0);
    }

    return self::$thiss;
  }
  public static function done_service()
  {
    $new_app = ['plugin_name'=>self::$newApp['service_namespace'],
    'plugin_path'=>self::$newApp['service_dir']];
    $found = Hkm_server_check_column('pludin_path','Plugin');
    if($found){
      $model = new PluginsModel();
      $model::$DBGroup = self::$DBGroup?self::$DBGroup:"system";

      if(empty($model::FIND(self::$newApp['service_namespace']))) $model::INSERT($new_app);
      else $model::UPDATE(self::$newApp['service_namespace'],$new_app);
    }
    return self::$thiss;
  }

  public static function new_service_namespace(string $namespace)
  {
    if (self::$is_cli) {
      self::$newApp['service_namespace'] = $namespace;
    }
    return self::$thiss;
  }

  public static function database_setting()
  {
    if (self::$is_cli) {
      CLI::write("[+] Generate Application done!", 'green');
      CLI::newLine(0);
      CLI::write("[-] Set Application Database!", 'green');
      CLI::newLine(0);

      self::$newApp['newApp_databaseName'] = Cli::prompt(hkm_lang(self::$requiredData['newApp_databaseName'],[],self::$lang ?self::$lang:"en"),null,
      "required");
      CLI::newLine(0);
      self::$newApp['newApp_databaseUser'] = Cli::prompt(hkm_lang(self::$requiredData['newApp_databaseUser'],[],self::$lang ?self::$lang:"en"),null,
      "required");
      CLI::newLine(0);
      self::$newApp['newApp_databasePass'] = Cli::prompt(hkm_lang(self::$requiredData['newApp_databasePass'],[],self::$lang ?self::$lang:"en"),null);
      CLI::newLine(0);
      
    }

    return self::$thiss;
  }

  public static function admin_user()
  {
    if (self::$is_cli) {
      CLI::write("[+] Generate Database setting done!", 'green');
      CLI::newLine(0);
      CLI::write("[-] Set Application Admin!", 'green');
      CLI::newLine(0);

      self::$newApp['newApp_Admin'] = Cli::prompt(hkm_lang(self::$requiredData['newApp_Admin'],[],self::$lang ?self::$lang:"en"),null,
      "required");
      CLI::newLine(0);
      self::$newApp['newApp_Admin_password'] = Cli::prompt(hkm_lang(self::$requiredData['newApp_Admin_password'],[],self::$lang ?self::$lang:"en"),null,
      "required");
      CLI::newLine(0);
      
    }
    return self::$thiss;
    
  }

 


  
  protected static function checking_app($appName=null,$app_url=null, $id=null)
  {
    $new = new NewAppModel();
    $builder= $new::BUILDER();
    if (!is_null($appName)) $builder->where('appName',$appName);
    if (!is_null($app_url)) $builder->where('appUrl',$app_url);
    if (!is_null($id)) $builder->where('id',$id);
    $user = $builder->get()
          ->getResult('array');
          if (count($user) > 0) {
            self::$appData = $user[0];
            return true;
          }else return false;
  }


  public static function build_app()
  {
    $new_app = ['appPath'=>self::$newApp['newApp_path'],'appName'=>self::$newApp['newApp_name'],'appUrl'=>self::$newApp['newApp_url'],'appDatabase'=>self::$newApp['newApp_databaseName'],'appDbUser' => self::$newApp['newApp_databaseUser'],'appPassword'=>self::$newApp['newApp_databasePass']];
    $appModel = new NewAppModel();
    $appModel::$DBGroup = self::$DBGroup?self::$DBGroup:"system";

    
    $check = self::checking_app($new_app['appName'],$new_app['appUrl']);
    if ($check) {
      self::$error = true;
      self::$errorData[]="App exist with the same name and url";
      # exit app exist with the same name and url
    }else{
      $app_id = $appModel::INSERT($new_app);
      if ($app_id) {
        self::$app_id = $app_id;
      }else {
        self::$error = true;
        self::$errorData[]="Error while creating app no connection to the server";
        # exit error while creating app no connection to the server
      }
    }
    return self::$thiss;

  }

  public static function admin_adding($user = null)
  {
    $admin = ['user_id'=>self::$newApp['newApp_Admin'],'app_id'=>self::$app_id];
    if (!is_null($user)) $admin['user_id'] = $user;
    
    $adminModel = new AdminsManagerModel();
    $adminModel::$DBGroup = self::$DBGroup?self::$DBGroup:"system";
    
    
    global $engine;

    if (self::$is_cli && $engine==".") $userData=true;
    else $userData = Auth_get_user($admin['user_id']);
    
    if ($userData) {
      if (self::$is_cli && $engine==".") null;
      else $admin['user_id'] = $userData->user_id;
      $admin_id = $adminModel::INSERT($admin);
      if ($admin_id) {
        self::$admin_id = $admin_id;
      }else{
        self::$error = true;
        self::$errorData[]="Error while creating app no connection to the server1";
        # exit
      }
    }else {
      self::$error = true;
      self::$errorData[]="No user with this email address";
      # exit no user with this email address
    }

    return self::$thiss;
  }
  public static function admin_role(int $role)
  {
    $admin_role = [
      'admin_id'=>self::$admin_id,
      'role_id'=>$role
    ];
    $adminRoleModel = new AdminRolesModel();
    $adminRoleModel::$DBGroup = self::$DBGroup?self::$DBGroup:"system";

    if ($role) {
      $id = $adminRoleModel::INSERT($admin_role);
      if ($id === false) {
        self::$error = true;
        self::$errorData[]="Error while creating app no connection to the server";
      }
    }

    return self::$thiss;

  }


  public static function done()
  {
    CLI::write("Application ready!", 'green');
		CLI::newLine(0);
    // return self::$thiss;
  }

  public static function website(string $url)
  {
      
    if (self::checking_app(null,$url)) {
      self::$error = false;
      self::$errorData = [];
    }else{
      self::$error = true;
      self::$errorData[] = "No appfound with this url!";
    }

    return self::$thiss;

  }

  public static function website_by_app_id(int $app)
  {
      
    if (self::checking_app(null,null,$app)) {
      self::$error = false;
      self::$errorData = [];
    }else{
      self::$error = true;
      self::$errorData[] = "No appfound with this id!";
    }

    return self::$thiss;

  }

  protected static function _admin_role($admin_role)
  {
    $adminRoleModel = new AdminRolesModel();
    $adminRoleModel::$DBGroup = self::$DBGroup?self::$DBGroup:"system";
    $r = $adminRoleModel::FIND($admin_role);

    if (is_array($r)&&count($r)>0) {
      if (hkm_isAssoc($r)) {
        return $r['role_id'];
      }else return $r[0]['role_id'];
    }else return false;
    
  }
  public static function get_admin_data( String $adminEmail,?String $url)
  {
        
    if (!is_null($url)) {
      self::website($url);
      if (!self::$error) {
        $adminModel = new AdminsManagerModel();
        $adminModel::$DBGroup = self::$DBGroup?self::$DBGroup:"system";
        $builder= $adminModel::BUILDER();
        $builder->where('user_id',$adminEmail);
        $builder->orWhere('admin_id',$adminEmail);
        $builder->where('app_id',self::$appData['id']);
        $user = $builder->get()
                ->getResult('array');   
                            
        if (count($user)>0) {
            $model = new NewAppModel();
            $model::$DBGroup = self::$DBGroup?self::$DBGroup:"system";
            $builder= $model::BUILDER();
            $builder->where('id',hkm_isAssoc($user)?$user['app_id']:$user[0]['app_id']);
            $app = $builder->get()
                    ->getResult('array');
            if(count($app)>0)self::$appData = hkm_isAssoc($app)?$app:$app[0];
            self::$error = false;
            self::$errorData = [];
          return [
            'user'=>$adminEmail,
            'appId'=> hkm_isAssoc($user)?$user['app_id']:$user[0]['app_id'],
            'role'=> hkm_isAssoc($user)?self::_admin_role($user['admin_id']):self::_admin_role($user[0]['admin_id']),
          ];
        }else {
          self::$error = true;
          self::$errorData = ['No Admin found with information!'];
          return false;
        }
      }else return false;
      
    }else {
      $adminModel = new AdminsManagerModel();
      $adminModel::$DBGroup = self::$DBGroup?self::$DBGroup:"system";
      $builder= $adminModel::BUILDER();
      $builder->where('user_id',$adminEmail);
      $builder->orWhere('admin_id',$adminEmail);
      $user = $builder->get()
              ->getResult('array');
      if (count($user)>0) {

        $model = new NewAppModel();
        $model::$DBGroup = self::$DBGroup?self::$DBGroup:"system";
        $builder= $model::BUILDER();
        $builder->where('id',hkm_isAssoc($user)?$user['app_id']:$user[0]['app_id']);
        $app = $builder->get()
                ->getResult('array');
        
        if(count($app)>0)self::$appData = hkm_isAssoc($app)?$app:$app[0];
        self::$error = false;
        self::$errorData = [];
        return [
          'user'=>hkm_isAssoc($user)?$user['admin_id']:$user[0]['admin_id'],
          'appId'=> hkm_isAssoc($user)?$user['app_id']:$user[0]['app_id'],
          'role'=> hkm_isAssoc($user)?self::_admin_role($user['admin_id']):self::_admin_role($user[0]['admin_id']),
        ];
      }else {
        self::$error = true;
        self::$errorData = ['No Admin found with information!'];
        return false;}
    }
  }
  
}