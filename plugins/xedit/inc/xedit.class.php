<?php

/**
 * $Id$
 */

class blox_Template_Collection {

    function blox_Template_Collection($idnames, &$blox) {
        $this->blox             = $blox;
        //$this->allChunkTvNames= array();
        //$this->allChunkDvNames= array();
        $this->templatenames    = array();
        $this->settings         = array();
        $this->tbl_st           = $blox->tbl_st;
        $this->tbl_cs           = $blox->tbl_cs;
        $this->docColumnNames   = $blox->docColumnNames;
        $this->tvnames          = $blox->tvnames;
        $this->startTag         = $blox->startTag;
        $this->endTag           = $blox->endTag;
        $this->editableTag      = $blox->editableTag;
        $this->inputTag         = $blox->inputTag;

        $blox->checkSettingsTable();
        $this->templates = $this->selectTemplates($idnames);
        $this->extractTemplateSettings();

    }

    function selectTemplates($idnames) {
        global $modx;
        $chunks=array();
        $this->templatenames=array();

        if (count($idnames) > 0) {
            $idnames=array_values($idnames);
            if ($idnames == "*")
                $where = "st.id<>0";
            else
                $where = (( (string)(int)$idnames[0] === (string)$idnames[0] )?"st.id":"st.templatename")." IN ('".implode("','", $idnames)."')";

            $query = "
        SELECT * FROM ".$this->tbl_st." st
        left join ".$this->tbl_cs." cs on cs.template_id = st.id 
		and cs.settings_for='template'
        WHERE ".$where;

            $rs = $modx->db->query($query);
            $rows = $modx->db->makeArray($rs);
            if (count ($rows)>0) {
                foreach ($rows as $template) {
                    $templates[$template['id']]=$template;
                    $this->templatenames[$template['id']]=$template['templatename'];
                }
            }
        }

        return $templates;
    }
    function extractTemplateSettings() {

        $this->settings=array();
        if (count($this->templates)>0) {
            foreach ($this->templates as $template) {
                $this->settings[$template['id']] = unserialize($template['settings']);
            }
        }
        return;
    }

    function getContainerNames($templateid) {
        $containers = explode('||', $this->settings[$templateid]['blox_settings']);
        $containerNames = array ();
        if (count($containers) > 0) {
            foreach ($containers as $value) {
                $container_array = explode(':', $value);
                $containerNames[] = trim($container_array[1]);
            }
        }
        return $containerNames;
    }

    function saveSettings() {
        global $modx;
        $templateids=array_flip($this->templatenames);
        //print_r($templateids);
        //$allChunkFields=$this->mergeAllFields();
        if (count($templateids)>0) {
            foreach ($templateids as $templateid) {
                $settings=$this->settings[$templateid];
                //$hiddenfields = explode(',',$settings['ditto_hiddenfields']);
                //gefundene variablen in hiddenfields einsetzen, falls nicht drinne
                //oder umgekehrt
                //print_r($allChunkFields);
		   /*
		   foreach ($hiddenfields as $hiddenfield){
		       if (!empty($hiddenfield)&&!in_array($hiddenfield,$allChunkFields)){
		          $allChunkFields[]= $hiddenfield;	
		       }    	
		   }
		   $settings['ditto_hiddenfields']=implode(',',$allChunkFields);
           */
                $serialized=serialize($settings);
                $fields=array();
                $fields['template_id']=$templateid;
                $fields['settings_for']='template';
                $fields['settings']=$serialized;
                if (empty($this->templates[$templateid]['template_id'])) {
                //echo 'setting ist nicht vorhanden - insert';
                    $rs=$modx->db->insert($fields,$this->tbl_cs);

                }else {
                //echo 'setting ist vorhanden - update';
                    $rs=$modx->db->update($fields,$this->tbl_cs,"template_id=".$fields['template_id']);
                }
            }
        }
    }

}


class blox_Chunk_Collection {

    function blox_Chunk_Collection($chunknames, &$blox) {
        $this->blox             = $blox;
        $this->allChunkTvNames  = array();
        $this->allChunkDvNames  = array();
        $this->chunknames       = array();
        $this->settings         = array();
        $this->tbl_hts          = $blox->tbl_hts;
        $this->tbl_cs           = $blox->tbl_cs;
        $this->docColumnNames   = $blox->docColumnNames;
        $this->tvnames          = $blox->tvnames;
        $this->startTag         = $blox->startTag;
        $this->endTag           = $blox->endTag;
        $this->editableTag      = $blox->editableTag;
        $this->inputTag         = $blox->inputTag;

        $blox->checkSettingsTable();
        $this->chunks = $this->selectChunks($chunknames);
        $this->extractChunkSettings();
        $this->collectVarsFromChunks($this->chunknames);



    }

    function selectChunks($idnames) {
        global $modx;
        $chunks=array();
        $this->chunknames=array();

        if (count($idnames) > 0) {
            $idnames=array_values($idnames);
            if ($idnames == "*")
                $where = "hts.id<>0";
            else
                $where = (( (string)(int)$idnames[0] === (string)$idnames[0] )?"hts.id":"hts.name")." IN ('".implode("','", $idnames)."')";



            $query = "
        SELECT * FROM ".$this->tbl_hts." hts 
        left join ".$this->tbl_cs." cs on cs.template_id = hts.id 
		and cs.settings_for='chunk'
        WHERE ".$where;

            $rs = $modx->db->query($query);
            $rows = $modx->db->makeArray($rs);
            if (count ($rows)>0) {
                foreach ($rows as $chunk) {
                    $chunks[$chunk['name']]=$chunk;
                    $this->chunknames[$chunk['id']]=$chunk['name'];
                }
            }
        }

        return $chunks;
    }

    function extractChunkSettings() {

        $this->settings=array();
        if (count($this->chunks)>0) {
            foreach ($this->chunks as $chunk) {
                $this->settings[$chunk['name']] = unserialize($chunk['settings']);
            }
        }
        return;
    }

/* hmm.. (cool..., sinnvoll?)
function setSetting($chunkname,$settingname,$settingvalue){
	$this->settings[$chunkname][$settingname]=$settingvalue;
}
*/

    function saveSettings() {
        global $modx;
        $chunkids=array_flip($this->chunknames);
        $allChunkFields=$this->mergeAllFields();
        if (count($this->chunknames)>0) {
            foreach ($this->chunknames as $chunkname) {
                $settings=$this->settings[$chunkname];
                $hiddenfields = explode(',',$settings['ditto_hiddenfields']);
                //gefundene variablen in hiddenfields einsetzen, falls nicht drinne
                //oder umgekehrt
                //print_r($allChunkFields);
                foreach ($hiddenfields as $hiddenfield) {
                    if (!empty($hiddenfield)&&!in_array($hiddenfield,$allChunkFields)) {
                        $allChunkFields[]= $hiddenfield;
                    }
                }
                $settings['ditto_hiddenfields']=implode(',',$allChunkFields);
                $serialized=serialize($settings);
                $fields=array();
                $fields['template_id']=$chunkids[$chunkname];
                $fields['settings_for']='chunk';
                $fields['settings']=$serialized;
                if (empty($this->chunks[$chunkname]['template_id'])) {
                //echo 'setting ist nicht vorhanden - insert';
                    $rs=$modx->db->insert($fields,$this->tbl_cs);

                }else {
                //echo 'setting ist vorhanden - update';
                    $rs=$modx->db->update($fields,$this->tbl_cs,"template_id=".$fields['template_id']);
                }
            }
        }
    }

    function collectVarsFromChunks($chunknames) {
        $this->collectedChunks = array ();

        $chunks = $this->chunks;
        $this->allChunkTvNames = array ();
        $this->allChunkDvNames = array ();
        if (count($chunks > 0)) {
            foreach ($chunks as $chunk) {

                $chunk['dvNames'] = $this->getVarsFromChunk($chunk['snippet'], $this->docColumnNames);
                $this->allChunkDvNames = $this->allChunkDvNames+$chunk['dvNames'];

                $chunk['tvNames'] = $this->getVarsFromChunk($chunk['snippet'], $this->tvnames);
                $this->allChunkTvNames = $this->allChunkTvNames+$chunk['tvNames'];

                $this->collectedChunks[$chunk['name']] = $chunk;
            }

        }
        return;
    }

    //auslesen der erkennbaren Variablen-Platzhalter
    //zusätzlich noch die hiddenfields aus den chunk-einstellungen holen
    function getVarsFromChunk($chunk_content, $varnames) {
        $chunk_Tvnames = array ();
        if (count($varnames) > 0) {
            foreach ($varnames as $key=>$tvname) {
                $search = $this->startTag.$tvname.$this->endTag;
                if (strpos($chunk_content, $search) !== false) {
                    $chunk_Tvnames[$key] = $tvname;
                }
                $search = $this->startTag.$this->editableTag.$tvname.$this->endTag;
                if (strpos($chunk_content, $search) !== false) {
                    $chunk_Tvnames[$key] = $tvname;
                }
                $search = $this->startTag.$this->inputTag.$tvname.$this->endTag;
                if (strpos($chunk_content, $search) !== false) {
                    $chunk_Tvnames[$key] = $tvname;
                }
            }
        }
        return $chunk_Tvnames;
    }

    function mergeAllFields() {

        $allChunkFieldNames=array_merge($this->allChunkTvNames,$this->allChunkDvNames);
        //print_r($allChunkFieldNames);

        if (!in_array('published',$allChunkFieldNames)) {
            $allChunkFieldNames[]='published';
        }
        if (!in_array('menuindex',$allChunkFieldNames)) {
            $allChunkFieldNames[]='menuindex';
        }
        if (!in_array('parent',$allChunkFieldNames)) {
            $allChunkFieldNames[]='parent';
        }
        return $allChunkFieldNames;
    }

    function setChunkContentsPH($block) {
        global $modx;
        $tvIds = array_flip($this->allChunkTvNames);
        $this->blox->getNewMultiTvs($tvIds);

        //$modx->setPlaceholder('childids', $childids);
        //$modx->setPlaceholder('collectedchunks', $xedit->collectedChunks);
        //$modx->setPlaceholder('allChunkTvNames', $xedit->allChunkTvNames);
        //ohne platzhalter
        foreach ($this->collectedChunks as $chunkname=>$chunk) {
            $chunkcontents[$chunkname] = $this->prepareChunkForXedit($chunk['snippet'], $block);
        }

        $modx->setPlaceholder('chunkcontents',$chunkcontents);
        return;
    }


    function prepareChunkForXedit($chunk_content,$block='',$setDefault='0',$forChunkSelecion='0') {

    //print_r($this->NewMultiTvs);
    //Todo Standardwert der TV einsetzen???
    //Ok nochmal untersuchen, ob das so richtig läuft mit den Standardwerten!
    //überhaupt das mit den Eingabetypen usw. prüfen in Textarea <p> mitliefern, sonst geht kein neuer Absatz


        if (count($this->tvnames)>0) {
            foreach ($this->tvnames as $key=>$tvname) {
                $search=$this->startTag.$this->editableTag.$tvname.$this->endTag;
                $search2=$this->startTag.$tvname.$this->endTag;
                $search3=$this->startTag.$this->inputTag.$tvname.$this->endTag;
                $defaultValue=($setDefault=='1'&&!empty($this->blox->NewMultiTvs[$tvname]['default_text']))?$this->blox->NewMultiTvs[$tvname]['default_text']:'<p>New '.$tvname.'</p>';
                if ($forChunkSelecion=='1') {
                    $ph=$defaultValue;
                    $chunk_content = str_replace($search2,$ph , $chunk_content);
                }else {
                    $ph=$this->startTag.$tvname.$this->endTag;
                    $ph3='[[bloX? &formElementValue=`[+'.$tvname.'+]`&formElementTv=`'.$tvname.'`]]';
                //$ph3=$this->blox->makeFormelement($this->blox->NewMultiTvs[$tvname],$prefix='tv');
                //funktioniert so noch nicht
                //$hidden.='<input type="hidden" name="'.$tvname.'[]" value="'.$ph.'">';
                }
                if ($GLOBALS['xedit_runs']=='1') {
                    $chunk_content = str_replace($search3,'<div resourceclass="modDocument" rowid="[+id+]" fieldname="'.$tvname.'" class="xedit_input">'.$ph3.'</div>' , $chunk_content);
                    $chunk_content = str_replace($search,'<div resourceclass="modDocument" rowid="[+id+]" fieldname="'.$tvname.'" class="xedit">'.$ph.'</div>' , $chunk_content);
                }else {
                    $chunk_content = str_replace($search ,$ph , $chunk_content);
                }
            //auch in nicht bearbeitbare TVs den Standartwert einbauen

            }
        }

        if (count($this->docColumnNames)>0) {
            foreach ($this->docColumnNames as $key=>$tvname) {
                $search=$this->startTag.$this->editableTag.$tvname.$this->endTag;
                $defaultValue='New '.$tvname;
                $ph=($forChunkSelecion=='1')?$defaultValue:$this->startTag.$tvname.$this->endTag;
                if ($GLOBALS['xedit_runs']=='1') {
                    $chunk_content = str_replace($search,'<div resourceclass="modDocument" rowid="[+id+]" fieldname="'.$tvname.'" class="xedit">'.$ph.'</div>' , $chunk_content);
                }else {
                    $chunk_content = str_replace($search ,$ph , $chunk_content);
                }
            }
        }


        return $chunk_content;
    }

}


