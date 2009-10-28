<?php
//print_r($row);
$timestampstart = $this->xettcal->get_ts_daystart($row['timestamp']);
$timestampend = $this->xettcal->get_ts_dayend($row['timestamp']);
$startDate = xetadodb_strftime("%Y-%m-%d", $timestampstart);
$endDate = xetadodb_strftime("%Y-%m-%d", $timestampend);
$contentFields = 'id,pagetitle,description';
$limit = 0;
$this->xettcal->xetconfig=$this->bloxconfig;
// Include Easy Events class and instantiate object
//include_once $modx->config['base_path'].'assets/snippets/EasyEvents/EasyEvents.class.php';
//$ee = new EasyEvents($row['room_ID']);

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
$bloxdatas=$events[0];
$bloxdatas['hasmid']=(count($events)>0)?'1':'0';
$bloxdatas['hasstart']='0';
$bloxdatas['hasend']='0';
foreach ($events as $event){
if ($this->xettcal->get_ts_daystart($event['Time'])==$timestampstart){
	$bloxdatas['hasstart']='1';
	$bloxdatas['hasmid']='0';
	$bloxdatas['start']=$event;
}	
if ($this->xettcal->get_ts_daystart($event['Timeend'])==$timestampstart){
	$bloxdatas['hasend']='1';
	$bloxdatas['hasmid']='0';
	$bloxdatas['end']=$event;
}		
}

$bloxdatas['groupeventscount'] = count($events);

?>
