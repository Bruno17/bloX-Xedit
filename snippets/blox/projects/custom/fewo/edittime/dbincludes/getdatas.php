<?php

$eventID=$_GET['eventID'];
$now=time();

$tablename = $modx->getFullTableName('belegplan'); 
$query="select *";
$query.=" from ".$tablename;
$query.=" where id= ".$eventID;
$rs = $modx->db->query($query);
$events = $modx->db->makeArray($rs);
$event=$events[0];
$room_ID=$event['room_ID'];

////////////////////////////
//find next reservation
////////////////////////////
$timestampstart = $event['Timeend']+86400;
$timestamp_high = $event['Timeend']+86400*365;

$tablename = $modx->getFullTableName('belegplan'); 
$query="select *";
$query.=" from ".$tablename;
$query.=" where room_ID= ".$room_ID;
$query.=" and Time <= '$timestamp_high' ";
$query.= " and Timeend >= '$timestampstart' ";
$query.= " and published = '1' ";
$query.= " order by Time limit 1";
$rs = $modx->db->query($query);
$events = $modx->db->makeArray($rs);

if (count($events)>0){
$tmpevent=$events[0];
$timestamp_high=$tmpevent['Time'];
}
$event['range_high']=xetadodb_strftime("%Y-%m-%d", $timestamp_high);

////////////////////////////
//find prev reservation
////////////////////////////
$timestamp_low = $event['Time']-86400*30;
$timestampend = $event['Time']-86400;

$query="select *";
$query.=" from ".$tablename;
$query.=" where room_ID= ".$room_ID;
$query.=" and Time <= '$timestampend' ";
$query.= " and Timeend >= '$timestamp_low' ";
$query.= " and published = '1' ";
$query.= " order by Time DESC limit 1";
$rs = $modx->db->query($query);
$events = $modx->db->makeArray($rs);

if (count($events)>0){
$tmpevent=$events[0];
$timestamp_low=$tmpevent['Timeend'];
}
$event['range_low']=xetadodb_strftime("%Y-%m-%d", $timestamp_low);
$xettdatas=$event;
unset($easyevents);
unset($tmpevent);

?>