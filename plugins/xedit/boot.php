<?php

$basePath = $modx->config['base_path'];
$managerpath = $basePath.'manager/';
//require_once ($managerpath.'includes/tmplvars.inc.php');
//require_once ($managerpath.'includes/tmplvars.commands.inc.php');
//include_once $managerpath."processors/cache_sync.class.processor.php";

$params['webpermissions']=isset($permissions_web)?$permissions_web:'';		
$params['mgrpermissions']=isset($permissions_mgr)?$permissions_mgr:'';	

$_xedit_container['params']=$params;
$_xedit_container['tables']=$tables;

include_once $pluginpath.'/inc/xedit.class.php';
include_once $pluginpath.'/inc/document.class.inc.php';
$xedit = new xedit( $_xedit_container );

?>