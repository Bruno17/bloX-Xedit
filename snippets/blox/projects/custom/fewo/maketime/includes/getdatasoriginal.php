<?php

$getday=$_GET['newday'];
$getmonth=$_GET['newmonth'];
$getyear=$_GET['newyear'];
$room_id=$_GET['room_ID'];
$now=time();
////////////////////////////
//find next reservation
////////////////////////////
$timestampstart = xetadodb_mktime(00, 00, 00, $getmonth, $getday, $getyear);
$timestamp_high = xetadodb_mktime(00, 00, 00, $getmonth+12, $getday, $getyear);
$startDate=xetadodb_strftime("%Y-%m-%d", $timestampstart);
$endDate=xetadodb_strftime("%Y-%m-%d", $timestamp_high);
$contentFields='id,pagetitle,description';
$limit=2;
$this->xettcal->xetconfig=$this->bloxconfig;
$easyevents=$this->xettcal->getEvents($startDate,$endDate,$contentFields,$limit,$orderDir,$room_id);
if (count($easyevents)>0){
	foreach ($easyevents as $tmpevent)
	{
		$eedate=$this->xettcal->getDateFromTV($tmpevent['startDate']);
        $arrdate=explode('-',$eedate);
        $tmp_ts=xetadodb_mktime(00, 00, 00, $arrdate[1], $arrdate[2], $arrdate[0]);
		if ($tmp_ts > $timestampstart){
			$timestamp_high=$tmp_ts;
			break;
		}
	}
}
$event['range_high']=xetadodb_strftime("%Y-%m-%d", $timestamp_high);

////////////////////////////
//find prev reservation
////////////////////////////
$timestamp_low = $now-86400*30;
$timestampend = xetadodb_mktime(00, 00, 00, $getmonth, $getday, $getyear);
$startDate=xetadodb_strftime("%Y-%m-%d", $timestamp_low);
$endDate=xetadodb_strftime("%Y-%m-%d", $timestampend);
$contentFields='id,pagetitle,description';
$limit=1;
$easyevents=$this->xettcal->getEvents($startDate,$endDate,$contentFields,$limit,'DESC',$room_id);
if (count($easyevents)>0){
$tmpevent=$easyevents[0];
$eedate=$this->xettcal->getDateFromTV($tmpevent['endDate']);
//$eetime=$ee->getTimeFromTV($event['endDate']);
$arrdate=explode('-',$eedate);
//$arrtime=explode(':',$eetime);
//$event['Timeend']=adodb_mktime(00, 00, 00, $arrdate[1], $arrdate[2], $arrdate[0]);
$timestamp_low=xetadodb_mktime(00, 00, 00, $arrdate[1], $arrdate[2], $arrdate[0]);
}
$event['range_low']=xetadodb_strftime("%Y-%m-%d", $timestamp_low);
$bloxdatas=$event;
unset($easyevents);
unset($tmpevent);

?>