<?php
namespace Hkm_traits;

/**
 *
 */
 use \Hkm_addIn\AddInObject\Object_ as ob;

class TraitManager extends ob
{

  private static $error=0;
  private static $app;
  private static $trait=array();
  private static $traitPath=array();

  function __construct($dir)
  {
    self::$app = $this;
    $a = self::CHECK_DIR($dir,"trait");
    if ($a) {
      self::$error = 0;
    }else {
      self::$error = 3;
    }
  }


  public static function CHECK_DIR($x,$array="")
  {
    $dir = __DIR__.'/../../../../'.$x;
    if (is_dir($dir)) {
      if ($array == "trait") {
        self::INCLUDE_G_DIR($dir);
        return 1;
      }
    }
    return 0;
  }

  protected static function INCLUDE_G_DIR($x){
      $files = scandir($x);

      foreach ($files as $file) {
       if ($file!='.' && $file!="..") {
         self::$traitPath[]=$x.'/'.$file;
       }
     }
    }

public static function RUN()
{
   if (self::$error == 0) {
     foreach (self::$traitPath as $trait) {
       $content = file_get_contents($trait);
       $array = explode("/", $trait);
       $name = $array[count($array)-1];
       $file = __DIR__."/AddOn/".$name;
       fopen($file,"w+");
       if (file_put_contents($file,$content)) {
        unlink($trait);
       }
       
     }

     return 1;
   }
   return 0;
}

}



 ?>