class xedit {

    function xedit( $container ) {
        $this->container        = $container;
        $this->pluginpath       = $container['params']['pluginpath'];
        $this->manager_action   = $container['params']['manager_action'];
        $this->docid            = $container['params']['id']; // stimmt so nicht immer
        $this->startTag         = $container['params']['placeholder_tags'][0];
        $this->endTag           = $container['params']['placeholder_tags'][1];
        $this->multiCategories  = $container['params']['multiCategories'];
        $this->editableTag      = $container['params']['editableTag'];
        $this->inputTag         = $container['params']['inputTag'];
        $this->webpermissions   = $container['params']['webpermissions'];
        $this->mgrpermissions   = $container['params']['mgrpermissions'];
        $this->settings_from    = $container['params']['settings_from'];
		$this->blox_tabs        = $container['params']['blox_tabs']; 
		$this->setting_tabs     = $container['params']['setting_tabs']; 
		$this->tv_tabs          = $container['params']['tv_tabs']; 
		
		$this->userPermissions  = $this->getUserPermissions();

        $this->tbl_mtc          = $container['tables']['mtc'];  //site_multiTVchunks
        $this->tbl_hts          = $container['tables']['hts'];  //site_htmlsnippets
        $this->tbl_tvmc         = $container['tables']['tvmc'];//site_tmplvar_multicontents
        $this->tbl_tv           = $container['tables']['tv'];    //site_tmplvars
        $this->tbl_tvc          = $container['tables']['tvc'];   //site_tmplvar_contentvalues
        $this->tbl_sc           = $container['tables']['sc'];   //site_content
        $this->tbl_cs           = $container['tables']['cs'];   //chunk_settings
        $this->tbl_st           = $container['tables']['st'];   //templates

        $this->chunkContainers  = array();
        $this->collectedChunks  = array();
        $this->containerTVs     = array();
        //$this->chunks
        $this->chunkids         = array();
        $this->allChunkTvNames  = array();
        $this->allChunkDvNames  = array();
        $this->MultiTvs         = array();
        $this->NewMultiTvs      = array();

        $this->template         = 'all';
        $this->tvnames          = array();
        $this->docColumnNames   = array();
        $this->tvids            = array();
        $this->getTvNames($this->template);
        $this->getDocColumnNames();
    }

    function include_eventfile($eventname) {
       
		include_once $this->pluginpath.'inc/events/'.strtolower( $eventname ).'.inc.php';
        return $output;
       
    }

    function checkSettingsTable() {

        global $modx;
        $query = "
	      CREATE TABLE if not exists ".$this->tbl_cs." (
          `template_id` int(11) NOT NULL default '0',
          `settings_for` varchar(50) default NULL,
          `settings` text,
          KEY `user` (`template_id`)
          ) ENGINE=MyISAM DEFAULT CHARSET=utf8";

        $rs = $modx->db->query($query);
        return;
    }


    /////////////////////////////////////////////////////////////////////////////
    //function to check for permission
    /////////////////////////////////////////////////////////////////////////////

    function getUserPermissions() {
        $webgroupnames=$this->getwebusergroupnames();
        $mgrgroupnames=$this->getManagerUserGroupNames();

        $userperms=array();
        //print_r($webgroupnames);
        //print_r($mgrgroupnames);
        if (count($webgroupnames)>0) {
            foreach($webgroupnames as $groupname) {
                if (isset($this->webpermissions[$groupname])) {
                    $perms=explode(',',$this->webpermissions[$groupname]);
                    $perms[]='runajaxsnippet';
                    $userperms=(count($userperms)>0)?array_merge($userperms,$perms):$perms;
                }

            }
        }

        if (count($mgrgroupnames)>0) {
            foreach($mgrgroupnames as $groupname) {
                if (isset($this->mgrpermissions[$groupname])) {
                    $perms=explode(',',$this->mgrpermissions[$groupname]);
                    $perms[]='runajaxmodule';
                    $userperms=(count($userperms)>0)?array_merge($userperms,$perms):$perms;
                }
            }
        }

        $perms=array();
        if (count($userperms)>0) {
            foreach ($userperms as $perm) {
                if (!empty($perm)) {
                    $perms[$perm]='1';
                }
            }
        }

        //$userperms=array_unique($userperms);

        return $perms;
    }


    /////////////////////////////////////////////////////////////////////////////
    //function to get the groupnames of the webuser
    /////////////////////////////////////////////////////////////////////////////

    function getWebUserGroupNames() {
        global $modx;
        $userid = $modx->getLoginUserID();
        $rows = array ();
        $names = array ();
        if (!$userid) {
            return $names;
        } else {
            $tablename1 = $modx->getFullTableName('web_groups').' wg';
            $tablename2 = $modx->getFullTableName('webgroup_names').' wn';
            $query = "SELECT distinct name
                       FROM ".$tablename1.", ".$tablename2." 
                       where webuser=".$userid." and wn.id = wg.webgroup";
            $result = $modx->db->query($query);
            $rows = $modx->db->makeArray($result);
            foreach ($rows as $row) {
                $names[] = $row['name'];
            }

        }
        return $names;
    }

    /////////////////////////////////////////////////////////////////////////////
    //function to get the groupnames of the manageruser
    /////////////////////////////////////////////////////////////////////////////

    function getManagerUserGroupNames() {
        global $modx;
        //$userid = $modx->getLoginUserID('mgr');//funktioniert nicht wie erwartet
        $userid = false;
        $context = 'mgr';
        if ($context && isset ($_SESSION[$context.'Validated'])) {
            $userid = $_SESSION[$context.'InternalKey'];
        }

        $rows = array ();
        $names = array ();
        if (!$userid) {
            return $names;
        } else {
            $tablename1 = $modx->getFullTableName('member_groups').' wg';
            $tablename2 = $modx->getFullTableName('membergroup_names').' wn';
            $query = "SELECT distinct name
                       FROM ".$tablename1.", ".$tablename2." 
                       where member=".$userid." and wn.id = wg.user_group";
            $result = $modx->db->query($query);
            $rows = $modx->db->makeArray($result);
            foreach ($rows as $row) {
                $names[] = $row['name'];

            }

        }
        return $names;
    }


    function makeAjaxUrl() {
        global $modx;
        $managerPath = $modx->getManagerPath();
        if ($this->userPermissions['runajaxmodule'] == '1') {
            include_once($this->pluginpath.'/inc/module.class.inc.php');
            $module = new Module;
            $module->getIdFromDependentPluginName($modx->Event->activePlugin);
            $moduleID = $module->id;
            $moduleActionID = 112;
            $moduleURL = $managerPath.'index.php?a='.$moduleActionID.'&id='.$moduleID;
            $ajax_url = $moduleURL;
        }
        elseif ($this->userPermissions['runajaxsnippet'] == '1') {
            $rs=$modx->db->select('id',$this->tbl_sc,"pagetitle='Xedit_AJAX'");
            $docs=$modx->db->makeArray($rs);
            if (count($docs)>0) {
            //$ajax_url = $modx->makeUrl('508');
                $ajax_url = $modx->makeUrl($docs[0]['id']);
            }
        //$ajax_url = "index.php?id=508";
        }

        return $ajax_url;
    }


    function getContainerTVids($docid) {
        global $modx;
        $containerTvs=$this->container['params']['containerTvCategories'];
        $query="
select distinct tv.id,tv.name,tv.caption from `modx_site_tmplvars` tv 
left join `modx_site_tmplvar_templates` tvt on tvt.tmplvarid=tv.id
left join `modx_site_content` sc on sc.template=tvt.templateid
left join `modx_categories` c on c.id=tv.category 
where sc.id=$docid and c.category='$containerTvs'
            ";
        $rs=$modx->db->query($query);
        $rows=$modx->db->makeArray($rs);
        $tvids=array();
        if (count($rows)>0) {
            foreach ($rows as $row) {
                $tvids[$row['name']]=$row['id'];
                $this->containerTVs[$row['name']]=$row;
            }
        }

        return $tvids;
    }

    //noch erweitern für mehrere parents und eigene Funktion für docids
    function getChunkContainersFromParents($parentid,$containerName) {
        global $modx;
        $chunkContainers = array ();
        $childrenoutput = $this->getChildChunks($parentid);

        $container=array();
        $container['value'] = $childrenoutput;
        $container['name'] = $containerName;
        $container['caption'] = $containerName;
        $chunkContainers[$containerName] = $container;
        $this->chunkContainers = $chunkContainers;
        //print_r($parentid);
        return;
    }

    function getChunkContainers($docid,$containerNames=array()) {
        global $modx;

        if (!count($containerNames)>0) {
            $doc = $modx->getPageInfo($docid,0,'template');
            $templateid = $doc['template'];
            $templateids=explode(',',$templateid);
            $btc = new blox_Template_Collection($templateids, $this);
            $containerNames=($btc->getContainerNames($templateid));
        }

    /*
	print_r($containerNames);
    $dTv_ids = $this->getContainerTVids($docid);
    $tmplvars = $modx->getTemplateVars(array_flip($dTv_ids), $fields = "*", $docid);
    */
        $childids = $this->getChildrenIds($docid);
        $childtitles = array_flip($childids);

        $chunkContainers = array ();
        if (count($containerNames) > 0) {
            foreach ($containerNames as $containerName) {
            //echo $tmplvar['caption'];

                if ( isset ($childids[$containerName])) {
                    $parentid = $childids[$containerName];
                    $childrenoutput = $this->getChildChunks($parentid);

                } else {
                    $parentid = 'new';
                }
                $container=array();
                $container['value'] = $childrenoutput;
                $container['name'] = $containerName;
                $container['caption'] = $containerName;
                $chunkContainers[$containerName] = $container;
            }
        }
        $this->chunkContainers = $chunkContainers;
        //print_r ($this->chunkContainers);

        return;
    }



    function getChunkContainers2($docid) {
        global $modx;
        $childids = $this->getChildrenIds($docid);
        $childtitles = array_flip($childids);

        $dTv_ids = $this->getContainerTVids($docid);
        $tmplvars = $modx->getTemplateVars(array_flip($dTv_ids), $fields = "*", $docid);

        $chunkContainers = array ();
        if ( !empty($tmplvars) && count($tmplvars) > 0) {
            foreach ($tmplvars as $tmplvar) {
            //echo $tmplvar['caption'];

                if ( isset ($childids[$tmplvar['caption']])) {
                    $parentid = $childids[$tmplvar['caption']];
                    $childrenoutput = $this->getChildChunks($parentid);

                } else {
                    $parentid = 'new';
                }
                $tmplvar['value'] = $childrenoutput;
                $chunkContainers[$tmplvar['id']] = $tmplvar;
            }
        }
        $this->chunkContainers = $chunkContainers;

        return;
    }


    function getChildChunks($parentid) {
        global $modx;
        $tvidnames = array ('id', 'pagetitle', 'chunkname', 'menuindex', 'published', 'deleted');
        $childrenoutput_pub = $modx->getDocumentChildrenTVarOutput($parentid, $tvidnames, 1, $docsort = "menuindex", $docsortdir = "ASC");
        $childrenoutput_unpub = $modx->getDocumentChildrenTVarOutput($parentid, $tvidnames, 0, $docsort = "menuindex", $docsortdir = "ASC");

        $childrenoutput_pub=(is_array($childrenoutput_pub))?$childrenoutput_pub:array();
        $childrenoutput_unpub=(is_array($childrenoutput_unpub))?$childrenoutput_unpub:array();

        $childrenoutput=array_merge($childrenoutput_pub,$childrenoutput_unpub);

        return $childrenoutput;
    }



