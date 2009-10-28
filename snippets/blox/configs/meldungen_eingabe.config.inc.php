<?php

//$task=$modx->documentIdentifier==$custom['id_rennen']?'start_zielzeit':'regatta_meldung'; 
$task=(isset($_REQUEST['task']))?$_REQUEST['task']:$task;
if ($task=='programmheft'){
	$task='meldeergebnis';
	$custom['showteam']='1';
}


$custom['rennennr']=isset($rennennr)?$rennennr:'0';
//$custom['docid']=isset($docid)?$docid:'0';

//$fields='rennennr,startnummer,startzeit,meldestatus,rennstart,rennziel,meldeverein,nachmeldung,rennstatus,boot';

$fields='*';
//$parents=$modx->getPlaceholder('year_folder'); 
//$depth='1';
//$parents='497';
//$project='regatta';

$xedit_tabs='@TV:xeditTabs'; 
$id='Meldungen';
//$bloxfolder='Meldungen';
//$extenders='request';
$filter=(isset($_REQUEST['blox_filter']))?$_REQUEST['blox_filter']:'';
$documents=(isset($_REQUEST['blox_documents']))?$_REQUEST['blox_documents']:'';
//$docid=$modx->getPlaceholder('year_folder'); 
$orderBy='rennennr ASC,startnummer ASC'; 
$perPage='50';
if (isset($_REQUEST['contentType'])&& $_REQUEST['contentType']=='pdf'){
	$perPage='500';
}

//echo $docid;

if ($task=='punktewertung' || $task=='ausgefallen' || $task=='start_zielzeit_csv'){
	$filter='';
	$documents='';
	$perPage='500';
}
?>