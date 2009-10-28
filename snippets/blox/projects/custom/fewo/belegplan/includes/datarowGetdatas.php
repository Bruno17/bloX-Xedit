<?php

$timestampstart = $this->xettcal->get_ts_daystart($row['tsday']);
$timestampend = $this->xettcal->get_ts_dayend($row['tsday']);
$startDate = xetadodb_strftime("%Y-%m-%d", $timestampstart);
$endDate = xetadodb_strftime("%Y-%m-%d", $timestampend);
$contentFields = 'id,pagetitle,description';
$limit = 0;

// Include Easy Events class and instantiate object
include_once $modx->config['base_path'].'assets/snippets/EasyEvents/EasyEvents.class.php';
$ee = new EasyEvents($row['room_ID']);
$easyevents = $ee->getEvents($startDate, $endDate, $contentFields, $limit);
$events=array();
if (count($easyevents) > 0)
{
    foreach ($easyevents as $event)
    {
        $eedate = $ee->getDateFromTV($event['startDate']);
        $eetime = $ee->getTimeFromTV($event['startDate']);
        $arrdate = explode('-', $eedate);
        $arrtime = explode(':', $eetime);
        $event['Time'] = xetadodb_mktime($arrtime[0], $arrtime[1], $arrtime[2], $arrdate[1], $arrdate[2], $arrdate[0]);
        $eedate = $ee->getDateFromTV($event['endDate']);
        $eetime = $ee->getTimeFromTV($event['endDate']);
        $arrdate = explode('-', $eedate);
        $arrtime = explode(':', $eetime);
        $event['Timeend'] = xetadodb_mktime($arrtime[0], $arrtime[1], $arrtime[2], $arrdate[1], $arrdate[2], $arrdate[0]);
        //array_push($events, $event);
        $events[] = $event;
        ;
    }
}
$xettdatas=$events[0];
$xettdatas['groupeventscount'] = count($easyevents);
?>
