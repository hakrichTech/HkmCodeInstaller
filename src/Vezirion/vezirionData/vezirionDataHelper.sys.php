<?php



namespace Hkm_code\Vezirion\vezirionData;




class vezirionDataHelper
{
	public static $fileData = [];
	protected static $fileDataTemp = [];
	protected static $key = " ";
	protected static $pObject = null;
	protected static $thiss;
	public static $error = false;
	protected static $file;
	
	public function __construct()
	{
	   self::$thiss = $this;	
	}
	public static function XML_ARRAY_GENERATE($datas)
	{

		$array = [];
    
		if($datas instanceof \DOMElement) if ($datas->hasAttributes()) foreach ($datas->attributes as $attribute) $array[$attribute->nodeName] = $attribute->nodeValue;
        if($datas instanceof \DOMNodeList) foreach ($datas as $node) if ($node->nodeType == XML_ELEMENT_NODE)$array[empty($node->getAttribute('url'))?$node->nodeName:$node->getAttribute('url')] = self::XML_ARRAY_GENERATE($node);			
		
		if (count($array)>0) return $array;
		else return " ";
		 
	}

	
	public static function XML_READER(string $file,$namespace = null, $tag=null)
	{
		 	
		$file = $file.".xml";
		if (is_file(ROOTPATH.$file)) {
            $dom=new \DOMDocument();
            $dom->load(ROOTPATH.$file);
			@$Defaults=$dom->getElementsByTagNameNs("http://example/".$tag,$namespace);
			$defaults =($Defaults !== false && $Defaults->length>0)?array_map("hkm_XMLSanitizeArray",self::XML_ARRAY_GENERATE($Defaults)):"No default Found!";
			if(is_array($defaults)){
				$defaults[$namespace]['group'] = ' ';
				$defaults[$namespace]['subgroup'] = $tag;
			}
			
			self::$fileData = $defaults;
            
            
			// $dom->save(ROOTPATH.$file);
			return self::$thiss;


		}else{
			self::$error = true;
		}
	}

	public static function XML_ADD_DATA(string $file ,array $dataArray,$type,$attr)
	{
		
		$file = $file.".xml";
		if (is_file(ROOTPATH.$file)) {
			$dom=new \DOMDocument();
			$dom->load(ROOTPATH.$file);
			$root = $dom->documentElement;
			@$datas=$root->getElementsByTagNameNs("http://example/".$dataArray['app_name'],$type);

			$is_name_exist = false;
			foreach ($datas as $data) if ($data->nodeType == XML_ELEMENT_NODE) if ($data->getAttribute($attr) == $dataArray['uniq'])$is_name_exist = true;
			
			if ($is_name_exist)return "Exist!";
			else{
				@$data = $dom->createElementNS("http://example/". $dataArray['app_name'],$type );
				$data->setAttribute( "url", $dataArray['from'] );
				$data->setAttribute( "pobohet", $dataArray['type'] );
				$data->setAttribute( "method", $dataArray['method'] );
				$data->setAttribute( "controller", $dataArray['to'] );
				$data->setAttribute( "options", $dataArray['options'] );
				$root->appendChild($data);
				if(is_writable(ROOTPATH.$file)){
					$dom->save(ROOTPATH.$file);
      				return "Created!";
				}else{
					echo "Permission denied for the file: ".ROOTPATH.$file;
					exit;
				}
			}


			
		}

	}
	public static function XML_MODIF_DATA(string $file ,array $dataArray)
	{
		$file = $file.".xml";
		if (is_file(ROOTPATH.$file)) {
            $dom=new \DOMDocument();
            $dom->load(ROOTPATH.$file);
			$root = $dom->documentElement;
			@$f = $dom->createElementNS("http://example/". $dataArray['tag'], $dataArray['namespace'] );
				foreach ($dataArray['data'] as $key => $value) {
					$f->setAttribute($key,$value);
				}
			$modif=$root->getElementsByTagNameNS("http://example/".$dataArray['tag'],$dataArray['namespace'])[0];
			if (!is_null($modif)) $modif->parentNode->replaceChild($f,$modif);
		    $dom->save(ROOTPATH.$file);
		}
		self::$error = false;
	}
}













// if (!function_exists('XMLModifier')) {
// 	function XMLModifier($fileName = "Example.xml", array $contents = [], $deletTagName = null, $add = false)
// 	{
// 		$dom=new \DOMDocument();
// 		$dom->load(ROOTPATH.$fileName);
// 		$root = $dom->documentElement;
// 		$tracks=$root->getElementsByTagName('Track');
// 		$modif = [];
// 		$modify = [];
// 		foreach ($tracks as $track) {
// 			if ($track->hasChildNodes() || $track->hasAttributes()) {
// 				if ($track->getAttribute('length') == "0:01:33") {
// 					$modif[] = $track;
// 					$f = $dom->createElement( "Track", "Highway Blues changed" );
// 					$f->setAttribute( "length", $track->getAttribute('length') );
// 					$f->setAttribute( "bitrate", $track->getAttribute('bitrate') );
// 					$f->setAttribute( "channels", $track->getAttribute('channels') );

// 					$modify [] = $f;
// 				}
// 			}
// 		}
		
// 		foreach ($modif as $key => $node) {
// 			$node->parentNode->replaceChild($modify[$key],$node);
// 		}
// 		$dom->save(ROOTPATH.$fileName);

// 	}
// }

// if (! function_exists('XMLCreator')) {
// 	function XMLCreator(string $fileName = "Example.xml")
// 	{
// 		$xml = new \DOMDocument( "1.0", "ISO-8859-15" );
// 		$xml_album = $xml->createElement( "Album" );
// 		$xml_track = $xml->createElement( "Track", "The ninth symphony" );


// 		$xml_track->setAttribute( "length", "0:01:15" );
// 		$xml_track->setAttribute( "bitrate", "64kb/s" );
// 		$xml_track->setAttribute( "channels", "2" );


// 		$xml_note = $xml->createElement( "Note", "The last symphony composed by Ludwig van Beethoven." );
// 		$xml_track->appendChild( $xml_note );
// 		$xml_album->appendChild( $xml_track );


// 		$xml_track = $xml->createElement( "Track", "Highway Blues" );

// 		$xml_track->setAttribute( "length", "0:01:33" );
// 		$xml_track->setAttribute( "bitrate", "64kb/s" );
// 		$xml_track->setAttribute( "channels", "2" );
// 		$xml_album->appendChild( $xml_track );

// 		$xml->appendChild( $xml_album );
// 		$xml->save(ROOTPATH.$fileName);
// 	}
// }