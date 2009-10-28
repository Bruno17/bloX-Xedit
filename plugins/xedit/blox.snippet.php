<?php

/**
 * $Id$
 */

/*
 * snippet: bloX
 * 
 * Parameter:
 * 
 * 
 * alle oder die meisten Ditto-Parameter 
 * &sortable = `1` //evtl. automatisiert auf 1 setzen, wenn sortBy=menuindex, sonst machts ja keinen Sinn
 * &fillable = `1` //Container kann nicht befüllt werden
 * &removeable = `1` //Aus dem Container gezogene Blox werden nicht verschoben, sondern kopiert
 * ist noch zu klären, ob nur der minimierte (sichtbare) Inhalt oder die Seite komplett kopiert werden soll.
 * dann alte docid merken und als kopiert markieren.
 * &showblock = `blockname`
 * &docid = `documentidetifier` //nur in verbindung mit blox_containern um auch mal blox_container aus anderen Dokumenten auflisten zu können.
 * &containertype = `type` //wird in der Regel automatisiert festgesetzt, denke ich
 * Typen: 
 * blox_container: container ist oder wird Unterordner des aktuellen Dokuments
 * parent_container: container beinhaltet Dokumente eines einzigen beliebigen Ordners
 * container: container mit dokumenten aus verschiedenen Ordnern. 
 * In diesen Container können keine neuen Blöcke reingezogen werden. 
 * Nur die Inhalte sind bearbeitbar. Sortierbarkeit macht auch wenig Sinn.
 * 
 * &htmlouter=`div` // div,ul,table
 * 
 * 
 */



include $pluginpath.'/config.php';
include $pluginpath.'/boot.php'; 


//macht das Sinn mit der docid, nochmal drüber nachdenken
//ist eigentlich Blödsinn. Kann immer übergeben werden wo benötigt, oder??
$docid=(isset($docid))?$docid:$modx->documentIdentifier;
//$containertype=(isset($containertype))?$containertype:'blox_container';

$xedit->docid=$docid;

//permissionTest

//$xedit->getUserPermissions();
//$permission='caneditalldocs';
//$xedit->checkpermission($permission);

$action=(isset($action))?$action:'showChunks';

if (isset($chunkname)) {
    $action='prepareDittoTpl';
}

if (isset($formElementValue) && isset($formElementTv)) {
    $action='makeFormelement';
}

/*
if (isset($allowed_chunks)){
	$action='showChunkSelection';
}
*/

