<?php


if (isset($_POST['clearcache'])){
$filename=$this->xetconfig['cachepath'].'/'.$this->xetconfig['projectname'].'.belegplanlong.*.cache.php';	
$this->cache ->clearCache($filename);
$message['messagetext'] = 'Cache wurde geleert';	
}

if (isset($_POST['anwenden'])){
$tablename = $modx->getFullTableName('belegplan'); 
if (count($_POST['delete'])>0){
foreach($_POST['delete'] as $key=>$value){
	
	if ($value=='1'){
       $id = $modx->db->escape($key);
       $modx->db->delete($tablename, "id = $id");		
	}
	
};	
$message['messagetext'] = 'markierte Buchungen wurden entfernt';	
}
foreach($_POST['published'] as $key=>$value){
       $id = $modx->db->escape($key);
	   $row=array('published'=>$value);
       $modx->db->update($row,$tablename, "id = $id");		
};			
}


$this->messages[]=$message;

?>
