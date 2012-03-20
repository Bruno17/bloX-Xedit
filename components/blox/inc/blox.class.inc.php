<?php
class blox {
// Declaring private variables 
    var $bloxconfig;
    var $bloxtpl;

    //
    // Constructor class
    //
    function blox($bloxconfig,$bloxtpl) {
        global $modx;
        $this->bloxID = $bloxconfig['id'];
        $this->bloxconfig = $bloxconfig;
		$this->bloxconfig['prefilter']='';
        $this->columnNames = array();
		$this->tvnames          = array();
        $this->docColumnNames   = array();
        $this->tvids            = array();
        $this->checkfilter();
        $this->bloxconfig['parents'] = $this->cleanIDs($bloxconfig['parents']);
		$this->bloxconfig['IDs'] = $this->cleanIDs($bloxconfig['IDs']);
		
		$this->xcc_button = array();
		$this->containerclassnames['containerclass']='bloxcontainer';
		$this->containerclassnames['rmclass']='';
		$this->containerclassnames['fillclass']='fillable_'.$bloxconfig['fillable'];
		$this->containerclassnames['saveclass']='';

        $this->checkparents();
		$this->checkIDs();		
		$this->checkContainerType();
		$this->checkRequiredFields();
		//bloxcontainer 
	 
        $this->containerattributes=$this->addBloxAttributes('id,xedit_tabs,tablename,resourceclass,saveable,c_type,c_parentid',$attributes=array());
        $this->containerattributes = $this->addAttribute('sender_id',$modx->resource->get('id'),$this->containerattributes);
        if ($this->bloxconfig['c_type'] == 'xcc_container') {
        	$this->containerattributes = $this->addBloxAttributes('container',$this->containerattributes);
        }
        if (!empty($this->bloxconfig['documentsTv']) ) {
        	$this->containerclassnames['ref_container']='ref_container';
        	$this->containerattributes = $this->addBloxAttributes('documentsTv',$this->containerattributes);
            $this->bloxconfig['removebtn']='1';
		}
        if (!empty($this->bloxconfig['orderByField']) ) {
        	$this->containerattributes = $this->addBloxAttributes('orderByField',$this->containerattributes);
		}						
		//$this->generateContainerAttributes();
        $this->tpls = array();
        //$GLOBALS['xetconfig']=$xetconfig;
        //$this->bloxtpl = array();
		$this->checktpls();
		
        $this->renderdepth=0;
        $this->eventscount = array();
        //$this->innerdatas = array();
        $this->output='';
        $this->date = xetadodb_mktime(0, 0, 0, $this->bloxconfig['month'], $this->bloxconfig['day'], $this->bloxconfig['year']);

        if (!class_exists("xetCache")) {
        	include_once(strtr(realpath(dirname(__FILE__))."/cache.class.inc.php", '\\', '/'));
            $this->cache = new xetCache($bloxconfig);
		}

        if (class_exists('xetCache')) {
            
        } else {
        //$output =  'xettcal class not found';
        //return;
        }

        include_once(strtr(realpath(dirname(__FILE__))."/formElements.class.inc.php", '\\', '/'));
        $this->fe= new bloxFormElements();
    }

  function checktpls(){
  	//example: &tpls=`bloxouter:myouter||row:contentonly`
	
	$this->tpls['bloxouter']= "@FILE:".$this->bloxconfig['tplpath']."/bloxouterTpl.html"; // [ path | chunkname | text ]
    if ($this->bloxconfig['tpls'] !== ''){
    	$tpls=explode('||',$this->bloxconfig['tpls']);
		foreach ($tpls as $tpl){
			//$tpl=explode(':',$tpl);
			//$this->tpls[$tpl[0]]=$tpl[1];
			$this->tpls[substr($tpl,0,strpos($tpl,':'))]=substr($tpl,strpos($tpl,':')+1);
			//Todo: check if chunk exists
		}
    }
  
  }


   function checkfilter(){
		
		if ($this->bloxconfig['resourceclass']=='modDocument'){
		if ($this->bloxconfig['showdeleted']=='0' ||$this->bloxconfig['showdeleted']=='0'){
            $filter = 'deleted|'.$this->bloxconfig['showdeleted'].'|=';				
			$this->bloxconfig['prefilter'] = ! empty($this->bloxconfig['prefilter'])?$filter.'++'.$this->bloxconfig['prefilter']:$filter;	
		}

		if ($this->bloxconfig['showunpublished']=='0' ||$this->bloxconfig['showunpublished']=='2'){
            
			$filter = 'published|'.(($this->bloxconfig['showunpublished']=='0')?'1':'0').'|=';				
			$this->bloxconfig['prefilter'] = ! empty($this->bloxconfig['prefilter'])?$filter.'++'.$this->bloxconfig['prefilter']:$filter;	
		}					
		}
   }

    function checkparents()
    
    {
        
		global $modx;
        if (! empty($this->bloxconfig['IDs']) || $this->bloxconfig['resourceclass'] !== 'modDocument')
        {
            return;
        }
		
        $this->bloxconfig['parents'] = $this->bloxconfig['parents'] !== ''?$this->bloxconfig['parents']:$modx->resource->get('id');
		
		$parents = explode(',', $this->bloxconfig['parents']);
        $depth = $this->bloxconfig['depth'];   

        if ($this->bloxconfig['bloxfolder'] !== ''){
        	$depth='1';
			$parents=$this->getBloxfolder($parents);
        }

        $parents = $this->getChildParents($parents, $depth);
		
		$parents = (count($parents) > 0)?$parents:array('9999999');
		

            //$filter = 'id|'.implode(',', $ids).'|IN';
			$filter = 'parent|'.implode(',', $parents).'|IN';
            $this->bloxconfig['prefilter'] = ! empty($this->bloxconfig['prefilter'])?$filter.'++'.$this->bloxconfig['prefilter']:$filter;
    }

   function getBloxfolder($parents){
       global $modx;
	   $parent = $parents[0];	
	   $tablename = $modx->getFullTablename(site_content);
	   $rs = $modx->db->select('id',$tablename,"parent=$parent and pagetitle='".$this->bloxconfig['bloxfolder']."'");
	   $count = $modx->db->getRecordCount($rs);
	   $row = $modx->db->getRow($rs);
	    
	   return $row = ($count>0)?array($row['id']):array('99999999');
	   
   }
	
    function checkIDs()
    
    {
        global $modx;
        if (! empty($this->bloxconfig['IDs']))
        {
        	$ids = $this->bloxconfig['IDs'];
            $filter = $this->bloxconfig['keyField'].'|'.$ids.'|IN';
            $this->bloxconfig['prefilter'] = ! empty($this->bloxconfig['prefilter'])?$filter.'++'.$this->bloxconfig['prefilter']:$filter;
        }
    
    }

    function checkRequiredFields()
    {
        if (count($this->bloxconfig['requiredFields']) > 0)
        {
            foreach ($this->bloxconfig['requiredFields'] as $field)
            {
                $this->bloxconfig['requiredFields'][$field] = $field;
            }
        }
		if ($this->bloxconfig['fields'] !== '*'){
        $fields=explode(',',$this->bloxconfig['fields']);
		if (count($fields) > 0)
        {
            foreach ($fields as $field)
            {
                $this->bloxconfig['requiredFields'][$field] = $field;
            }
        }				
		}
	
        $fieldarray = array ('keyField', 'chunknameField', 'captionField','orderByField','filterByField');
        foreach ($fieldarray as $field)
        {
            if ($this->bloxconfig[$field] !== ''){
            	$this->bloxconfig['requiredFields'][$this->bloxconfig[$field]] = $this->bloxconfig[$field];
            }
			
        }
		if ($this->bloxconfig['resourceclass']=='modDocument'){
			$this->bloxconfig['requiredFields']['published'] = 'published';
		}
    
    }

