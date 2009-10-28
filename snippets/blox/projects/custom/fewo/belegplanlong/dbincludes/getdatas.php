<?php
global $modx;

$firstmonth = abs($this->xetconfig['month']);
$year=$this->xetconfig['year'];

$timestampfirstday = xetadodb_mktime(0, 0, 0, $firstmonth, 01, $year);
$cal=$this->xettcal->getMonthDays($year, $firstmonth);
//print_r($cal);

$monthlist=array();
for ($monthcount = 1; $monthcount <= 12; $monthcount++) { 
$monthlist[$monthcount]['tsmonth']=xetadodb_mktime(0, 0, 0, $this->xetconfig['nowmonth']+$monthcount-1, 01, $this->xetconfig['nowyear']);
} 

//Caching for template monthTpl
$cachename = 'month_'.$year.'_'.$this->xetconfig['month'].'_'.$this->xetconfig['canedit'].'.cache.php';
if ( ($cacheoutput = $this->cache -> readCache($cachename)) === false) {
$cacheaction='1';//render new and save cachefile
}
else {
$cacheaction='2';//use cached output  	
}

if ($cacheaction!=='2'){
 
$roomsparent=$this->xetconfig['docids']['rooms'];
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
{
	$days=array();
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
$xettdatas = $outerdata;	

//$xettdatas['cacheaction']=$cacheaction;
//$xettdatas['cachefile']=$cachefile;
//$xettdatas['cacheoutput']=$cacheoutput;
$xettdatas['tsmonth']=$timestampfirstday;

?>
