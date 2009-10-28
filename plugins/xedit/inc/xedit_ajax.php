<?php

/**
 * $Id$
 */


include $pluginpath.'/config.php';
include $pluginpath.'/boot.php';

//hier mal hardcoded ne Konfiguration für TV-tabs
//die soll er sich dann aus der config für das jeweilige chunk holen
//wo auch immer die mal gespeichert wird
//wird jetzt in der settings-tabelle gespeichert bzw. in der config.php
/*   	
 $xedit_tabs['content']['caption']='Content';
 $xedit_tabs['content']['tv_names']='content';
 $xedit_tabs['text']['caption']='Text';
 $xedit_tabs['text']['tv_names']='text';
 $xedit_tabs['sonstige']['caption']='Sonstige Tvs';
 $xedit_tabs['sonstige']['tv_names']='pagetitle,chunkname,checkbox,bild';
 */

if ( isset ($XCC))
{

    //[!Xedit? &XCC=`1`!]

    $output = $xedit->makeXCC();
    return;
}

if ( isset ($_POST['directory']))
{

include $pluginpath.'inc/FileManager/filemanager.php';
$savemassage='';
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