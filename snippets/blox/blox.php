<?php

$basePath = $modx->config['base_path'];



/*
 * Todos:
 * rowTpl - 
 * &rowTpl = `@FIELD:`
 * &xeditTabs = `@FIELD:` - Datensatzfeld, wechselnd von Datensatz zu Datensatz 
 * &xeditTabs = `@TV:` - in TV des aufrufenden Documents - done
 * 
 * 
 * container-typen einbauen
 * 
 * dokumentengruppen in getdocs
 * 
 * blox-container subfolder kram einbauen
 * 
 * 
 */


global $modx;
//Include adodb-time.inc.php

$bloxpath=$GLOBALS['blox_path'];
$bloxconfig['path']=$modx->config['base_path'].$bloxpath;

$configs = ( isset ($configs))?explode(',', $configs):array ();
$configs = array_merge( array ('master'), $configs);



foreach ($configs as $config)
{
    $configFile = $bloxconfig['path'].'configs/'.$config.'.config.inc.php'; // [ file ]
	
	if (file_exists($configFile))
    {
        include($configFile);
    }
}
$includes = ( isset ($includes))?explode(',', $includes):array ();
$includes = array_merge( array ('blox'), $includes);


$adodbFile = $bloxconfig['path'].'inc/adodb-time.inc.php';
if (file_exists($adodbFile)) {
	include_once($adodbFile);
}

$bloxconfig['id'] = isset($id) ? $id : ''; // [ string ]
$bloxconfig['id_'] = isset($id) ? $id.'_' : ''; // [ string ]
$bloxconfig['distinct'] = isset($distinct)&&$distinct=='0' ? '' : 'distinct'; // 1 or 0 [ string ]
$bloxconfig['projectname']=(isset($project))?$project:'blox';
$bloxconfig['tablename']=(isset($tablename))?$tablename:'';
$bloxconfig['resourceclass']=($bloxconfig['tablename']!=='')?'modTable':'modDocument';
$bloxconfig['resourceclass']=(isset($resourceclass))?$resourceclass:$bloxconfig['resourceclass'];
$bloxconfig['htmlouter']=isset($htmlouter)?$htmlouter:'div';
$bloxconfig['projectpath']=$bloxpath."projects/blox/".$bloxconfig['resourceclass'].'/';
$bloxconfig['projectpath']=(isset($project))?$bloxpath."projects/custom/".$project.'/':$bloxconfig['projectpath'];
$bloxconfig['canedit_webgroups']=(isset($canedit_webgroups))?$canedit_webgroups:'admins';//for compatibility with xett
$bloxconfig['setfields']=(isset($setfields))?$setfields:'';//formfields to save
$bloxconfig['date_divider']=(isset($date_divider))?$date_divider:'.';//needed in getinputTime
$bloxconfig['date_format']=(isset($date_format))?$date_format:'d,m,y';//needed in getinputTime
//echo $bloxconfig['projectpath'];

//use htmlouter div,table,ul as task if nothing else defined
//see projects/blox/...
$bloxconfig['task']=(isset($task))?$task:$bloxconfig['htmlouter'];
//$bloxconfig['task']=(isset($_REQUEST['task']))?$_REQUEST['task']:$bloxconfig['task'];
/*
$bloxconfig['configpath']=(isset($config_path))?$modx->config['base_path'].$bloxconfig['projectpath']."configs/".$config_path:'';
$bloxconfig['configpath']=($bloxconfig['configpath']=='')?$bloxconfig['projectpath']."configs/".$bloxconfig['task']:$bloxconfig['configpath'];
*/

$bloxconfig['tpls']=isset($tpls)?$tpls:'';
$bloxconfig['tplpath']=(isset($tpl_path))?$bloxconfig['projectpath']."templates/".$tpl_path:'';
$bloxconfig['tplpath']=($bloxconfig['tplpath']=='')?$bloxconfig['projectpath'].$bloxconfig['task']."/templates":$bloxconfig['tplpath'];

//echo $bloxconfig['tplpath'];

