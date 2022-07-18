<?php
namespace Hkm_code\CLI;
/**
 * 
 */
use Hkm_code\Vezirion\ServicesSystem;


trait VezirionTrait
{
    	/**
	 * Component Name
	 *
	 * @var string
	 */
    protected $vezirionType;
    /**
	 * The params array for easy access by other methods.
	 *
	 * @internal
	 *
	 * @var array
	 */
    private $params = [];
    
	private $subCommand = [
        'select' => 'Prints a list of the config type in your application',
		'modif' => 'Set config type',
		'exit' => 'Exit configuration mode',
		'list' => 'Exit configuration mode',
    ];
    
    /**
	 * Gets a single command-line option. Returns TRUE if the option exists,
	 * but doesn't have a value, and is simply acting as a flag.
	 *
	 * @param string $name
	 *
	 * @return mixed
	 */
	protected function getOption(string $name)
	{
		if (! array_key_exists($name, $this->params))
		{
			return CLI::getOption($name);
		}

		return is_null($this->params[$name]) ? true : $this->params[$name];
    }

    public function execute(string $type, string $namespace,string $appName)
    {
        $v = ServicesSystem::DOM_XML_IN_ARRAY();
        $v::XML_READER($type.'s.config',$namespace,$appName);
        if ($v::$error) {
            CLI::error('There is no this type of congiguration ~ '.$type);
            exit;
        }
        $f = $v::$fileData;

        if ($this->getOption('d')) {
			$v::XML_READER($type.'s.description.config',$namespace,$appName);
			$des = $v::$fileData;

			$f[$namespace]['group'] = $type;
			$length = max(array_map('strlen', array_keys($f[$namespace])));
            $lengthv = max(array_map('count_values', array_values($f[$namespace])));
            if ($this->getOption('nv')) $this->listSimple($f,$des[$namespace],$length,true);
            else $this->listFull($f,$des[$namespace],$length, $lengthv,true);		

		}else{
			$f[$namespace]['group'] = $type;
			$length = max(array_map('strlen', array_keys($f[$namespace])));
            if ($this->getOption('nv')) $this->listSimple($f,[],$length);
            else $this->listFull($f,[],$length, 0);
        }
        
        $rg = false;

        while (!$rg) {
            $rg = $this->innerCommand(CLI::prompt(CLI::color('['.$appName.':Config]', 'blue'),null,'required'));
            
        }
        CLI::print("Succefull!");
        CLI::newLine();
    }

    public function innerCommand($command)
    {
        $a = explode(' ',$command);
        $st = strtolower($a[0]);
        if (in_array($st,array_keys($this->subCommand)) && $st == 'select') {
            unset($a[0]);
            if (!isset($a[1])){
               CLI::error("Unknown your selected item!");
               return false;
            }
            $property = $this->property_exist($a[1]);
            unset($a[1]);
            if ($property) {
                if (!isset($a[2])){
                    $modif = $this->secondCmd($property);
                }else{
                    $modif = $this->secondCmd(strtolower($a[2]),$a);
                }
                if ($modif === false) return $modif;
                else return $this->modif($property,$modif);
                
            }else return $property;

        }else{
            if ($st == "exit") {
                exit;
            }elseif ($st == "list") {
                $this->execute($this->vezirionType,$this->namespace,$this->appName);
            }
            CLI::error("Unknown command [$st]");
            return false;
        }
    }

