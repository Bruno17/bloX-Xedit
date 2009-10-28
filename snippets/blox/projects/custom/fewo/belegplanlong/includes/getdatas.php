<?php
global $modx;


$firstmonth = abs($this->bloxconfig['month']);
$year=$this->bloxconfig['year'];

$timestampfirstday = xetadodb_mktime(0, 0, 0, $firstmonth, 01, $year);
$cal=$this->xettcal->getMonthDays($year, $firstmonth);
//print_r($cal);
$timestampday=xetadodb_strftime("%d",$this->bloxconfig['nowtimestamp']);
$timestampmonth=xetadodb_strftime("%m",$this->bloxconfig['nowtimestamp']);
$timestampyear=xetadodb_strftime("%Y",$this->bloxconfig['nowtimestamp']);


$monthlist=array();
for ($monthcount = 1; $monthcount <= 12; $monthcount++) { 
$monthlist[$monthcount]['tsmonth']=xetadodb_mktime(0, 0, 0, $timestampmonth+$monthcount-1, 01, $timestampyear);
} 

//Caching for template monthTpl
$cachename = 'month_'.$year.'_'.$this->bloxconfig['month'].'_'.$this->bloxconfig['canedit'].'.cache.php';
if ( ($cacheoutput = $this->cache -> readCache($cachename)) === false) {
$cacheaction='1';//render new and save cachefile
}
else {
$cacheaction='2';//use cached output  	
}

if ($cacheaction!=='2'){
 
$roomsparent=$this->bloxconfig['custom']['docids']['rooms'];
// Wohnungen suchen
$tablename1 = $modx->getFullTableName('site_content').' sc'; 

$query="select sc.id as room_ID,sc.pagetitle as room_title";
$query.=" from ".$tablename1;
$query.=" where parent= ".$roomsparent;
$query.= " and description <> 'noroom' ";
$query.= " order by room_title ";

$rows = $this->getevents($query);


$rooms=array();

foreach ($rows as $room)
{	$days=array();
    //$room_id=$room['room_ID'];
	foreach($cal['days']as $key=>$day){
		$day=array_merge($day,$room);
	    $day['getDatasOnRender']='1';
		$days[$key]=$day;	
	}
	$room['innerrows']['day']=$days;
	$rooms[]=$room;
}

}
$monthdata['tsmonth']=$timestampfirstday;
$monthdata['innerrows']['rooms']=$rooms;
$monthdata['innerrows']['monthdays']=$cal['days'];
$monthdata['cacheaction']=$cacheaction;
$monthdata['cachename']=$cachename;
$monthdata['cacheoutput']=$cacheoutput;

$outerdata['innerrows']['monthlist']=$monthlist;
$outerdata['innerrows']['month'][]=$monthdata;
$bloxdatas = $outerdata;	

//$bloxdatas['cacheaction']=$cacheaction;
//$bloxdatas['cachefile']=$cachefile;
//$bloxdatas['cacheoutput']=$cacheoutput;
$bloxdatas['tsmonth']=$timestampfirstday;

?>