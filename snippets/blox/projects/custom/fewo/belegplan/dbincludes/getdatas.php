<?php
global $modx;

$roomsparent=$this->xetconfig['docids']['rooms'];
// Wohnungen suchen
$tablename1 = $modx->getFullTableName('site_content').' sc'; 

$query="select sc.id as room_ID,sc.pagetitle as room_title";
$query.=" from ".$tablename1;
$query.=" where parent= ".$roomsparent;
$query.= " and description <> 'noroom' ";

$rooms = $this->getevents($query); //$modx->db->makearray oder so
//$innerdatas['rooms']=$tmpevents;
$events=array();

foreach ($rooms as $room)
{
    $room_id=$room['room_ID'];
 
	$room['getDatasOnRender']='1';
	$events[$room_id]['groupdatas']=$room;

}
unset($easyevents);

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
