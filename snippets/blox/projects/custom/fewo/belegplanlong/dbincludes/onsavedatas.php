<?php

//$createdby=$modx->getLoginUserID();
$rows = $this->getformfields();
$tablename = $modx->getFullTableName('belegplan'); 
$xettdatas=$events[0];
$row=$rows[0];
$message=$row;
//check if there is no reservation
$timestampstart = $row['`Time`'];
$timestampend = $row['`Timeend`'];
$tablename = $modx->getFullTableName('belegplan'); 
$query="select id";
$query.=" from ".$tablename;
$query.=" where room_ID= ".$row['`room_ID`'];
$query.=" and Time < '$timestampend' ";
$query.= " and Timeend > '$timestampstart' ";
$query.= " and published = '1' ";
if ($_POST['action']=='update'){
$query.= " and id <> ".$_POST['event_ID'];
}
$query.= " order by Time";
$rs = $modx->db->query($query);
$events = $modx->db->makeArray($rs);

if (count($events)>0){
	$message['saved_ok']='0';
	$message['messagetext'] = 'Da war einer schneller mit der Buchung';
}else{
	$message['saved_ok']='1';
	$message['messagetext'] = 'Alles ok';

if ($_POST['action']=='update'){
	$modx->db->update($row, $tablename, 'id = '.$_POST['event_ID']);
}else{
	$modx->db->insert($row, $tablename);
}

//remove cachefiles for monthTpl

$tsmonth = xetadodb_mktime(0, 0, 0, xetadodb_strftime("%m", $row['`Time`']), 01, xetadodb_strftime("%Y", $row['`Time`'])); 
$monthend = xetadodb_mktime(0, 0, 0, xetadodb_strftime("%m", $row['`Timeend`']), 01, xetadodb_strftime("%Y", $row['`Timeend`'])); 

$oldtsmonth = xetadodb_mktime(0, 0, 0, xetadodb_strftime("%m", $_POST['oldTime']), 01, xetadodb_strftime("%Y", $_POST['oldTime'])); 
$oldmonthend = xetadodb_mktime(0, 0, 0, xetadodb_strftime("%m", $_POST['oldTimeend']), 01, xetadodb_strftime("%Y", $_POST['oldTimeend'])); 

if (isset($_POST['oldTime'])){
	$tsmonth = ($oldtsmonth<$tsmonth)?$oldtsmonth:$tsmonth;
	$monthend = ($oldmonthend>$monthend)?$oldmonthend:$monthend;
}


$i = 1;
while ($tsmonth <= $monthend):
    $month=xetadodb_strftime("%m", $tsmonth);
	$year=xetadodb_strftime("%Y", $tsmonth);
	$cachename = 'month_'.$year.'_'.$month.'_1.cache.php';
	$this->cache ->deleteCache($cachename)  ;
	$cachename = 'month_'.$year.'_'.$month.'_.cache.php';
	$this->cache ->deleteCache($cachename)  ;
	$tsmonth = xetadodb_mktime(0, 0, 0, xetadodb_strftime("%m", $row['`Time`'])+$i, 01, xetadodb_strftime("%Y", $row['`Time`'])); 
    $i++;
endwhile;
}
$this->messages[]=$message;

?>