    function getChunksFromContainers() {
        $chunknames = array ();
        if (count($this->chunkContainers) > 0) {
            foreach ($this->chunkContainers as $container) {
                $chunknames = $this->getChunksFromContainer($container, $chunknames);
            }
        }

        //$this->collectVarsFromChunks($chunknames);

        return $chunknames;
    }

    function getChunksFromContainer($container, $chunknames) {
        if (! empty($container['value']) && count($container['value']) > 0) {
            foreach ($container['value'] as $chunkkey=>$chunk) {
                $chunknames[$chunk['chunkname']] = $chunk['chunkname'];
            }
        }
        return $chunknames;
    }


    function getTvNames($template = 'all') {
        global $modx;

        if ($template !== 'all') {
            $table1 = $this->tbl_tv.' st ';
            $table2 = $modx->getFullTableName('site_tmplvar_templates').' stt ';
            $tablenames = $table1.','.$table2;
            $query = 'SELECT id,name FROM '.$tablenames.'WHERE templateid='.$template.' and stt.tmplvarid=st.id';
            $result = $modx->db->query($query);
        }
        else {
            $table = $this->tbl_tv;
            $result = $modx->db->select('*', $table, '');
        }
        $tv_arrays = $modx->db->makeArray($result);
        $tvnames = array ();
        $tvids = array ();
        foreach ($tv_arrays as $tv_array) {
            $tvid = $tv_array['id'];
            $tvnames[$tvid] = $tv_array['name'];
            $tvids[$tv_array['name']] = $tvid;
        }
        $this->tvnames = $tvnames;
        $this->tvids = $tvids;
        return;
    }

    function getDocColumnNames() {
        global $modx;
        $table = $modx->getFullTableName('site_content');
        $result = $modx->db->select('*', $table, '');
        $this->docColumnNames = $modx->db->getColumnNames($result);
        return;
    }
    function getDocColumnNamesRevo() {
        global $modx;
        $fields = $modx->getFields('modResource');
        $this->docColumnNames=array();
        foreach ($fields as $key=>$field) {
            $this->docColumnNames[] = $key;
        }
        return;
    }

    function makeFrontEndTvTabs($template, $docid, $type = 'ajax',$prefix='') {
        global $modx;
        $pageinfo = $modx->getPageInfo($docid, 0, 'published');
		$published = $pageinfo['published'];
        
        switch($type) {
            case 'settingTabs':
                $btc = new blox_Template_Collection(array ($template), $this);
                $settings=$btc->settings[$template]['setting_tabs'];
                //$attributes = 'class="docinput"';
				$classnames = array('docinput');
                $c_type = 'container';
                /*for revo
                $template = $modx->getObject('modTemplate', array ('id'=>$template), true);
                $settings = $template->getProperties('setting_tabs');
                $settings = $settings['setting_tabs'];			
                */
                break;
            case 'tvTabs':
                $btc = new blox_Template_Collection(array ($template), $this);
                $settings=$btc->settings[$template]['tv_tabs'];
                //$attributes = 'class="docinput"';
				$classnames = array('docinput');
                $c_type = 'container';
                break;
            default:
			
                if (isset($_POST['xedit_tabs'])&& !empty($_POST['xedit_tabs']) && isset ($_POST['containers'])) {
                    $containers = (array)json_decode(($_POST['containers']), true);
                    $xedit_tabs = explode(':',$_POST['xedit_tabs']);
                    if ($xedit_tabs[0]=='@TV') {
                    	
                        $tv = $modx->getTemplateVarOutput($xedit_tabs[1],$containers[0]['sender_id']);
                        $settings = $tv[$xedit_tabs[1]];
                    }
                    if ($xedit_tabs[0]=='@CONFIG') {
                        $settings = $this->container['params']['xeditTabs'][$_POST['chunkname']]; 
                    }					
                }
                else {
                    $chunknames = array ($template);
                    $bcc = new blox_Chunk_Collection($chunknames, $this);
                    $settings=$bcc->settings[$template]['xedit_tabs'];
                }


				/*for revo
                $chunk = $modx->getObject('modChunk', array ('name'=>$_POST['chunkname']), true);
                $settings = $chunk->getProperties('xedit_tabs');
				$settings = $settings['xedit_tabs'];				
                */
                //$attributes = 'class="bloxinput"';
				$classnames = array('bloxinput');
                $c_type=$_POST['containertyp'];
                $hidden = '
				<input type="hidden" id="'.$prefix.'input_savemode" name="input_savemode" value="'.$_POST['savemode'].'"/>
    		    <input type="hidden" id="'.$prefix.'input_chunkname" name="input_chunkname" value="'.$_POST['chunkname'].'"/>
    		    <input type="hidden" id="'.$prefix.'input_c_parentid" name="input_c_parentid" value="'.$_POST['containerparent'].'"/>
    		    <input type="hidden" id="'.$prefix.'input_c_id" name="input_c_id" value="'.$_POST['containerid'].'"/>				
				';

                break;
        }
        $xedit_tabs = (trim($settings)!=='')?explode('||',$settings):array();


        /*
         $xedit_tabs['content']['caption']='Content';
         $xedit_tabs['content']['tv_names']='content';
         $xedit_tabs['text']['caption']='Text';
         $xedit_tabs['text']['tv_names']='text';
         $xedit_tabs['sonstige']['caption']='Sonstige Tvs';
         $xedit_tabs['sonstige']['tv_names']='pagetitle,chunkname,checkbox,bild';
         */

        $tabs_output = '';

        if (count($xedit_tabs) > 0) {
            foreach ($xedit_tabs as $tab) {
                $tab = trim($tab);
                if ( $tab !== '') {
                    $tab = explode(':', $tab);
                    $caption=$tab[0];
					
                    if (trim($tab[1])=='@CHUNK') {
                        $tv_section=$modx->parseDocumentSource('{{'.trim($tab[2]).'}}');
                /*for revo
				$tv_section=$modx->getChunk(trim($tab[2]));
				*/				
                    }
                    else {
		    /*example for tv-tabs:
		     * tab1_caption:docfield1,docfield2,tvfield3||
             * tab2_caption:hidden:docfield4,tvfield5||
             * tab3_caption:@CHUNK:chunkname||
             * tab4_caption:field1;field-caption;default-value;output-type,field2;field-caption;default-value;output-type,field3;field-caption;default-value;output-type
		     */                        
		
                        $tabfields=$tab[1];
                        if (count($tab)==3) {
                            $tabfields=$tab[2];
                            if ($tab[1]=='hidden') {
                                $mode=$tab[1];
                            }
                        }
						$tabfields=explode(',',$tabfields);
						
						$tablename=(isset($_POST['tablename'])&&$_POST['tablename']!=='')?$_POST['tablename']:'';
       					$tv_section = $this->makeFrontEndTvSection($tabfields, $docid, $published, $prefix,$mode,$attributes,$tablename,$classnames);
                        
						if ($tv_section=='') {$tv_section='keine gefunden';}
                    }


                    $tabs_output .= '
        		    <h4 title="hier der Title">'.$caption.'</h4>
    				<div>
    				'.$tv_section.'
    				</div>
    				';            		
                }
            }
        }else {
            $tabs_output = '
        		    <h4 title="hier der Title">no Tabs defined</h4>
    				<div>
    				empty
    				</div>
    				';              	

        }

        $output = '
             	<div class="tab_block">
               	'.$tabs_output.'
               	</div>
        ';

        // Todo: die id des ajax-empfängers in die config-datei
        // und ersetzen mit [~'.$ajax_id.'~] oder so

        //die url kommt jetzt aus onwebpageprerender
        //und es wird ein modul als ajax-empfänger verwendet
        //so allerdings nur mit manager-login möglich

        if ($type == 'ajax') {
            $ajax_url = $_SESSION['xedit_moduleURL'];

            $output = '
    		<form method="post" action="'.$ajax_url.'" id="'.$type.'">
    		<input type="hidden" id="'.$prefix.'save_tv_tabs" name="save_tv_tabs" value="yes"/>
    		<input type="hidden" id="'.$prefix.'input_rowid" name="input_rowid" value="'.$docid.'"/>
   		    <input type="hidden" id="'.$prefix.'input_c_type" name="input_c_type" value="'.$c_type.'"/>			
            '.$hidden.$output.'

    		</form>';        	
        }
       
	    
	   
        return $output;

    }

    function makeFrontEndTvSection($tabfields, $docid, $published=1, $prefix='',$mode='classic', $attributes='',$tablename='',$classnames=array()) {


		    /*example for tv-tabs:
		     * tab1_caption:docfield1,docfield2,tvfield3||
             * tab2_caption:hidden:docfield4,tvfield5||
             * tab3_caption:@CHUNK:chunkname||
             * tab4_caption:field1;formelementTV,field2;formelementTV;field-caption;formelementTV
		     */

        //print_r($tabfields); 
        //echo $docid;
		$fields=array();
        $tvnames=array();
        if (count($tabfields) > 0)
        {
            foreach ($tabfields as $tabfield)
            {
                $fieldparts = explode(';', trim($tabfield));
                if (count($fieldparts) == 2)
                {
                    $field = array ();
                    // use TVs for inputFormatting of tablefields
                    $formelementTVs = $this->getNewMultiTvs(array($fieldparts[1]));
            
					if (count($formelementTVs) > 0){
						$field=$formelementTVs[$fieldparts[1]];
						$field['name'] = $fieldparts[0];
						$field['fTV_assigned']='1';
                    	$fields[$field['name']] = $field;
                    }
                }
				elseif ($tablename !== ''){
					$field=array();
					$field['name'] = $fieldparts[0];
					$field['type'] = 'text';
					$fields[$field['name']] = $field;
				}
				
                $tvnames[] = $fieldparts[0];
            }
            
        }
		
        require_once($this->pluginpath.'inc/formElements.class.inc.php');
        $fe= new formElements();
        //Todo: Richtexteditor einbinden für richtext-Tvs usw.
        //print_r($this->NewMultiTvs);
        global $modx;
        //print_r($chunk);
        $tvSection = '';
        if ($docid == 'new')
        {
            if ($tablename == '')
            {
                $tvs = $this->getNewMultiTvs($tvnames);
                //merge documentfields to tvfields
                if (count($tvnames) > 0)
                {
                    foreach ($tabfields as $Tvname)
                    {
                        $Tvname = trim($Tvname);
                        if (! isset ($tvs[$Tvname]))
                        {
                            $tvs[$Tvname]['name'] = $Tvname;
                        }
                    }
                }
            }
            else
            {
                $tvs=$fields;
            }
       
        }
        else
        {
            if ($tablename == '')
            {
                $tvs = $modx->getTemplateVars($tvnames, "*", $docid, $published, $sort = "rank", $dir = "ASC");
			}
			else{
				$keyfield='id';
				$table=$modx->getFullTablename($tablename);
				$rs = $modx->db->select('*',$table,$keyfield.'='.$docid);
				$row = $modx->db->getRow($rs);
				foreach($fields as & $field){
					$field['value']=$row[$field['name']];
				}
				$tvs = $fields;
			}
        }

        if (is_array($tvs) && count($tvs) > 0) {
            foreach ($tvs as $key=>$tv) {

            if (isset($fields[$tv['name']]['fTV_assigned'])){
            	$fields[$tv['name']]['value']=$tv['value'];
				$tv=$fields[$tv['name']];
				
            }else{
            	if ($tv['name']=='content')$tv['type'] = 'textarea';
            }
			 	
            //$tv['content'] = $tvvalues[$tvName];
            //$tv['mTVchunkid'] = $chunk['id'];
            //$tv['formname'] = $tvName.'_'.$containerkey.'_'.$chunkkey;
                $tv['formname'] = $prefix.'_input_'.$tv['name'];
                if (!isset($tv['caption']))$tv['caption'] = $tv['name'];
                
                $tv['FormElement'] = $fe->makeFormelement($tv,'tv', $attributes.' fieldname = "'.$tv['name'].'"',$classnames);
                if ($mode=='hidden') {
                    $tvSection .= '
							<div class="xcc_formelement">
								<label>'.$tv['caption'].'</label>
								'.$tv['FormElement'].'
							</div>
    				';                	
                }else {
                    $tvSection .= '
                    <tr><td colspan="2"><div class="split"/></td></tr>              
    		        <tr style="height: 24px;">
                    <td align="left" width="150" valign="top">
                        <span class="warning">'.$tv['caption'].'</span><br/><span class="comment">'.$tv['description'].'</span>
                    </td>
                    <td valign="top" style="position: relative;">
                    '.$tv['FormElement'].'
    				</td>
                  </tr>				
    				';                	
                }
            }

            if ($mode!=='hidden') {
                $tvSection = '
						<table cellspacing="0" cellpadding="3" border="0" width="96%" style="position: relative;">              
                        <tbody>'
                    .$tvSection.
                    '</tbody></table>';
            }

        }
        else {
            $tvSection.= $_lang['tmplvars_novars'];
        } //end check to see if there are template variables to display
        return $tvSection;
    }
	
