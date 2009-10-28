<?php

//print_r($_POST);
$chunknames= array($_POST['name']);
//print_r($chunknames);
$bcc = new blox_Chunk_Collection($chunknames, $this);
//$settingslist=array('blox_template','ditto_hiddenfields','xedit_tabs');
$settingFields=explode(',',$this->container['params']['chunk_SettingFields']);
//print_r($settingFields);
if (count($settingFields)>0){
foreach ($settingFields as $settingField){
	$settingField=explode(':',$settingField);
	//$bcc->setSetting($_POST['name'],$settingname,$_POST[$settingname]);
	$bcc->settings[$_POST['name']][$settingField[0]]=$_POST[$settingField[0]];	
}
//print_r($bcc->settings);
$bcc->saveSettings();	
}


//$modx->db-pinsert();


?>