    function checkContainerType()
    {
    

        if ($this->bloxconfig['c_type'] == 'xcc_container')
        {
            $this->containerclass = 'xcc_bloxcontainer';
            $this->bloxconfig['saveable'] = '0';
            $this->bloxconfig['removeable'] = '0';
            $this->bloxconfig['fillable'] = '0';
			$this->bloxconfig['removebtn']='1';
			//Todo: remove this??:
            $this->xcc_button['top'] = '<div class="xcc_button">';
            $this->xcc_button['caption'] = '[+'.$this->bloxconfig['captionField'].'+]';
            $this->xcc_button['xtools'] = '<div style="opacity: 0; visibility: hidden; width:85px;" class="xtools">
                                               <span class="drag">drag</span>
                                               </div>';
 		    $this->xcc_button['xtools'] = '';
            $this->xcc_button['bottom'] = '</div>';
			//
            $this->containerclassnames['containerclass'] = 'xcc_container';
            $this->containerclassnames['rmclass'] = 'unremoveable';
            $this->containerclassnames['fillclass'] = 'fillable_0';
            $this->containerclassnames['saveclass'] = '';
			$this->bloxconfig['savemode'] = 'copy';
			
            return;
        }
        ;
    
    
    
    
        if ($this->bloxconfig['resourceclass'] == 'modDocument')
        {
            $parent = explode(',', $this->bloxconfig['parents']);
            if (! empty($this->bloxconfig['parents']))
            {
    
                if (count($parent) == 1)
                {
                    $this->bloxconfig['c_parentid'] = $parent[0];
                    if ($this->bloxconfig['bloxfolder'] == '')
                    {
                        $this->bloxconfig['c_type'] = 'parent_container';
                    }
    
                }
                else
                {
                    $this->bloxconfig['c_type'] = 'container';
                    $this->bloxconfig['c_parentid'] = 'unknown';
                }
            }
    
            if (! empty($this->bloxconfig['documents']))
            {
                $this->bloxconfig['c_type'] = 'container';
                $this->bloxconfig['c_parentid'] = 'unknown';
            }
        }
    }