	function makeXCC(){
    ///////////////////////////////////////
    // xeditControlCenter einbauen
    //////////////////////////////////////
	global $modx;
	
    if ($this->userPermissions['cancreatedocs'] == '1')
    {
    
        $GLOBALS['xedit_runs'] = '1';
        //$GLOBALS['ajax_url'] = $this->makeAjaxUrl();
    }
	$body_top='';
	
	
	if ($GLOBALS['xedit_runs'] == '1') {
	
	$ajax_url=$GLOBALS['ajax_url'];//from onparsedocument
	
    $templateid = $modx->documentObject['template'];
    if ($this->settings_from == 'CONFIG')
    {
		$bs = $this->blox_tabs[$templateid];
    
    } else
    {
        $templateids = explode(',', $templateid);
        $btc = new blox_Template_Collection($templateids, $this);
    
        //for revo
        //$templateid = $modx->resource->template;
        $bs = $btc->settings[$templateid]['blox_settings'];
    
    }

	
    if (substr($bs,0,3)=='@TV'){
    	$bs=explode(':',$bs);
		$tvbs=$modx->getTemplateVarOutput($bs[1],$doc_id);
		$blox_settings = explode('||', $tvbs[$bs[1]]);
    }else{
    	$blox_settings = explode('||', $bs);
    }

    if (count($blox_settings)==1 && trim($blox_settings[0])=='')
    {
    	$bloxContainers = '';
		$bloxArea = '';
    
    } else
    {
    	
        $container_chunks = array ();
        $chunk_tabs = array ();
        $chunknames = array ();
        //$allowed_chunks = array();
    
        foreach ($blox_settings as $value)
        {
            $container_array = explode(':', $value);
            $tab_name = $container_array[0];
            $tab_destination = $container_array[1];
            $destinations[$tab_name] = $tab_destination;
            if (trim($container_array[2]) == '@CHUNK' || trim($container_array[2]) == '@TV')
            {
                switch(trim($container_array[2]))
                {
                    case '@CHUNK':
                        $tabcontent = $modx->parseDocumentSource('{{'.trim($container_array[3]).'}}');
                        break;
                    case '@TV':
                        $tabcontent = $modx->parseDocumentSource('[*'.trim($container_array[3]).'*]');
                        //$tabcontent = '[*'.trim($container_array[3]).'*]';
						break;
                    default:
                        $tabcontent = '';
                        break;
                }
                $container_chunks[$tab_name] = $tabcontent;
                $chunk_tabs[$tab_name] = 'yes';
            }
            else
            {
                /*
				$container_chunks[$tab_name] = explode(',', $container_array[2]);
                //$allowed_chunks[$tab_destination] = $container_chunks[$tab_name];
    
                foreach ($container_chunks[$tab_name] as $chunkname)
                {
                    $chunknames[$chunkname] = $chunkname;
                }
                */
				
				$tabcontent = $modx->parseDocumentSource(trim($container_array[2]));
				//$tabcontent = (trim($container_array[2]));
                $container_chunks[$tab_name] = $tabcontent;
                $chunk_tabs[$tab_name] = 'yes';   
    
            }
	
        }
    
        $bcc = new blox_Chunk_Collection($chunknames, $this);
        //$xedit->collectVarsFromChunks($chunknames);
        $bcc->allChunkTvNames[] = 'menuindex';
        $tvIds = array_flip($bcc->allChunkTvNames);
        $this->getNewMultiTvs($tvIds);
        //Umbau wenn Marc soweit ist, dann die chunks für alle Blöcke bereitstellen
    
        //print_r($allowed_chunks);
        /* 
         $chunks = $allowed_chunks['left'];
         $chunks_output = $bcc->prepareChunkSelection($chunks, 'left');
         */
        //$modx->setPlaceholder('allowed_chunks', $allowed_chunks);
    
        $bloxContainers = '';
        if (count($container_chunks) > 0)
        {
            foreach ($container_chunks as $containerName=>$tab_output)
            {
                if ( isset ($chunk_tabs[$containerName]) && $chunk_tabs[$tab_name] == 'yes')
                {
                    $tab_output = '
    				<div>
            		'.$tab_output.'
    				</div>
        			';
                }
                else
                {
                    $chunks_output = $this->prepareXccButtons($tab_output, $bcc->collectedChunks, $containerName);
                    $tab_output = '
    				<ul class="xcc_bloxcontainer unremoveable" container="'.$destinations[$containerName].'">
            		'.$chunks_output.'
    				</ul>
        			';
    
                }
    
                $bloxContainers .= '
    			<h4>'.$containerName.'</h4> 
            	'.$tab_output;
    
            }
            $bloxTab = '<div class="xcc_level1 xcc_area_blox xcc_level1_inactive">
    					<div class="level1_inner">
    						<a class="xcc_area_box xcc_level1_link" href="#">Block hinzufügen</a>
    					</div>
    				</div>';
    
        }
			$bloxArea='
			<div id="xcc_area_blox" class="xcc_area">
				<div class="xcc_area_inner">
					<div id="xcc_blox_block" class="">

                    '.$bloxContainers.'
							
					</div>
				</div>
			</div>					
			';
  	

    }
    $prefix='_doc';
    $tvTabs = $this->makeFrontEndTvTabs($templateid,$doc_id,'tvTabs',$prefix);
    $settingTabs = $this->makeFrontEndTvTabs($templateid,$doc_id,'settingTabs',$prefix);


	$settingTab = '<div class="xcc_level1 xcc_area_settings xcc_level1_inactive">
					<div class="level1_inner">
					<a href="#">Seiteneinstellungen</a>
					</div>
				</div>';
	$tvTab = '<div class="xcc_level1 xcc_area_tvs xcc_level1_inactive">
					<div class="level1_inner">
					<a href="#">Template Variablen</a>
					</div>
				</div>';				
    // Save Button hinzugefügt. Erstmal ein einfacher Save Button für alles.
    $userid = $modx->getLoginUserID('mgr');
    $body_top = '<div id="chunks">
			<h2>Available Chunks, click to insert:</h2>
			'.$chunks_output.'User:'.$userid.'
			<a id="saveall">Save</a>
			</div>
			';

    $body_top = '

		<div id="xcc">
			<span id="minimize" class="maximize">&nbsp;</span>
			<span id="saveall">&nbsp;</span>
			<div id="xcc_panel" class="clearfix">
			'.$bloxTab.$settingTab.$tvTab.'
				<div class="xcc_level1 xcc_area_blox_edit xcc_level1_inactive">
					<div class="level1_inner">
					<a href="#">Block bearbeiten</a>
					</div>
				</div>
			</div>
            '.$bloxArea.'
    		<form method="post" action="'.$ajax_url.'" id="document_form">
    		<input type="hidden" id="save_tv_tabs" name="save_tv_tabs" value="yes"/>
    		<input type="hidden" id="'.$prefix.'input_rowid" name="input_rowid" value="'.$doc_id.'"/>
   		    <input type="hidden" id="'.$prefix.'input_c_type" name="input_c_type" value="container"/>			
            <input type="hidden" id="input_prefix" name="input_prefix" value="tv'.$prefix.'_input_"/>			
			<div id="xcc_area_settings" class="xcc_area">
				<div class="xcc_area_inner">
					<div id="xcc_settings_block" class="">
                     
					'.$settingTabs.' 
					 
					</div>				
				</div>
			</div>
			<div id="xcc_area_tvs" class="xcc_area">
				<div class="xcc_area_inner">
					<div id="xcc_tvs_block" class="">

                    '.$tvTabs.'

					</div>	
				</div>
			</div>
    		</form>		
			<div id="xcc_area_blox_edit" class="xcc_area">
				<div class="xcc_area_inner">
					<span id="xcc_edit_cancel">Cancel</span>
					<span id="xcc_edit_save">&nbsp;</span>
				</div>
			</div>
		</div>

';		
}

return $body_top;		
	}
	
/*for revo
    function makeFrontEndTvSection($Tvnames, $docid, $published = 1, $prefix = '', $mode = 'classic')
    {
        //Todo: Richtexteditor einbinden für richtext-Tvs usw.
        //print_r($this->NewMultiTvs);
        global $modx;
        $tvs=$this->getTemplateVars($Tvnames, $docid );
		
        $tvSection = '';
        if (is_array($tvs) && count($tvs) > 0)
        {
            foreach ($tvs as $key=>$tv)
            {
                //$formelement = $tv->renderInput($docid);
    
                $tv['content'] = $tv['value'];
                //$tv['content'] = $tvvalues[$tvName];
                //$tv['mTVchunkid'] = $chunk['id'];
                //$tv['formname'] = $tvName.'_'.$containerkey.'_'.$chunkkey;
                $tv['formname'] = $prefix.'_input_'.$tv['name'];
                if (! isset ($tv['caption']))$tv['caption'] = $tv['name'];
                if ($tv['name'] == 'content')
                    $tv['type'] = 'textarea';
    
                $tv['FormElement'] = $this->makeFormelement($tv);
            //$tv['FormElement'] = $formelement;
            if ($mode == 'hidden')
            {
                $tvSection .= '
    							<div class="xcc_formelement">
    								<label>'.$tv['caption'].'</label>
    								'.$tv['FormElement'].'
    							</div>
        				';
        } else
        {
            $tvSection .= '
                        <tr><td colspan="2"><div class="split"/></td></tr>              
        		        <tr style="height: 24px;">
                        <td align="left" width="150" valign="top">
                            <span class="warning">'.$tv['caption'].'</span><br/><span class="comment">'.$tv['description'].'</span>
                        </td>
                        <td valign="top" style="position: relative;">
                        '.$tv['FormElement'].'
        				</td>
                      </tr>				
        				';
    }
    }
    
    if ($mode !== 'hidden')
    {
        $tvSection = '
    						<table cellspacing="0" cellpadding="3" border="0" width="96%" style="position: relative;">              
                            <tbody>'
        .$tvSection.
        '</tbody></table>';
    }
    
    }
    else
    {
        $tvSection .= $_lang['tmplvars_novars'];
    } //end check to see if there are template variables to display
    return $tvSection;
    }

 function getTemplateVars($Tvnames, $docid ){
        global $modx;

        $resourceId = $docid;
        $resourceClass = 'modDocument';
        $resource = $modx->getObject($resourceClass, $resourceId);
        $docid = $resource->get('id') == $resourceId?$resourceId:'new';
		$criteria = "`name` IN ('" . implode("','", $Tvnames) . "')";
    
        if ($docid == 'new')
        {
        	$tvCollection = $modx->getCollection('modTemplateVar');
			$tvObjects = $modx->getCollection('modTemplateVar', $criteria);
			$resourceFields = $modx->getFields($resourceClass);
        } else
        {
            //$tvs = $modx->getTemplateVars($Tvnames, $fields = "*", $docid, $published , $sort = "rank", $dir = "ASC");
            //$resourceClass= isset ($_REQUEST['class_key']) ? $_REQUEST['class_key'] : 'modDocument';
            //see manager\controllers\resource\tvs.php
            $c = $modx->newQuery('modTemplateVar');
            $c->select('
                DISTINCT modTemplateVar.*,
                IF(ISNULL(tvc.value),modTemplateVar.default_text,tvc.value) AS value,
                IF(ISNULL(tvc.value),0,'.$resource->get('id').') AS resourceId
            ');
            $c->innerJoin('modTemplateVarTemplate','tvtpl',array(
                '`tvtpl`.`tmplvarid` = `modTemplateVar`.`id`',
                '`tvtpl`.templateid' => $resource->get('template'),
            ));
            $c->leftJoin('modTemplateVarResource','tvc',array(
                '`tvc`.`tmplvarid` = `modTemplateVar`.`id`',
                '`tvc`.contentid' => $resource->get('id'),
            ));
			$c->where("`name` IN ('" . implode("','", $Tvnames) . "')");			
			
            $tvObjects = $resource->getMany('modTemplateVar',$c);
			$resourceFields = $resource->toArray();
        }
        $tvs = array ();
        if (is_array($tvObjects) && count($tvObjects) > 0)
        {
            foreach ($tvObjects as $tvObject)
            {
                $tv = $tvObject->toArray();
				print_r($tv);
                if (in_array($tv['name'], $Tvnames))
                {
                    $tvs[$tv['name']] = $tv;
                }
            }
        }

        //merge documentfields to tvfields
        foreach ($resourceFields as $key=>$field){
			if (in_array($key,$Tvnames)){
					$tvs[$key]['name'] = $key;
                    $tvs[$key]['value'] = $field;
			}
        }
        return $tvs;  
	
}
*/

