<?php
/**
 * $Id$
 * 
 * 
 */

switch( $this->manager_action )
{

    case '3':     // Dokumentübersicht

        break;
    
    case '7': // waiting-screen nach doc speichern der zwischen schirm mit der "aufräumen" meldung
       /* ob_start();
        include $ch->getSkinFile('tpl/waiting_screen.tpl.phtml');
        ob_flush();
        $ch->resetMessages();*/       
        break;
    
    case '4': // neues Dokument
        /**
         * Meldungen zurücksetzen
         */
        //$ch->resetMessages();
        //$ch->setParam('id',0);
        break;
    
    case '27': // Dokument bearbeiten
    if (1)
    {

    /*
    $this->getmultichunks($this->docid);
	$tvIds = array_flip($this->allChunkTvNames);
    $this->getMultiTvs($tvIds);
    //print_r($this->chunks);
	//print_r($this->MultiTvs);
	$chunk_output='';
	if (count($this->chunks)>0){
		foreach ($this->chunks as $chunk){

            $tvSection=$this->makeTvSection($chunk);			
			
            $chunk_output.='
			<div class="sectionHeader">
			<p>'.$chunk['caption'].' ('.$chunk['name'].')</p>
			</div>
	        <div class="sectionBody">
	        '.$tvSection.'
	        </div>';   				
		}
	}
    */
	//ob_start();  
	
    
    $this->getChunkContainers($this->docid);
	$chunknames = $this->getChunksFromContainers();
	
	$bcc = new blox_Chunk_Collection($chunknames, $this);
	
	$tvIds = array_flip($bcc->allChunkTvNames);
	$this->getNewMultiTvs($tvIds);
    //$_SESSION['allChunkTvNames']=$bcc->allChunkTvNames;	
    //$chunknames=$this->getchunknames($this->multiCategories);
    $containers_output='';
	//print_r($this->chunkContainers);
	//$_SESSION['chunkContainers']=$this->chunkContainers;
	//$_SESSION['containerTVs']=array();
	if (count($this->chunkContainers)>0){

	foreach ($this->chunkContainers as $containerkey=>$chunkContainer){
    	$chunk_output='';
		if (!empty($chunkContainer['value'])&&count($chunkContainer['value'])>0){
		foreach ($chunkContainer['value'] as $chunkkey=>$chunk){
           
			$tvSection=$this->makeTvSection($chunk,$containerkey,$chunkkey,$bcc);

			$chunk_output.='
			<div class="sectionHeader">
			<p>'.$chunk['pagetitle'].' ('.$chunk['chunkname'].')</p>
			<input type="hidden" name="input_chunkname_'.$containerkey.'[]" value="'.$chunk['chunkname'].'"/>
			<input type="hidden" name="input_docid_'.$containerkey.'[]" value="'.$chunk['id'].'"/>
			</div>
	        <div class="sectionBody">
	        '.$tvSection.'
	        </div>';   				   	    		
    	}
		}

$containers_output.='   
            <div class="sectionBody">
            <h3> '.$chunkContainer['caption'].' ('.$this->docid.')</h3>
			<input type="hidden" name="container_key[]" value="'.$containerkey.'" id="container_key_'.$containerkey.'" class="container_key"/>
			<div class="split"></div>
			'.$chunk_output.'
			</div>';
    }
	}
	  			
$output='
            <div class="hidden" id="mchunk-html" style="display:none;">
            <!-- tab chunk-container -->
            <div class="tab-page" id="tabmTv">
            <h2 class="tab" id="header_tabmTv">multi_chunk_Tvs</h2>
            <div id="mTv-tab-body">
            '.$containers_output.'
			</div>
            </div>
            </div>';			

$output.='	
<script id="fcc-script-1" src="media/script/mootools/mootools.js" type="text/javascript"></script>
<script id="fcc-script-2" src="../assets/plugins/multi_chunk_Tvs/js/pageprerender_domready.js" type="text/javascript"></script>				
<script id="fcc-script-3" type="text/javascript">';
$output.="

";
$output.='
</script>';		
	
        //ob_flush(); 

}
        break;
    
    default : 
        // echo 'Over all... not good, will demage some things';
        return;
        break;     
}
return $output;
?>

