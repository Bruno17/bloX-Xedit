<?php

/*
 * pagetitle,der Titel,=++parent,"25,30,40",IN++(rennennr,10,>||(published,1,=++deleted,0,=)||id,"1,2,3,4,5",IN)
 * 
 * pagetitle,der Titel,=++parent,"25,30,40",IN
 * rennennr,10,>||(published,1,=++deleted,0,=)||id,"1,2,3,4,5",IN)
 * 
 * 
 */


$resultrows = $this->getrows();

$cols = $this->columnNames;
$fieldnames = array();
if (count($cols)>0){
	foreach ($cols as $col){
		$col=array('fieldname'=>$col);
	    $fieldnames[]=$col;	
	}
} 

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

//pagination
$numRows = $this->totalCount;
require_once ($GLOBALS['blox_path'].'inc/Pagination.php');
$p = new Pagination( array ('per_page'=>$this->bloxconfig['perPage'],
'num_links'=>$this->bloxconfig['numLinks'],
'cur_item'=>$this->bloxconfig['pageStart'],
'total_rows'=>$numRows));
$bloxdatas['pagination'] = $p->create_links();


$bloxdatas['innerrows']['fieldnames']=$fieldnames;
/*
print_r($bloxdatas);
echo '---------------------------------------';
print_r($rows);
*/

$bloxdatas['innerrows']['row'] = $rows;

//print_r($bloxdatas);

/*
 [!loopDbChunk? &tableName=`modx_regatta_aktivenliste` &chunkName=`regatta_aktivenliste` &perPage=`50` &orderby=`vereinsname,nachname asc` &missingValTxt=`--`&header=`<table><tr><th>AktivenID</th><th>Vorname</th><th>Nachname</th><th>Jahrgang</th><th>Geschlecht</th><th>Vereinsname</th><th>Vereinsnummer</th></th></tr><tbody class="chunkcontainer" resourceclass="modTable">`&footer=`</tbody></table>`&numLinks=`15`&sql=`[+filter_aktiven+]`!]
 */
?>
