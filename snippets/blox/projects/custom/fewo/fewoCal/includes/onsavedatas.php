<?php
//$createdby=$modx->getLoginUserID();
$rows = $this->getformfields();
$xettdatas=$events[0];
$row=$rows[0];
$message=$row;
//check if there is no reservation
$timestampstart = $row['`Time`']+86400;
$timestampend = $row['`Timeend`']-86400;
$startDate = xetadodb_strftime("%Y-%m-%d", $timestampstart);
$endDate = xetadodb_strftime("%Y-%m-%d", $timestampend);
$contentFields = 'id,pagetitle,description';
$limit = 0;
$this->xettcal->xetconfig=$this->xetconfig;
$easyevents = $this->xettcal->getEvents($startDate, $endDate, $contentFields, $limit,$orderDir,$row['`room_ID`']);
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
		
        if (($_POST['action']=='update')&&($event['id']==$_POST['event_ID'])){
            
        }else		
		{
			$events[] = $event;
		}
        
        ;
    }
}


if (count($events)>0){
	$message['saved_ok']='0';
	$message['messagetext'] = 'Da war einer schneller mit der Buchung';
}else{
	$message['saved_ok']='1';
	$message['messagetext'] = 'Alles ok';

$Xettpath='./'.XETT_PATH;
require_once($Xettpath.'document.class.inc.php');

$createdby=$modx->getLoginUserID();

$doc = new Document($_POST['event_ID']);
$doc->Set('parent',$row['`room_ID`']);
$doc->Set('content',$row['`content`']);
$doc->Set('description',$row['`description`']);
$doc->Set('template',$row['`template`']);
$doc->Set('pagetitle',$row['`pagetitle`']);
$doc->Set('longtitle',$row['`longtitle`']);
$doc->Set('menutitle',$row['`menutitle`']);
$doc->Set('cacheable','0');
$doc->Set('hidemenu','1');
$doc->Set('published',$row['`published`']);
$doc->Set('isfolder','0');
$doc->Set('createdby',$createdby);
$doc->Set('tvEasyEvents_HideTime',$row['`hide_time`']);
$doc->Set('tvEasyEvents_Start',xetadodb_strftime("%d-%m-%Y %H:%M:%S", $row['`Time`']));
$doc->Set('tvEasyEvents_End',xetadodb_strftime("%d-%m-%Y %H:%M:%S", $row['`Timeend`']));
$doc->Save();
$parent = $doc->fields['id'];

$tsmonth = xetadodb_mktime(0, 0, 0, xetadodb_strftime("%m", $row['`Time`']), 01, xetadodb_strftime("%Y", $row['`Time`'])); 
$monthend = xetadodb_mktime(0, 0, 0, xetadodb_strftime("%m", $row['`Timeend`']), 01, xetadodb_strftime("%Y", $row['`Timeend`'])); 

$oldtsmonth = xetadodb_mktime(0, 0, 0, xetadodb_strftime("%m", $_POST['oldTime']), 01, xetadodb_strftime("%Y", $_POST['oldTime'])); 
$oldmonthend = xetadodb_mktime(0, 0, 0, xetadodb_strftime("%m", $_POST['oldTimeend']), 01, xetadodb_strftime("%Y", $_POST['oldTimeend'])); 

if (isset($_POST['oldTime'])){
	$tsmonth = ($oldtsmonth<$tsmonth)?$oldtsmonth:$tsmonth;
	$monthend = ($oldmonthend>$monthend)?$oldmonthend:$monthend;
}


$i = 1;
$fileprefix=$this->xetconfig['cachepath'].'/'.$this->xetconfig['projectname'].'.belegplanlong.';	
while ($tsmonth <= $monthend):
    $month=xetadodb_strftime("%m", $tsmonth);
	$year=xetadodb_strftime("%Y", $tsmonth);
	$cachename = $fileprefix.'month_'.$year.'_'.$month.'_1.cache.php';
	$this->cache ->deleteCache($cachename,true)  ;
	//echo $cachename; 
	$cachename = $fileprefix.'month_'.$year.'_'.$month.'_.cache.php';
	$this->cache ->deleteCache($cachename,true)  ;
	$tsmonth = xetadodb_mktime(0, 0, 0, xetadodb_strftime("%m", $row['`Time`'])+$i, 01, xetadodb_strftime("%Y", $row['`Time`'])); 
    $i++;
endwhile;
}
$this->messages[]=$message;

?>

