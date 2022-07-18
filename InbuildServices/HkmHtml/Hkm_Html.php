<?php
namespace Hkm_services\HkmHtml;

class Hkm_Html implements HtmlInterface,ElementInterface
{
    protected static $instance = null;
    protected static $header = [];
    protected static $body = [];
    protected static $footer = [];

    public static function INIT() :ElementInterface
	{
		if (!is_object(self::$instance)) {
			self::$instance = new self();
		}
		return self::$instance;
	}
    public static function SET_HEADER(array $data) :ElementInterface
    {
        $d = [
            'title'=>$data['title']??'No title',
            'description'=>$data['description']??"No description",
            "image"=>$data['image']??"sample.jpg",
            "url"=>$data['url']??"sample.jpg"
        ];
        $d = (object) $d;

        self::$header[]="<title>$d->title</title>";
        static::INIT();

        self::$instance->setElement('header','meta',['name'=>'title','content'=>$d->title]);
        self::$instance->setElement('header','meta',['name'=>'description','content'=>$d->description]);
        self::$instance->setElement('header','meta',['name'=>'image','content'=>$d->image]);
        self::$instance->setElement('header','meta',['property'=>'og:title','content'=>$d->title]);
        self::$instance->setElement('header','meta',['property'=>'og:description','content'=>$d->description]);
        self::$instance->setElement('header','meta',['property'=>'og:image','content'=>$d->image]);
        self::$instance->setElement('header','meta',['property'=>'og:url','content'=>$d->url]);

        self::$instance->setElement('header','meta',['property'=>'twitter:title','content'=>$d->title]);
        self::$instance->setElement('header','meta',['property'=>'twitter:description','content'=>$d->description]);
        self::$instance->setElement('header','meta',['property'=>'twitter:card','content'=>$d->description]);
        self::$instance->setElement('header','meta',['property'=>'twitter:image','content'=>$d->image]);
        self::$instance->setElement('header','meta',['property'=>'twitter:url','content'=>$d->url]);


        return self::$instance;
    }

    public static function SET_BODY(array $data): HtmlInterface
    {
      return new static;  
    }
    
    public static function SET_FOOTER(array $data): HtmlInterface
    {
      return new static;   
    }
    public static function GET_HEADER(): string
    {
        $d ="";

        
        foreach (self::$header as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $key1 => $value1) {
                    $d.=$value1;

                }
            }else $d.=$value;
        }

     return $d;
    }

    public static function GET_BODY(): string
    {
        
      return "";
    }

    public static function GET_FOOTER(): string
    {
        $d ="";

        
        foreach (self::$footer as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $key1 => $value1) {
                    $d.=$value1;

                }
            }else $d.=$value;
        }

     return $d;
    }
    public function setElement(string $elementParty, string $elementType, array $attributes): ElementInterface
    {
        static::INIT();

        hkm_helper('html');
        $allowedElements = ['meta','link','script','script_string'];
        
        if (in_array($elementType,$allowedElements)) {
            $dat = '';
            switch ($elementType) {
                case 'meta':
                    $dat = hkm_meta($attributes);
                    break;
                case 'link':
                    $dat = hkm_link_tag($attributes);
                    break;
                case 'script':
                    $dat = hkm_script_tag($attributes['src']??'',$attributes['indexPage']??false);
                    break;
                case 'script_string':
                    $dat = hkm_script_string($attributes['script']);
                    break;
                    
            }
            self::$$elementParty[$elementType][] = $dat;
        }

        return self::$instance;
    }
    public function getElements(string $type): string
    {
        static::INIT();

        return "";
    }
}
