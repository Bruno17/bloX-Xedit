<?php

include($pluginpath.'inc/FileManager/Backend/FileManager.php');

// Please add your own authentication here
function UploadIsAuthenticated($get){
	if(!empty($get['session'])) return true;
	
	return false;
}

$fieldtype=(isset($_REQUEST['fieldtype']))?$_REQUEST['fieldtype']:'';
$bloxProps=(isset($_REQUEST['bloxProps']))?$_REQUEST['bloxProps']:'';

$directory = $xedit->makeFileManagerPath($fieldtype,$bloxProps);

$parts=explode('/',$directory);
unset($parts[count($parts)-1]);
$url=implode('/',$parts).'/';

$browser = new FileManager(array(
	'directory' => $basePath.'assets/'.$directory,
	'assetBasePath' => $basePath.'assets/plugins/xedit/inc/FileManager/Assets',
	'domain' => 'http://bruno.tattoocms.de/',
	'baseURL' => 'assets/'.$url,
	'upload' => true,
	'destroy' => true
));

$modx->logEvent(0, 1, print_r($_REQUEST,true) , 'filemanager');

$browser->fireEvent(!empty($_REQUEST['event']) ? $_REQUEST['event'] : null);