<?php
//$_SESSION['xedit_runs']='0';
global $modx;
$GLOBALS['xedit_runs']='0';
$GLOBALS['ajax_url']=$this->makeAjaxUrl();

/*
if ($this->userPermissions['cancreatedocs'] == '1') {
	
$GLOBALS['xedit_runs']='1';
$GLOBALS['ajax_url']=$this->makeAjaxUrl();
}
*/

//$output= $modx->documentOutput;
//$body_top = '<p>das testen wir jetzt mal</p>';
//$output = preg_replace('~(<body[^>]*>)~i', '\1'.$body_top, $output);
//$output = str_replace('[#XCC#]', $body_top, $output);
?>