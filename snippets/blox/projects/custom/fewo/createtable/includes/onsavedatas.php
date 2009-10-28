<?php
	$tablename = $modx->getFullTableName('belegplan');
	$sql = "CREATE TABLE IF NOT EXISTS $tablename (
  `id` int(10) NOT NULL auto_increment,
  `Time` bigint(30) NOT NULL default '0',
  `Timeend` bigint(30) NOT NULL,
  `category` varchar(50) NOT NULL default '0',
  `published` int(1) NOT NULL,
  `pagetitle` varchar(250) NOT NULL,
  `tpl` varchar(100) NOT NULL,
  `class` varchar(20) NOT NULL,
  `room_ID` int(10) NOT NULL default '0',
  `description` text NOT NULL,
  `createdby` int(10) NOT NULL default '0',
  `owner_id` int(10) NOT NULL,
  `category_ID` int(10) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;";
	$query1 = $modx->db->query($sql);

/*
	$tablename = $modx->getFullTableName('fewocache');
	$sql = "CREATE TABLE IF NOT EXISTS $tablename (
  `cachetitle` varchar(250) NOT NULL,
  `content` int(10) NOT NULL default '0',
  PRIMARY KEY  (`cachetitle`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;";
	$query1 = $modx->db->query($sql);	
*/	
	return;
?>