    function makeTvSection($chunk,$containerkey,$chunkkey,$bcc) {
    //Todo: Richtexteditor einbinden für richtext-Tvs
    //print_r($this->NewMultiTvs);
        global $modx;
        //print_r($chunk);

        $chunkkey=$chunk['id'];

        $tvSection='';
        //$chunk_Tvnames = $this->getTvsFromChunk($chunk['snippet']);
        //$this->getTvsFromChunk($chunk['snippet']);
        $chunk_Tvnames = $bcc->collectedChunks[$chunk['chunkname']]['tvNames'];
        //print_r($chunk_Tvnames);

        $tvs=$modx->getTemplateVars($chunk_Tvnames, $fields= "*", $chunkkey, $published= 1, $sort= "rank", $dir= "ASC");

        $tvvalues=array();
        if (is_array($tvs)&&count($tvs)>0) {
            foreach ($tvs as $tv) {
                $tvvalues[$tv['name']]=$tv['value'];
            }
        }

        unset($tvs);
        //print_r($tvvalues);

        //$chunk_Tvnames = $chunk['tvNames'];
        //print_r($chunk_Tvnames);
        if (count($chunk_Tvnames)>0) {
            $tvSection.='<table cellspacing="0" cellpadding="3" border="0" width="96%" style="position: relative;">
                            <tbody>';
            foreach ($chunk_Tvnames as $tvid=>$tvName) {
                $tv=$this->NewMultiTvs[$tvName];
                if (isset($chunk['TVs'][$tvid])&&!empty($chunk['TVs'][$tvid]['content'])) {
                    $tv['content']=$chunk['TVs'][$tvid]['content'];
                }
                $tv['content']=$tvvalues[$tvName];
                $tv['mTVchunkid']=$chunk['id'];
                $tv['formname']='_'.$containerkey.'_'.$chunkkey.'_'.$tvName;
                $tv['FormElement']=$this->makeFormelement($tv);
                $tvSection.='
                <tr><td colspan="2"><div class="split"/></td></tr>              
		        <tr style="height: 24px;">
                <td align="left" width="150" valign="top">
                    <span class="warning">'.$tv['caption'].'</span><br/><span class="comment">'.$tv['description'].'</span>
                </td>
                <td valign="top" style="position: relative;">
                '.$tv['FormElement'].'
				</td>
              </tr>				
				'; 

            }
            //$_SESSION['containerTVs'][$containerkey][$chunkkey]=$chunk_Tvnames;
            $tvSection.='</tbody></table>';
        }
        else {
            $tvSection.= $_lang['tmplvars_novars'];
        } //end check to see if there are template variables to display
        return $tvSection;
    }



    function makeSettingsOutput($settingFields, $settings) {

        $settingsoutput = '';
        if (count($settingFields) > 0) {
            foreach ($settingFields as $settingField) {
                $settingField = explode(':', $settingField);
                switch($settingField[1]) {
                    case 'textarea':
                        $settingsinput = '
                    <textarea style="width: 100%;" onchange="documentDirty=true;" rows="15" cols="40" name="'.$settingField[0].'" id="'.$settingField[0].'">'.$settings[$settingField[0]].'</textarea>';

                        break;
                    default:
                        $settingsinput = '
                    <input type="text" style="width: 300px;" onchange="documentDirty=true;" value="'.$settings[$settingField[0]].'" name="'.$settingField[0].'" id="'.$settingField[0].'"/>
	                ';
                        break;
                }
                $settingsoutput .= '
            <tr><td colspan="2"><div class="split"/></td></tr>
            <tr style="height: 24px;">
            <td align="left" width="150" valign="top">
            <span class="warning">'.$settingField[0].'</span>
            <br/><span class="comment"></span>
            </td>
            <td valign="top" style="position: relative;">
            '.$settingsinput.'
            </td>
            </tr>
            ';


            }
        }

        $output = '
    <div class="sectionHeader">Chunk Settings </div>
    <div class="sectionBody">
    <table cellspacing="0" cellpadding="3" border="0" width="96%" style="position: relative;">
    <tbody>
    '.$settingsoutput.'
    </td>
    </tr>
    </tbody>
    </table>
    </div>
    ';
        return $output;

    }

    function prepareXccButtons($chunknames,$collectedChunks,$block='') {

        $setDefault='1';
        $forChunkSelecion='1';

        $output='';

        if (count($chunknames)>0) {
            foreach ($chunknames as $chunkname) {
            //$drag_content=$this->prepareChunkForXedit($this->collectedChunks[$chunkname]['snippet'], $block, $setDefault,$forChunkSelecion );
                $button_caption=$collectedChunks[$chunkname]['description'];
                $published=$this->container['params']['published_default'];
                $drag_content = '
    <h2><div class="xedit" fieldname="pagetitle" resourceclass="modDocument" rowid="new">pagetitle</div></h2>('.$chunkname.')
	';
                $output.=$this->prepareXccButton($button_caption,$published,$chunkname,$drag_content);
            }
        }

        return $output;
    }

    function prepareXccButton($button_caption, $published, $chunkname, $drag_content, $btn_docid='new', $btn_savemode='') {
        $output .= '
		<li class="xcc_button">
		'.$button_caption.'
		<div class="xtools"><span class="drag">drag</span></div>
        <div class="bildchunk blox clearfix dragelement" resourceclass="modDocument" rowid="'.$btn_docid.'" chunkname="'.$chunkname.'" published="'.$published.'" savemode="'.$btn_savemode.'">
        <div class="xtools"><span class="drag">drag</span><span class="xtrash">trash</span><span class="save">save</span><span class="remove">remove</span></div>
        <div class="edit">
        '.$drag_content.'
        </div>
        </div>
		</li>';

        return $output;

    }

    function getMultiTvs($tvIds) {
        global $modx;
        if (count($tvIds) > 0 && count($this->chunkids) > 0) {
        //$sql = "SELECT DISTINCT tv.*, IF(tvc.value!='',tvc.value,tv.default_text) as value ";
            $sql = "SELECT DISTINCT tvmc.id as tvmcid,tv.*, tvmc.content as content, tvmc.mTVchunkid "; //new by Bruno
            $sql .= "FROM $this->tbl_tv tv ";
            //$sql .= "INNER JOIN $dbase.`" . $table_prefix . "site_tmplvar_templates` tvtpl ON tvtpl.tmplvarid = tv.id ";
            $sql .= "LEFT JOIN $this->tbl_tvmc tvmc ON tvmc.tmplvarid=tv.id ";
            //$sql .= "LEFT JOIN $dbase.`" . $table_prefix . "site_tmplvar_access` tva ON tva.tmplvarid=tv.id  ";
            //$sql .= "WHERE tvtpl.templateid = " . $template . " AND (1='" . $_SESSION['mgrRole'] . "' OR ISNULL(tva.documentgroup)" . ((!$docgrp) ? "" : " OR tva.documentgroup IN ($docgrp)") . ") ORDER BY tvtpl.rank,tv.rank;";
            $sql .= "where tv.id IN (".implode(',', $tvIds).")";
            $sql .= " AND tvmc.mTVchunkid IN (".implode(',', $this->chunkids).")";
            $rs = $modx->db->query($sql);
            $Tvs = $modx->db->makeArray($rs);
            $this->testMultiTvs = array ();
            $this->MultiTvs = array ();
            foreach ($Tvs as $Tv) {
                if ( !empty($Tv['mTVchunkid'])) {
                    $this->MultiTvs[$Tv['name'].'_'.$Tv['mTVchunkid']] = $Tv;
                }
                if ( !empty($Tv['mTVchunkid'])) {

                    $tmp_TV=array();
                    $tmp_TV['id']= $Tv['id'];
                    $tmp_TV['name']= $Tv['name'];
                    $tmp_TV['content']= $Tv['content'];

                    $this->testMultiTvs[$Tv['mTVchunkid']][$Tv['id']] = $tmp_TV;
                }
            }
            $this->getNewMultiTvs($tvIds);
            unset ($Tvs);
        }

        return;
    }

    function getNewMultiTvs($idnames) {

        global $modx;
        if (count($idnames) > 0) {
            $idnames=array_values($idnames);
            if ($idnames == "*")
                $query = "tv.id<>0";
            else
                $query = (( (string)(int)$idnames[0] === (string)$idnames[0] )?"tv.id":"tv.name")." IN ('".implode("','", $idnames)."')";


            //removed $sql = "SELECT DISTINCT tv.*, IF(tvc.value!='',tvc.value,tv.default_text) as value ";
            $sql = "SELECT DISTINCT tv.* "; //new by Bruno
            $sql .= "FROM $this->tbl_tv tv ";
            //$sql .= "INNER JOIN $dbase.`" . $table_prefix . "site_tmplvar_templates` tvtpl ON tvtpl.tmplvarid = tv.id ";
            //$sql .= "LEFT JOIN $this->tbl_tvmc tvmc ON tvmc.tmplvarid=tv.id ";
            //$sql .= "LEFT JOIN $dbase.`" . $table_prefix . "site_tmplvar_access` tva ON tva.tmplvarid=tv.id  ";
            //$sql .= "WHERE tvtpl.templateid = " . $template . " AND (1='" . $_SESSION['mgrRole'] . "' OR ISNULL(tva.documentgroup)" . ((!$docgrp) ? "" : " OR tva.documentgroup IN ($docgrp)") . ") ORDER BY tvtpl.rank,tv.rank;";
            $sql .= "where ".$query;
            //$sql .= " AND (tvmc.mTVchunkid INs (".implode(',', $this->chunkids).") OR tvmc.mTVchunkid is null)";
            $rs = $modx->db->query($sql);
            $Tvs = $modx->db->makeArray($rs);
            $this->NewMultiTvs = array ();
            $newTvs=array();
            foreach ($Tvs as $Tv) {
                $newTvs[$Tv['name']] = $Tv;
                $Tv['mTVchunkid'] = 'new';
                $Tv['content'] = '';
                $Tv['tvmcid'] = '';
                $this->NewMultiTvs[$Tv['name']] = $Tv;

            }
            unset ($Tvs);
        }

        return $newTvs;
    }



    function getchunknames($categories='') {
        global $modx;
        $tbl_cat=$modx->getFullTablename('categories');
        $chunknames=array();
        $wherecategorie='';
        if ($categories !== '') {
            $wherecategorie="where c.category IN ('$categories')";
        }
        $query="
select hts.id,hts.name from $this->tbl_hts hts
join $tbl_cat c on c.id = hts.category ".$wherecategorie;

        $rs=$modx->db->query($query);
        $rows=$modx->db->makeArray($rs);
        if (count($rows>0)) {
            foreach($rows as $row) {
                $chunknames[$row['id']]=$row['name'];
            }
        }
        return $chunknames;
    }

