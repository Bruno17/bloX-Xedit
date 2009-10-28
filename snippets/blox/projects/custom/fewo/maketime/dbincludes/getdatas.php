<?php

$getday=$_GET['newday'];
$getmonth=$_GET['newmonth'];
$getyear=$_GET['newyear'];
$room_ID=$_GET['room_ID'];
$now=time();
////////////////////////////
//find next reservation
////////////////////////////
$timestampstart = xetadodb_mktime(00, 00, 00, $getmonth, $getday, $getyear);
$timestamp_high = xetadodb_mktime(00, 00, 00, $getmonth+12, $getday, $getyear);

$tablename = $modx->getFullTableName('belegplan'); 
$query="select *";
$query.=" from ".$tablename;
$query.=" where room_ID= ".$room_ID;
$query.=" and Time >= '$timestampstart' ";
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
$timestamp_low = $now-86400*30;
$timestampend = xetadodb_mktime(00, 00, 00, $getmonth, $getday, $getyear);

$query="select *";
$query.=" from ".$tablename;
$query.=" where room_ID= ".$room_ID;
$query.= " and Timeend <= '$timestampstart' ";
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