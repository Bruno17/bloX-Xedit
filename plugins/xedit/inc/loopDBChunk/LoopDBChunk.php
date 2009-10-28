<?php

/*
 * Title: LoopDBChunk Class
 * Desc: loop over a database table and replace placeholders in
 *       a chunk with the query results 
 * Author: Amir Marcovitz
 * Version: 1.2 
 */

     
class LoopDBChunk {

	var $tableName = '';
	var $chunkName = '';
	var $sql = '';
	var $orderby = '';
	var $missingValText = '';
	
	var $fieldNames = array();
	
	var $header = '';
	var $footer = '';
	var $chunk = '';
	
	// array  [placeholder name] => [placeholder index within the chunk]
	var $placeholders = array();

	function LoopDBChunk($paramArray)
	{
		$this->tableName = $paramArray['table'];
		$this->chunkName = $paramArray['chunk'];
		$this->sql	     = $paramArray['sql'];
		$this->orderby   = $paramArray['order'];
		$this->missingValText = $paramArray['missingTxt'];
		$this->perPage = $paramArray['per_page'];
		$this->numLinks = $paramArray['num_links']; 
		$this->header = $paramArray['header'];
		$this->footer = $paramArray['footer'];
		$this->getPlaceholders();

	}

	function addFieldPh($ph, $idx)
   {
		$this->placeholders[] = $ph . '##' . $idx;
		$pieces = explode(':', $ph);
     	if(count($pieces) > 1)
     	{  	   
     	   for($i = 2; $i < count($pieces); $i++) 
         {          
            $this->fieldNames[] = $pieces[$i];
         }
      }
      else if($ph != 'lo_ord')
      {
         $this->fieldNames[] = $ph;
		}
	}
		
	
	// description : parse the chunk, put all the placeholders names and
	// their locations in the array 'placeholders' 
	function getPlaceholders() 
	{
		global $modx;
		$chunk = $modx->getChunk($this->chunkName);
		$len = strlen($chunk); 
	   $idx = strpos($chunk, '[+');
		while(($idx !== false) && ($idx < $len)) 
		{			
			$endIdx = strpos($chunk, '+]',  $idx + 2); 
			$ph = substr($chunk, $idx + 2, $endIdx - $idx - 2);			
			$this->addFieldPh($ph, $idx);			
			$idx = strpos($chunk, '[+', $endIdx + 1);
		}
	}


	
	/**
	 *		function: getChunkReplaced
	 *		---------------------------
	 *		description: 	replace the placeholders in a chunk with
	 *							data from the DB
	 *		$row: one row of the query results
	 *		$chunk: the chunk to be processed
	 *		
	 *		@return: the chunk with all the placeholders replaced
	 */							
	function getChunkReplaced($row, $rowIdx)
	{
		$i = 0;
		$shiftIdx = 0;
		$record = $this->chunk;

		foreach ($this->placeholders as $phIdx ) 
		{
			list($ph, $idxInChunck) = explode('##', $phIdx);
			$origLen = strlen($ph) + 4;			
			$transformFunc = explode(":", $ph);
			//var_dump($transformFunc);
			if(count($transformFunc) > 1) 
			{
				if(function_exists($transformFunc[1])) 
				{
					$funcParams = array();
					// get the function parameters
					// for each parameter translate from field name to 
					// the actual value for this row in the database 
					for($i = 2; $i < count($transformFunc); $i++) 
					{
						if(array_key_exists($transformFunc[$i], $row)) 
						{
							$funcParams[] = $row[$transformFunc[$i]];
						}
						else 
						{
							if($transformFunc[$i] == 'lo_ord')
							{
								$funcParams[] = $rowIdx;
							}
							else 
							{ 
								$funcParams[] = $transformFunc[$i];
							}
						}
						
					}
					//var_dump($funcParams);
					$replaceVal = call_user_func_array($transformFunc[1], $funcParams);                  
				}
			}
			else { 
				$replaceVal = ($ph == 'lo_ord') ? $rowIdx+1 : $row[$ph];
				if(empty($replaceVal) && ($ph != 'lo_ord'))
				{
					$replaceVal = $this->missingValText;
				}
			} 	
			// do the actual replacement
			$record = substr_replace($record, 
													$replaceVal,											  
													$idxInChunck + $shiftIdx,											 
													$origLen);						
			// update the shift of the placeholders index
			$shiftIdx += strlen($replaceVal) - $origLen;	
	
		} 
		return($record);
	} 


	/**
	 *		function: getPageStart
	 *		-----------------------
	 *		description: look for a query string with the key 'pagestart'
	 *		and return its value if found
	 *		
	 */		
	function getPageStart()
	{
		// Determine the current page number.		
		parse_str($_SERVER['QUERY_STRING'], $urlParams);
		if(isset($_GET['pagestart']) && is_numeric($_GET['pagestart']))
		{
			return($_GET['pagestart']);
	   }
	   else
	   {
	   	return (1);
	   }
	}
		
      
 
	
	
	
	/**
	 *		function: renderChunks
	 *		-----------------------
	 *		description: do the sql query,
	 *		call getChunkReplaced() for each row of the result,
	 *		and return the modified chunks.
	 *		
	 */		
	function renderChunks() 
	{
		global $modx;
		require_once("Pagination.php");
		
		$query = '';
		$rs = '';
		$output = $this->header;
		
		$pageStart = $this->getPageStart();
		
		if($this->sql != '') 
		{
			$query = str_replace('_eq_', '=', $this->sql);
		}
		else 
		{
			$fields = implode(',', array_unique($this->fieldNames));
         $query = 'SELECT ' . $fields . ' FROM ' . $this->tableName;
			if ($this->orderby != '') {
				$query .= ' ORDER BY ' . $this->orderby;
			}
		}	
				
		$rs = $modx->db->query($query);
		$numRows = $modx->db->getRecordCount($rs);
		
		if($this->perPage > 0) 
		{
   		$from = $pageStart - 1;
   		$query .= ' LIMIT ' . $from . ', ' . $this->perPage;
   	}
		
		$rs = $modx->db->query($query);
		// echo $query . '<br/>';
		$this->chunk = $modx->getChunk($this->chunkName);
      
      $i = 0;
		while( $row = $modx->db->getRow($rs) ) 
		{
			$output .= $this->getChunkReplaced($row, $i);	
         $i++;		
		}
		
		$output .= $this->footer;

      if($this->perPage > 0) 
      {
         $p = new Pagination(array('per_page' => $this->perPage,
      								  'num_links' => $this->numLinks,
      								  'cur_item'  => $pageStart,	
      								  'total_rows' => $numRows	));
      
         $output .=  '<div class="ldb_pagination">'  . $p->create_links() . '</div>';								  							  
      }
		return($output);
	}
}

?>
