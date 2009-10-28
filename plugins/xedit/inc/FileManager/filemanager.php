<?php

include($pluginpath.'inc/FileManager/Backend/FileManager.php');

// Please add your own authentication here
function UploadIsAuthenticated($get){
	if(!empty($get['session'])) return true;
	
	return false;
}

$browser = new FileManager(array(
	'directory' => $basePath.'assets/galleries/',
	'assetBasePath' => $basePath.'assets/plugins/xedit/inc/FileManager/Assets',
	'baseURL' => 'http://bruno.tattoocms.de/assets/',
	'upload' => true,
	'destroy' => true
));

$browser->fireEvent(!empty($_POST['event']) ? $_POST['event'] : null);