    public function secondCmd($property,$a = null,$index = 2)
    {
        $t = true;
        $modif = false;
         if (is_null($a)) {
            while ($t) {
                $f = strtolower(CLI::prompt(CLI::color('['.$this->appName.':Config~'.$property.']', 'blue'),null,'required'));
                $f = trim($f);
                
                if (in_array($f,array_keys($this->subCommand)) && $f == 'modif'){
                    CLI::error('Please add a new value to modifying!');
                    $t=true;
                }
                else {
                    $a = explode(' ',$f);
                    $modif = $a[0];
                    unset($a[0]);
    
                    if (in_array($modif,array_keys($this->subCommand)) && $modif == 'modif'){
                        if (isset($a[1])) {
                            $modif = join(" ",$a);
                            $t = false;
                        }else{
                            CLI::error('Please insert a new value to modifying!');
                            $t=true;
                        }
                    }
                    else {    
                        CLI::error("Unknown command [$modif]");
                        $t=true;
                    }  
                }
            }
         }else{
            if (in_array($property,array_keys($this->subCommand)) && $property == 'modif'){
                unset($a[$index]);
                $modif = join(" ",$a); 
            }else{
                CLI::error("Unknown command [$property]");
                $modif = false;
            }
         }

         return $modif;

        
    }
    public function modif($property,$value)
    {
        $this->properties[$property] = $value;
        $p = array_map("hkm_XMLSanitizeValue",$this->properties);
        $ar = ['tag'=>$this->appName,
               'namespace'=>$this->namespace,
               'data' => $p ];
        
        $xml = ServicesSystem::DOM_XML_IN_ARRAY();
        $xml::XML_MODIF_DATA($this->vezirionType.'s.config',$ar);
        if (!$xml::$error) {
            CLI::print("You have successfuly modifying ".$property." in your app!","white","blue");
            CLI::newLine();
            return false;
             
        }else{
            CLI::error("Unexpected error while modification proccess in your app!");
            CLI::newLine();
            return false;
        }
        
        
    }
    public function property_exist($property)
    {
        if (in_array($property,array_keys($this->properties))) return $property;
        else{
            CLI::error("Unknown selected config [$property]");
            return false;
        }
        
    }
    /**
	 * Lists the commands with accompanying info.
	 *
	 * @param array $commands
	 */
	protected function listFullDesc(array $vezirion,array $description,$length,$length2)
	{
        
        foreach ($vezirion as $title => $value)
		{
            if (is_array($value)) {
                $title = $title == 'vezirion'?"Configuration":$title;

                $subg = $value['subgroup'];
                $sg = $value['group'];
                
                unset($value['subgroup']);
                unset($value['group']);
                $i = 1;
                CLI::write("[".$title."][".$subg."][".$sg."]", 'yellow');
                CLI::newLine();
                foreach ($value as $name => $val)
                {
                    if (!in_array($name,array_keys($this->properties))) $this->properties[$name]=$val;
                    $nameM   = $this->setPad($name, $length, 2, 2);
                    $output = CLI::color($nameM, 'green');
                    if (!is_array($val))
                    {
                        $val   = $this->setPad($val, $length2, 2, 2);
                        $output .= CLI::wrap($val, 125, strlen($nameM));
                        if (isset($description[$name])) {
                            $DESCR   = $this->setPad($description[$name], $length2, 2, 2);

                           $output .= CLI::color(CLI::wrap($DESCR , 125, strlen($val)+strlen($nameM)+2), 'cyan');
                        }
                    }
                    $i++;
                    CLI::newLine();
                    
                    CLI::write($output);
                    

                }


                if ($title !== array_key_last($vezirion))
                {
                    CLI::newLine();
                }


            }else{
                $title = $title == 'vezirion'?"Configuration":$title;
                CLI::write("[".$title."]", 'yellow');
                $name   = $this->setPad($title, $length, 2, 2);
                $output = CLI::color($name, 'green');
                $output .= CLI::wrap($value, 125, strlen($name));
                CLI::write($output);
                if ($title !== array_key_last($vezirion))
                {
                    CLI::newLine();
                }




            }
            
		}


    }
    /**
	 * Lists the commands with accompanying info.
	 *
	 * @param array $commands
	 */
	protected function listFull(array $vezirion,array $description,$length, $lengthV, $des = false)
	{
		// Sort into buckets by group
		
        ksort($vezirion);

       if ($des) {
           $this->listFullDesc($vezirion,$description,$length,$lengthV);
       }else {
        foreach ($vezirion as $title => $value)
		{
            if (is_array($value)) {
                $title = $title == 'vezirion'?"Configuration":$title;

                $subg = $value['subgroup'];
                $sg = $value['group'];
                
                unset($value['subgroup']);
                unset($value['group']);
                $i = 1;
                CLI::write("[".$title."][".$subg."][".$sg."]", 'yellow');
                CLI::newLine();
                foreach ($value as $name => $val)
                {
                    if (!in_array($name,array_keys($this->properties))) $this->properties[$name]=$val;
                    $nameM   = $this->setPad($name, $length, 2, 2);
                    $output = CLI::color($nameM, 'green');
                    if (!is_array($val))
                    {
                        $val   = $this->setPad($val, $lengthV, 2, 2);
                        $output .= CLI::wrap($val, 125, strlen($nameM));
                    }
                    $i++;
                    
                    CLI::write($output);
                    

                }


                if ($title !== array_key_last($vezirion))
                {
                    CLI::newLine();
                }


            }else{
                $title = $title == 'vezirion'?"Configuration":$title;
                CLI::write("[".$title."]", 'yellow');
                $name   = $this->setPad($title, $length, 2, 2);
                $output = CLI::color($name, 'green');
                $output .= CLI::wrap($value, 125, strlen($name));
                CLI::write($output);
                if ($title !== array_key_last($vezirion))
                {
                    CLI::newLine();
                }




            }
            
		}
       }
        

		

		
    }
    /**
	 * Lists the commands only.
	 *
	 * @param array $commands
	 */
	protected function listSimpleDesc(array $vezirion,$description,$length){
        foreach ($vezirion as $title => $value)
		{
            if (is_array($value)) {
                $title = $title == 'vezirion'?"Configuration":$title;

                $subg = $value['subgroup'];
                $sg = $value['group'];
                
                unset($value['subgroup']);
                unset($value['group']);
                $i = 1;
                CLI::write("[".$title."][".$subg."][".$sg."]", 'yellow');
                CLI::newLine();
                foreach ($value as $name => $val)
                {
                    if (!in_array($name,array_keys($this->properties))) $this->properties[$name]=$val;

                    $nameM   = $this->setPad($name, $length, 2, 2);
                    $output = CLI::color($nameM, 'green');
                    if (!is_array($val))
                    {
                        // $val   = $this->setPad($val, $length, 2, 2);
                        // $output .= CLI::wrap($val, 125, strlen($nameM));
                        if (isset($description[$name])) {
                            $DESCR   = $this->setPad($description[$name], $length, 2, 2);

                           $output .= CLI::color(CLI::wrap($DESCR , 125, strlen($nameM)+2), 'cyan');
                        }
                    }
                    $i++;
                    CLI::newLine();
                    
                    CLI::write($output);
                    

                }


                if ($title !== array_key_last($vezirion))
                {
                    CLI::newLine();
                }


            }else{
                $title = $title == 'vezirion'?"Configuration":$title;
                CLI::write("[".$title."]", 'yellow');
                $name   = $this->setPad($title, $length, 2, 2);
                $output = CLI::color($name, 'green');
                $output .= CLI::wrap($value, 125, strlen($name));
                CLI::write($output);
                if ($title !== array_key_last($vezirion))
                {
                    CLI::newLine();
                }




            }
            
		}
    }

