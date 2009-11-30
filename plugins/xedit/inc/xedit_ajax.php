<?php

/**
 * $Id$
 */

include $pluginpath.'/config.php';
include $pluginpath.'/boot.php';

if ( isset ($XCC))
{

    //[!Xedit? &XCC=`1`!]

    $output = $xedit->makeXCC();
    return;
}

if ( isset ($_REQUEST['directory']))
{
    //$modx->logEvent('29','3',serialize($_POST),'Fancy Upload Post');
    //$modx->logEvent('29','3',serialize($_GET),'Fancy Upload Get');
    include $pluginpath.'inc/FileManager/filemanager.php';
    $savemassage = '';
}


if ( isset ($_POST['save_tv_tabs']))
{

    $last_docid = $xedit->saveFrontEndTvTabs();
    $savemassage = '<div id="response_rowid">'.$last_docid.'</div>';
    //print_r($savemassage);
}

if ( isset ($_POST['editx']))
{
    $editx = (array)json_decode(($_POST['editx']), true);
    //$xedit->docid = 55;
    //print_r($editx);

    $savemassage = $xedit->saveModifiedChunks($editx);

}

if ( isset ($_POST['reload']) && $_POST['reload'] == 'wrapper')
{

    $docid = $_POST['docid'];

    if ($modx->documentIdentifier != $docid)
    {
        //$modx->sendForward($docid);
        $modx->sendRedirect($_POST['docurl']);
    }
}


if ( isset ($_POST['get_tv_tabs']))
{

    $chunkname = $_POST['chunkname'];

    //$tvnames=explode(',',$_POST['tv_section_ids']);
    $docid = $_POST['rowid'];

    //$published=$_POST['published'];
    //$savemassage = $xedit->makeFrontEndTvSection($tvnames,$docid,$published);

    $savemassage = $xedit->makeFrontEndTvTabs($chunkname, $docid);

}


echo $savemassage;

?>
