<?php

$tablename='regatta_meldungen';

$custom['rennennr']=isset($rennennr)?$rennennr:'0';
$custom['docid']=isset($docid)?$docid:'0';
$custom['id_rennen']='855';
$custom['id_meldungen']='818';
$custom['id_aktivenliste']='815';

/*
$tasks=array();
$tasks ['Meldungen bearbeiten']='regatta_meldung';
$tasks ['Meldeergebnis']='meldeergebnis';
$tasks ['Programmheft']='programmheft';
$tasks ['Vereinsmeldungen']='vereinsmeldungen';
$tasks ['Rechnungen']='rechnungen';
$tasks ['Rennergebnisse']='rennergebnis';

$custom[$custom['id_meldungen'].'_tasks']=$tasks;

$tasks=array();
$tasks ['Start/Zielzeiten']='start_zielzeit';
$tasks ['Rennergebnis']='rennergebnis';
$tasks ['Punktewertung']='punktewertung';

$custom[$custom['id_rennen'].'_tasks']=$tasks;
//$docids['aktivenliste']='815';
*/
$project='regatta';



?>