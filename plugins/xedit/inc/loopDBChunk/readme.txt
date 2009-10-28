
										loopDBChunk 1.2
									 -------------------
This snippet is used for displaying data from a database while allowing to format each row with a chunk.
The snippet will loop over a database table and for 
each row will inject some (or all) of the fields into placeholders
contained in a chunk.
Optionally, each database field's value can be transformed by a given function. 
for example, a long text can be shortened, a date field can be formatted, etc.

Change history
----------------

version 1.2:

	1. ( important - not compatibale with older versions!! )
		The parameter 'sql' (for a customised sql query) must use the string '_eq_' instead of '='
		(Changed from 'eq' in previous versions)   
	
	2. Fixed a bug that caused wrong output in some cases when a placeholder exists more than once in the chunk.

 	
version 1.1:  
  
   1. $this->transformArr and the parameter 'transform' are deprecated.
      instead, for modifying a field, there is a new format of placeholder
      explained later in this file.
   
   2. added parameters 'header' and 'footer', to add content before and after the
      database loop.

	3. added a field 'ld_ord' to be replaced with the 'current' row number.

   4. Pagination is added at the bottom (under the footer if exists) whenever the 
      parameter 'perPage' exists with value > 0
 
   5. ( important ): 
      A snippet parameter CANNOT include the '=' character,
      because of the way MODx parses snippets parameters. So, inside a custom sql
      query use '_eq_' instead.


 usage:
--------
	1. 	make a directory named 'loopDbChunk' under <root_modx_dir>/assets/snippets/ and copy the 
		files LoopDBChunk.php and Pagination.php there.

	2. 	create a new snippet named 'loopDbChunk' and copy the content of the file 'snippet.txt' 
		into it.

	3. 	create a chunk with placeholders with the field names you want to get replaced by database values.  
		
		for example: lets say I have a database table with the fields itemDesc, imgpath, price. 
		I will create the following chunk and call it 'displayProducts':

		<div class="product_block">
		   <div  class="product_desc">
		         [+itemDesc+]<br/><br/>
		   </div>
		   <div class="product_details">
		        <div style="position: relative; top: 60px;">price : [+price+]</div>
		        <div style="float:right;"><img   src="[+imgpath+]"  width="60" height="60"></img></div>
		   </div>
		</div>
		
		In addition to field names from the database, a special placeholder named 'lo_ord' can be used, 
		which will be replaced with the 'current' row number. 
		
		note: placeholders names must match 
				the field names from the chosen table. 

	4. call the snippet from your page/template.
		    
      parameters
      -----------

         tableName	- 	the relevant table name (required, unless 'sql' is defined)
 	 		
 			chunkName 	- 	the name of the chunk to format the data (required)
 			
 			orderby 		- 	a database field name to order the results by.
 								add 'desc' to get the results in a reverse order.	
 	 		
 			sql			-	a custom sql query.
                        replace '=' with '_eq_'. for example:
                        "select * from adress where client_id _eq_ 101"
 								when this parameter is set, the 'orderby' 
 								parameters will be ignored.
 								if this parameter is not set, the query will select all the columns
 								from the given table.  
  			
 			missingValTxt 	- 	an optional string to display when a database value
 								is missing (defaults to 'Not Available').	
 			
 			perPage		-  the number of items per page. (default: 0 - no pagination)
 			
 			numLinks		-  the number of page-number links shown from each side
 								of the current page number. (	default: 3)	
 								
         header      -  text to be put before the loop
 
         footer      -  text to put after the loop

	example: 
	[!loopDbChunk? &tableName=`products` &chunkName=`displayProducts` &perPage=`3` &orderby=`price desc` &missingValTxt=`--`!]		
		
   5. if pagination is required (perPage > 0), pagination links will be attached
      to the bottom of the chunk. 
      the pagination links will be located inside a div with the class name
      'ldb_pagination'.
  
	6. (optional)
		in order to format/transform the database values, add a file named 
		transformFuncs.php to the snippets/loopDbChunk directory.
		this file should contain a list of 'transform' functions. 
		
      in the chunk put:
      [+t:func_name:field1:field2+] 

      where func_name is a function defined in transformFuncs.php, 
      field1, field2, etc. are the parameters for this function. 
      Each of these parameters can be in one of 3 forms:
      a. field name from the database table.
      b. a literal (interperted as a string)
      c. a custom tag - 'lo_ord' which will be replaced with the 'current' record number
      the parameter count must match the actual number of the function parameters.

		An example for this file's contents:
	
	<?php

		function fixImgPath($imgPath) {
			return('assets/snippets/loopDbChunk/img/' . $imgPath);
		}
		
	?>

  
   and inside the chunk call it like this:
   
   <img src="[+t:fixImgPath:image+]" /> 

   where 'image' is a field in the table being queried  
	
