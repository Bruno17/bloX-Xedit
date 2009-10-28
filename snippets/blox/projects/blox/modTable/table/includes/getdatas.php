<?php
/*
$where = $this->bloxconfig['where'];
$orderBy = $this->bloxconfig['orderBy'];
$pageStart = $this->bloxconfig['pageStart'];
$perPage = $this->bloxconfig['perPage'];
$numLinks = $this->bloxconfig['numLinks'];
$fields = $this->bloxconfig['fields'];

$start = $pageStart-1;
$table = $modx->getFullTablename($this->bloxconfig['tablename']);

$rs = $modx->db->select($fields, $table, $where, $orderBy, $start.', '.$perPage);
$cols = $modx->db->getColumnNames( $rs );	// Get column names - in the order you select them
$fieldnames = array();
if (count($cols)>0){
	foreach ($cols as $col){
		$col=array('fieldname'=>$col);
	    $fieldnames[]=$col;	
	}
} 

$resultrows = $modx->db->makeArray($rs);
*/
$resultrows = $this->getrows();

$rows=array();

if (count($resultrows) > 0)
{
    foreach ($resultrows as $row)
    {
    	$colums=array();
        if (count($row) > 0)
        {
            foreach ($row as $fieldname=>$col)
            {
                $col = array ('value'=>$col,'fieldname'=>$fieldname);
                $colums[] = $col;
            }
			$row['innerrows']['rowvalue'] = $colums;
			$rows[]=$row;
        }
    }
}

//$rs = $modx->db->select('id', $table, $where);
//$numRows = $modx->db->getRecordCount($rs);
$numRows = $this->totalCount;
require_once ($GLOBALS['blox_path'].'inc/Pagination.php');
$p = new Pagination( array ('per_page'=>$perPage,
'num_links'=>$this->bloxconfig['numLinks'],
'cur_item'=>$this->bloxconfig['pageStart'],
'total_rows'=>$numRows));

$bloxdatas['innerrows']['fieldnames']=$fieldnames;
/*
print_r($bloxdatas);
echo '---------------------------------------';
print_r($rows);
*/
$bloxdatas['pagination'] = $p->create_links();
$bloxdatas['innerrows']['row'] = $rows;



/*
 [!loopDbChunk? &tableName=`modx_regatta_aktivenliste` &chunkName=`regatta_aktivenliste` &perPage=`50` &orderby=`vereinsname,nachname asc` &missingValTxt=`--`&header=`<table><tr><th>AktivenID</th><th>Vorname</th><th>Nachname</th><th>Jahrgang</th><th>Geschlecht</th><th>Vereinsname</th><th>Vereinsnummer</th></th></tr><tbody class="chunkcontainer" resourceclass="modTable">`&footer=`</tbody></table>`&numLinks=`15`&sql=`[+filter_aktiven+]`!]
 */
?>
