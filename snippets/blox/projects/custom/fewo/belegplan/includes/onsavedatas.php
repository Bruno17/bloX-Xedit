<?php
$Xettpath='./'.XETT_PATH;
require_once($Xettpath.'document.class.inc.php');

$createdby=$modx->getLoginUserID();
$rows = $this->getformfields();
foreach ($rows as $row){

$doc = new Document($row['`docid`']);
$doc->Set('parent',$row['`room_ID`']);
$doc->Set('content',$row['`content`']);
$doc->Set('description',$row['`description`']);
$doc->Set('template',$this->xetconfig['docids']['eventtemplate']);
$doc->Set('pagetitle',$row['`pagetitle`']);
$doc->Set('longtitle',$row['`longtitle`']);
$doc->Set('menutitle',$row['`menutitle`']);
$doc->Set('cacheable','0');
$doc->Set('hidemenu','1');
$doc->Set('published',$row['`published`']);
$doc->Set('isfolder','0');
$doc->Set('createdby',$createdby);
$doc->Set('tvEasyEvents_HideTime',$row['`hide_time`']);
$doc->Set('tvEasyEvents_Start',xetadodb_strftime("%d-%m-%Y %H:%M:%S", $row['`Time`']));
$doc->Set('tvEasyEvents_End',xetadodb_strftime("%d-%m-%Y %H:%M:%S", $row['`Timeend`']));
$doc->Save();
$parent = $doc->fields['id'];
//$output.='$docids'."['vcal_eventcal']='".$parent."';<br/>";	

}
//$savenormal='0';
?>