	/**
	 * Lists the commands only.
	 *
	 * @param array $commands
	 */
	protected function listSimple(array $vezirion,$description,$length,$desc = false)
	{
        if ($desc) {
            $this->listSimpleDesc($vezirion,$description,$length);
        }else{
            foreach ($vezirion as $title => $value)
            {
                if (is_array($value)) {
                    $title = $title == 'vezirion'?"Configuration":$title;

                    $subg = $value['subgroup'];
                    $sg = $value['group'];
                    
                    unset($value['subgroup']);
                    unset($value['group']);

                    $i = 1;
                    
                    CLI::write("[".$title."][".$subg."][".$sg."]", 'yellow');
                    CLI::newLine();
                    foreach ($value as $name => $val)
                    {
                    if (!in_array($name,array_keys($this->properties))) $this->properties[$name]=$val;

                        $nameM   = $this->setPad($name, $length, 2, 2);
                        $output = CLI::color($nameM, 'green');
                        $i++;
                        CLI::write($output);
                        
                      
                    }


                    if ($title !== array_key_last($vezirion))
                    {
                        CLI::newLine();
                    }


                }else{
                    $title = $title == 'vezirion'?"Configuration":$title;
                    CLI::write("[".$title."]", 'yellow');
                    $name   = $this->setPad($title, $length, 2, 2);
                    $output = CLI::color($name, 'green');
                    CLI::write($output);
                    if ($title !== array_key_last($vezirion))
                    {
                        CLI::newLine();
                    }




                }
                
            }
        }
	}
}