    function make_select($selectname, $values = array (),  $selectedvalue = '',$classname='', $array_key=false, $addEmpty = '0') {
        $class=($classname !== '')?'class="'.$classname.'"':'';
        $select = ($array_key)
            ?'<select name="'.$selectname.'['.$key.']" '
            :'<select name="'.$selectname.'" ';
        $select.= ' id="'.$selectname.'" '.$class.' style="width:100px">';
        if ($addEmpty == '1') {
            $select.='<option value="0">--empty--</option>';
        }
        if (count($values) > 0) {
            foreach ($values as $selectvalue) {
                $selected = ($selectedvalue == $selectvalue)?'selected="selected"':'';
                $select .= '<option value="'.$selectvalue.'"'.$selected.'>'.$selectvalue.'</option>';
            }
        }
        $select .= '</select>';
        return $select;
    }



    function getChildrenIds($docid) {

        global $modx;
        $children = $modx->getAllChildren($docid, 'menuindex', 'ASC', $fields = 'id, pagetitle, description, parent, alias, menutitle');
        $childids = array ();
        if (count($children) > 0) {
            foreach ($children as $child) {
                $childids[$child['pagetitle']] = $child['id'];
            }
        }
        return $childids;
    }

/*
 * saveBackendChunks
 * 
 * Hier werden die POST-Daten aus dem blox-manager im backend gesammelt
 * in das container-array verpackt
 * und an saveModifiedChunks übergeben
 * 
 */

    function saveBackendChunks() {
        global $modx;
        $containers = array ();

        if (is_array($_POST['container_key']) && count($_POST['container_key']) > 0) {
        //$childids=$this->getChildrenIds($this->docid);
        //$childtitles = array_flip($childids);

            foreach ($_POST['container_key'] as $ckey=>$containerTv_id) {
                $container = array ();
                $container['containerid'] = $_POST['container_caption'][$ckey];
                $container['c_type'] = 'blox_container';
                $container['c_parentid'] = $this->docid;
                $chunks = array ();
                //$chunk_docids = array();

                if ( isset ($_POST['mChunk_docid_'.$containerTv_id])) {
                    foreach ($_POST['mChunk_docid_'.$containerTv_id] as $key=>$docid) {
                        $chunk = array ();
                        $chunk['docid'] = $docid;
                        //$chunk_docids[$docid]=$docid;
                        //$chunk['docid']=($oldsort=='new')?'':$oldsort;

                        $chunk['deleted'] = $_POST['mChunk_deleted_'.$containerTv_id][$key];
                        $chunk['published'] = $_POST['mChunk_published_'.$containerTv_id][$key];
                        $fields=array();
                        $field=array();
                        $field['fieldname'] = 'pagetitle';
                        $field['value'] = $_POST['mChunk_caption_'.$containerTv_id][$key];
                        $fields[]=$field;
                        $field['fieldname'] = 'chunkname';
                        $field['value'] = $_POST['mChunk_name_'.$containerTv_id][$key];
                        $fields[]=$field;
                        $chunk['fields']=$fields;
                        //$chunk['TVs'] = $chunkContainers[$containerTv_id]['value'][$oldsort]['TVs'];
                        $chunks[] = $chunk;
                    }
                }
                $container['chunks']=$chunks;
                $containers[]=$container;
            }
        }
        //print_r($containers);
        $this->saveModifiedChunks($containers);
    }

/*
 * saveBloxContainer
 * 
 * prüfen ob BloxFolder vorhanden
 * wenn nicht neuen anlegen und id zurückliefern
 * 
 */

    function saveBloxContainer($container) {
    	
		if ($container['c_resourceclass']=='modDocument'){
        $parent = $container['c_parentid'];
        $childids = $this->getChildrenIds($parent);
        $childtitles = array_flip($childids);
        //blox_folder vorhanden?
        if ( isset ($childids[$container['containerid']])) {
            $containerID = $childids[$container['containerid']];
        }
        else {
            $doc = new Document($parent, 'id,template');
            $doc->Set('isfolder', '1');
            $parentTemplate = $doc->Get('template');
            $doc->Save();
            $doc = new Document();
            $doc->Set('isfolder', '1');
            $doc->Set('published', '0');
            $doc->Set('parent', $parent);
            $doc->Set('pagetitle', $container['containerid']);
            $doc->Set('template', '0');
            $doc->Save();
            $containerID = $doc->Get('id');
        }
        return $containerID;			
		}
		

    }



    function saveBackendTvs() {
        global $modx;
        $containers=array();
        if (is_array($_POST['container_key']) && count($_POST['container_key']) > 0) {//print_r($_POST['container_key']);
        //$tvIds = array_flip($_SESSION['allChunkTvNames']);
        //$this->getNewMultiTvs($tvIds);
        //$tmplvars=array();

            foreach ($_POST['container_key'] as $containerkey) {//echo $containerkey;
                $chunks=array();
                if ($_POST['input_docid_'.$containerkey] && count($_POST['input_docid_'.$containerkey]) > 0) {
                    foreach ($_POST['input_docid_'.$containerkey] as $key=>$contentid) {
                    //echo $contentid;
                    //das noch prüfen!!!!
                        $chunkname=$_POST['input_chunkname_'.$containerkey][$key];

                        $prefix='tv_'.$containerkey.'_'.$contentid.'_';
                        $fields = $this->collectPostedDocFields($contentid,$prefix);
                        $chunk=array();
                        $chunk['docid']=$contentid;
                        $chunk['chunkname']=$chunkname;
                        $chunk['fields']=$fields;
                        $chunks[]=$chunk;
                    }
                }
                $container=array();
                $container['c_type']='blox_container';
                $container['containerid']=$containerkey;
                $container['c_parentid']=$_POST['id'];
                $container['chunks']=$chunks;
                $containers[]=$container;
            }

            //print_r($containers);

            $last_docid=$this->saveModifiedChunks($containers);

            return;
        //$this->saveTvs($tmplvars);

        }
/*
		$contentid=$_POST['input_rowid'];
		$chunkname=$_POST['input_chunkname'];
		
		$tvvalues=array();
		$tvIds=array();
		$fields=array();
		$prefix='tv_input_';

        $fields = $this->collectPostedDocFields($prefix);

		$chunks=array();
		$chunks[0]['rowid']=$contentid;
		$chunks[0]['chunkname']=$chunkname;	
		$chunks[0]['fields']=$fields;
		
		$containers=array();
		$containers[0]['c_type']=$_POST['input_c_type'];
		$containers[0]['containerid']=$_POST['input_c_id'];
		$containers[0]['c_parentid']=$_POST['input_c_parentid'];
		$containers[0]['chunks']=$chunks;		

        $last_docid=$this->saveModifiedChunks($containers);
*/

        return;
    }



/*
 * saveFrontEndTvTabs
 * 
 * POST-Daten aus den Frontend TV-Tabs sammeln
 * und containerarray draus bauen 
 * und an saveModifiedChunks übergeben
 * 
 */

    function saveFrontEndTvTabs() {
        global $modx;

        if (isset($this->container['params']['onbeforesave'][$_POST['onbeforesave']])){
        $settings = $this->container['params']['onbeforesave'][$_POST['onbeforesave']]; 
        $tab=explode(':',$settings);
		if (trim($tab[0])=='@CHUNK') {
        $onbeforesave=$modx->parseDocumentSource('{{'.trim($tab[1]).'}}');
        }	
        }
        
        /*
		$contentid=$_POST['input_rowid'];
		$tvvalues=array();
		$tvIds=array();
		$fields=array();
		$prefix=(isset($_POST['input_prefix']))?$_POST['input_prefix']:'tv_input_';

        $fields = $this->collectPostedDocFields($contentid,$prefix);

		$chunks=array();
		$chunks[0]['rowid']=$contentid;
		$chunks[0]['savemode']=$_POST['input_savemode'];
		$chunks[0]['fields']=$fields;
		if (isset($_POST['input_chunkname'])){
			$chunkname=$_POST['input_chunkname'];
		    $chunks[0]['chunkname']=$chunkname;	
		}
		
		$containers=array();
		$containers[0]['c_type']=$_POST['input_c_type'];
		$containers[0]['containerid']=$_POST['input_c_id'];
		$containers[0]['c_parentid']=$_POST['input_c_parentid'];
		$containers[0]['chunks']=$chunks;		
        */
        //print_r($_POST);
        $containers = (array)json_decode(($_POST['coll_container']), true);
        //print_r($containers);

        $last_docid=$this->saveModifiedChunks($containers);

        return $last_docid;


    }

    function collectPostedDocFields($contentid,$prefix) {
        if (count($this->docColumnNames) > 0) {
            foreach ($this->docColumnNames as $tvName) {
                if ( isset ($_POST[$prefix.$tvName])) {
                    $field = array ();
                    $field['value'] = $_POST[$prefix.$tvName];
                    $field['rowid'] = $contentid;
                    $field['fieldname'] = $tvName;
                    $fields[] = $field;
                }
            }
        }

        if (count($this->tvnames) > 0) {
            foreach ($this->tvnames as $tvid=>$tvName) {
                if (isset($_POST[$prefix.$tvName])) {
                    $tvIds[]=$tvid;
                }
            }
            $this->getNewMultiTvs($tvIds);
            if (count($tvIds)>0) {
                foreach ($tvIds as $tvid) {
                    $field=array();
                    $tvName=$this->tvnames[$tvid];
                    $tmplvar=$this->saveTv_prepare($tvName, $tvName, $contentid, $prefix);
                    $field['value']= $tmplvar['value'];
                    $field['rowid']= $contentid;
                    $field['fieldname']= $tvName;
                    $fields[]=$field;
                }
            }

        }
        return $fields;
    }


/*
 * saveModifiedChunks
 * 
 * hier wird das fertige array aus front und backend abgearbeitet
 * und alles dorthin gespeichert wo es hingehört - hoffe ich mal
 * 
 */
    function saveModifiedChunks($containers) {

        global $modx;
        $output='';

        if (is_array($containers) && count($containers) > 0) {
        //$chunkContainers=($_SESSION['chunkContainers']);

        //Todo:je nach containertyp (blox_container/parent_container/container/table_container) unterschiedlich verfahren
        //$modx->db-escape noch einbauen
        //print_r($containers);

            foreach ($containers as $container) {
            //$containerID = $container['c_parentid'];
            //vorläufig die docid hier holen
            //$this->docid=$containerID;//später ins javascript liefern und von dort dem request mitgeben
                $GLOBALS['resort']='1';
                unset($GLOBALS['documentsTv']);
                unset($GLOBALS['sortids']);
                $GLOBALS['orderByField']='menuindex';
                //hier noch (unsortable,unfillable) checken

                //echo $container['c_resourceclass'];

                if ($container['c_type'] == 'parent_container') {
                //parent ist festgelegt
                    $GLOBALS['containerID'] = $container['c_parentid'];

                }
				if ($container['c_type'] == 'blox_container'&& $container['c_resourceclass']=='modDocument') {
                //parent ist der bloXcontainer
                    $GLOBALS['containerID']=$this->saveBloxContainer($container);

                }
                if ($container['c_type'] == 'container') {
                //können verschiedene parents sein, also nicht containergebunden
                //und nicht sortierbar
                    unset($GLOBALS['containerID']);
                    $GLOBALS['resort']='0';
                    if (isset($container['documentsTv'])&&$container['documentsTv']!=='0') {
                        $GLOBALS['documentsTv']=$container['documentsTv'];
                        $GLOBALS['sortids']=array();
                    }

                }
                if (isset($container['orderByField'])&&$container['orderByField']!=='0') {
                    $GLOBALS['orderByField']=$container['orderByField'];
                }

                $chunks = $container['chunks'];
	
                $db = new xedit_db($this);
                if (count($chunks > 0)) {
                    $GLOBALS['sort_index']=1;
                    foreach ($chunks as $key=>$chunk) {
                	/*
					if (empty($chunk['rowid'])){
                		echo 'docid is empty';
                	}
					*/

                        $GLOBALS['save_blox_key']=$key;
                        if ($chunk['rowid'] !== 'dummy') {
                            $db->process($chunk, $container);

                    }}
                }
                if (isset($GLOBALS['documentsTv'])) {
                    $sortids=implode(',',$GLOBALS['sortids']);
                    $doc = new Document($container['sender_id'], 'id');//hier $chunk['rowid']
                    if ($GLOBALS['documentsTv']=$db->checkfieldtype($GLOBALS['documentsTv'])) {
                        $doc->Set($GLOBALS['documentsTv'], $sortids);
                    }
                    $doc->Save();
                }
            }
        }

        if ($modx->isFrontend())//klappt nicht im Backend
        {
            $this->emptyCache();
        }
        else {
            $this->emptyCache();
        //$modx->clearCache();//ob das hilft, wei� ich nicht
        }

        //
        return $db->last_docid;
    }

