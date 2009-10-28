<?php

//$timestampstart = $this->xettcal->get_ts_daystart($row['timestamp']);
//$timestampend = $this->xettcal->get_ts_dayend($row['timestamp']);

$tablename = $modx->getFullTableName('belegplan'); 
$query="select *";
$query.=" from ".$tablename;
$query.=" where room_ID= ".$row['room_ID'];
//$query.=" and Time <= '$timestampend' ";
//$query.= " and Timeend >= '$timestampstart' ";
//$query.= " and published = '1' ";
$query.= " order by Time";
$rs = $modx->db->query($query);
$events = $modx->db->makeArray($rs);
$xetconfig['countmonthevents']='1';
$xettdatas=$this->xettcal->makeDayArray($xetconfig,$events, $events[0]['Time']);


//$xettdatas=array();
//$xettdatas['innerrows']['datarow']=$events;

$xettdatas['groupeventscount'] = count($events);

?>
