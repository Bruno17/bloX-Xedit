<?php

$this->xettcal->xetconfig = $this->bloxconfig;
//$sufix='__0_null';
$sufix = '';

foreach ($_REQUEST as $key=>$value)
{
    //$key = str_replace($sufix, '', $key, $count);
    /*
     if ($count>0){
     $posts[$key]=$value;
     }
     */
    $posts[$key] = $value;
}


$now = time();

if ( isset ($_REQUEST['eventID']))
{
    $eventID = $_REQUEST['eventID'];
    //require_once($modx->config['base_path'].$GLOBALS['blox_path'].'inc/document.class.inc.php');
    $doc = new Document($eventID);
    $event = $doc->fields;
    $event[hide_time] = $doc->GetTv(EasyEvents_HideTime);
    $tvVal = $doc->GetTv(EasyEvents_Start);
    $eedate = substr($tvVal, 6, 4).'-'.substr($tvVal, 3, 2).'-'.substr($tvVal, 0, 2);
    $eetime = substr($tvVal, 11, 8);
    $startdate = explode('-', $eedate);
    $starttime = explode(':', $eetime);
    $event['Time'] = xetadodb_mktime($starttime[0], $starttime[1], $starttime[2], $startdate[1], $startdate[2], $startdate[0]);
    $tvVal = $doc->GetTv(EasyEvents_End);
    $eedate = substr($tvVal, 6, 4).'-'.substr($tvVal, 3, 2).'-'.substr($tvVal, 0, 2);
    $eetime = substr($tvVal, 11, 8);
    $enddate = explode('-', $eedate);
    $endtime = explode(':', $eetime);
    $event['Timeend'] = xetadodb_mktime($endtime[0], $endtime[1], $endtime[2], $enddate[1], $enddate[2], $enddate[0]);
    $room_id = $event['parent'];
    ////////////////////////////
    //find next reservation
    ////////////////////////////
    $timestampstart = xetadodb_mktime($endtime[0], $endtime[1], $endtime[2], $enddate[1], $enddate[2]+1, $enddate[0]);
    $timestamp_high = xetadodb_mktime(00, 00, 00, $enddate[1]+12, $enddate[2], $enddate[0]);
    $startDate = xetadodb_strftime("%Y-%m-%d", $timestampstart);
    $endDate = xetadodb_strftime("%Y-%m-%d", $timestamp_high);
    $contentFields = 'id,pagetitle,description';
    $limit = 1;

    //$easyevents=$ee->getEvents($startDate,$endDate,$contentFields,$limit);
    $this->xettcal->xetconfig = $this->bloxconfig;
    $easyevents = $this->xettcal->getEvents($startDate, $endDate, $contentFields, $limit, $orderDir, $room_id);
    if (count($easyevents) > 0)
    {
        $tmpevent = $easyevents[0];
        $eedate = $this->xettcal->getDateFromTV($tmpevent['startDate']);
        //$eetime=$ee->getTimeFromTV($event['startDate']);
        $arrdate = explode('-', $eedate);
        //$arrtime=explode(':',$eetime);
        $timestamp_high = xetadodb_mktime(00, 00, 00, $arrdate[1], $arrdate[2], $arrdate[0]);
    }
echo    $event['range_high'] = xetadodb_strftime("%d-%m-%Y", $timestamp_high);
    $timestamp_low = $event['Time']-86400*30;
    $timestampend = xetadodb_mktime(00, 00, 00, $startdate[1], $startdate[2]-1, $startdate[0]);
}
else
{
    $getday = $_REQUEST['newday'.$sufix];
    $getmonth = $_REQUEST['newmonth'.$sufix];
    $getyear = $_REQUEST['newyear'.$sufix];
    $room_id = $_REQUEST['room_ID'.$sufix];
	$event['Time']=xetadodb_mktime(00, 00, 00, $getmonth, $getday, $getyear);
    $event['Timeend'] = $event['Time'];
	$event['published'] = '1';

    ////////////////////////////
    //find next reservation
    ////////////////////////////

	$timestampstart = $event['Time'];
    $timestamp_high = xetadodb_mktime(00, 00, 00, $getmonth+12, $getday, $getyear);
    $startDate = xetadodb_strftime("%Y-%m-%d", $timestampstart);
    $endDate = xetadodb_strftime("%Y-%m-%d", $timestamp_high);
    $contentFields = 'id,pagetitle,description';
    $limit = 2;
    
    $easyevents = $this->xettcal->getEvents($startDate, $endDate, $contentFields, $limit, $orderDir, $room_id);
    if (count($easyevents) > 0)
    {
        foreach ($easyevents as $tmpevent)
        {
            $eedate = $this->xettcal->getDateFromTV($tmpevent['startDate']);
            $arrdate = explode('-', $eedate);
            $tmp_ts = xetadodb_mktime(00, 00, 00, $arrdate[1], $arrdate[2], $arrdate[0]);
            if ($tmp_ts > $timestampstart)
            {
                $timestamp_high = $tmp_ts;
                break;
            }
        }
    }
    $event['range_high'] = xetadodb_strftime("%d-%m-%Y", $timestamp_high);
    $timestamp_low = $now-86400*30;
    $timestampend = xetadodb_mktime(00, 00, 00, $getmonth, $getday, $getyear);
}

////////////////////////////
//find prev reservation
////////////////////////////

$startDate = xetadodb_strftime("%Y-%m-%d", $timestamp_low);
$endDate = xetadodb_strftime("%Y-%m-%d", $timestampend);
$contentFields = 'id,pagetitle,description';
$limit = 1;
$easyevents = $this->xettcal->getEvents($startDate, $endDate, $contentFields, $limit, 'DESC', $room_id);
if (count($easyevents) > 0)
{
    $tmpevent = $easyevents[0];
    $eedate = $this->xettcal->getDateFromTV($tmpevent['endDate']);
    //$eetime=$ee->getTimeFromTV($event['endDate']);
    $arrdate = explode('-', $eedate);
    //$arrtime=explode(':',$eetime);
    //$event['Timeend']=adodb_mktime(00, 00, 00, $arrdate[1], $arrdate[2], $arrdate[0]);
    $timestamp_low = xetadodb_mktime(00, 00, 00, $arrdate[1], $arrdate[2], $arrdate[0]);
}
$event['range_low'] = xetadodb_strftime("%d-%m-%Y", $timestamp_low);
$bloxdatas = $event;
$bloxdatas['POST'] = $posts;
unset ($easyevents);
unset ($tmpevent);

?>
