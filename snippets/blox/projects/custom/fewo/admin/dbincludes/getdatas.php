<?php

$roomsparent=$this->xetconfig['docids']['rooms'];
// Wohnungen suchen
$tablename1 = $modx->getFullTableName('site_content').' sc'; 
$query="select sc.id as room_ID,sc.pagetitle as room_title";
$query.=" from ".$tablename1;
$query.=" where parent= ".$roomsparent;
$query.= " and description <> 'noroom' ";
$query.= " order by room_title ";

$rows = $this->getevents($query);

$rooms=array();

foreach ($rows as $room)
{
	$room['getDatasOnRender']='1';
	$rooms[]=$room;
}

$outerdata['innerrows']['rooms']=$rooms;
$xettdatas = $outerdata;	

?>