    function getTvNames($template = 'all') {
        global $modx;
        $t_tv = $modx->getFullTableName('site_tmplvars');
        if ($template !== 'all') {
            $table2 = $modx->getFullTableName('site_tmplvar_templates').' stt ';
            $tablenames = $t_tv.','.$table2;
            $query = 'SELECT id,name FROM '.$tablenames.'WHERE templateid='.$template.' and stt.tmplvarid=st.id';
            $result = $modx->db->query($query);
        }
        else {
            $result = $modx->db->select('*', $t_tv, '');
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
        $fields = $modx->getFields('modResource');
        $this->docColumnNames=array();
        foreach ($fields as $key=>$field) {
            $this->docColumnNames[] = $key;
        }
        return;
    }

    function addAttribute($key,$value,$attributes=array()) {

        $attributes[$key]=$key.'="'.$value.'"';
        return $attributes;

    }

    function addBloxAttributes($keys,$attributes=array()) {
        $keys = explode(',', $keys);
        if (count($keys) > 0) {
            foreach ($keys as $key) {
                if ($this->bloxconfig[$key] !== '') {
                    $attributes = $this->addAttribute($key,$this->bloxconfig[$key],$attributes);
                }

            }
        }
        return $attributes;
    }
    //////////////////////////////////////////////////
    //Display bloX
    /////////////////////////////////////////////////

    function displayblox() {

        $datas = $this->getdatas($this->date,$this->bloxconfig['includesfile']);
        return $this->displaydatas($datas);

    }

    //////////////////////////////////////////////////
    //displaydatas (bloxouterTpl)
    /////////////////////////////////////////////////

    function displaydatas($outerdata = array ()) {
        global $modx;

//$outerdata['innerrows']['row']='innerrows.row';
        $start=time();
        $this->regSnippetScriptsAndCSS();
        $cache = $outerdata['cacheaction'];
        $cachename = $outerdata['cachename'];
        if ($cache == '2') {
            return $outerdata['cacheoutput'];
        }

        
        $bloxouterTplData = array ();
        $bloxinnerrows = array ();
		$bloxinnercounts = array ();

        if ($GLOBALS['xedit_runs'] == '1'){
        	$outerdata['innerrows']['bloxdummy'][]=array('caption'=>'Dummy');	
        }

        $innerrows = $outerdata['innerrows'];
		unset($outerdata['innerrows']);

        if (count($innerrows) > 0) {
            foreach ($innerrows as $key=>$row) {
 				$daten = '';
				$innertpl='';
				if (isset($this->tpls[$key])){
					$innertpl=$this->tpls[$key];
				}
				else{
                $tplfile = $this->bloxconfig['tplpath'] . "/" . $key . "Tpl.html";
                if (file_exists($modx->config['base_path'].$tplfile)) {
                    $innertpl = "@FILE:" . $tplfile;
                }					
				}
				
				if ($innertpl !== ''){
                    $data=$this->renderdatarows($row,$innertpl,$key,$outerdata);
					$bloxinnerrows[$key] = $data;
					$bloxinnercounts[$key] = count($row);
				}

            }
        }
        $outerdata['innerrows']=$bloxinnerrows;
		$outerdata['innercounts']=$bloxinnercounts;
        $bloxouterTplData['containerattributes'] = implode(' ',$this->containerattributes);

/*
        $key='message';
        $tplfile = $this->bloxconfig['tplpath'] . "/" . $key . "Tpl.html";
        $innertpl=(file_exists($tplfile))?$innertpl="@FILE:".$tplfile:'';
        $row=$this->messages;
        $massagedata=$this->renderdatarows($row,$innertpl,$key);
        $bloxouterTplData['messages'] = $massagedata;
*/
        $bloxouterTplData['row'] = $outerdata;
        $bloxouterTplData['config'] = $this->bloxconfig;		
		$bloxouterTplData['containerclassnames'] = implode (' ',$this->containerclassnames);
        $outerdata['blox']=$bloxouterTplData;

        $tpl = new xettChunkie($this->tpls['bloxouter']);
		$tpl->placeholders=$outerdata;
        $daten = $tpl->Render();
		unset ($tpl);
        if ($cache == '1') {
            $this->cache->writeCache($cachename, $daten);
        }
        $end=time();
		//echo ($end-$start);       
        return $daten;
    }
    //////////////////////////////////////////////////
    //renderdatarows
    /////////////////////////////////////////////////
    function renderdatarows($rows, $tpl, $rowkey='',$outerdata=array()) {
        //$this->renderdepth++;//Todo
        $output = '';
        if (is_array($rows)) {
		$iteration = 0;
		$rowscount = count($rows);
            foreach ($rows as $row) {
            	$iteration++;
                $out=$this->renderdatarow($row, $tpl,$rowkey,$outerdata,$rowscount,$iteration);
				$output .=$out; 

            }
        }
        return $output;
    }

    //////////////////////////////////////////////////
    //renderdatarow and custom-innerrows (bloxouterTpl)
    /////////////////////////////////////////////////
    function renderdatarow($row, $rowTpl = 'default',$rowkey='',$outerdata=array(),$rowscount,$iteration) {
        global $modx;

        $date = $this->date;

        if ( isset ($row['tpl'])) {
            $tplfilename = $this->bloxconfig['tplpath']."/".$row['tpl'];
            if (($row['tpl'] !== '') && (file_exists($tplfilename))) {
                $rowTpl = "@FILE:".$tplfilename;
            }
        }

		if (substr($rowTpl,0,7) == '@FIELD:'){
            $rowTpl=($row[substr($rowTpl,7)]);
		}						

        $datarowTplData = array ();
        $bloxinnerrows = array ();
		$bloxinnercounts = array ();
        $innerrows = $row['innerrows'];
		unset($row['innerrows']);

        
        if ((is_array($innerrows)) && (count($innerrows) > 0)) {
            foreach ($innerrows as $key=>$innerrow) {
 				$daten = '';
				$innertpl='';
				if (isset($this->tpls[$key])){
					$innertpl=$this->tpls[$key];
				}
				else{
                $tplfile = $this->bloxconfig['tplpath'] . "/" . $key . "Tpl.html";
                if (file_exists($modx->config['base_path'].$tplfile)) {
                    $innertpl = "@FILE:" . $tplfile;
                }					
				}
				if (isset($this->templates[$innertpl])||$innertpl !== ''){
                    $data = $this->renderdatarows($innerrow, $innertpl, $key, $row);
                    $datarowTplData['innerrows'][$key] = $data;
                    $bloxinnerrows[$key] = $data;
					$bloxinnercounts[$key] = count($innerrow);
				}

            }
        }
 
		if(count($bloxinnerrows)>0){
		$row['innerrows']=$bloxinnerrows;
		$row['innercounts']=$bloxinnercounts;			
		}

        if (count($row)>0) {
            foreach($row as $field=>$value) {
                if (!is_array($value)) {
				
					$outputvalue=$value;

                    if ($this->bloxconfig['processTVs'] == '1' && $this->bloxconfig['resourceclass'] == 'modDocument' && in_array($field, $this->tvnames))
                    
                    {
                        //$outputvalue = $modx->getTemplateVarOutput($field, $row['id']);
                        //$outputvalue = $outputvalue[$field];
                        //$resource = $modx->getObject('modResource', $row['id']);
                        //$outputvalue = $resource->getTVValue($field);

                        $tv = $modx->getObject('modTemplateVar', array ('name'=>$field));
                        $outputvalue = $tv->renderOutput($row['id']);
                    
                    }
					
					//$outputvalue=$value;
					
                    //$tpl->addVar($field,$outputvalue);
					$row[$field]=$outputvalue;
                    $fieldname=$field;
                    if ($rowkey=='rowvalue') {
                        $fieldname=$row['fieldname'];
                    //$value=$row['value'];
                    }
                    $tag='##';
                    //$tpl->addVar($tag.$field,$this->generateDivForXedit($row,$fieldname,$value,$tag,$bloxattributes));
                    $row[$tag.$field]=$this->generateDivForXedit($row,$fieldname,$value,$tag,$bloxattributes);
					$tag='#';
                    //$tpl->addVar($tag.$field,$this->generateDivForXedit($row,$fieldname,$outputvalue,$tag,$bloxattributes));
                    $row[$tag.$field]=$this->generateDivForXedit($row,$fieldname,$value,$tag,$bloxattributes);
				}
				else {
					$row[$field]=$value;
				}
            }
        }

        $datarowTplData['parent'] = $outerdata;
        $datarowTplData['event'] = $row;
        $datarowTplData['date'] = $date;
        $datarowTplData['row'] = $row;
		$datarowTplData['rowscount'] = $rowscount;
		$datarowTplData['iteration'] = $iteration;		
		$datarowTplData['xcc_button'] = $this->xcc_button;
        $datarowTplData['config'] = $this->bloxconfig;
        $datarowTplData['userID'] = $this->bloxconfig['userID'];
        //$tpl->addVar('blox', $datarowTplData);
		$row['blox']=$datarowTplData;
		//echo '<br/>'.$rowkey.':'.$row['rowvalue'].'---------------<br/>';
		//print_r($row);		
        //$tpl = new xettChunkie($rowTpl,& $this->templates);
		$tpl = new xettChunkie($rowTpl);		
        $tpl->placeholders=$row;
		$output = $tpl->Render();
		
		if ($rowkey=='row'||$rowkey=='fieldnames'||($rowkey=='rowvalue'&&$row['value']=='test')){

            //echo '<br/>'.$rowkey.':'.$row['rowvalue'].'---------------<br/>';
			//echo $output = $tpl->Render();
			
		}
		unset($tpl,$row);
		
		return $output;
    }




    //////////////////////////////////////////////////
    //renderdatarow and custom-innerrows (bloxouterTpl)
    /////////////////////////////////////////////////
    function renderdatarow2($row, $rowTpl = 'default',$rowkey='',$outerdata=array(),$rowscount,$iteration) {
        global $modx;
		
		$cache = $row['cacheaction'];
        $cachename = $row['cachename'];
        if ($cache == '2') {
            return $row['cacheoutput'];
        }
        $date = $this->date;
        //echo $rowkey;
        //$innerdatas = $this->innerdatas;
        //$rowTpl = ($rowTpl == 'default')?$this->bloxtpl['datarowTpl']:$rowTpl;
		//Todo: add functionality for chunk,file,code
        if ( isset ($row['tpl'])) {
            $tplfilename = $this->bloxconfig['tplpath']."/".$row['tpl'];
            if (($row['tpl'] !== '') && (file_exists($tplfilename))) {
                $rowTpl = "@FILE:".$tplfilename;
            }
        }

        //print_r($row);
        //echo $this->bloxconfig['includespath'] . "/" . $rowkey . "Getdatas.php <br/>";
        if ($row['getDatasOnRender']=='1') {
        $this->bloxconfig['includespath'] . "/" . $rowkey . "Getdatas.php";
            $getdatas = $this->getdatas($date,$this->bloxconfig['includespath'] . "/" . $rowkey . "Getdatas.php",$row);
            $row=array_merge($row,$getdatas);
        }

		if (substr($rowTpl,0,7) == '@FIELD:'){
            $rowTpl=($row[substr($rowTpl,7)]);
		}						
		
        //$tpl = new xettChunkie($rowTpl,& $this->templates);
		//$tpl = new xettChunkie($rowTpl);
        $datarowTplData = array ();
        $bloxinnerrows = array ();
		$bloxinnercounts = array ();
        $innerrows = $row['innerrows'];
		unset($row['innerrows']);
/*
        if ((is_array($innerrows)) && (count($innerrows) > 0)) {
            foreach ($innerrows as $key=>$innerrow) {
                $tplfile = $this->bloxconfig['tplpath']."/".$key."Tpl.html";
                $innertpl = "@FILE:".$tplfile;
                
                if (isset ($this->templates[$innertpl])||file_exists($modx->config['base_path'].$tplfile)) {
                    $data = $this->renderdatarows($innerrow, $innertpl, $key, $row);
                    $datarowTplData['innerrows'][$key] = $data;
                    $bloxinnerrows[$key] = $data;
                }
            }
        }
*/
        
        if ((is_array($innerrows)) && (count($innerrows) > 0)) {
            foreach ($innerrows as $key=>$innerrow) {
 				$daten = '';
				$innertpl='';
				if (isset($this->tpls[$key])){
					$innertpl=$this->tpls[$key];
				}
				else{
                $tplfile = $this->bloxconfig['tplpath'] . "/" . $key . "Tpl.html";
                if (file_exists($modx->config['base_path'].$tplfile)) {
                    $innertpl = "@FILE:" . $tplfile;
                }					
				}
				if (isset($this->templates[$innertpl])||$innertpl !== ''){
                    $data = $this->renderdatarows($innerrow, $innertpl, $key, $row);
                    $datarowTplData['innerrows'][$key] = $data;
                    $bloxinnerrows[$key] = $data;
					$bloxinnercounts[$key] = count($innerrow);
				}

            }
        }
        
		
		$row['innerrows']=$bloxinnerrows;
		$row['innercounts']=$bloxinnercounts;

        if ($GLOBALS['xedit_runs'] == '1') {
            $bloxattributes = $this->addBloxAttributes('xedit_tabs,tablename,resourceclass,savemode');
            //$bloxattributes = $this->addAttribute('chunkname',$row[$this->bloxconfig['chunknameField']],$bloxattributes);
			$bloxattributes = $this->addAttribute('chunkname',$rowTpl,$bloxattributes);
            $bloxattributes = $this->addAttribute('rowid',$row[$this->bloxconfig['keyField']],$bloxattributes);
			$bloxattributes = $this->addAttribute('published',$row['published'],$bloxattributes);
            $dragbtn = $this->bloxconfig['dragbtn']!=='0'?'<span class="drag">drag</span>':'';
			$trashbtn = $this->bloxconfig['trashbtn']!=='0'?'<span class="xtrash">trash</span>':'';
			$savebtn = $this->bloxconfig['savebtn']!=='0'?'<span class="save">save</span>':'';
			$removebtn = $this->bloxconfig['removebtn']!=='0'?'<span class="remove">remove</span>	':'';
			
			
			$xtools='
        <div class="xtools" style="opacity: 0; visibility: hidden;width:85px;">
        '.$dragbtn.$trashbtn.$savebtn.$removebtn.'
        </div>		
    	';

            $datarowTplData['bloxattributes'] = implode(' ',$bloxattributes);
            $datarowTplData['xtools'] = $xtools;
        }
		
        if (count($row)>0) {
            foreach($row as $field=>$value) {
                if (!is_array($value)) {
                	
					$outputvalue=$value;
					
					if ($this->bloxconfig['resourceclass']=='modDocument' && in_array($field,$this->tvnames)){
						
						$outputvalue=$modx->getTemplateVarOutput($field,$row['id']);
						$outputvalue=$outputvalue[$field];
					}
					
                    //$tpl->addVar($field,$outputvalue);
					$row[$field]=$outputvalue;
                    $fieldname=$field;
                    if ($rowkey=='rowvalue') {
                        $fieldname=$row['fieldname'];
                    //$value=$row['value'];
                    }
                    $tag='##';
                    //$tpl->addVar($tag.$field,$this->generateDivForXedit($row,$fieldname,$value,$tag,$bloxattributes));
                    $row[$tag.$field]=$this->generateDivForXedit($row,$fieldname,$value,$tag,$bloxattributes);
					$tag='#';
                    //$tpl->addVar($tag.$field,$this->generateDivForXedit($row,$fieldname,$outputvalue,$tag,$bloxattributes));
                    $row[$tag.$field]=$this->generateDivForXedit($row,$fieldname,$value,$tag,$bloxattributes);
				}
				else {
					$row[$field]=$value;
				}
            }
        }

        $datarowTplData['parent'] = $outerdata;
        $datarowTplData['event'] = $row;
        $datarowTplData['date'] = $date;
        $datarowTplData['row'] = $row;
		$datarowTplData['rowscount'] = $rowscount;
		$datarowTplData['iteration'] = $iteration;		
		$datarowTplData['xcc_button'] = $this->xcc_button;
        $datarowTplData['config'] = $this->bloxconfig;
        $datarowTplData['userID'] = $this->bloxconfig['userID'];
        //$tpl->addVar('blox', $datarowTplData);
		$row['blox']=$datarowTplData;
		//print_r($tpl);
        //$tpl = new xettChunkie($rowTpl,& $this->templates);
		$tpl = new xettChunkie($rowTpl);		
        $tpl->placeholders=$row;
		$output = $tpl->Render();
		unset($tpl);
        if ($cache == '1') {
            $this->cache->writeCache($cachename, $output);
        }
        if ($row['makexccbutton'] == '1')
        {
            /*
			$dragtools='
                    <div style="opacity: 0; visibility: hidden; width:85px;" class="xtools">
                        <span class="drag">drag</span>
                    </div>
                	';
            */    	
            $output = '
                	<div class="xcc_button">
                	'.$row[$this->bloxconfig['captionField']].$dragtools.$output.'
                	</div>
                	';
            //$this->xcc_button['caption'] = '[+'.$this->bloxconfig['captionField'].'+]';
            //echo $row[$this->bloxconfig['captionField']] ;
            //echo 'haaaaaaaaaaaaaallo';
            /*
             $key='bloxXCCbutton';
             $buttonrow['caption']=$row[$this->bloxconfig['captionField']];
             $buttonrow['bloxrow']=$output;
             $output = $this->renderdatarow($buttonrow, $innertpl, $key, $row);
             */
            /*
             $tpl = new xettChunkie($rowTpl,& $this->templates);
             $tpl->addVar('bloxrow',$output);
             $tpl->addVar('caption',$row[$this->bloxconfig['captionField']]);
             //$this->xcc_button['caption'] = '[+'.$this->bloxconfig['captionField'].'+]';
             echo $row[$this->bloxconfig['captionField']] ;
             //echo 'haaaaaaaaaaaaaallo';
             $output = $tpl->Render();
             */
        
        }
		
		return $output;
    }

    function generateDivForXedit($row, $fieldname, $value, $tag, $bloxattributes) {
        $div_content = $value;
        if ($GLOBALS['xedit_runs'] == '1' && $fieldname!==$this->bloxconfig['keyField']) {

        //TODO:  Feldattribute (tablename,resourceclass,rowid) individuell pro Feld überschreiben
        //$bloxattributes = $this->addAttribute('rowid',$row[$this->bloxconfig['keyField']],$bloxattributes);
            switch($tag) {
                case '#':
                    $div_content = '<div '.implode(' ', $bloxattributes).' fieldname="'.$fieldname.'" class="xedit">'.$value.'</div>';
                    break;
                case '##':
                    $tv = array ();
                    switch($this->bloxconfig['resourceclass']) {
                        case 'modDocument':
                            /*
                             $tv = $xedit->getNewMultiTvs( array ($formElementTv));
                             $tv = $tv[$formElementTv];
                             $tv['content'] = $formElementValue;
                             */
                            $tv['content'] = $value;
                            $tv['type'] = 'text';
                            $tv['formname'] = $fieldname;
                            break;
                        case 'modTable':
                            $tv['content'] = $value;
                            $tv['type'] = 'text';
                            $tv['formname'] = $fieldname;

                            break;
                        default:
                            break;
                    }
                    $prefix = 'tv';
                    $formelement = $this->fe->makeFormelement($tv, $prefix);
                    $div_content = '<div '.implode(' ', $bloxattributes).' fieldname="'.$fieldname.'" class="xedit_input">'.$formelement.'</div>';
                    break;
                default:

                    break;
            }
        }
        return $div_content;

    }

    //////////////////////////////////////////////////
    //neue Daten in Tabelle speichern
    /////////////////////////////////////////////////
    function dbinsert($row, $table = 'default') {
        global $modx;
        if ($table == 'default') {
            $table = $modx->getFullTableName($this->bloxconfig['xet_tablename']);
        }
        $fieldsarray = $row;
        $values = '';
        $fields = '';
        $deli = '';
        foreach ($fieldsarray as $key => $value) {
            $fields .= $deli . $key;
            $values .= $deli . "'" . $value . "'";
            $deli = ',';
        }
        $query = "INSERT INTO {$table}({$fields}) VALUES ({$values})";
        return $modx->db->query($query);
    }

    //////////////////////////////////////////////////
    //Zeiten aus Tabelle holen
    /////////////////////////////////////////////////
    function getevents($query) {
        global $modx;
        $rs = $modx->db->query($query);
        $allrows = $modx->db->makeArray($rs);

        if ($this->bloxconfig['processfilter'] == '1') {
            $allrows = $this->filterrows($allrows);
        }
        return $allrows;
    }


    //////////////////////////////////////////////////////
    //Daten-array holen
    //////////////////////////////////////////////////////
    function getdatas($date,$file,$row=array()) {
        global $modx;

        $file=$modx->config['base_path'].$file;
        if ($date == 'dayisempty') {
            $bloxdatas = array ();
        } else {
            $userID = $this->bloxconfig['userID'];

            if (file_exists($file)) {
            //$innerdatas = array ();
                include ($file);
            //$this->innerdatas=$innerdatas;
            } else {
                $daten = "includes-File " . $file . " nicht gefunden3";
            }
        }

        return $bloxdatas;

    }

    //////////////////////////////////////////////////
    //Formulardaten in array speichern
    //spezial processing with date and time formfields
    //handles array and non-array input-fields
    ///////////////////////////////////////////////////
    function getformfields($addbacktic='1') {
        $setfields = explode(',', $this->bloxconfig['setfields']);
        $id=(isset($_POST['eventID']))?$_POST['eventID']:0;
        $rows=array();
        $backtic=($addbacktic=='1')?'`':'';
        foreach ($setfields as $setfieldfull) {
        //setfields for date
        //Format A Feldname:inputfield-date||inputfield-time
        //Format B Feldname:inputfield-day||inputfield-month||inputfield-year||inputfield-hour||inputfield-minute
            $setfieldarr=explode(':',$setfieldfull);
            $setfield=$setfieldarr[0];
            $datefieldsplits=array();
            if (count($setfieldarr) > 1) {
                $datefieldsplits = explode('||', $setfieldarr[1]);
                if ((count($datefieldsplits) == 2) || (count($datefieldsplits) == 5)) {
                    $dates = $_POST[$datefieldsplits[0]];
                    $times = $_POST[$datefieldsplits[1]];
                    $days = $_POST[$datefieldsplits[0][$key]];
                    $months = $_POST[$datefieldsplits[1][$key]];
                    $years = $_POST[$datefieldsplits[2][$key]];
                    $hours = $_POST[$datefieldsplits[3][$key]];
                    $minutes = $_POST[$datefieldsplits[4][$key]];
                    if (is_array($dates)) {
                        foreach ($dates as $key => $value) {
                            if (count($datefieldsplits) == 2) {
                                $value = $this->getinputTime($dates[$key], $times[$key]);
                            }
                            if (count($datefieldsplits) == 5) {
                                $value = xetadodb_mktime($hours[$key], $minutes[$key], 0, $months[$key], $days[$key], $years[$key]);
                            }
                            $rows[$key][$backtic . $setfield . $backtic] = $value;
                        }
                    } else {
                        $key = $id;
                        $value = $postarray;
                        if (count($datefieldsplits) == 2) {
                            $date = $_POST[$datefieldsplits[0]];
                            $time = $_POST[$datefieldsplits[1]];
                            $value = $this->getinputTime($date, $time);
                        }
                        if (count($datefieldsplits) == 5) {
                            $day = $_POST[$datefieldsplits[0]];
                            $month = $_POST[$datefieldsplits[1]];
                            $year = $_POST[$datefieldsplits[2]];
                            $hour = $_POST[$datefieldsplits[3]];
                            $minute = $_POST[$datefieldsplits[4]];
                            $value = xetadodb_mktime($hour, $minute, 0, $month, $day, $year);
                        }
                        $rows[$key][$backtic . $setfield . $backtic] = $value;
                    }
                }
            } else {
                $postarray = $_POST[$setfield];
                if (is_array($postarray)) {
                    foreach ($postarray as $key => $value) {
                        $rows[$key][$backtic . $setfield . $backtic] = $value;
                    }
                } else {
                    $key = $id;
                    $value = $postarray;
                    $rows[$key][$backtic . $setfield . $backtic] = $value;
                }
            }
        }
        return $rows;
    }

    //////////////////////////////////////////////////
    //Get Timevalues from Form
    ///////////////////////////////////////////////////
    function getinputTime($date, $time) {
    //Todo validate Date and Time
        $dateformatarr = explode(',', $this->bloxconfig['date_format']);
        $datearr = explode($this->bloxconfig['date_divider'], $date);
        $key = array_search('d', $dateformatarr);
        $day = $datearr[$key];
        $key = array_search('m', $dateformatarr);
        $month = $datearr[$key];
        $key = array_search('y', $dateformatarr);
        $year = $datearr[$key];
        $timearr = explode(':', $time);
        $hour = $timearr[0];
        $minute = $timearr[1];
        return xetadodb_mktime($hour, $minute, 0, $month, $day, $year);
    }

    //////////////////////////////////////////////////
    //Formulardaten speichern
    ///////////////////////////////////////////////////
    function saveblox() {
        global $modx;
        if (file_exists($modx->config['base_path'].$this->bloxconfig['onsavefile'])) {
            include ($modx->config['base_path'].$this->bloxconfig['onsavefile']);
        }

        return;
    }

    ///////////////////////////////////////////////////////////////////
    //function to register css and javascript from snippet parameters
    ////////////////////////////////////////////////////////////////////
    function regSnippetScriptsAndCSS() {
        global $modx;
        if ($this->bloxconfig['css'] != "") {
            if ($modx->getChunk($this->bloxconfig['css']) != "") {
                $modx->regClientCSS($modx->getChunk($this->bloxconfig['css']));
            } else
                if (file_exists($modx->config['base_path'] . $this->bloxconfig['css'])) {
                    $modx->regClientCSS('<link rel="stylesheet" href="' . $modx->config['base_url'] . $this->bloxconfig['css'] . '" type="text/css" media="screen" />');
                } else {
                    $modx->regClientCSS($this->bloxconfig['css']);
                }
        }
        if ($this->bloxconfig['js'] != "") {
            if ($modx->getChunk($this->bloxconfig['js']) != "") {
                $modx->regClientStartupScript($modx->getChunk($this->bloxconfig['js']));
            } else
                if (file_exists($modx->config['base_path'] . $this->bloxconfig['js'])) {
                    $modx->regClientStartupScript($modx->config['base_url'] . $this->bloxconfig['js']);
                } else {
                    $modx->regClientStartupScript($this->bloxconfig['js']);
                }
        }
    }

    //////////////////////////////////////////////////////////////////////////
    //Member Check
    //////////////////////////////////////////////////////////////////////////
    function isMemberOf($groups) {
        global $modx;
        if ($groups == 'all') {
            return true;
        } else {
            $webgroups = explode(',', $groups);
            if ($modx->user->isMember($webgroups)) {
                return true;
            } else {
                return false;
            }
        }
    }
    /////////////////////////////////////////////////////////////////////////////
    //function to check for permission
    /////////////////////////////////////////////////////////////////////////////

    function checkpermission($permission) {
        $groupnames=$this->getwebusergroupnames();
        $perms='';
        foreach($groupnames as $groupname) {
            $perms.=$this->bloxconfig['permissions'][$groupname].',';
        }
        $perms=explode(',',$perms);
        return in_array($permission, $perms);
    }
    /////////////////////////////////////////////////////////////////////////////
    //function to get the groupnames of the webuser
    /////////////////////////////////////////////////////////////////////////////

    function getwebusergroupnames() {
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
    //function to filter the rows
    /////////////////////////////////////////////////////////////////////////////
    function filterrows($rows) {
        $filters_arr = $this->bloxconfig['filters_arr'];
        $filters = array ();
        $filterbys = array ();
        if ($filters_arr > 0) {
            foreach ($filters_arr as $theFilter) {
                $thefilters = explode('(', $theFilter);
                $filterBy = $thefilters[0];
                $filterbys[] = $filterBy;
                $filterValues = str_replace(')', '', $thefilters[1]);
                $filters[$filterBy] = $filterValues;
            }
        }
        $outputrows = array ();
        foreach ($rows as $row) {
            if (count($filterbys) == 0) {
                $pusharray = 1;
            } else {
                foreach ($filterbys as $filterBy) {
                    $pusharray = 0;
                    $filterValues = $filters[$filterBy];
                    if ($filterValues == '')
                        unset ($filterValues);
                    if (isset ($filterValues)) {
                        $Values = explode("|", $filterValues);
                        foreach ($Values as $filterValue) {
                            if ($xdbconfig['showempty'] == $filterValue && (trim($row[$filterBy]) == '' || empty ($row[$filterBy]))) {
                                $pusharray = 1;
                            }
                            elseif (trim($row[$filterBy]) !== '' && !empty ($row[$filterBy])) {
                                $isValue = strpos(strtolower($filterValue), strtolower($row[$filterBy]));
                                $isValueAlt = (strtolower($row[$filterBy]) == strtolower($filterValue));
                                if ($isValueAlt === false) {
                                } else
                                    $pusharray = 1;
                            }
                        }
                    }
                    elseif (!empty ($row[$filterBy]) || trim($row[$filterBy]) !== '') {
                        $pusharray = 0;
                    }
                    if ($pusharray == 0)
                        break;
                }
            }
            if ($pusharray == 1) {
                $outputrows[] = $row;
            }
        }

        return $outputrows;

    }

    function filter2sqlwhere($filters, &$TVarray=array(), $resourceclass='modDocument',&$having='') {

    //$filter = 'pagetitle|der Titel|=++parent|25,30,40|IN++(rennennr|10|>||(published|1|=++deleted|0|=)++id|1,2,3,4,5|IN)||parent|25|=';
    //$where = '`pagetitle`="der Titel" AND `parent` IN (25,30,40) AND (`rennennr` > 10 OR `published` = 1 AND `deleted` = 0) OR `id` IN (1,2,3,4,5))';
    //Todo??:
	//title,body|database|MATCH
	//SELECT * FROM articles WHERE MATCH (title,body) AGAINST ('database');
	//use $filter='title|%database%|like||pagetitle|%database%|like';

    // das Suchmuster mit Delimiter und Modifer (falls vorhanden)
        $pattern = '#(\|\|)|(\+\+)#';

        // RegEx mit preg_split() auswerten
        // Auch die eingeklammerten Ausdrücke des
        // Trennsymbol-Suchmusters erfasst und zurückgegeben
        $filterarray = preg_split($pattern, $filters, -1, PREG_SPLIT_DELIM_CAPTURE);

        // formatierte Ausgabe
        //echo '<pre>'.print_r($filterarray, true).'</pre>';
        //$fieldsarray = explode(',',$fields);
        $alias=$resourceclass=='modDocument'?'sc.':'';

        $where = '';
		$delimiter='|';

        foreach ($filterarray as $filter) {
            if (! empty($filter)) {
                $filter = trim($filter);
                $o_bracket = '';
                $c_bracket = '';

                if (substr($filter, 0, 1) == '(') {
                    $o_bracket = '(';
                    $filter = str_replace('(', '', $filter);
                }
                if (substr($filter, -1, 1) == ')') {
                    $c_bracket = ')';
                    $filter = str_replace(')', '', $filter);
                }

                switch($filter) {
                    case '||':
                        $andOr = ' OR ';
                        break;
                    case '++':
                        $andOr = ' AND ';
                        break;
                        default:
                            $pieces = explode($delimiter, $filter);
                            if (count($pieces) == 3)
                            {
                                $o_enc = '"';
                                $c_enc = '"';
								$pieces[1] = ($pieces[1] == '#EMPTY#')?'':$pieces[1];
                                switch($pieces[2])
                                {
                                case 'IN':
                                    $o_enc = '(';
                                    $c_enc = ')';
                                    break;
                                case 'eq':
                                    $pieces[2] = '=';
                                    break;
                                case 'gt':
                                    $pieces[2] = '>=';
                                    break;
                                case 'lt':
                                    $pieces[2] = '<=';
                                    break;
                                case 'ne':
                                    $pieces[2] = '<>';
                                    break;									
                                /*
								case 'isempty':
								case 'not isempty':
                                    $o_enc = '(';
                                    $c_enc = ')';
									$pieces[1]=$pieces[0];
									$scipfield=true;								
                                    break;
                                */    									
                                default:
                                    break;
                            }
	                        $field = $pieces[0];
                            if ($resourceclass == 'modDocument' && in_array($field, $this->tvnames))
                            {
                                $TVarray[$field] = $field;
                                //$field = $field.'_tvcv.`value`';
         						//$field = " IF(".$field."_tvcv.value!='',".$field."_tvcv.value,".$field."_tv.default_text) "; 
                                $alias='';
    						}
		                    $field = $scipfield?'':$alias.'`'.$field.'`';
                            $where .= $andOr.$o_bracket.$field.$pieces[2].$o_enc.$pieces[1].$c_enc.' '.$c_bracket;
                        }
                        break;
                }
            }
        }
        //echo $where;

        return $where;

    }

    function getrows(){
		$result=array();
		switch ($this->bloxconfig['resourceclass']){
    		case 'modDocument':
			$result=$this->getdocs();
			break;
			case 'modTable':
			$result=$this->gettablerows();
			break;
			default:
			break;
			
    	}

        $result=$this->checkXCCbuttons($result);
		$result=$this->checkDocSort($result);
		//$result=$this->orderResult($result);
		//$result=$this->filterResult($result);

		return $result;
    }

    function orderResult($result){
        //Todo: orderResult

		return $result;   	
    }

    function filterResult($result){
        //Todo: filterResult

		return $result;   	
    }	

    function checkDocSort($result){
        //add button arround this row for blox-creating
		
		if (!empty($this->bloxconfig['IDs']) && empty($this->bloxconfig['orderBy'])){
			$rows=array();
			foreach($result as $row){
				$rows[$row['id']]=$row;
				
			}
		$ids=explode(',',$this->bloxconfig['IDs']);
        $result=array();
		foreach ($ids as $id){
		    $result[]=$rows[$id];	
		}
		} 
		
		return $result;   	
    }

    function checkXCCbuttons($result){
        //add button arround this row for blox-creating
		if ($this->bloxconfig['c_type'] == 'xcc_container'&& count($result)>0){
			foreach($result as & $row){
				$row['makexccbutton']='1';
			}
		} 
		return $result;   	
    }

    function gettablerows()
    {
        global $modx;
    
        //$docmatch_array = array ();
    
        $where = $this->bloxconfig['where'];
        $orderBy = $this->bloxconfig['orderBy'];
		$groupby = $this->bloxconfig['groupBy'] !==''?' GROUP BY '.$this->bloxconfig['groupBy']:'';
        $pageStart = $this->bloxconfig['pageStart'];
        $perPage = $this->bloxconfig['perPage'];
        $numLinks = $this->bloxconfig['numLinks'];
        $fields = $this->bloxconfig['fields'];
        $requiredFields = $this->bloxconfig['requiredFields'];
		$filter = $this->bloxconfig['prefilter']==''?$this->bloxconfig['filter']:$this->bloxconfig['prefilter'].'++'.$this->bloxconfig['filter'];
        $start = $pageStart-1;
        $limit = $start.', '.$perPage;
        $where = $this->filter2sqlwhere($filter,array(),$this->bloxconfig['resourceclass']);
		//$where = $where !== ''?' AND '.$where:'';
        $table = $modx->getFullTablename($this->bloxconfig['tablename']);
       
		
        /*

        foreach ($requiredFields as $field)
        {
            if ($fields !== '*' && in_array($field, $this->docColumnNames))
            {
                $Fields[] = " sc.$field ";
            }
            elseif (in_array($field, $this->tvnames))
            {
                $in_tvarray[] = $field;
                //$Fields[] = $field."_tvcv.value AS $field ";
                $Fields[] = " IF(".$field."_tvcv.value!='',".$field."_tvcv.value,".$field."_tv.default_text) AS $field ";
                $Froms[] = "$t_tv AS ".$field."_tv ";
                $tvJoins .= " LEFT JOIN $t_cv AS ".$field."_tvcv ON ".$field."_tvcv.contentid = sc.id AND ".$field."_tvcv.tmplvarid = ".$field."_tv.id";
                $tvNames .= " AND ".$field."_tv.name = '$field'";
            }
        }
        */
        $rs = $modx->db->select($this->bloxconfig['distinct'].' '.$fields, $table, $where.$groupby);
        $this->totalCount = $modx->db->getRecordCount($rs);

        $rs = $modx->db->select($this->bloxconfig['distinct'].' '.$fields, $table, $where.$groupby, $orderBy, $start.', '.$perPage);
        //$this->columnNames = $modx->db->getColumnNames( $rs );	// Get column names - in the order you select them     
        $rows = $modx->db->makeArray($rs);
		
		$this->columnNames = array_keys($rows[0]);    
    
        return $rows;
	
	}   


    function getdocs()
    {


        global $modx;
    
        //$docmatch_array = array ();
    
        $where = $this->bloxconfig['where'];
        $orderby = $this->bloxconfig['orderBy'];
		$groupby = $this->bloxconfig['groupBy'] !==''?' GROUP BY '.$this->bloxconfig['groupBy']:'';
        $pageStart = $this->bloxconfig['pageStart'];
        $perPage = $this->bloxconfig['perPage'];
        $numLinks = $this->bloxconfig['numLinks'];
        $fields = $this->bloxconfig['fields'];
        $requiredFields = $this->bloxconfig['requiredFields'];
        $start = $pageStart-1;
        $limit = $start.', '.$perPage;
    
        $t_sc = $modx->getFullTableName('site_content');
        $t_tv = $modx->getFullTableName('site_tmplvars');
        $t_cv = $modx->getFullTableName('site_tmplvar_contentvalues');
    
        $this->getTvNames();
        $this->getDocColumnNames();
        $Tvarray = array ();


        $where = $this->filter2sqlwhere($this->bloxconfig['prefilter'], $Tvarray, 'modDocument');
        $where = $where !== ''?' AND '.$where:'';

        $having = $this->filter2sqlwhere($this->bloxconfig['filter'], $Tvarray, 'modDocument');
		$having = $having == ''?'':' HAVING '.$having.' ';
		
    
        //echo $where;
        //
        $orderby = (! empty($orderby))?$orderby:'sc.menutitle';
        //$fields = (! empty($fields))?$fields:'*';
        $contentFields = array ();
        $getTVs = array ();
        $Fields = array ();
        $Froms = array ();
        $Froms[] = "$t_sc AS sc";
        $tvValues = '';
        $tvFroms = '';
        $tvJoins = '';
        $matchTvJoins = '';
        $tvNames = '';
        $in_tvarray = array ();
        $Fields = array ('sc.*');

        foreach ($requiredFields as $field)
        {
            if (in_array($field, $this->tvnames))
            {
                $in_tvarray[] = $field;
                //$Fields[] = $field."_tvcv.value AS $field ";
                $Fields[] = " IF(".$field."_tvcv.value!='',".$field."_tvcv.value,".$field."_tv.default_text) AS $field ";
                $Froms[] = "$t_tv AS ".$field."_tv ";
                $tvJoins .= " LEFT JOIN $t_cv AS ".$field."_tvcv ON ".$field."_tvcv.contentid = sc.id AND ".$field."_tvcv.tmplvarid = ".$field."_tv.id";
                $tvNames .= " AND ".$field."_tv.name = '$field'";
            }
        }
     
    
        foreach ($Tvarray as $field)
        {
            if (!in_array($field, $in_tvarray))
            {
                $Fields[] = " IF(".$field."_tvcv.value!='',".$field."_tvcv.value,".$field."_tv.default_text) AS $field ";
				$Froms[] = "$t_tv AS ".$field."_tv ";
                $tvJoins .= " LEFT JOIN $t_cv AS ".$field."_tvcv ON ".$field."_tvcv.contentid = sc.id AND ".$field."_tvcv.tmplvarid = ".$field."_tv.id";
                $tvNames .= " AND ".$field."_tv.name = '$field'";
            }
        }
    
        $rows = array ();
    
    
        $orderby = (! empty($orderby))?'ORDER BY '.$orderby:'';
        $limit = ($limit !== '0')?"LIMIT $limit":'';
        /*
         $where = '';
         if (count($docmatch_array) > 0) {
         foreach ($docmatch_array as $key=>$value) {
         $whereIN= ($key=='id'||$key=='parent')?'IN ('.$value.')':'= "'.$value.'"';
         $where .= ' and CAST(sc.`'.$key.'`AS CHAR) '.$whereIN;
         }
         }
         */
    
        /*
         if (count($tvmatch_array) > 0) {
         foreach ($tvmatch_array as $key=>$value) {
         $tv='tv'.$key;
         $where .= ' and '.$tv.'.value = "'.$value.'"';
         $matchTvJoins .= "
         LEFT JOIN
         $t_cv AS ".$tv." ON ".$tv.".contentid = sc.id AND ".$tv.".tmplvarid = ".$key;
         }
         }
         */
        /*
         if (count($getTVs)>0){
         foreach ($getTVs as $tv) {
         $Fields[] = $tv."_tvcv.value AS $tv ";
         $Froms[] = "$t_tv AS ".$tv."_tv ";
         $tvJoins .= "
         LEFT JOIN
         $t_cv AS ".$tv."_tvcv ON ".$tv."_tvcv.contentid = sc.id AND ".$tv."_tvcv.tmplvarid = ".$tv."_tv.id";
         $tvNames .= "
         AND
         ".$tv."_tv.name = '$tv'";
         }
         }
         */
    
        $Fields = $this->bloxconfig['distinct'].' '.implode(',', $Fields);
        $Froms = implode(',', $Froms);
        // Build query
        $sql = "
                        SELECT 
                $Fields
                        FROM
                        ($Froms)
                $tvJoins
                $matchTvJoins
                        WHERE 1
                $tvNames
                $where
				$groupby 
				$having       
                $orderby ";
        // Get rows
        //echo '<br/><br/>'. $sql;

        $rs = $modx->db->query($sql);
        $this->totalCount = $modx->db->getRecordCount($rs);

        $sql .= $limit;
        $rs = $modx->db->query($sql);
	
		//$this->columnNames = $modx->db->getColumnNames($sql);
   
        $rows = $modx->db->makeArray($rs);

        $this->columnNames = array_keys($rows[0]);
        return $rows;
		
    }



    /**
     * Sort DB result
     *
     * @param array $data Result of sql query as associative array
     *
     * Rest of parameters are optional
     * [, string $name  [, mixed $name or $order  [, mixed $name or $mode]]]
     * $name string - column name i database table
     * $order integer - sorting direction ascending (SORT_ASC) or descending (SORT_DESC)
     * $mode integer - sorting mode (SORT_REGULAR, SORT_STRING, SORT_NUMERIC)
     *
     * <code>
     *
     * // You can sort data by several columns e.g.
     * $data = array();
     * for ($i = 1; $i <= 10; $i++) {
     *     $data[] = array( 'id' => $i,
     *                      'first_name' => sprintf('first_name_%s', rand(1, 9)),
     *                      'last_name' => sprintf('last_name_%s', rand(1, 9)),
     *                      'date' => date('Y-m-d', rand(0, time()))
     *                  );
     * }
     * $data = sortDbResult($data, 'date', SORT_DESC, SORT_NUMERIC, 'id');
     * printf('<pre>%s</pre>', print_r($data, true));
     * $data = sortDbResult($data, 'last_name', SORT_ASC, SORT_STRING, 'first_name', SORT_ASC, SORT_STRING);
     * printf('<pre>%s</pre>', print_r($data, true));
     *
     * </code>
     *
     * @return array $data - Sorted data
     */

    function sortDbResult($_data) {
    	

		
        $_argList = func_get_args();
        $_data = array_shift($_argList);
        if (empty($_data)) {
            return $_data;
        }
        $_max = count($_argList);
        $_params = array();
        $_cols = array();
        $_rules = array();
        for ($_i = 0; $_i < $_max; $_i += 3) {
            $_name = (string) $_argList[$_i];
            if (!in_array($_name, array_keys(current($_data)))) {
                continue;
            }
            if (!isset($_argList[($_i + 1)]) || is_string($_argList[($_i + 1)])) {
                $_order = SORT_ASC;
                $_mode = SORT_REGULAR;
                $_i -= 2;
            } else if (3 > $_argList[($_i + 1)]) {
                    $_order = SORT_ASC;
                    $_mode = $_argList[($_i + 1)];
                    $_i--;
                } else {
                    $_order = $_argList[($_i + 1)] == SORT_ASC ? SORT_ASC : SORT_DESC;
                    if (!isset($_argList[($_i + 2)]) || is_string($_argList[($_i + 2)])) {
                        $_mode = SORT_REGULAR;
                        $_i--;
                    } else {
                        $_mode = $_argList[($_i + 2)];
                    }
                }
            $_mode = $_mode != SORT_NUMERIC
                ? $_argList[($_i + 2)] != SORT_STRING ? SORT_REGULAR : SORT_STRING
                : SORT_NUMERIC;
            $_rules[] = array('name' => $_name, 'order' => $_order, 'mode' => $_mode);
        }
        foreach ($_data as $_k => $_row) {
            foreach ($_rules as $_rule) {
                if (!isset($_cols[$_rule['name']])) {
                    $_cols[$_rule['name']] = array();
                    $_params[] = &$_cols[$_rule['name']];
                    $_params[] = $_rule['order'];
                    $_params[] = $_rule['mode'];
                }
                $_cols[$_rule['name']][$_k] = $_row[$_rule['name']];
            }
        }
        $_params[] = &$_data;
        call_user_func_array('array_multisort', $_params);
        return $_data;
    }

/*
 * $link['page'] = 3;
 * $link['aname'] = 'avalue';
 * $link['another'] = 'one';
 * echo smartModxUrl($modx->documentObject["id"],NULL, $link);
 */

function smartModxUrl($docid, $docalias, $array_values,$removearray=array()) {
		global $modx;
		$array_url = $_GET;
		$urlstring = array();
		
		unset($array_url["id"]);
		unset($array_url["q"]);
		
		$array_url = array_merge($array_url,$array_values);

		foreach ($array_url as $name => $value) {
			if (!is_null($value)&& !in_array($name,$removearray)) {
			  $urlstring[] = $name . '=' . urlencode($value);
			}
		}
		
		return $modx->makeUrl($docid, $docalias, join('&',$urlstring));
	}

	// ---------------------------------------------------
	// Function: getChildIDs
	// Get the IDs ready to be processed
	// Similar to the modx version by the same name but much faster
	// ---------------------------------------------------

	function getChildParents($IDs, $depth) {
		global $modx;
		$depth = intval($depth);
		$kids = array();
		$parents = array();
		$docIDs = array();
		
		if ($depth == 0 && $IDs[0] == 0 && count($IDs) == 1) {
			$parents['0']= 0 ; 
			
			foreach ($modx->documentMap as $null => $document) {
				foreach ($document as $parent => $id) {
					//$kids[] = $id;
					$parents[$parent]= $parent ;
				}
			}
			return $parents;
		} else if ($depth == 0) {
			$depth = 10000;
				// Impliment unlimited depth...
		}
		
		foreach ($modx->documentMap as $null => $document) {
			foreach ($document as $parent => $id) {
				$kids[$parent][] = $id;
			}
		}

		foreach ($IDs AS $seed) {
			if (!empty($kids[intval($seed)])) {
				$docIDs = array_merge($docIDs,$kids[intval($seed)]);
				$parents[intval($seed)]= intval($seed);
				unset($kids[intval($seed)]);
			}
		}
		$depth--;

		while($depth != 0) {
			$valid = $docIDs;
			foreach ($docIDs as $child=>$id) {
				if (!empty($kids[intval($id)])) {
					$docIDs = array_merge($docIDs,$kids[intval($id)]);
					$parents[intval($id)]= intval($id);
					unset($kids[intval($id)]);
				}
			}
			$depth--;
			if ($valid == $docIDs) $depth = 0;
		}

		return $parents;
	}


//////////////////////////////////////////////////////////////////////
// Ditto - Functions
// Author: 
// 		Mark Kaplan for MODx CMF
//////////////////////////////////////////////////////////////////////

	// ---------------------------------------------------
	// Function: cleanIDs
	// Clean the IDs of any dangerous characters
	// ---------------------------------------------------
	
	function cleanIDs($IDs) {
		//Define the pattern to search for
		$pattern = array (
			'`(,)+`', //Multiple commas
			'`^(,)`', //Comma on first position
			'`(,)$`' //Comma on last position
		);

		//Define replacement parameters
		$replace = array (
			',',
			'',
			''
		);

		//Clean startID (all chars except commas and numbers are removed)
		$IDs = preg_replace($pattern, $replace, $IDs);

		return $IDs;
	}
	// ---------------------------------------------------
	// Function: getChildIDs
	// Get the IDs ready to be processed
	// Similar to the modx version by the same name but much faster
	// ---------------------------------------------------

	function getChildIDs($IDs, $depth) {
		global $modx;
		$depth = intval($depth);
		$kids = array();
		$docIDs = array();
		
		if ($depth == 0 && $IDs[0] == 0 && count($IDs) == 1) {
			foreach ($modx->documentMap as $null => $document) {
				foreach ($document as $parent => $id) {
					$kids[] = $id;
				}
			}
			return $kids;
		} else if ($depth == 0) {
			$depth = 10000;
				// Impliment unlimited depth...
		}
		
		foreach ($modx->documentMap as $null => $document) {
			foreach ($document as $parent => $id) {
				$kids[$parent][] = $id;
			}
		}

		foreach ($IDs AS $seed) {
			if (!empty($kids[intval($seed)])) {
				$docIDs = array_merge($docIDs,$kids[intval($seed)]);
				unset($kids[intval($seed)]);
			}
		}
		$depth--;

		while($depth != 0) {
			$valid = $docIDs;
			foreach ($docIDs as $child=>$id) {
				if (!empty($kids[intval($id)])) {
					$docIDs = array_merge($docIDs,$kids[intval($id)]);
					unset($kids[intval($id)]);
				}
			}
			$depth--;
			if ($valid == $docIDs) $depth = 0;
		}

		return array_unique($docIDs);
	}

    function getSiteMap($items,$level=0){
        /* $start = array (array ('id'=>0));
         * $map = getSiteMap($start);
         * print_r($map);
         */
        
        global $modx;
        $pages = array ();
        foreach ($items as $item)
        {
            $page = array ();
			$page['id']= $item['id'];
			$page['level'] = $level;
            $page['pagetitle'] = $item['pagetitle'];
            //$page['URL'] = $modx->makeUrl($item['id']);
            $children = $modx->getAllChildren($item['id'], 'menuindex ASC, pagetitle', 'ASC', 'id,isfolder,pagetitle,description,parent,alias,longtitle,published,deleted,hidemenu');
            if (count($children) > 0)
            {
                $children = $this->getSiteMap($children,$level+1);
                $page['innerrows']['level_'.$level+1] = $children;
            }
            $pages[] = $page;
        }
    
        return $pages;
    }
	
}
?>