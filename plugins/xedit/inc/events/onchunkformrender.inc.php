<?php

/**
 * $Id$
 */

// das mit den event-params-zeug noch entwurschteln

//echo $this->container['params']['id'];
$chunkid=$this->container['params']['id'];
$chunkids=explode(',',$chunkid);
$bcc = new blox_Chunk_Collection($chunkids, $this);
$settingFields=explode(',',$this->container['params']['chunk_SettingFields']);
$settings=$bcc->settings[$bcc->chunknames[$chunkid]];
$output = $this->makeSettingsOutput($settingFields,$settings);

?> 