switch($action) {
    /* nix extenter 
    case 'DittoExtenter':
       
	   //für parents und docids die chunks untersuchen, falls nicht tpl festgesetzt ist
	   //ditto kann dann voerst nur in mit chunk-tpl oder mit chunkname-tv
	   //und mit den parametern docids bzw, parents richtig verarbeitet werden, oder??
	   //untersuchen wie das dann laufen muß
	   
       $childChunks = $xedit->getChildChunks($parents);
	   $chunknames=array();
	   foreach ($childChunks as $chunk)
        {
            $chunknames[$chunk['chunkname']] = $chunk['chunkname'];
        }
       $bcc = new blox_Chunk_Collection($chunknames, $xedit);
	   //$xedit->collectVarsFromChunks($chunknames);
	   $bcc->setChunkContentsPH($block);  
	   $allChunkFieldNames=$bcc->mergeAllFields();
		if (isset($docids)){
			$params['docids'] = $docids;	
		}else{
			$params['parents'] = $childids[$showblock];
		}		
		if (isset($parents)){
		    $params['parents'] = $parents;	
		}

        $params['hiddenFields'] = implode(',', $allChunkFieldNames);
        //$params['hiddenFields'] = implode(',', $xedit->allChunkTvNames);//vorl�ufig noch 
        $params['tpl'] = '@CODE:[[bloX? &chunkname=`[+chunkname+]`]]';
	  
	   $modx->setPlaceholder('xedit_ditto_params',$params);
	
	break;
	*/
    case 'makeFormelement':
        require_once($this->pluginpath.'inc/formElements.class.inc.php');
        $fe= new formElements();
        $tv=$xedit->getNewMultiTvs(array($formElementTv));
        $tv=$tv[$formElementTv];
        $tv['content']=$formElementValue;
        $output=$fe->makeFormelement($tv,$prefix='tv');
        
        break;


    case 'prepareDittoTpl':



        $htmlinner = isset($htmlinner)?$htmlinner:'div';
        $xedit->docid=(isset($docid))?$docid:$modx->documentIdentifier;

        //$collectedchunks = $modx->getPlaceholder('collectedchunks');
        //$chunk_content = $xedit->prepareChunkForXedit($collectedchunks[$chunkname]['snippet'], $block);

        //Ende dragelement

        $chunk_contents=$modx->getPlaceholder('chunkcontents');
        $chunk_content=(isset($tpl))?$chunk_contents[$tpl]:$chunk_contents[$chunkname];

        $ditto_tpl=(isset($tpl))?'tpl="[+ditto_tpl+]"':'';
        if ($xedit->userPermissions['cancreatedocs'] == '1') {
            $prepareforxedit=true;
        }

        if ($prepareforxedit) {

            $collapsed='
 		<div class="collapsed" style="display:none">
		<h2><span fieldname="pagetitle" >[+pagetitle+]</span></h2>('.$chunkname.')	
        </div>
        ';		
            $xtools=' ><span class="drag">drag</span><span class="xtrash">trash</span><span class="save">save</span>';
        }

        $innerout=($htmlinner=='tr')?$chunk_content.'<td class="xtools"'.$xtools.'</td>':'<div class="xtools"'.$xtools.'</div>'.$chunk_content.$collapsed;

        $output = '
        <'.$htmlinner.' class="bildchunk blox published_[+published+] clearfix" resourceclass="modDocument" rowid="[+id+]" menuindex="[+menuindex+]" parent="[+parent+]" published="[+published+]" chunkname="[+chunkname+]" '.$ditto_tpl.'>
		'.$innerout.'
        </'.$htmlinner.'>';

        if ($xcc_buttons == '1') {
            $button_caption = '[+pagetitle+]';
            $published = '[+published+]';
            $chunkname = '[+chunkname+]';
            $drag_content = (isset($drag_chunk))?$chunk_contents[$drag_chunk]:'<h2>[+pagetitle+]</h2>([+chunkname+])';
            $btn_docid = '[+id+]';
            $btn_savemode = 'copy';
            $output = $xedit->prepareXccButton($button_caption, $published, $chunkname, $drag_content, $btn_docid, $btn_savemode);
        }


        break;
    case 'showChunks':

        $removeable=(isset($removeable))?$removeable:'1';
        $fillable=(isset($fillable))?$fillable:'1';
        $htmlouter=isset($htmlouter)?$htmlouter:'div';
        $hideblock=isset($hideblock)?$hideblock:'0';
        $theadTpl=isset($theadTpl)?$theadTpl:'';
        if ($hideblock !== '1') {
            switch ($htmlouter) {
                case 'ul':
                    $htmlinner='li';
                    break;
                case 'table':
                    $htmlinner='tr';
                    break;
                default:
                    $htmlinner='div';
                    break;
            }

            //$UserPermissions=$modx->getPlaceholder('xedit_userPermissions');
            //$UserPermissions = $xedit->getUserPermissions();



            $allParameters = func_get_args();
            $allParameters[1]['htmlouter']=$htmlouter;
            $blox_Ditto = new blox_Ditto($allParameters, $xedit);
            $chunkname = '[+chunkname+]';
            $ditto_tpl = '';

            if ( isset ($tpl)) {
                $chunknames = array ($tpl=>$tpl);
                /*
				$bcc = new blox_Chunk_Collection($chunknames, $xedit);
                $bcc->setChunkContentsPH($block);
                $allChunkFieldNames = $bcc->mergeAllFields();
                $blox_Ditto->set('hiddenFields', implode(',', $allChunkFieldNames));
				*/
                $ditto_tpl = $tpl;
                $modx->setPlaceholder('ditto_tpl',$tpl);
                $ditto_tpl = '&tpl=`[+ditto_tpl+]`';
            }
            else {

                if ($blox_Ditto->containertype == 'blox_container') {
                    $childids = $xedit->getChildrenIds($xedit->docid);
                    if ( isset ($childids[$showblock])) {
                        $containerNames=array($showblock);
                        $xedit->getChunkContainers($xedit->docid,$containerNames);
                        $chunknames = $xedit->getChunksFromContainers();
                        //print_r($xedit->chunkContainers);
                        /*
						$bcc = new blox_Chunk_Collection($chunknames, $xedit);
                        $bcc->setChunkContentsPH($block);
                        $allChunkFieldNames = $bcc->mergeAllFields();
						$blox_Ditto->set('hiddenFields', implode(',', $allChunkFieldNames));
                        */
                        //print_r($allChunkFieldNames);
                        //$blox_Ditto->containerparent = $xedit->docid;
                        $blox_Ditto->set('parents', $childids[$showblock]);
                    }else {
                        $blox_Ditto->forceNoResults='1';

                    }
                }

                if ($blox_Ditto->containertype == 'parent_container') {

                    $xedit->getChunkContainersFromParents($blox_Ditto->ditto_params['parents'], $showblock);
                    $chunknames = $xedit->getChunksFromContainers();
                    /*
					$bcc = new blox_Chunk_Collection($chunknames, $xedit);
                    $bcc->setChunkContentsPH($block);
                    $allChunkFieldNames = $bcc->mergeAllFields();
                    $blox_Ditto->set('hiddenFields', implode(',', $allChunkFieldNames));
                    */            
                }
                if ($blox_Ditto->containertype == 'container') {
                //let ditto run to find all chunknames in the Tv chunkname
                //only ditto knows the documents to get at this time
                //use ditto for that in all cases (blox_container and parent_container) too???
                    $ditto_output = '-';
                    $blox_Ditto->set('noResults', $ditto_output);
                    $blox_Ditto->set('tpl', '@CODE:[+chunkname+],');
                    $blox_Ditto->set('display', 'all');
                    $blox_Ditto->set('showPublishedOnly', ($xedit->userPermissions['canseeunpublisheddocs'] == '1')?'0':'1');
                    $ditto_chunknames = $blox_Ditto->run('1');
                    if ($ditto_chunknames !== '-') {
                        $ditto_chunknames = substr($ditto_chunknames, 0, -1);
                        $chunknames = explode(',',$ditto_chunknames);
                    }
                }
            }

            $block=isset($block)?$block:'';
            $bcc = new blox_Chunk_Collection($chunknames, $xedit);
            $bcc->setChunkContentsPH($block);
            $allChunkFieldNames = $bcc->mergeAllFields();
            if (isset($orderByField)) {
                $allChunkFieldNames[]=$orderByField;
            }


            $blox_Ditto->set('hiddenFields', implode(',', $allChunkFieldNames));



            //parents hat vorrang vor documents, oder??

            /* das werk irgendwie anders untersuchen
			if (isset($documents)){
				$blox_Ditto->set('docids', $documents);
			}else{
				$blox_Ditto->set('parents', $childids[$showblock]);
			}		
			if (isset($parents)){
			    $blox_Ditto->set('parents', $parents);
			}
			*/
            $xcc_buttons=(isset($containertype)&&$containertype=='xcc_container')?'1':'0';
            $blox_Ditto->htmlinner=$htmlinner;
            $blox_Ditto->set('noResults',isset($noResults)?$noResults:' ');

            if (!isset($blox_Ditto->ditto_params['orderBy'])) {
                $blox_Ditto->set('orderBy',(isset($orderBy))?$orderBy:'menuindex ASC');
            }
            $drag_chunk=isset($drag_chunk)?'&drag_chunk=`'.$drag_chunk.'`':'';

            $blox_Ditto->set('tpl', '@CODE:[[bloX? &chunkname=`'.$chunkname.'`&htmlinner=`'.$htmlinner.'`&xcc_buttons=`'.$xcc_buttons.'`'.$ditto_tpl.$drag_chunk.']]');
            $blox_Ditto->set('display', 'all');
            $blox_Ditto->set('showPublishedOnly',($xedit->userPermissions['canseeunpublisheddocs']=='1')?'0':'1' );
            $output = $blox_Ditto->run();
        }
        break;

/*
 * der Teil ist jetzt im Plugin
 * 
    case 'showChunkSelection':


//permissionTest
//ich denke sowas muß in ein plugin 
//die userpermissions dann in ne $_SESSION[xedit_userPermissions]
//oder in placeholder?????? oder $_GLOBALS?????????
//damit die userPermissions nur einmal gelesen werden müssen???

$UserPermissions=$xedit->getUserPermissions();
//glaub ich keine gute Idee
//$modx->setPlaceholder('xedit_userPermissions',$UserPermissions);

//mal pauschal um das ganze rumgebaut als PermissionTest 
if ($UserPermissions['cancreatedocs']=='1'){
		$allowed=explode('|',$allowed_chunks);
		$allowed_chunks=array();
		$chunknames=array();
		
		foreach ($allowed as $value){
			$container_array=explode(':',$value);
			$allowed_chunks[$container_array[0]]=explode(',',$container_array[1]);
	        foreach ($allowed_chunks[$container_array[0]] as $chunkname){
	        	$chunknames[$chunkname]=$chunkname;
	        }	
		}
		
		$bcc = new blox_Chunk_Collection($chunknames, $xedit);
		//$xedit->collectVarsFromChunks($chunknames);
		$bcc->allChunkTvNames[] = 'menuindex';
        $tvIds = array_flip($bcc->allChunkTvNames);
        $xedit->getNewMultiTvs($tvIds);		
		//Umbau wenn Marc soweit ist, dann die chunks für alle Blöcke bereitstellen
	
		$chunks=$allowed_chunks['left'];

		$chunks_output=$bcc->prepareChunkSelection($chunks,'left');

		$modx->setPlaceholder('allowed_chunks', $allowed_chunks);
	
};
			


 // Save Button hinzugefügt. Erstmal ein einfacher Save Button für alles.
		$userid=$modx->getLoginUserID('mgr');
		$output='<div id="chunks">
			<h2>Available Chunks, click to insert:</h2>
			'.$chunks_output.'User:'.$userid.'
			<a id="saveall">Save</a>
			</div>
			';	
        //$output.='<div class="chunks">allowed_chunks: '.$allowed_chunks.'</div>';
        break;
*/
}


?>

