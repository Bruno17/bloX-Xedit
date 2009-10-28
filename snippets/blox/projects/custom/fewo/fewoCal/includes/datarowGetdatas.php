<?php

$timestampstart = $this->xettcal->get_ts_daystart($row['tsday']);
$timestampend = $this->xettcal->get_ts_dayend($row['tsday']);
$startDate = xetadodb_strftime("%Y-%m-%d", $timestampstart);
$endDate = xetadodb_strftime("%Y-%m-%d", $timestampend);
$contentFields = 'id,pagetitle,description';
$limit = 0;
$this->xettcal->xetconfig=$this->xetconfig;

$easyevents = $this->xettcal->getEvents($startDate, $endDate, $contentFields, $limit,$orderDir,$row['room_ID']);
$events=array();
if (count($easyevents) > 0)
{
    foreach ($easyevents as $event)
    {
    	$eedate = $this->xettcal->getDateFromTV($event['startDate']);
        $eetime = $this->xettcal->getTimeFromTV($event['startDate']);
        $arrdate = explode('-', $eedate);
        $arrtime = explode(':', $eetime);
        $event['Time'] = xetadodb_mktime($arrtime[0], $arrtime[1], $arrtime[2], $arrdate[1], $arrdate[2], $arrdate[0]);
        $eedate = $this->xettcal->getDateFromTV($event['endDate']);
        $eetime = $this->xettcal->getTimeFromTV($event['endDate']);
        $arrdate = explode('-', $eedate);
        $arrtime = explode(':', $eetime);
        $event['Timeend'] = xetadodb_mktime($arrtime[0], $arrtime[1], $arrtime[2], $arrdate[1], $arrdate[2], $arrdate[0]);
        //array_push($events, $event);
		$event['room_ID']=$row['room_ID'];
        $events[] = $event;
        ;
    }
}
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