    function saveModifiedChunksold($containers) {

    //invoke saveevents  hier noch einbauen

        global $modx;
        $output='';

        if (is_array($containers) && count($containers) > 0) {
        //$chunkContainers=($_SESSION['chunkContainers']);

        //Todo:je nach containertyp (blox_container/parent_container/container) unterschiedlich verfahren
        //$modx->db-escape noch einbauen
        //print_r($containers);

            foreach ($containers as $container) {
            //$containerID = $container['c_parentid'];
            //vorläufig die docid hier holen
            //$this->docid=$containerID;//später ins javascript liefern und von dort dem request mitgeben
                $resort='1';
                unset($documentsTv);
                unset($sortids);
                $orderByField='menuindex';
                //hier noch (unsortable,unfillable) checken

                if ($container['c_type'] == 'parent_container') {
                //parent ist festgelegt
                    $containerID = $container['c_parentid'];

                }
                if ($container['c_type'] == 'blox_container') {
                //parent ist der bloXcontainer
                    $containerID=$this->saveBloxContainer($container);

                }
                if ($container['c_type'] == 'container') {
                //können verschiedene parents sein, also nicht containergebunden
                //und nicht sortierbar
                    unset($containerID);
                    $resort='0';
                    if (isset($container['documentsTv'])&&$container['documentsTv']!=='0') {
                        $documentsTv=$container['documentsTv'];
                        $sortids=array();
                    }

                }
                if (isset($container['orderByField'])&&$container['orderByField']!=='0') {
                    $orderByField=$container['orderByField'];
                }

                $chunks = $container['chunks'];
                if (count($chunks > 0)) {
                    $index=1;
                    foreach ($chunks as $key=>$chunk) {
                	/*
					if (empty($chunk['docid'])){
                		echo 'docid is empty';
                	}
					*/
                        $GLOBALS['save_blox_key']=$key;
                        if ($chunk['docid'] !== 'dummy') {
                            $invokearray=array ('mode'=>'new');
                            $invokemode='new';
                            $GLOBALS['savedoc']=true;
                            if ( isset ($chunk['docid']) && $chunk['docid'] !== 'new' && $chunk['savemode'] == 'copy' && ! isset ($documentsTv)) {
                                $doc = new Document($chunk['docid']);
                                if ($doc->isNew !== '1') {
                                    $doc->Duplicate();
                                }

                            } else {
                                $doc = new Document($chunk['docid'], 'id');//hier $chunk['docid']
                                if ($chunk['docid'] !== 'new'&&$doc->isNew !== '1') {
                                    $invokemode='upd';
                                    $invokearray=array('mode'=>'upd', 'id'=>$chunk['docid']);
                                }
                            }
                            //dont save new documents if docid isnot 'new'
                            if ($doc->isNew == '1'&&$chunk['docid'] !== 'new') {
                                $GLOBALS['savedoc']=false;
                            }

                            //in config einstellbar machen!!
                            $modx->invokeEvent('OnBeforeDocFormSave',$invokearray);

                            if (isset($containerID)) {
                                $doc->Set('parent', $containerID);
                            }

                            //$doc->Set('pagetitle', $chunk['fields']['pagetitle']);
                            if ($resort == '1') {

                                if ($orderByField=$this->checkfieldtype($orderByField)) {
                                    $doc->Set($orderByField, $index);
                                }

                            }

                            if ($chunk['modified'] !== 'no') {
                                if (isset($chunk['chunkname'])) {
                                    $doc->Set('tvchunkname', $chunk['chunkname']);
                                }

                                //echo $chunk['savemode'];
                                if ($chunk['docid'] == 'new') {
                                    $doc->Set('template', $parentTemplate);//Todo:--------
                                }
                                if (isset($container['filterByField'])&&$container['filterByField']!=='0'&&isset($container['filterValue'])&&$container['filterValue']!=='0') {
                                    if ($filterByField=$this->checkfieldtype($container['filterByField'])) {
                                        $doc->Set($filterByField, $container['filterValue']);
                                    }
                                }
                                if (count($chunk['fields'])>0) {
                                    foreach ($chunk['fields'] as $field) {
                                    //echo $field['tablename'];
                                        if ( isset ($_POST[$field['postname']])) {
                                            $field['value'] = $_POST[$field['postname']];
                                        }
                                        //convert images from directresize

                                        $field['value']=$this->ConvertFromBackend($field['value']);
                                        $field['value'] = $modx->db->escape($field['value']);

                                        if ($fieldname=$this->checkfieldtype($field['fieldname'])) {
                                            $doc->Set($fieldname, $field['value']);

                                        }

                                    }
                                }

                                if ( isset ($chunk['published']) && (($chunk['published'] == '1') || $chunk['published'] == '0')) {
                                    $doc->Set('published', $chunk['published']);
                                }
                                if ( isset ($chunk['deleted']) && (($chunk['deleted'] == '1') || $chunk['deleted'] == '0')) {
                                    $doc->Set('deleted', $chunk['deleted']);
                                }

                            }

                            if (isset($documentsTv)&&$chunk['docid']=='new') {


                            //dont save new documents in referenz-containers???
                            //or define an folder where to save???
                            }elseif($GLOBALS['savedoc']) {
                                $doc->Save();

                                $index++;
                                $last_docid=$doc->Get('id');
                                $modx->invokeEvent('OnDocFormSave', array ('mode'=>$invokemode, 'id'=>$last_docid));
                                if (isset($documentsTv)) {
                                    $sortids[]=$chunk['docid'];
                                }
                            }

                    }}
                }
                if (isset($documentsTv)) {
                    $sortids=implode(',',$sortids);
                    $doc = new Document($container['sender_id'], 'id');//hier $chunk['docid']
                    if ($documentsTv=$this->checkfieldtype($documentsTv)) {
                        $doc->Set($documentsTv, $sortids);
                    }
                    $doc->Save();
                }
            }
        }

        if ($modx->isFrontend())//klappt nicht im Backend
        {
            $this->emptyCache();
        }
        else {
            $this->emptyCache();
        //$modx->clearCache();//ob das hilft, wei� ich nicht
        }

        //
        return $last_docid;
    }


    function emptyCache() {
        global $modx;
/*	
    $basePath = $modx->config['base_path'];
    include_once $basePath."manager/processors/cache_sync.class.processor.php";
    $sync = new synccache();
    $sync->setCachepath($basePath."assets/cache/");
	$sync->setReport(false);
    $sync->emptyCache();	
*/
        // empty cache

        include_once($modx->config['base_path'] . 'manager/processors/cache_sync.class.processor.php');
        $sync = new synccache();
        $sync->setCachepath($modx->config['base_path'] .'assets/cache/');
        $sync->setReport(false);
        $sync->emptyCache(); // first empty the cache


    }

    //-------------------------------------------------------------------------------------------------


/*
function saveModifiedTvs(){
global $modx;	

if (is_array($_POST['container_key']) && count($_POST['container_key']) > 0)
{//print_r($_POST['container_key']);
$tvIds = array_flip($_SESSION['allChunkTvNames']);
$this->getNewMultiTvs($tvIds);
$tmplvars=array();    
	foreach ($_POST['container_key'] as $containerkey)
    {//echo $containerkey;
        if ($_POST['mChunk_oldsort_'.$containerkey] && count($_POST['mChunk_oldsort_'.$containerkey]) > 0)
        {
            foreach ($_POST['mChunk_oldsort_'.$containerkey] as $chunkkey)
            {
            	$chunk_Tvnames=$_SESSION['containerTVs'][$containerkey][$chunkkey];
				if (is_array($chunk_Tvnames)&&count($chunk_Tvnames)>0){
					foreach ($chunk_Tvnames as $tvid=>$tvName){
                     $tmplvars[]=$this->saveTv_prepare($tvName.'_'.$containerkey.'_'.$chunkkey,$tvName,$chunkkey);
					}
				}
            }
        }
    }

//return;	
$this->saveTvs($tmplvars);

}

return;
}
*/
    function saveTv_prepare($key,$tvName,$contentid,$prefix='tv') {
    //print_r($this->NewMultiTvs);

        global $modx;
        $value = '';
        $tv=$this->NewMultiTvs[$tvName];
        //print_r($tv);
        switch ($tv['type']) {
            case 'url':
                $value = $_POST[$prefix .$key];
                if ($_POST[$prefix .$key . '_prefix'] != '--') {
                    $value = str_replace(array (
                        "ftp://",
                        "http://"
                        ), "", $value);
                    $value = $_POST[$prefix .$key . '_prefix'] . $value;
                }
                break;
            case 'file':
            // Modified by Timon for use with resource browser
                $value = $_POST[$prefix .$key];
                break;
            default:
                if (is_array($_POST[$prefix .$key])) {
                // handles checkboxes & multiple selects elements
                    $feature_insert = array ();
                    $lst = $_POST[$prefix .$key];
                    while (list ($featureValue, $feature_item) = each($lst)) {
                        $feature_insert[count($feature_insert)] = $feature_item;
                    }
                    $value = implode("||", $feature_insert);
                } else {
                    $value = $_POST[$prefix .$key];
                }
                break;

        }
        //echo $_POST[$prefix .$key];
        // save value if it was modified
        //if (in_array($row['name'], $variablesmodified)) {
        if (strlen($value) > 0 && $value != $tv['default_text']) {
            $tmplvar = array (
                'value'=>$value,
                'tmplvarid'=>$tv['id'],
                'contentid'=>$contentid
            );
        } else {
        // Mark the variable for deletion
            $tmplvar = array (
                'tmplvarid'=>$tv['id'],
                'contentid'=>$contentid
            );
        }
        return $tmplvar;
    }
/*
function saveTvs($tmplvars)
{
    global $modx;
    $deletions = array ();
    $tvChanges = array ();
    foreach ($tmplvars as $field=>$value)
    {
        if (isset($value['value']) && ! empty($value))
        {
        	$tvChanges[] = $value;
        } else
        {
            if (! empty($value))
            {
                $deletions[] = $value;
            }
        }
    }

    if (! empty($deletions))
    {
    	if (count($deletions) > 0){
    		$del=array();
    		foreach ($deletions as $value){
    			$dels[$value['contentid']][$value['tmplvarid']]=$value['tmplvarid'];
    		}
			foreach($dels as $contentid=>$del){
			$sql = 'DELETE FROM '.$this->tbl_tvc.' WHERE tmplvarid IN ('.implode(',', $del).') AND contentid ='.$contentid;
			$rs = $modx->db->query($sql);   	
			}
		}
    }
        if (count($tvChanges)>0){
        foreach ($tvChanges as $key=>$value){
            $rs=$modx->db->select('id,value',$this->tbl_tvc,'tmplvarid="'.$value['tmplvarid'].'" AND contentid ='.$value['contentid']);
			if ($modx->db->getRecordCount( $rs )>0){
            	$rs=$modx->db->update($value,$this->tbl_tvc,'tmplvarid="'.$value['tmplvarid'].'" AND contentid ='.$value['contentid']);
            }else{
            	$rs=$modx->db->insert($value,$this->tbl_tvc);
            }
        }        	
        }

    return;
}
*/
}

class blox_Ditto {
    function blox_Ditto($allParameters,&$blox) {
        global $modx;
        $this->params           = array();
        $this->ditto_params     = array();
        $this->forceNoResults   = '0';
        unset($this->containerclass);
        $this->selectParameters($allParameters);
        $this->blox             = $blox;
        $this->showblock        = $this->params['showblock'];
        $this->containerparent  = $blox->docid;
        $this->containertype    = (isset($this->params['containertype']))?$this->params['containertype']:'blox_container';
        $this->attr_documentsTv ='';
        if (isset($this->params['filterByField']) && isset($this->params['filterValue'])) {
            $this->attr_filterByField=' filterByField="'.$this->params['filterByField'].'" ';
            $this->attr_filterValue=' filterValue="'.$this->params['filterValue'].'" ';
            $this->set('filter',$this->params['filterByField'].','.$this->params['filterValue'].',1');
			/*
			$extenders = isset($this->ditto_params['extenders'])?explode(',',$this->ditto_params['extenders']):array();
			$extenders[]='documentssort';
			$this->ditto_params['extenders']=implode(',',$extenders);	
			*/
        }
        if (isset($this->params['orderByField'])) {
            $this->attr_orderByField=' orderByField="'.$this->params['orderByField'].'" ';
            $this->set('orderBy',$this->params['orderByField'].' ASC');
			/*
			$extenders = isset($this->ditto_params['extenders'])?explode(',',$this->ditto_params['extenders']):array();
			$extenders[]='documentssort';
			$this->ditto_params['extenders']=implode(',',$extenders);	
			*/
        }

        if (isset($this->params['documentsTv'])) {
            $this->attr_documentsTv=' documentsTv="'.$this->params['documentsTv'].'" ';
            $tvoutput=$modx->getTemplateVarOutput($this->params['documentsTv']);
            $this->set('documents',$tvoutput[$this->params['documentsTv']]);
			/*
			$extenders = isset($this->ditto_params['extenders'])?explode(',',$this->ditto_params['extenders']):array();
			$extenders[]='documentssort';
			$this->ditto_params['extenders']=implode(',',$extenders);	
			*/
        }
        $this->checkContainerType();
    }

