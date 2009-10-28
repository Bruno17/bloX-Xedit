<?php

$timestampstart = $this->xettcal->get_ts_daystart($row['tsday']);
$timestampend = $this->xettcal->get_ts_dayend($row['tsday']);

$tablename = $modx->getFullTableName('belegplan'); 
$query="select *";
$query.=" from ".$tablename;
$query.=" where room_ID= ".$row['room_ID'];
$query.=" and Time <= '$timestampend' ";
$query.= " and Timeend >= '$timestampstart' ";
$query.= " order by Time";
$rs = $modx->db->query($query);
$events = $modx->db->makeArray($rs);

$xettdatas=$events[0];
$xettdatas['groupeventscount'] = count($events);
?>
