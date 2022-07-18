<?php
namespace Hkm_services\HkmHtml;

class Html_Convertor
{
    protected static $elements = [
     "<p>" => '[:p]',
     '</p>' => '[:/p]',
     '<ul>' => '[:ul]',
     '<li>' => '[:li]',
     '</ul>' => '[:/ul]',
     '</li>' => '[:/li]',
     "<h4><strong>" => '[:h4]',
     "</strong></h4>" => '[:/h4]',
    ];

    protected static $imgElement = [
     "<figure class='tw_blog_image_{position}'> <img src='#src' alt='#alt'> <figcaption> #figcaption </figcaption></figure>" => '[:img /]',
    ];

    protected static $imgElementPrepend = [];
    protected static $content = "";
    protected static $caches = [];

    protected static $imgStart = ['[:img /]'=>0];

    public static function convert(string $content)
    {
        $conts = '';
        foreach (self::$elements as $elem => $symbol) {
               if(empty($conts)) $conts = str_replace($symbol,$elem,$content);
               else $conts = str_replace($symbol,$elem,$conts);
        }
        self::$content = $conts;
        self::convertImg($conts);

        foreach (self::$imgElementPrepend as $positions => $figs) {
            @list($start, $end) = explode("-",$positions);

            $start = (int) $start;
            $end = (int) $end;

            $p = substr(self::$content,$start,($end - $start));
            if(!empty($p)){
                self::$imgElementPrepend[$p] = $figs;
                unset(self::$imgElementPrepend[$positions]);
            }
        }
        self::convertingImg();
    }

    protected static function convertImg(string $content)
    {
        
        foreach (self::$imgElement as $elem => $symbol) {

            if (isset(self::$imgStart[$symbol]) && self::$imgStart[$symbol] == 0) self::syncImg($content,$elem,$symbol);
        }
    }


    protected static function __convImg__($rpImg,$clImg)
    {
        $pattern = "/#([A-Za-z])\w+/i";
        $m = [];
        $r = [];
        if(preg_match_all($pattern, $clImg, $matches)) {
          $m = $matches[0];
        }
        
        $srcP="";
        foreach ($m as $sch) {
            if($sch !='#src'){
                $sch = ltrim(trim($sch),"#");
                if(empty($srcP)) $srcP.="(\b".$sch.":[-A-Z0-9+&@#\/%=~_|$?!:,.]*[A-Z0-9+&@#\/%=~_|$])";
                else $srcP.="|(\b".$sch.":[-A-Z0-9+&@#\/%=~_|$?!:,.]*[A-Z0-9+&@#\/%=~_|$])";
            }else{
                if(empty($srcP)) $srcP .= "(\bsrc:(?:(?:https?|ftp|file):\/\/|www\.|ftp\.)[-A-Z0-9+&@#\/%=~_|$?!:,.]*[A-Z0-9+&@#\/%=~_|$])|(\bsrc:[-A-Z0-9+&@#\/%=~_|$?!:,.]*[A-Z0-9+&@#\/%=~_|$])";
                else $srcP .= "|(\bsrc:(?:(?:https?|ftp|file):\/\/|www\.|ftp\.)[-A-Z0-9+&@#\/%=~_|$?!:,.]*[A-Z0-9+&@#\/%=~_|$])|(\bsrc:[-A-Z0-9+&@#\/%=~_|$?!:,.]*[A-Z0-9+&@#\/%=~_|$])";
            }
        }
        $pattern1 = "/$srcP/i";
        if(preg_match_all($pattern1, $rpImg, $matches)) {
            $r = $matches[0];
        }

        $baliz = self::$caches[self::$imgElement[$clImg]];
        $alClas = ['Right','Left','Center'];
        
        for ($i=0; $i < count($alClas); $i++) { 
            $bl = ltrim(trim($baliz),"[").$alClas[$i];
            $patrn = "/$bl/i";
            if(preg_match($patrn, $bl)){
                $clImg = str_replace('{position}',strtolower($alClas[$i]),$clImg);
                break;
            }
        }

        $rM =[];
        foreach ($r as $dr) {
            $rf = explode(":",$dr);
            unset($rf[0]);
            $rM[]=implode(':',$rf);
        }

        $clImg = str_replace($m,$rM,$clImg);
        return $clImg;
    }

    protected  static function convertingImg()
    {
        foreach (self::$imgElementPrepend as $rpImg => $clImg)  self::$content = str_replace($rpImg,self::__convImg__($rpImg,$clImg),self::$content);
    }

    protected  static function syncImg($content,$elem,$symbol){
       
        $symbolOrig = trim($symbol);

        do {
            $syEnd = substr($symbolOrig,-3);
            $syStart = trim(str_replace($syEnd,' ',$symbol));
            self::$caches[$symbolOrig] = $syStart;
            $syPosStart = strpos($content,$syStart,self::$imgStart[$symbolOrig]);
            if(gettype($syPosStart) == "integer"){
                $syPosEnd = strpos($content,$syEnd,($syPosStart - 1) + strlen($syStart));
                if($syPosEnd){
                    self::$imgElementPrepend[$syPosStart."-".($syPosEnd+3)] = $elem;
                    self::$imgStart[$symbolOrig] = $syPosEnd+3;
                }else self::$imgStart[$symbolOrig] = strlen($content) + 1;
            }else self::$imgStart[$symbolOrig] = strlen($content) + 1;
            
            
        } while (self::$imgStart[$symbolOrig] < strlen($content) );
    }

    public static function GET_CONTENT()
    {
        return self::$content;
    }
}


// preg_match_all('/\b(?:(?:https?|ftp|file):\/\/|www\.|ftp\.)[-A-Z0-9+&@#\/%=~_|$?!:,.]*[A-Z0-9+&@#\/%=~_|$]/i', $subject, $result, PREG_PATTERN_ORDER);
// $result = $result[0];