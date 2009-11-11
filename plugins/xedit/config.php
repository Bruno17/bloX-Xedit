<?php

$params['pluginpath']=$pluginpath;
$params['manager_action']=$modx->manager->action; 
$params['placeholder_tags'][0]='[+';
$params['placeholder_tags'][1]='+]';
$params['multiCategories']='multichunks';
$params['containerTvCategories']='containerTvs';
$params['editableTag']='#';
$params['inputTag']='##';  
$params['published_default']='1'; 

//XCC- Tabs: $params[tabname][templateid]='@TV:bloxTabs';
/* examples: 
 * tabcaption:id-div-destination:@TV:xccButtons1||nextTab||nextTab
 * tabcaption:id-div-destination:[[bloX? &id=`buttons1`&containertype=`xcc_container`&parents=`55`]]||nextTab||nextTab
 * 
 *   
 */
$params['settings_from']='CONFIG';//where to get settings 'CONFIG' alternativ from chunk_template_settings-table: '' 
$params['blox_tabs'][4]='@TV:bloxTabs';//XCC- Tabs for bloX-adding-Tab 
$params['setting_tabs'][4]='@TV:settingTabs';//XCC - Tabs for setting-tab 
$params['tv_tabs'][4]='@TV:tvTabs';//XCC - Tabs for TV-tab 

//$params['blox_tabs'][4]='Links:left:[[bloX? &id=`buttons1`&containertype=`xcc_container`&parents=`0`&showunpublished=`1`]]';

//XCC xeditTabs for bloxparameter &xeditTabs=`@CONFIG` uses chunkname for key:
//for chunkname see chunkname in html-sourcecode
$params['xeditTabs']['titlecontent']='Content:content;richcontent,pagetitle||Sonstige Tvs:chunkname,deleted;deleteMe';
$params['xeditTabs']['imagecaption']='Content:content||Sonstige Tvs:pagetitle,chunkname,bild,datei,deleted;deleteMe';
$params['xeditTabs']['wayfinder']='Parameter:startId,sortBy,sortOrder||Sonstige Tvs:hidden:pagetitle,chunkname,deleted;deleteMe';
$params['xeditTabs']['reservation']='Reservation bearbeiten:@CHUNK:reservation';

//try with events 
//in xeditTabs -> <input type="hidden" name="onbeforesave" value="reservation"/>
$params['onbeforesave']['reservation']='@CHUNK:reservation';



//hier könnten noch irgendwelche Grundeinstellungen für die chunk_SettingFields rein
$params['chunk_SettingFields']='blox_template:text,ditto_hiddenfields:text,xedit_tabs:textarea';
$params['template_SettingFields']='blox_settings:textarea,setting_tabs:textarea,tv_tabs:textarea,xedit_tabs:textarea';

//default
$params['filemanager']['image_path']='images/';
$params['filemanager']['file_path']='files/';

//different path per page:
$params['filemanager']['image_path_TV']='imagespath';//used if not empty
$params['filemanager']['file_path_TV']='filespath';//used if not empty

//different path per row:
$params['filemanager']['image_path_FIELD']='imagespath';//used if not empty
$params['filemanager']['file_path_FIELD']='filespath';//used if not empty

//different path per user: is used if path = @USER in one of above path-configs
//you can also use @USERID in all of above configs as part of path
$params['filemanager']['image_path_user']='userfolders/@USERID/images/';
$params['filemanager']['file_path_user']='userfolders/@USERID/files/';

//if path not exists, it would be created, when filemanager opens

//examples:
//$permissions_web['moderatoren']='caneditowndocs,cancreatedocs';
//possible grouppermisions:
/*
 * caneditalldocs
 * caneditowndocs
 * cancreateinallparents 
 * cancreateinownparents
 * cansortdocs 
 * canunpublishdocs 
 * canseeunpublisheddocs
 * 
 * 
 */

$permissions_mgr['admins']='runxedit,caneditalldocs,cancreatedocs,canseeunpublisheddocs';
$permissions_web['admins']='runxedit,caneditalldocs,cancreatedocs';
//$permissions_web['moderatoren']='canseeunpublisheddocs';


$tables['mtc'] = $modx->getFullTablename('site_multiTVchunks');
$tables['hts']  = $modx->getFullTablename('site_htmlsnippets');
$tables['tvmc']  = $modx->getFullTablename('site_tmplvar_multicontents');
$tables['tv']  = $modx->getFullTablename('site_tmplvars');
$tables['tvc']  = $modx->getFullTablename('site_tmplvar_contentvalues');
$tables['sc']  = $modx->getFullTablename('site_content');
$tables['cs']  = $modx->getFullTablename('chunk_template_settings');
$tables['st']  = $modx->getFullTablename('site_templates');  

?>