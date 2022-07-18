<?php
    namespace Hkm_Config\YAMLParse\hkm_src;

use Symfony\Component\Yaml\Yaml;

/**
     * Class YAMLParse.
     */
    class YAMLParse
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
         * @var array $data_yaml_source
         */
        protected static $data_yaml_source;


        /**
         * @var YAMLParse $thiss
         */
        protected static $thiss;

        function __construct()
        {
            self::$thiss = $this;
        }
    
        /**
         * method for get data of yaml source.
         *
         * @param string $yaml_source     path of yaml source
         * @param array|null $callback     callback
         * @param int    $parse_mode parse mode YAMLParse::YAML_STREAM, YAMLParse::YAML_URL or YAMLParse::YAML_FILE
         *
         * @return array yaml source data in a array
         */
        public static function GET($yaml_source, $parse_mode = YAMLParse::YAML_FILE)
        {        
            self::$parse_mode = $parse_mode;

            self::SET_YAML_SOURCE($yaml_source);
            self::_PARSING_YAML();
            return self::$data_yaml_source;
        }

        public static function SET_YAML_SOURCE($source){
            
            if (!empty($source)) {
                $source = filter_var($source,FILTER_SANITIZE_FULL_SPECIAL_CHARS);
                self::$path_to_yaml_source = $source;
            }

            return self::$thiss;

        }


        private static function _PARSING_YAML()
        {
            if (!empty(self::$path_to_yaml_source)) {
                
                switch (self::$parse_mode) {
                    case YAMLParse::YAML_FILE:
                        $data = Yaml::parseFile(self::$path_to_yaml_source,
                        Yaml::PARSE_CONSTANT
                        );
                        self::$data_yaml_source = $data;
                        break;
                    case YAMLParse::YAML_STREAM:
                        $data = Yaml::parse(self::$path_to_yaml_source,
                        Yaml::PARSE_CONSTANT
                        );

                        self::$data_yaml_source = $data;
                        break;
                    case YAMLParse::YAML_URL:
                        break;
                    
                    default:
                        # code...
                        break;
                }

            }

        }


    }


    