$bloxconfig['includespath']=(isset($includes_path))?$bloxconfig['projectpath'].$includes_path:'';
$bloxconfig['includespath']=($bloxconfig['includespath']=='')?$bloxconfig['projectpath'].$bloxconfig['task']."/includes":$bloxconfig['includespath'];
//$xetconfig['cachepath']=(isset($cache_path))?$xetconfig['projectpath'].$cache_path:'';
//$xetconfig['cachepath']=($xetconfig['cachepath']=='')?$xetconfig['projectpath'].$xetconfig['task']."/cache":$xetconfig['cachepath'];
$bloxconfig['cachepath']=$bloxconfig['path'].'cache';
$bloxconfig['includesfile']=$bloxconfig['includespath']."/getdatas.php"; // [ file ]
$bloxconfig['onsavefile']=$bloxconfig['includespath']."/onsavedatas.php"; // [ file ]
$timestamp=time();
$timestampday=xetadodb_strftime("%d",$timestamp);
$timestampmonth=xetadodb_strftime("%m",$timestamp);
$timestampyear=xetadodb_strftime("%Y",$timestamp);
$bloxconfig['nowtimestamp']=$timestamp;
$bloxconfig['day']=(isset($day))?$day:$timestampday;
$bloxconfig['day']=(isset($_REQUEST['day'])&&(trim($_REQUEST['day']!=='')))?$_REQUEST['day']:$bloxconfig['day'];
//$bloxconfig['month']=(isset($month))?$month:$bloxconfig['months'][0];
$bloxconfig['month']=(isset($month))?$month:$timestampmonth;
$bloxconfig['month']=(isset($_REQUEST['month'])&&(trim($_REQUEST['month']!=='')))?$_REQUEST['month']:$bloxconfig['month'];
$bloxconfig['year']=(isset($year))?$year:$timestampyear;
$bloxconfig['year']=(isset($_REQUEST['year'])&&(trim($_REQUEST['year']!=='')))?$_REQUEST['year']:$bloxconfig['year'];
$bloxconfig['processpost']=(isset($processpost))?$processpost:'1';
$bloxconfig['custom']=(isset($custom))?$custom:array();
$bloxconfig['permissions']=(isset($permissions))?$permissions:array();
$bloxconfig['path'] = $bloxpath; // [ path ]
$bloxconfig['userID']=$modx->getLoginUserID();
//$bloxtpl['bloxouterTpl']= (isset($bloxouterTpl)) ? $bloxouterTpl : "@FILE:".$bloxconfig['tplpath']."/bloxouterTpl.html"; // [ path | chunkname | text ]
$bloxconfig['rowTpl']=(isset($rowTpl))?$rowTpl:'';

//$bloxconfig['where']=(isset($where))?$where:'';
$bloxconfig['fields']=(isset($fields))?$fields:'*';
$bloxconfig['orderBy']=(isset($orderBy))?$orderBy:'';
$bloxconfig['perPage']=(isset($perPage))?$perPage:10;
$bloxconfig['numLinks']=(isset($numLinks))?$numLinks:5;
//Todo: pagestart+id for multiple containers with pagination
$bloxconfig['pageStart']=(isset($pageStart))?$pageStart:1;
$bloxconfig['pageStart']=( isset ($_GET['pagestart']) && is_numeric($_GET['pagestart']))?$_GET['pagestart']:$bloxconfig['pageStart'];

//variables for xedit:

$bloxconfig['keyField']=(isset($keyField))?$keyField:'id';
$bloxconfig['captionField']=(isset($captionField))?$captionField:($bloxconfig['resourceclass']=='modDocument'?'pagetitle':'');
$bloxconfig['requiredFields']=(isset($requiredFields))?explode(',',$requiredFields):array();
$bloxconfig['chunknameField']=(isset($chunknameField))?$chunknameField:'chunkname';//TV or tablefield
$bloxconfig['xedit_tabs']=(isset($xedit_tabs))?$xedit_tabs:'@CONFIG';//@CONFIG or @TV:tvname
$bloxconfig['parents']=(isset($parents))?$parents:'';
$bloxconfig['depth']=(isset($depth))?$depth:'1';
$bloxconfig['bloxfolder']=(isset($bloxfolder))?$bloxfolder:'';//together with the first id in &parents here comes the pagetitle of subfolder for bloxcontainer
$bloxconfig['documentsTv']=(isset($documentsTv))?$documentsTv:'';//TV where to store reference-ids, makes container to ref_container
$bloxconfig['documents']=(isset($documents))?trim($documents)==''?'999999999':$documents:'';
$bloxconfig['IDs']=(isset($IDs))?$IDs:$bloxconfig['documents'];



$bloxconfig['filter']=(isset($filter))?$filter:'';
$bloxconfig['filterByField']=(isset($filterByField))?$filterByField:'';
$bloxconfig['orderByField']=(isset($orderByField))?$orderByField:'';
$bloxconfig['removeable']=(isset($removeable))?$removeable:'1';
$bloxconfig['fillable']=(isset($fillable))?$fillable:'1';
$bloxconfig['saveable']=(isset($saveable))?$saveable:'1';
$bloxconfig['sortable']=(isset($sortable))?$sortable:'1';
$bloxconfig['savemode']=(isset($savemode))?$savemode:'move';
$bloxconfig['c_type']=(isset($containertype))?$containertype:'blox_container';
$bloxconfig['container']=(isset($container))?$container:'';//destination for bloxbuttons


