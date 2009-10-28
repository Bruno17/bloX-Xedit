<?php
/*
$task=$modx->documentIdentifier==$custom['id_rennen']?'rennen_start':'regatta_meldung'; 
$task=(isset($_REQUEST['task']))?$_REQUEST['task']:$task;
if ($task=='programmheft'){
	$task='meldeergebnis';
	$custom['showteam']='1';
}
*/


if ( isset ($_REQUEST['get_tv_tabs'])){
	$table=$modx->getFullTablename('regatta_meldungen');
	$rs=$modx->db->select('startnummer',$table,'id='.$_REQUEST['rowid']);
	$row=$modx->db->getRow($rs);
	$_REQUEST['startnummer']=$row['startnummer'];
	
}

$jahr=$modx->getPlaceholder('regatta_jahr');
$task='save_zeiten';
$fields='id,rennennr,startnummer,rennstatus,rennstart,rennziel';
//$parents=$modx->getPlaceholder('year_folder'); 
$id='Meldungen';
//$bloxfolder='Meldungen';
$tablename='regatta_start_zielzeiten';
$filter=(isset($_REQUEST['startnummer']))?'startnummer|'.$_REQUEST['startnummer'].'|eq++eventjahr|'.$jahr.'|eq':'';
$orderBy='rennennr ASC,startnummer ASC'; 
$perPage='500';




//if(isset($_REQUEST['startnummer']))

?>