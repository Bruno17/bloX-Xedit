<?php
/**
 * $Id: onmanagerpageinit.inc.php 
 * 
 * 
 */


switch( $this->manager_action )
{

    case '3':     // Dokumenübersicht
    $this->docid=$_REQUEST['id'];	
    
	if (isset($_POST['save_mChunks'])){
    	$this->saveBackendChunks();
		$reload_output='
		<script type="text/javascript">
    	top.mainMenu.reloadtree();
        </script>
		';
		
    }

    if (1)
    {
    	
    $sort_output='
<style type="text/css">
#addTask {
	width: 490px;
	margin: 10px;
	background: #efefef;
	border: 1px solid #a7a7a7;
	text-align: center;
	padding: 5px;
}

#todo li .drag-handle,ol.chunklist li .drag-handle {
	cursor: pointer;
	width: 16px;
	height: 16px;
	background: url("media/style/MODxLight/images/icons/sort.png") no-repeat center;
	float: right;
	margin-right: 5px;
}

ol.chunklist a.remove_li {
background:transparent url(media/style/MODxLight/images/icons/delete.gif) no-repeat scroll center center;
float:right;
margin-right:10px;
text-indent:-999em;
width:18px;
}

.deleted_1 a.remove_li {
border:solid 1px red;
}

ol.chunklist a.unpublish_li {
background:transparent url(media/style/MODxLight/images/icons/delete.png) no-repeat scroll center center;
float:right;
margin-right:10px;
text-indent:-999em;
width:18px;
}
.published_0 a.unpublish_li {
border:solid 1px red;
}

ol.chunklist li{
	list-style: none;
}

ol.chunklist p {
	float: left;
}

ol.chunklist div.sectionHeader {
	height: 17px;
}


#todo {
	list-style: none;
	border: 1px solid #ccc;
	margin: 10px auto 10px auto;
	width: 75%;
	padding: 10px 5% 10px 5%;
}

#listArea {
	width: 500px;
	border: 1px solid #ccc;
	background: #efefef;
	margin: 10px;
}

ol.chunklist input, ol.chunklist select {
float:left;
margin-right:18px;
}

</style>
';   		
    $this->getChunkContainers($this->docid);
	$chunknames=$this->getchunknames($this->multiCategories);
    $containers_output='';
	//print_r($this->chunkContainers);
	//$_SESSION['chunkContainers']=$this->chunkContainers;
	if (count($this->chunkContainers)>0){
	foreach ($this->chunkContainers as $containerkey=>$chunkContainer){
        
    	$chunk_output='';
		if (!empty($chunkContainer['value'])&&count($chunkContainer['value'])>0){
		foreach ($chunkContainer['value'] as $chunkkey=>$chunk){
			$select=$this->make_select("mChunk_name_".$containerkey."[]",$chunknames,$chunk['chunkname'],'add_chunkname');			
			$deletedclass=($chunk['deleted']=='1')?' deleted_1':'';
			$publishedclass=($chunk['published']=='0')?' published_0':'';
			$chunk_output.='
			<li>
			<div class="sectionHeader'.$deletedclass.$publishedclass.'">
			<input type="text" name="mChunk_caption_'.$containerkey.'[]" value="'.$chunk['pagetitle'].'"/>
			'.$select.'
			<input class="input_docid" type="hidden" name="mChunk_docid_'.$containerkey.'[]" value="'.$chunk['id'].'"/>
            <input class="input_published" type="hidden" name="mChunk_published_'.$containerkey.'[]" value="'.$chunk['published'].'"/>
            <input class="input_deleted" type="hidden" name="mChunk_deleted_'.$containerkey.'[]" value="'.$chunk['deleted'].'"/>
			<div class="drag-handle"></div>
			</div>
			</li>';   	    		
    	}
		}
$select_chunks=$this->make_select('add_chunkname_'.$containerkey,$chunknames,'','add_chunkname');			

//noch die id des documentenordners aus dem content der containerTV???
$containers_output.='   
            <div class="sectionBody">
            <h3> '.$chunkContainer['caption'].' ('.$this->docid.')</h3>
			'.$select_chunks.'
			<a name="add_chunk_'.$containerkey.'" href="#" id="add_chunk_'.$containerkey.'" class="add_chunk">add chunk</a> 
			<input type="hidden" name="container_key[]" value="'.$containerkey.'" id="container_key_'.$containerkey.'" class="container_key"/>
            <input type="hidden" name="container_caption[]" value="'.$chunkContainer['caption'].'" id="container_key_'.$containerkey.'" class="container_key"/>			
			<input class="send" name="save_mChunks" type="submit" value="Save" />
			<div id="data">
			</div>
			<div class="split"></div>
			<ol id="chunklist_'.$containerkey.'" class="chunklist">
			'.$chunk_output.'
            </ol>
			</div>';
    }
	}

	//ob_start();

$output='
            <div class="hidden" id="mchunk-html" style="display:none;">
            <!-- tab chunk-container -->
            <div class="tab-page" id="tabmchunks">
            <h2 class="tab" id="header_tabmchunks">Manage dyn-chunks</h2>
			<div id="mTv-tab-body" >
            '.$sort_output.' 
			<form method="post" action="" enctype="multipart/form-data">
            '.$containers_output.' 
			</form>
            </div>
            </div>
			</div>
			';			

$output.='
<script id="mchunks-script-1" src="media/script/mootools/mootools.js" type="text/javascript"></script>	
<script id="mchunks-script-2" src="../assets/plugins/multi_chunk_Tvs/js/ondomready.js"></script>
<script id="mchunks-script-3" src="../assets/plugins/multi_chunk_Tvs/js/pageinit_domready.js" type="text/javascript"></script>			

';

$output.=$reload_output;

	
        //ob_flush(); 

}
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

        break;
    
    default : 
        // echo 'Over all... not good, will demage some things';
        return;
        break;     
}
return $output;
?>

