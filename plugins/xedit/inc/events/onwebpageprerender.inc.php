<?php

global $modx;
$output= $modx->documentOutput;

//only for the first permission-test
//we need more different permissions later
//see config.php
//$_SESSION['xedit_runs']='0';

if ($GLOBALS['xedit_runs'] == '1') {
    //$_SESSION['xedit_runs']='1';
    $managerPath = $modx->getManagerPath();
    $doc_id = $modx->documentObject['id'];;
    $site_url = MODX_BASE_URL;
    //$output= $modx->documentOutput;

//quick fix: switch mootools version

$tv = $modx->getTemplateVarOutput(array("mootools"),$doc_id);
$mootools = $tv['mootools'];
if (empty($mootools)){
	$mootools='
	<script src="/assets/plugins/xedit/js/mootools-1.2.3.js" type="text/javascript"></script>
	<script src="/assets/plugins/xedit/js/mootools-1.2.3.1-more.js" type="text/javascript"></script>	
	';
}

$filemanager='
	<link rel="stylesheet" media="all" type="text/css" href="/assets/plugins/xedit/inc/FileManager/Css/FileManager.css" />
	<link rel="stylesheet" media="all" type="text/css" href="/assets/plugins/xedit/inc/FileManager/Css/Additions.css" />
	<script type="text/javascript" src="/assets/plugins/xedit/inc/FileManager/Source/FileManager.js"></script>
	<script type="text/javascript" src="/assets/plugins/xedit/inc/FileManager/Source/Language/Language.en.js"></script>
	<script type="text/javascript" src="/assets/plugins/xedit/inc/FileManager/Source/Language/Language.de.js"></script>
	<script type="text/javascript" src="/assets/plugins/xedit/inc/FileManager/Source/Additions.js"></script>
	
	<script type="text/javascript" src="/assets/plugins/xedit/inc/FileManager/Source/Uploader/Fx.ProgressBar.js"></script>
	<script type="text/javascript" src="/assets/plugins/xedit/inc/FileManager/Source/Uploader/Swiff.Uploader.js"></script>

	<script type="text/javascript" src="/assets/plugins/xedit/inc/FileManager/Source/Uploader.js"></script>
	<script type="text/javascript" src="/assets/plugins/xedit/inc/FileManager/Source/Gallery.js"></script>
';


    $ajax_url=$GLOBALS['ajax_url'];//from onparsedocument

    $_SESSION['xedit_moduleURL']=$ajax_url;

    // Define the CSS and Javascript that we will add to the header of the page
    $head =<<<EOD

<!-- Start Xedit headers -->
	<link href="{$site_url}assets/plugins/xedit/css/mooinline.css" rel="stylesheet" media="screen" type="text/css" />
	<link href="{$site_url}assets/plugins/xedit/css/xcc.css" rel="stylesheet" media="screen" type="text/css" />
        <link href="{$site_url}assets/plugins/xedit/css/sexyalertbox.css" rel="stylesheet" media="screen" type="text/css" />		
        <link href="{$site_url}assets/plugins/xedit/css/screen.css" rel="stylesheet" media="screen" type="text/css" />		
        <link href="{$site_url}assets/snippets/maxigallery/css/default.css" rel="stylesheet" media="screen" type="text/css" /> 
	    {$mootools}
		{$filemanager}
	<script src="{$site_url}assets/plugins/xedit/js/blox_sortables.js" type="text/javascript"></script>
	<script src="{$site_url}assets/plugins/xedit/js/mooinline.js" type="text/javascript"></script>
	<script src="{$site_url}assets/plugins/xedit/js/editx_bruno.js" type="text/javascript"></script>		
        <script src="{$site_url}assets/plugins/xedit/js/tabs.js" type="text/javascript"></script>
        <script src="{$site_url}assets/plugins/xedit/js/groar.js" type="text/javascript"></script>
        <script src="{$site_url}assets/plugins/xedit/js/xedit_xcc.js" type="text/javascript"></script>		
        <script src="{$site_url}assets/plugins/xedit/js/sexyalertbox.js" type="text/javascript"></script>						
        <script src="{$site_url}assets/plugins/xedit/js/ckeditor/ckeditor.js" type="text/javascript"></script>						
<script type="text/javascript">
window.addEvent('domready', function(){
	
mySortables = new blox_Sortables();

mte = new MooInline('.xedit', {
                    defaults: ['bold,italic,underline,justifyleft,justifycenter,justifycenter,insertorderedlist,insertunorderedlist'],
                    location: 'pageTop',
                    floating: true
                });	 
                         
brunoclass = new Mif.brunoclass(
{
    ajax_url: '{$ajax_url}',
    doc_id: '{$doc_id}'
});
xtoolsStart = new Mif.xtools();
startXtools('{$ajax_url}','{$doc_id}');
startxcc('{$ajax_url}','{$doc_id}');
});
</script>   
<!-- End Xedit headers -->

EOD;

    // If the javascript hasn't already been added to the page, do it now
    if (strpos($output, $head) === false) {

    //$output = str_replace('</head>', $head, $output);
        $output = preg_replace('~(</head>)~i', $head.'\1', $output);
    }

    /*
    $body_top=$this->makeXCC();

    if (strpos($output, $body_top) === false) {
        $output = preg_replace('~(<body[^>]*>)~i', '\1'.$body_top, $output);
    }
    */

}

?>