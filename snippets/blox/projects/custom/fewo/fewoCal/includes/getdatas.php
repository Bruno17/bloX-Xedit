<?php
global $modx;

$roomsparent=$this->xetconfig['docids']['rooms'];
// Wohnungen suchen
$tablename1 = $modx->getFullTableName('site_content').' sc'; 

$query="select sc.id as room_ID,sc.pagetitle as room_title";
$query.=" from ".$tablename1;
$query.=" where id= ".$modx->documentIdentifier;
$query.= " and description <> 'noroom' ";

$rs = $modx->db->query($query); 
$rooms=$modx->db->makearray($rs);
//$innerdatas['rooms']=$tmpevents;

$events=array();

$room = $rooms[0];
$room_id = $room['room_ID'];
$room['getDatasOnRender'] = '1';
$events[$room_id]['groupdatas'] = $room;


//$events=array();
$firstmonth = abs($this->xetconfig['month']);
$year=$this->xetconfig['year'];
$timestampfirstday = xetadodb_mktime(0, 0, 0, $firstmonth, 01, $year);

$this->xettcal->events_grouped='1';
$cal=$this->xettcal->getMonthCal($year, $firstmonth);
$outerdata=$this->xettcal->makeMonthArray($this->xetconfig,$cal,array(),$events);
$outerdata['tsconfig'] = $timestampfirstday;
$xettdatas = $outerdata;


?>