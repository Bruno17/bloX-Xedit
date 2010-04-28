<?php
####
#
#	Name: Chunkie
#	Version: 1.0
#	Author: Armand "bS" Pondman (apondman@zerobarrier.nl)
#	Date: Oct 8, 2006 00:00 CET
#
####

class xettChunkie {
 	var $template, $phx, $phxreq, $phxerror, $check;
	
	function xettChunkie($template = '',$templates=array()) {
		//if (!class_exists("PHxParser")) include_once(strtr(realpath(dirname(__FILE__))."/phx.parser.class.inc.php", '\\', '/')); 
		$this->templates = & $templates;
		$this->template = $this->getTemplate($template);
		$this->depth = 0;
		$this->maxdepth = 4;
		/*
		$this->phx = new PHxParser();
		$this->phxreq = "1.4.4";
		$this->phxerror = '<div style="border: 1px solid red;font-weight: bold;margin: 10px;padding: 5px;">
			Error! This MODx installation is running an older version of the PHx plugin.<br /><br />
			Please update PHx to version '.$this->phxreq .' or higher.<br />OR - Disable the PHx plugin in the MODx Manager. (Manage Resources -> Plugins)
			</div>';
		$this->check = ($this->phx->version < $this->phxreq) ? 0 : 1;
        */ 
	}

	function CreateVars($value = '', $key = '', $path = '') {
		
	    $this->depth ++;
		
		
		
		if ($this->depth>$this->maxdepth) return;
		
		$keypath = !empty($path) ? $path . "." . $key : $key;
		echo $this->depth.':'.$keypath.'<br/>';
		$this->placeholders[$keypath] = $value;
		return;
		
		if (is_array($value)) {
				foreach ($value as $subkey => $subval) {
					$this->CreateVars($subval, $subkey, $keypath);
					$this->depth --;
					}
			} 
			else { 
			//$this->phx->setPHxVariable($keypath, $value); 
			$this->placeholders[$keypath] = $value;
			}
			
	}
	
	function AddVar($name, $value) {
		//if ($this->check) $this->CreateVars($value,$name);
		//$this->CreateVars($value,$name);
		//$this->depth --;
		$this->placeholders[$name] = $value;
	}
	
    function Render()
    {
        global $modx;
		/*
		$search=array('[[+',']]');	
		$replace=array('[.[+','].]');		
		echo str_replace($search,$replace,$this->template);
		echo '<br/>----------------------------------------';        
		
		print_r($this->placeholders);
		echo '<br/>----------------------------------------';

		echo $this->template;
        */
		
		//$search=array('[+','+]');
		//$replace=array('[[+',']]');
	
		//$template=str_replace($search,$replace,$this->template);
		//$placeholders=$this->placeholders;
		//$this->placeholders=array();
		//$this->placeholders['testvalue']=$placeholders['testvalue'];
		//print_r($this->placeholders);
		$template=$this->template;
		    //$modx->setPlaceholders($this->placeholders);
            $chunk = $modx->newObject('modChunk');
            $chunk->setCacheable(false);
            $template = $chunk->process($this->placeholders, $template);
			unset($chunk);
        /*
        if (!$this->check)
        {
            $template = $this->phxerror;
        } else
        {
            $template = $this->phx->Parse($this->template);
        }
        */
        return $template;
    }
	
    function getTemplate($tpl)
    {
        // by Mark Kaplan
        global $modx;
        $template = "";
        if ( isset ($this->templates[$tpl]))
        {
            $template = $this->templates[$tpl];
        }
        else
        {
            if (substr($tpl, 0, 6) == "@FILE:"){
                $template = $this->get_file_contents($modx->config['base_path'].substr($tpl, 6));
            } else if (substr($tpl, 0, 6) == "@CODE:"){
                $template = substr($tpl, 6);    
            } else if ($chunk = $modx->getObject('modChunk', array ('name'=>$tpl), true)){
                $template = $chunk->getContent();
            } else {
                $template = FALSE;
            }
            $this->templates[$tpl] = $template;
        }
    
        return $template;
    }

	function get_file_contents($filename) {
		// Function written at http://www.nutt.net/2006/07/08/file_get_contents-function-for-php-4/#more-210
		// Returns the contents of file name passed
		if (!function_exists('file_get_contents')) {
			$fhandle = fopen($filename, "r");
			$fcontents = fread($fhandle, filesize($filename));
			fclose($fhandle);
		} else	{
			$fcontents = file_get_contents($filename);
		}
		return $fcontents;
	}
}
?>
