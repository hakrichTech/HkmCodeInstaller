<?php
namespace Hkm_Config\YAMLParse\hkm_src;

use Symfony\Component\Yaml\Yaml;


hkm_helper('yaml');

/**
 * Class YAMLEmit.
 */
class YAMLEmit
{
    const YAML_FILE = 1;
    const YAML_STREAM = 0;
    const YAML_URL = 2;
    /**
     * @var string $path_to_yaml_source
     */
    protected static $path_to_yaml_source;




    /**
     * @var int $parse_mode
     */
    protected static $parse_mode;


     /**
     * @var string $yaml_documents
     */
    protected static $yaml_updates;
    protected static $ar = null;

    /**
     * @var array $data_yaml_source
     */
    protected static $data_yaml_source;

      /**
     * @var array $data_yaml_file
     */
    protected static $data_yaml_file;


    /**
     * @var YAMLEmit $thiss
     */
    protected static $thiss;
    /**
     * Constructor.
     *
     * @param string $data_yaml_file     path of yaml source
     *
     */
    public function __construct($data_yaml_file)
    {
        self::$thiss = $this;

        self::SET_YAML_SOURCE($data_yaml_file);

        if (file_exists($data_yaml_file) === true) {
            $YAML = new YAMLParse();
            self::$data_yaml_file = $YAML::GET($data_yaml_file,YAMLParse::YAML_FILE);
        }else{
            self::$data_yaml_file = [];
        }


        if (self::$data_yaml_file === false) {
            throw new \Exception(sprintf('Unable to parse file yaml: %s', self::$data_yaml_file));
        }
    
    }


    public static function SET_YAML_SOURCE($source){
            
        if (!empty($source)) {
            $source = filter_var($source,FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            self::$path_to_yaml_source = $source;
        }

        return self::$thiss;

    }
    /**
     * method for change value in the yaml file.
     *
     * @param array $new_value
     *
     * @return $this
     */
    public static function UPDATE(array $new_value)
    {
        
        $rt = hkm_update_yaml($new_value);
        self::$data_yaml_file = array_replace_recursive(self::$data_yaml_file, hkm_rewrite_update($rt));

        return self::$thiss;
    }


   

    /**
     * method for create yaml file.
     *
     * @param array $data
     *
     * @return $this
     */
    public static function CREATE(array $data)
    {
        self::$data_yaml_file = $data;

        return self::$thiss;
    }

    /**
     * method for erase yaml file.
     *
     * @return $this
     */
    public static function ERASE()
    {
        self::$data_yaml_file = [];

        return self::$thiss;
    }

    /**
     * method for add new value in the yaml file.
     *
     * @param array $add_new_value
     *
     * @return $this
     */
    public static function ADD(array $add_new_value)
    {
        if (is_array(self::$data_yaml_file)) self::$data_yaml_file = array_merge_recursive(self::$data_yaml_file, $add_new_value);
        else self::$data_yaml_file = $add_new_value;

        return self::$thiss;
    }

    /**
     * method for remove some values in the yaml file.
     *
     * @param array $rm_value
     *
     * @return $this
     */
    public static function REMOVE(array $rm_value)
    {
        $rt = hkm_update_yaml(self::$data_yaml_file);
        $rt0= hkm_update_yaml($rm_value);

        self::$data_yaml_file = self::arrayDiffRecursive($rt, $rt0);

        return self::$thiss;
    }

    /**
     * method for write data in the yaml file.
     *
     * @return bool
     */
    public static function WRITE()
    {
        $data = Yaml::dump(self::$data_yaml_file,2, 4, Yaml::DUMP_NULL_AS_TILDE);
        $result = file_put_contents(self::$path_to_yaml_source, $data);
        if (false === $result) {
            throw new \Exception(sprintf('Unable to write in the file yaml: %s', self::$path_to_yaml_source));
        }

        return ($result !== false) ? true : false;
    }

   

    /**
     * Computes the difference of 2 arrays recursively
     * source : http://php.net/manual/en/function.array-diff.php#91756.
     *
     * @param array $array1
     * @param array $array2
     *
     * @return array
     */
    private static function arrayDiffRecursive(array $array1, array $array2)
    {
        $finalArray = [];
        foreach ($array1 as $Array1) {
            $r = false;
            foreach ($array2 as $Array2) {
                if ($Array1 == $Array2) {
                    $r = true;
                }
            }
            if(!$r) $finalArray[]=$Array1;
        }

        return hkm_rewrite_update($finalArray);
        // return $finalArray;
    }
}

  

// $YAMLEmit = new YAMLEmit(__DIR__."/sample.yaml",null);
// $YAMLEmit::UPDATE(['database'=>['default'=>['hostname'=>"localhost"]]])::WRITE();

