<?php

$this->xettcal->xetconfig=$this->xetconfig;
$easyevents=$this->xettcal->getEvents(null,null,null,0,'ASC',$row['room_ID'],'1');
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


$xetconfig['countmonthevents']='1';
$xettdatas=$this->xettcal->makeDayArray($xetconfig,$events, $events[0]['Time']);
$xettdatas['groupeventscount'] = count($events);

?>
