<?php

//print_r($_POST);
$templateids= array($_POST['id']);
//print_r($templateids);
$btc = new blox_Template_Collection($templateids, $this);
//$settingslist=array('blox_template','ditto_hiddenfields','xedit_tabs');
$settingFields=explode(',',$this->container['params']['template_SettingFields']);
//print_r($settingFields);
if (count($settingFields)>0){
foreach ($settingFields as $settingField){
	$settingField=explode(':',$settingField);
	//$bcc->setSetting($_POST['name'],$settingname,$_POST[$settingname]);
	$btc->settings[$_POST['id']][$settingField[0]]=$_POST[$settingField[0]];	
}
//print_r($btc->settings);
$btc->saveSettings();	
}


//$modx->db-pinsert();


?>