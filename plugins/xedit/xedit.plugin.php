<?php

/**
 * $Id$
 */

/*      in mutate_content_dynamic  
 *      change require to require_once ('tmplvars.inc.php');
 *      require_once ('tmplvars.commands.inc.php');
 *      or and all functions in both files with if (!function_exists......)
 *      perhaps there is another way??
 * 
 * 
 * 
 */
$e = $modx->event;

	    
include $pluginpath.'/config.php';
include $pluginpath.'/boot.php';



//print_r($e);
    
    // include_once $ch->params['path'].'inc/events/'.strtolower( $e->name ).'.inc.php';
    switch( $e->name )
    {

        case 'OnParseDocument': 
		//ob_start();
		
		$xedit->include_eventfile($e->name);
		
		//echo $output;
		//$output=
		//ob_flush(); 	
        //$modx->documentOutput=$output; 
		break;

        case 'OnWebPagePrerender' :
        //$xedit->output=$modx->documentOutput; 
		
		ob_start();
		
		$output = $xedit->include_eventfile($e->name);
		
		ob_flush(); 	  
        $modx->documentOutput = $output;  		
        break;

        case 'OnChunkFormSave': 
				
		echo $xedit->include_eventfile($e->name);
		break;

        case 'OnChunkFormRender': 
		ob_start();		
		echo $xedit->include_eventfile($e->name);
        ob_flush();   
		break;

        case 'OnTempFormRender': 
		ob_start();		
		echo $xedit->include_eventfile($e->name);
        ob_flush();   
		break;

        case 'OnTempFormSave': 
	
		echo $xedit->include_eventfile($e->name);
		break;
				
        case 'OnManagerPageInit': // wird jedesmal ausgeführt 
		ob_start(); 
		echo $xedit->include_eventfile($e->name);
		ob_flush(); 	        
            //include_once $pluginpath.'inc/events/'.strtolower( $e->name ).'.inc.php';
            break;
    
        case 'OnDocFormPrerender' :
		ob_start(); 
		echo $xedit->include_eventfile($e->name);
		ob_flush(); 	
            //include_once $pluginpath.'inc/events/'.strtolower( $e->name ).'.inc.php';
            break;
            
        case 'OnDocFormRender':
            //include_once $ch->params['path'].'inc/events/'.strtolower( $e->name ).'.inc.php';
            break;
        
        case 'OnBeforeDocFormSave':
            /* 
			$tvid='21';
			$tv=$modx->getTemplateVar($tvid,'*',$_POST['id']);
			$elements=explode('||',$tv['elements']);
     		$uncheckedoptions=array();
			foreach($elements as $element){
				if (!in_array($element,$_POST['tv'.$tvid])){
					$uncheckedoptions[]=$element;
				}
			}
            print_r($uncheckedoptions);			
			*/
			//die();
			
			//include_once $ch->params['path'].'inc/events/'.strtolower( $e->name ).'.inc.php';
            break;
          
        case 'OnDocFormSave':
		   
		ob_start(); 
		echo $xedit->include_eventfile($e->name);
		ob_flush(); 	
        break;
    
        case 'OnPluginFormRender':
            /*
			$current_plugin = $content['name'];
            if( strtolower( $current_plugin ) === 'content_history' ) //TODO set variable plugin_name
            {
                include_once $ch->params['path'].'inc/events/'.strtolower( $e->name ).'.inc.php';
            }
            */
            break;
            
        default: 
            return; 
            break;
    }
?>