    function selectParameters($allParameters) {

        $bloxParameters = array('sortable','showblock','docid','containertype','removeable','fillable','htmlouter','saveable','documentsTv','orderByField','filterByField','filterValue','theadTpl');
        foreach ($allParameters[1] as $param=>$value) {
            if (in_array($param,$bloxParameters) && substr($param,-3) != 'tpl') {
                $this->params[$param] = $value;
            }else {
                $this->ditto_params[$param] = $value;
            }
        }
    }


    function checkContainerType() {
    //print_r($this->params);

        if($this->params['containertype']=='xcc_container') {
            $this->containerclass='xcc_bloxcontainer';
            $this->params['saveable']='0';
            $this->params['removeable']='0';
            $this->params['fillable']='0';
        };

        $parent = explode(',', $this->ditto_params['parents']);
        if ( isset ($this->ditto_params['parents'])) {
            if ( count($parent) == 1) {
                $this->containerparent = $parent[0];
                $this->containertype = 'parent_container';
            }
            else {
                $this->containertype = 'container';
                $this->containerparent = 'unknown';
            }
        }

        if (isset($this->ditto_params['documents'])) {
            $this->containertype = 'container';
            $this->containerparent = 'unknown';
        }
    }

    function set($param, $value) {
        $this->ditto_params[$param] = $value;
    }

    function run($output_only='0') {
        global $modx;
        $rmclass=($this->params['removeable']=='0')?'unremoveable':'';
        $fillclass=($this->params['fillable']=='0')?'fillable_0':'fillable_1';
        $savable=($this->params['saveable']=='0')?'saveable="0"':'';
        $thead='{{'.$this->params['theadTpl'].'}}';
        $pr_tbody='';
        $su_tbody='';
        $pr_td='';
        $su_td='';
        if ($this->params['htmlouter']=='table') {
            $pr_tbody='>'.$thead.'<tbody';
            $su_tbody='</tbody>';
            $pr_td='<td>';
            $su_td='</td>';
        }

        $filterby=(isset($this->params['bloXfilterBy']))?' filterby="'.$this->params['bloXfilterBy'].'" ':'';
        $containerclass=(isset($this->containerclass))?$this->containerclass:'bloxcontainer';
        if ($this->forceNoResults !== '1') {
            $ditto_output = $modx->runSnippet('Ditto', $this->ditto_params);
        }else {
            $ditto_output = $this->ditto_params['noResults'];
        }
        $blox_dummy = '<'.$this->htmlinner.' class="bildchunk blox clearfix bloxdummy hidden" resourceclass="modDummy" rowid="dummy" >
                      '.$pr_td.'Dummy'.$su_td.'
                      </'.$this->htmlinner.'>';
        $output = '<'.$this->params['htmlouter'].$pr_tbody.' id="'.$this->showblock.'" class="'.$containerclass.' '.$rmclass.' '.' '.$fillclass.' '.$saveclass.'" c_type="'.$this->containertype.'" c_parentid="'.$this->containerparent.'" sender_id="'.$modx->documentIdentifier.'" '.$savable.$this->attr_documentsTv.$this->attr_orderByField.$this->attr_filterByField.$this->attr_filterValue.' >';
        $output .= $blox_dummy.$ditto_output;
        $output .= $su_tbody.'</'.$this->params['htmlouter'].'>';

        return ($output_only=='0')?$output:$ditto_output;
    }
}

class xedit_db {
    function xedit_db( & $blox) {
        $this->blox = $blox;
    }

    function process($chunk, $container) {
        global $modx;
        $GLOBALS['savedoc'] = true;
        $this->invokearray = array ('mode'=>'new');
        $this->invokemode = 'new';
        $this->last_docid = null;
        $this->doc = false;
        $this->checkfordoc($chunk, $container);
        $this->tablerows = array ();
        $this->processChunk($chunk, $container);
        $this->save($chunk);

    }

    function checkfordoc($chunk, $container) {
        global $modx;
        
		if ($container['c_resourceclass']=='modDocument'){
		if ( isset ($chunk['rowid']) && $chunk['rowid'] !== 'new' && $chunk['savemode'] == 'copy' && ! isset ($GLOBALS['documentsTv'])) {
            $this->doc = new Document($chunk['rowid']);
            if ($this->doc->isNew !== '1') {
                $this->doc->Duplicate();
                $chunk['rowid']='new';
            }

        } else {
            $this->doc = new Document($chunk['rowid'], 'id');//hier $chunk['rowid']
            if ($chunk['rowid'] !== 'new' && $this->doc->isNew !== '1') {
                $this->invokemode = 'upd';
                $this->invokearray = array ('mode'=>'upd', 'id'=>$chunk['rowid']);
            }
        }
        //dont save new documents if rowid isnot 'new'
        if ($this->doc->isNew == '1' && $chunk['rowid'] !== 'new') {
            $GLOBALS['savedoc'] = false;
        }			
		}


    }

    function processChunk($chunk,$container) {
        global $modx;
        //in config einstellbar machen!!

        $modx->invokeEvent('OnBeforeDocFormSave', $this->invokearray);

        if ($container['c_resourceclass']=='modDocument'){
        if ( isset ($GLOBALS['containerID'])) {
            $this->doc->Set('parent', $GLOBALS['containerID']);
        }
        //$doc->Set('pagetitle', $chunk['fields']['pagetitle']);
        if ($GLOBALS['resort'] == '1') {
            if ($GLOBALS['orderByField'] = $this->checkfieldtype($GLOBALS['orderByField'])) {
                $this->doc->Set($GLOBALS['orderByField'], $GLOBALS['sort_index']);
            }
        }        	
        }

        if ($container['c_resourceclass']=='modTable'){
        if ($GLOBALS['resort'] == '1'&& $container['orderByField']!=='0') {
           
           $this->tablerows[$container['c_tablename']][$chunk['rowid']][$container['orderByField']]=$GLOBALS['sort_index'];
        }        	
        }

        if ($chunk['modified'] !== 'no') {

            if ($container['c_resourceclass']=='modDocument'){//Todo: sowas auch für modTable einbauen 
            /*
			if ( isset ($chunk['chunkname'])) {
                $this->doc->Set('tvchunkname', $chunk['chunkname']);
            }
            */
            //echo $chunk['savemode'];
            if ($chunk['rowid'] == 'new') {
                $this->doc->Set('template', $parentTemplate);//Todo:--------
            }
            if ( isset ($container['filterByField']) && $container['filterByField'] !== '0' && isset ($container['filterValue']) && $container['filterValue'] !== '0') {
                if ($filterByField = $this->checkfieldtype($container['filterByField'])) {
                    $this->doc->Set($filterByField, $container['filterValue']);
                }
            }
            if ( isset ($chunk['published']) && (($chunk['published'] == '1') || $chunk['published'] == '0')) {
                $this->doc->Set('published', $chunk['published']);
            }
            if ( isset ($chunk['deleted']) && (($chunk['deleted'] == '1') || $chunk['deleted'] == '0')) {
                $this->doc->Set('deleted', $chunk['deleted']);
            }			
			}
			
            if (count($chunk['fields']) > 0) {
                foreach ($chunk['fields'] as $field) {
                    $this->setfield($field);
                }
            }

        }
    }

    function setfield($field) {   global $modx;

        if ( isset ($_POST[$field['postname']])) {
            $field['value'] = $_POST[$field['postname']];
        }
        //convert images from directresize
        // Todo find a way to process array-fields (checkbox etc.)		

        $field['value'] = $this->ConvertFromBackend($field['value']);
        $field['value'] = $modx->db->escape($field['value']);
        switch ($field['resourceClass']) {
            case 'modTable':
                if ( isset ($field['tablename']) && ! empty($field['tablename'])) {
                    //$tablerow[$field['tablename']][$field['rowid']][$field['fieldname']]=$field['value'];
                    //$this->tablerows[]=$tablerow;
					$this->tablerows[$field['tablename']][$field['rowid']][$field['fieldname']]=$field['value'];
                }
                break;
            default:
                if ($fieldname = $this->checkfieldtype($field['fieldname'])) {
                    $this->doc->Set($fieldname, $field['value']);
                }
                break;
        }

    }



    function replaceDR($matches) {
        return str_replace($matches[2], $matches[4], $matches[0]);
    }


    function ConvertFromBackend($content) {
        global $modx;

        //hier mal hardcoded das image_processor_zeugs aus directresize
        //blick ich noch nich wie das anders zu lösen ist.
        $this->drglobal["image_processor"] = "image.php";
        define(DR_IMAGE_PROCESSOR, $modx->config["site_url"].$this->drglobal["image_processor"]);

        $s = array ("/", ".");
        $r = array ("\\/", "\.");
        $url = str_replace($s, $r, DR_IMAGE_PROCESSOR);
        //echo $url.'</br>';

        $pattern = "<img[^>]*src=(['\"])(({$url}).*?src=([^&]*?)&.*?)\\1[^>]*>";
        $pattern = "/{$pattern}/si";

        //echo $pattern.' --  '.$content;

        //preg_match($pattern, $content, $treffer);
        //print_r($treffer);
        /*
         $content =  preg_replace_callback($pattern,
         create_function(
         '$matches',
         'return str_replace($matches[2], str_replace("tn_","",$matches[4]), $matches[0]);'
         ),
         $content);
         */
        $content = preg_replace_callback($pattern,
            create_function(
            '$matches',
            'return str_replace($matches[2], $matches[4], $matches[0]);'
            ),
            $content);

        $content = preg_replace("/\s*drcss_\w+\s*/", "", $content);

        return $content;
    }

    function checkfieldtype($fieldname) {
        if (in_array($fieldname, $this->blox->tvnames)) {
            return 'tv'.$fieldname;
        }
        elseif (in_array($fieldname, $this->blox->docColumnNames)) {
            return $fieldname;
        }

        return false;
    }

    function save($chunk) {
        if ($this->doc) {
            $this->savedoc($chunk);
        }
        if (count($this->tablerows)>0) {
            $this->saveTableRows($chunk);
        }
		$GLOBALS['sort_index']++;
    }
    function savedoc($chunk) {
        global $modx;
        if ( isset ($GLOBALS['documentsTv']) && $chunk['rowid'] == 'new') {
        //dont save new documents in referenz-containers???
        //or define an folder where to save???
        } elseif ($GLOBALS['savedoc']) {
			
            $this->doc->Save();
            //$GLOBALS['sort_index']++;
            $this->last_docid = $this->doc->Get('id');
            $modx->invokeEvent('OnDocFormSave', array ('mode'=>$this->invokemode, 'id'=>$this->last_docid));
            if ( isset ($GLOBALS['documentsTv'])) {
                $GLOBALS['sortids'][] = $chunk['rowid'];
            }
        }
    }
    function saveTableRows($chunk)
    {
        global $modx;
    
        if (count($this->tablerows) > 0)
        {
            foreach ($this->tablerows as $tablename=>$tablerows)
            {
                if (count($tablerows) > 0)
                {
                    foreach ($tablerows as $key=>$fields)
                    {
                        //print_r($tablerows);
                        if ($key == 'new')
                        {
                            $modx->db->insert($fields, $modx->getFullTableName($tablename));
                        } else
                        {
                            $modx->db->update($fields, $modx->getFullTableName($tablename), "id=$key");
                        }
                    }
                }
            }
    
    
        }
    
    }
    
    
    }