$bloxconfig['showdeleted']=(isset($showdeleted))?$showdeleted:'0';//0 = no, 1 = yes, 2 = only deleted
$bloxconfig['showunpublished']=(isset($showunpublished))?$showunpublished:'0';
$bloxconfig['dragbtn']=(isset($dragbtn))?$dragbtn:'1';
$bloxconfig['trashbtn']=(isset($trashbtn))?$trashbtn:'1';
$bloxconfig['savebtn']=(isset($savebtn))?$savebtn:'1';
$bloxconfig['removebtn']=(isset($removebtn))?$removebtn:'0';



///////////////////////////////////////////////////////////////////////////////////////////////////
//-------------------------------------------------------------------------------------------------
//  SNIPPET LOGIC CODE STARTS HERE
//-------------------------------------------------------------------------------------------------
//$GLOBALS[$xetconfig['id']]['xetconfig']=$xetconfig;


//Todo: make this better:
foreach ($includes as $includeclass)
{
if (!class_exists($includeclass)) {
	$includefile=$modx->config['base_path'].$bloxpath.'inc/'.$includeclass.'.class.inc.php';
	if(file_exists($includefile)){
		include_once($includefile);
	} else {
		$output = 'Cannot find '.$includeclass.' class file! ('.$includefile.')'; 
		return;
	}
}

switch($includeclass)
{
    case 'blox':
        // Initialize class
        if (class_exists($includeclass))
        {
            $blox = new blox($bloxconfig, $bloxtpl);
        } else
        {
            $output = $includeclass.' class not found';
            return;
        }
    break;
    case 'xettcal':
        // Initialize class
        if (class_exists($includeclass))
        {
            $blox->xettcal = new xettcal($bloxconfig['id']);
        } else
        {
            $output = $includeclass.' class not found';
            return;
        }
    break;	
}
}


/* nur wenn gabraucht laden für calendar - zeugs
if (!class_exists('xettcal')) {
	$xetcalclass=$modx->config['base_path'].$bloxpath.'xettcal.class.inc.php';
	if(file_exists($xetcalclass)){
		include_once($xetcalclass);
	} else {
		$output = 'Cannot find xett class file! ('.$xetcalclass.')'; 
		return;
	}
}
// Initialize class
if (class_exists('xettcal')) {
   $xet->xettcal = new xettcal($bloxconfig['id']);
} else {
	$output =  'xettcal class not found'; 
	return;
}
*/

if (!class_exists('xettChunkie')) {
	$chunkieclass = $modx->config['base_path'].$bloxpath.'chunkie/chunkie.class.inc.php';
	if (file_exists($chunkieclass)) {
		include_once $chunkieclass;
	} else {
		$output = 'Cannot find chunkie class file! ('.$chunkieclass.')'; 
		return $output;
	}
}
//Spezialvariablen 
//Todo: permissions
$blox->bloxconfig['canedit'] = $blox->isMemberOf($bloxconfig['canedit_webgroups']); // [boolean]

//begin work
//for form-postprocessing

if ($bloxconfig['processpost'] == '1') {
if ((isset($_POST['saveevent'])) || (isset($_POST['saveblox'])) || (isset($_POST['makeevents'])) || (isset($_POST['dbinsert']))|| (isset($_POST['dbsave']))){	
	if ($blox->bloxconfig['canedit'] == '1'){
		if (($_POST['saveevent']) || ($_POST['saveblox']) || ($_POST['dbsave'])|| ($_POST['dbinsert'])){

			$blox->saveblox();
		}
		if ($_POST['makeevents']) {
			$blox->makeevents();
			
		}

	} else {
		$message=array();
		$message['messagetext'] = 'Keine Berechtigung Daten zu speichern oder?';
        $blox->messages[]=$message;
	}}
}

//Output
$output=$blox->displayblox();

//store the blox-object for use in other scripts e.g. ajax-scripts
$_SESSION['bloxobject'][$modx->documentIdentifier][$bloxconfig['id']]=$blox;


//bloX

$basePath = $modx->config['base_path'];
$pluginpath = $basePath.'assets/classes/tcpdf/';

if ($_GET['contentType']=='pdf'){
include $pluginpath.'examples/example_001.php';
}
else{
	return $output;
}



?>