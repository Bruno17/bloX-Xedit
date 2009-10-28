<?php


if (isset($_POST['clearcache'])){
$filename=$this->xetconfig['cachepath'].'/'.$this->xetconfig['projectname'].'.belegplanlong.*.cache.php';	
$this->cache ->clearCache($filename);
$message['messagetext'] = 'Cache wurde geleert';	
}

if (isset($_POST['anwenden'])){
$tablename = $modx->getFullTableName('site_content'); 
if (count($_POST['delete'])>0){
foreach($_POST['delete'] as $key=>$value){
	
	if ($value=='1'){
       $id = $modx->db->escape($key);
       $modx->db->update('deleted = 1',$tablename, "id = $id");		
	}
	
};	
$message['messagetext'] = 'markierte Buchungen wurden auf gel&ouml;scht gesetzt';	
}
foreach($_POST['published'] as $key=>$value){
       $id = $modx->db->escape($key);
	   $row=array('published'=>$value);
       $modx->db->update($row,$tablename, "id = $id");		
};			
}


$this->messages[]=$message;

?>
