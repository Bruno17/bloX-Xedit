//<![CDATA[
//window.addEvent('domready',function(){
	onDOMReady(function(){
    var tp = docSettings.addTabPage( $('tabmchunks') );
    $('childPane').adopt('tabmchunks');
    //top.mainMenu.reloadtree(); 
	//docSettings.setSelectedIndex(tp.index);
    //if( $E('div.ch_messages') !== null ){
    //    $E('div.ch_messages').injectAfter( $E('div.subTitle') );
    //}
	
	//This is the function that will run every time a new item is added or the 
	//list is sorted.
	var showNewOrder = function() {
		//This function means we get serialize() to tell us the text of each 
		//element, instead of its ID, which is the default return.
		var serializeFunction = function(el) { return el.get('text'); };
		//We pass our custom function to serialize();
		var orderTxt = sort.serialize(serializeFunction);
		//And then we add that text to our page so everyone can see it.
		$('data').set('text', orderTxt.join(' '));
	};
	
    var togglePublished = function(el){
        var inp = el.getElement('.input_published');
       
        if (inp.get('value') == '1') {
            inp.set('value', '0');
            el.removeClass('published_1');
            el.addClass('published_0');
        }
        else {
            inp.set('value', '1');
            el.removeClass('published_0');
            el.addClass('published_1');
        }
        
    };
    var toggleDeleted = function(el){
        var inp = el.getElement('.input_deleted');
       
        if (inp.get('value') == '1') {
            inp.set('value', '0');
            el.removeClass('deleted_1');
            el.addClass('deleted_0');
        }
        else {
            inp.set('value', '1');
            el.removeClass('deleted_0');
            el.addClass('deleted_1');
        }
        
    };	

	$$('.chunklist .sectionHeader').each(function(el){  

        var li_remove = new Element('a', {
            'href': '#',
            'class': 'remove_li' ,
            'html': 'Remove',
            'events': {
                'click': function(){
                    //sort.removeItems(el.getParent()).destroy();
                    //showNewOrder();
					toggleDeleted(el);
                }
            }
        });

        var li_unpublish = new Element('a', {
            'href': '#',
            'class': 'unpublish_li' ,
            'html': 'Unpublish',
            'events': {
                'click': function(){
                    //sort.removeItems(el.getParent()).destroy();
                    //showNewOrder();
					togglePublished(el);
                }
            }
        });
     
	 li_remove.inject(el);
	 li_unpublish.inject(el);

     });  

	//This code initalizes the sortable list.
	var chunksort=$$('.chunklist');
	var sort = new Sortables(chunksort, {
		handle: '.drag-handle',
		//This will constrain the list items to the list.
		constrain: true,
		//We'll get to see a nice cloned element when we drag.
		clone: true,
		opacity:0.7,
		//This function will happen when the user 'drops' an item in a new place.
		//onComplete: showNewOrder
	});
   
   var i=1;
   
    $$('#mTv-tab-body .sectionBody').each(function(el){

		var containerkey = el.getElement('.container_key').get('value');
		var add_chunkname = el.getElement('.add_chunkname');
		var chunklist = el.getElement('.chunklist');
		//el.set('html',containerkey);
		el.getElement('.add_chunk').addEvent('click', function(e) {
		e.stop();
		//Get the value of the text input.
		var val = add_chunkname.get('value');
		//The code here will execute if the input is empty.
		if (!val) {
			$('add_chunkname').highlight('#f00').focus();	
			return; //Return will skip the rest of the code in the function. 
		}
		//Create a new <li> to hold all our content.
		var li = new Element('li');
		//This handle element will serve as the point where the user 'picks up'
		var section = new Element('div', {id: 'item-'+i, 'class':'sectionHeader'});
		section.inject(li, 'top');
		//the draggable element.
		var handle = new Element('div', {
			id:'handle-'+i, 
			'class':'drag-handle'});
		
		//Set the value of the form to '', since we've added its value to the <li>.
		//$('add_chunkname').set('value', '');

        var li_caption = new Element('p', {
            text: val
        });

        var li_input_name = new Element('input', {
			'class': 'input_chunkname',
            'name': 'mChunk_name_'+containerkey+'[]',
            'value': val,
            'type': 'hidden'
        });
        var li_input_published = new Element('input', {
			'class': 'input_published',
            'name': 'mChunk_published_'+containerkey+'[]',
            'value': '1',
            'type': 'hidden'
        });
        var li_input_deleted = new Element('input', {
			'class': 'input_deleted',
            'name': 'mChunk_deleted_'+containerkey+'[]',
            'value': '0',
            'type': 'hidden'
        });				
		
		var li_input_id = new Element('input', {
			'class': 'input_id',
            'name': 'mChunk_docid_'+containerkey+'[]',
            'value': 'new',
            'type': 'hidden'
        });
		var li_input_caption = new Element('input', {
			'class': 'input_caption',
            'name': 'mChunk_caption_'+containerkey+'[]',
            'value': 'new caption',
            'type': 'text'
        });		

        var li_remove = new Element('a', {
            'href': '#',
            'class': 'remove_li',
            'html': 'Remove',
            'events': {
                'click': function(){
                    //sort.removeItems(li).destroy();
                    //showNewOrder();
					toggleDeleted(this.getParent());
                }
            }
        });

        var li_unpublish = new Element('a', {
            'href': '#',
            'class': 'unpublish_li',
            'html': 'Unpublish',
            'events': {
                'click': function(){
                    togglePublished(this.getParent());
                    //showNewOrder();
                }
            }
        });		

		handle.inject(section, 'top');
		li_input_caption.inject(section);
		li_caption.inject(section);
		li_input_id.inject(section);				
		li_input_name.inject(section);
		li_input_published.inject(section);
		li_input_deleted.inject(section);				
		li_remove.inject(section);
		li_unpublish.inject(section);
		
		
		//Add the <li> to our list.
		chunklist.adopt(li);
		//Do a fancy effect on the <li>.
		li.highlight();
		//We have to add the list item to our Sortable object so it's sortable.
		sort.addItems(li);
		//We put the new order inside of the data div.
		//showNewOrder();
		i++;
	});	
    }); 


	
})
//]]>