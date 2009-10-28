<?php

$timestampstart = $this->xettcal->get_ts_daystart($row['timestamp']);
$timestampend = $this->xettcal->get_ts_dayend($row['timestamp']);

$tablename = $modx->getFullTableName('belegplan'); 
$query="select *";
$query.=" from ".$tablename;
$query.=" where room_ID= ".$row['room_ID'];
$query.=" and Time <= '$timestampend' ";
$query.= " and Timeend >= '$timestampstart' ";
$query.= " and published = '1' ";
$query.= " order by Time";
$rs = $modx->db->query($query);
$events = $modx->db->makeArray($rs);

//$xettdatas=array();
$xettdatas=$events[0];
$xettdatas['hasmid']=(count($events)>0)?'1':'0';
$xettdatas['hasstart']='0';
$xettdatas['hasend']='0';
foreach ($events as $event){
if ($this->xettcal->get_ts_daystart($event['Time'])==$timestampstart){
	$xettdatas['hasstart']='1';
	$xettdatas['hasmid']='0';
	$xettdatas['start']=$event;
}	
if ($this->xettcal->get_ts_daystart($event['Timeend'])==$timestampstart){
	$xettdatas['hasend']='1';
	$xettdatas['hasmid']='0';
	$xettdatas['end']=$event;
}		
}

$xettdatas['groupeventscount'] = count($events);

?>
