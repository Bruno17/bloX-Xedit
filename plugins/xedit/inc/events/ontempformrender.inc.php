<?php

/**
 * $Id$
 */


// das mit den event-params-zeug noch entwurschteln

//echo $this->container['params']['id'];blox_Template_Collection($idnames, &$blox)
$templateid=$this->container['params']['id'];
$templateids=explode(',',$templateid);



$btc = new blox_Template_Collection($templateids, $this);
$settingFields=explode(',',$this->container['params']['template_SettingFields']);
$settings=$btc->settings[$templateid];
$output = $this->makeSettingsOutput($settingFields,$settings);
 
?> 