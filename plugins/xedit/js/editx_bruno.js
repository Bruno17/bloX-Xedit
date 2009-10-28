/*
Mif.Tree

//Mif noch umbenennen
*/
/**
 * @author marc
 */

var runfilemanager = function(el){
			/* Filemanager - Simple Example */
			var elid=el.get('id');
			var fieldtype=el.get('fieldtype');
			var baseURL = 'assets/';//modx-zugriff
			var startDir = '';
			var el = $(elid);
			//var ajax_url = front_ajax_url;
			/*
			var ajax_url = 'manager.php';
			var baseURL = './assets/';//manager.php-zugriff	
			*/
			var manager1 = new FileManager({
				url: ajax_url,
				assetBasePath: 'assets/plugins/xedit/inc/FileManager/Assets',
				directory: startDir,
				baseURL: baseURL,
				fieldtype: fieldtype,
				language: 'de',
				selectable:true,
				uploadAuthData: {session: sessionId},
				onComplete: function(path, file){
				el.set('value', baseURL+path);
				}
			});
			manager1.show();
			
			/* gallery-test
			var manager1 = new FileManager.Gallery({
				url: 'manager.php',
				assetBasePath: 'assets/plugins/xedit/inc/FileManager/Assets',
				directory: startDir,
				baseURL: baseURL,
				language: 'de',
				uploadAuthData: {session: 'MySessionId'},
				onShow: function(){
					var obj;
					$try(function(){ obj = JSON.decode(el.get('value')); });
					this.populate(obj);
				},
				onComplete: function(serialized){
					el.set('value', JSON.encode(serialized));
				}
			});			 
			 
			 */
			/* End Filemanager Examples */				
}

            var startXtools = function(){
                //console.log(doc_id);
				//var ajax_url=ajax_url;
				//Slimbox.scanPage();
	
				
				var unremoveables = $$('.unremoveable');
				var unfillables = $$('.fillable_0 , .xcc_bloxcontainer');
				var containers = $$('.bloxcontainer , .xcc_bloxcontainer');
             
				var rte=$('rte');
				if (rte) rte.destroy();
				
				mte.initialize('.xedit', {
                    defaults: ['bold,italic,underline,justifyleft,justifycenter,justifycenter,insertorderedlist,insertunorderedlist'],
                    location: 'pageTop',
                    floating: true
                });	                
              
                // ... bis hier baut das Snippet den JS-Teil zusammen und pflanzt ihn in die Seite. 
                xtoolsStart.initialize();
                mySortables.initialize(containers, {
                    // die Container müssen noch durch das Snippet belegt werden. Denke da an regClientScript oder wie das heißt.
                    // Die anderen Optionen kommen ja auch vom Snippet. Also von hier ....
                    // von Bruno: ich denke, die Optionen sollten vom js eingelesen werden, 
                    // nicht das js im snippet zusammenbauen, oder? 
					unfillables: unfillables,
                    unremoveables: unremoveables,
                    revert: true,
                    clone: true,
                    constrain: false,
                    opacity: .5,
                    handle: 'span.drag',
                    onStart: function(el){
                        //passes element you are dragging
                        el.highlight('#F3F865');
                    },
                    onSort: function(el){
                        el.highlight('#F3F865');
                    },
					onComplete: function(el){
     						xtoolsStart.prepareSortable(el);
							//xtoolsStart.initialize();
					},
                    
                    revertOptions: {
                        duration: 1000,
                        transition: Fx.Transitions.Elastic.easeOut
                    }
                });
                
                // var multichunkStart = new multichunks();	
                
                // Der Button-Kram sollte auch in eine eigene Klasse. Stefan?
                var buttons = $$('.xcc_button');
                
			    
				buttons.each(function(button){
                    
			var xtoolsFx = new Fx.Morph(button.getElement('.xtools'), {
						duration: 500,
						transition: Fx.Transitions.Sine.easeOut
					});
					button.removeEvents('mouseenter');
					button.removeEvents('mouseleave');
					button.addEvents({
						mouseenter: function(){
							xtoolsFx.cancel();
							xtoolsFx.start({
								link:'cancel',
								'opacity': '1'
							})
						},
						mouseleave: function(){
							xtoolsFx.cancel();
							xtoolsFx.start({
								link:'cancel',
								'opacity': '0'
							})
						}
					});					
					
					
					button.removeEvents('click');
					button.addEvent('click', function(){
                        var blox = button.getElement('.blox');
                        var multinew = blox.clone();
                        var destination =$(button.getParent().get('container'));
						if (destination){
							multinew.inject(destination, 'top');
						}
						else{
							if (document.getElement('.fillable_1')){
								multinew.inject(document.getElement('.fillable_1'), 'top');
							}
						}
						
                        
						mySortables.addItems(multinew);
						
                        var myFx = new Fx.Scroll(window, {
                            offset: {
                                'x': -30,
                                'y': -30
                            }
                        }).toElement(multinew);
						
						multinew.setOpacity(0);
                        var multinewFx = new Fx.Morph(multinew, {
                            duration: 1000,
                            transition: Fx.Transitions.Sine.easeOut
                        });
                        multinew.setStyle('display', 'block');
						
                        multinewFx.start({
                            opacity: 1
                        });
                        xtoolsStart.prepareSortable(multinew);
						//xtoolsStart.initialize();
                        var lists = [];
                        lists.push(multinew.getParent());	
                        mySortables.checkChildCounts(lists);							
                     });
                });
                /*
				$('rte').set({
                    'display': 'block',
                    'opacity': 1
                });
                */
                // Test mal hier mit Speichern Button
				$('saveall').removeEvents('click');
                $('saveall').addEvent('click', function(e){
				 new Event(e).stop();	
                 brunoclass.saveall('all')
				 
                 }
				);
                // nur Blox-Editarea speichern
				$('xcc_edit_save').removeEvents('click');
                $('xcc_edit_save').addEvent('click', function(e){
				 new Event(e).stop();	
                 brunoclass.saveall('xcc_edit')
				 
                 }
				);				
        containers = $$('.bloxcontainer');
        var theDump = brunoclass.collectContainers('reset',containers);				

            }



if(!Mif) var Mif={};

Mif.brunoclass = new Class({

    Implements: [new Options],
    
    initialize: function(options){
        this.setOptions(options);
        this.ajax_url = ajax_url;
		this.doc_id = doc_id;
        var brunoclass = this;
		var level1 = $$('.xcc_level1');
        level1.each(function(el, i){
            if (el.hasClass('xcc_area_blox_edit')) {
                brunoclass.bloxTab = el;
            }
        })
		this.edited_blox=0;
		this.coll_containers=[];
		this.sexy_contents={
			'all':'<h1>Alles Speichern?</h1><p>Sind Sie sicher, dass Sie die komplette Seite so speichern wollen?</p>',
		    'xcc_edit':'<h1>Formular speichern?</h1><p>Es wird nur das Formular in die Datenbank gespeichert, die Ansicht wird nicht aktualisiert. Ausf&uuml;hren?</p>'};
		//console.log(this.ajax_url);
    },
    
    collectContainers: function(action,items,chunks){
        var serial = [];
		var brunoclass = this;
		var chunks = chunks;
        this.postfields = '';
        items.each(function(el, i){
            var saveable=el.get('saveable');
			if (saveable !== '0'){
			//var sortby = el.get('sortby')||'0';
			col_chunks = chunks||el.getElements('.blox');
            var containerid = el.id;
			var resourceClass = el.get('resourceclass')||'modDocument';
			var tablename = el.get('tablename')||'';
            var c_props = {
                containerid: containerid,
                c_parentid: el.get('c_parentid'),
                documentsTv: el.get('documentsTv')||'0',
				orderByField: el.get('orderByField')||'0',
				filterByField: el.get('filterByField')||'0',
				filterValue: el.get('filterValue')||'0',
				sender_id: el.get('sender_id'),
				c_type: el.get('c_type'),
				c_resourceclass: resourceClass,
				c_tablename: tablename                
            };
			el.store('c_props',c_props);
			serial[i] = $merge(c_props, {chunks: brunoclass.collectChunks(action, col_chunks, containerid, resourceClass, tablename)});
							
			}
        });
        return serial;
    },
    collectChunks: function(action, items, containerid, resourceClass, tablename){
        var serial = [];
        var brunoclass = this;
		var tablename = tablename;
		var resourceClass = resourceClass;
        items.each(function(el, i){
            var editdiv = el;
            var rowid = el.get('rowid');
			var savemode = el.get('savemode')
			resourceClass = el.get('resourceclass')||resourceClass;
			tablename = el.get('tablename')||tablename;
			if (!savemode){
				savemode = 'default';
			}
			var fields = brunoclass.collectFields(action, el, editdiv, i, containerid, rowid, resourceClass, tablename);
			var chunk_props = {
                rowid: rowid,
				modified : (brunoclass.editingElement == el)?'no':el.get('modified'),
				savemode : savemode,
				tpl: el.get('tpl')||'0',
                chunkname: el.get('chunkname'),
                xedit_tabs: el.get('xedit_tabs'),
                parent: el.get('parent'),
                published: el.get('published'),
				resourceclass: resourceClass,
				tablename: tablename				
            };
            el.store('properties',chunk_props); 
            serial[i] = $merge(chunk_props,{fields: fields});

        });
        return serial;
    },
    collectFields: function(action, el, editdiv, sort, containerid, parent_rowid, resourceClass, tablename){
        //values in postfields merken und als eigene, eindeutig erkennbare post-variablen mitsenden 
        //wegen problemen beim decoden auf php-seite
        var parent_rowid = parent_rowid;
		var serial = [];
		var fields = [];
		var editspans = editdiv.getElements('.xedit, .xedit_input');
		var tablename = tablename;
		var resourceClass = resourceClass;
		var postfields = '';		
        editspans.each(function(el, i){
            //var rowid = el.get('rowid');
			tablename = el.get('tablename')||tablename;
			resourceClass = el.get('resourceclass')||resourceClass;
			rowid = el.get('rowid')||parent_rowid;
			var tablesuffix=(resourceClass == 'modTable')?'_' +tablename + '_' + rowid:'';
            var fieldname = el.get('fieldname');
            var postname = fieldname + '_' + containerid + '_' + sort + '_' + parent_rowid + tablesuffix;
            var postvalue = brunoclass.getXeditValue(el);
			
			postvalue = encodeURIComponent(postvalue);	
            serial[i] = {
                fieldname: fieldname,
                postname: postname,
				resourceClass : resourceClass,
                tablename: tablename,
                rowid: rowid			
             };
            fields[i] = {
                fieldname: fieldname,
 				resourceClass : resourceClass,
                tablename: tablename,
                rowid: rowid,
				value: postvalue
            };
			postfields = postfields + '&' + postname + '=' + postvalue;
        });
   			var modified = 'yes';
			var jsonFields = JSON.encode(fields);
			var oldfields = el.retrieve('fields');
			if (action == 'reset'){
			    el.store('fields',jsonFields);
				modified = 'no';
			}else{
			if (jsonFields == oldfields && action == 'saveblox'){
                modified = 'no';
				serial = null;				
			}else
			{
			    modified = 'yes';
				brunoclass.postfields = brunoclass.postfields + postfields;
			}				
			} 
            el.set('modified',modified);		
        return serial;
    },
    collectInputs: function(inputs, tablename, resourceClass, parent_rowid){
		var fields = [];
		var postnames = [];
		var tablesuffix='';
        var fieldname = '';
        var postname = '';
					
		inputs.each(function(el, i){

            if (el.hasClass('richtext')){
			var editor=el.get('id');
			var editor_data = CKEDITOR.instances[editor].getData();	
			el.set('value',editor_data);			
			}


			
			fieldtablename = el.get('tablename')||tablename;
			fieldresourceClass = el.get('resourceclass')||resourceClass;
			rowid = el.get('rowid')||parent_rowid;
			tablesuffix=(fieldresourceClass == 'modTable')?'_' +fieldtablename + '_' + rowid:'';
            fieldname = el.get('fieldname');
            postname = el.get('name');
            
            if (!postnames.contains(postname)) {
                postnames.push(postname);
                fields[i] = {
                    fieldname: fieldname,
                    postname: postname,
                    resourceClass: fieldresourceClass,
                    tablename: fieldtablename,
                    rowid: rowid
                };
            }

			//postfields = postfields + '&' + postname + '=' + postvalue;
        });
	
        return fields;
    },
	
	getXeditValue: function(el){
        if (el.hasClass('xedit_input')||el.hasClass('bloxinput')) {
            var input = el.getElement('select')||el.getElement('input')||null;
            //Todo: hier noch dran arbeiten
			if (input.get('type') == 'checkbox'){
				return input.checked?input.get('value'):null;
			}
			return input.get('value');
        }
        else {
            return brunoclass.removeThumbPrefix(el);
        }
		//return postvalue;
	},
    saveall: function(mode){
    
        //sende Formulardaten
        var brunoclass=this;
		var xcc_container = $('xcc');
		//var level1 = $$('.xcc_level1');	
        Sexy.confirm(this.sexy_contents[mode], {
            textBoxBtnOk: 'Ja!',
            textBoxBtnCancel: 'Nein!',
            onComplete: function(returnvalue){
                if (returnvalue) {
                    form = $('ajax');
                    if (form) {
                        notification.alert('Blox - Formular senden');
                        var erfolg = brunoclass.sendBloxForm(form,mode);
                        // Alles speichern, Bearbeitungsblock leeren und zusammenfahren. 
						brunoclass.bloxTab.morph({
                            opacity: 0,
                            onComplete: function(){
                                //$('xcc_area_blox_edit').getElement('div.xcc_area_inner').set('html',''); // mal auf die Schnelle
                                brunoclass.remove_xcc_area_blox_edit();
                            }
                        })
                        xcc_container.morph({
                            height: '60px'
                        })
                        xcc.initialize; // zurück auf los.                         
                    }
					else {
						brunoclass.edited_blox=0;
						brunoclass.sendDocForm(mode)
						//brunoclass.saveBlox(brunoclass);
						
					}
                }
                else {
                    // nix tun, weiter gehts
                    // notification.alert('Erfolgreich!','Block wurde gespeichert');
                }
            }
        });
        
        //brunoclass.sendForm(form,sort,brunoclass);
    
    
    },

    sendBloxForm: function(form,mode){
    
        //Empty the log and show the spinning indicator.
        //var log = $('message').addClass('ajax-loading');
        var editingElement = this.editingElement;
        //var rowid=this.editingElement.get('rowid');
        //var samedocids = $(document.body).getElements('.blox[rowid='+rowid+']');
        
        //Set the options of the form's Request handler. 
        //("this" refers to the $('myForm') element).
        var container = editingElement.getParent();
		var c_props = container.retrieve('c_props');
		var chunk_props = editingElement.retrieve('properties');
        //chunk_props.set('modified','yes');
 
        var resourceClass = editingElement.get('resourceclass') || 'modDocument';
        var tablename = editingElement.get('tablename') || '';
		var rowid = editingElement.get('rowid') || 'new';
        var coll_inputs = this.collectInputs($$('.bloxinput'), tablename, resourceClass, rowid);
        
		coll_inputs = $merge(chunk_props,{'modified':'yes'}, {fields: coll_inputs});
		coll_inputs = $merge(c_props, {chunks: [coll_inputs]});
		coll_inputs = JSON.encode([coll_inputs]);
        var input = new Element('input', {
            id: 'coll_container',
            'type': 'hidden',
            'name': 'coll_container',
            'value': coll_inputs
        });
        input.inject($('ajax'), 'top');
        form.set('send', {
            method: 'post',
            onComplete: function(response){
                //log.removeClass('ajax-loading');
                //$('message').set('html', response);
                editingElement.set('savemode', 'move');
                
                var ajax_response = new Element('div', {
                    id: 'ajax_response',
                    'style': 'display:none'
                });
                ajax_response.inject(document.body, 'bottom').set('html', response);
                var ajax_response = $('ajax_response');
                var new_doc_id = $('response_rowid').get('text');
                ajax_response.destroy();
                
                if (new_doc_id) {
                    editingElement.set('rowid', new_doc_id);
					editingElement.addClass('dirty');
                }
                /*
                 if (samedocids && rowid !== 'new'){
                 samedocids.set('modified', 'no');
                 }
                 else{
                 editingElement.set('modified', 'no');
                 }
                 */
                
                if (mode=='all'){
					notification.alert('Blox - Formular wurde gespeichert', 'Sende Doc - Formular');
					brunoclass.sendDocForm(mode)
				}
				
                //brunoclass.saveBlox(brunoclass);				
            }
        });
        //Send the form.
        form.send();
        
    },

    sendDocForm: function(mode){
        var brunoclass=this;
        var form = $('document_form');
        
        if (form) {
            //Empty the log and show the spinning indicator.
            //var log = $('message').addClass('ajax-loading');
            var editingElement = this.editingElement;
			var resourceClass = "modDocument";
			var tablename = '';
			var rowid = this.doc_id;
			var coll_inputs = this.collectInputs($$('.docinput'), tablename, resourceClass, rowid);
			var chunk_props = {
				"rowid" : rowid,
				"modified":"yes",
				"resourceclass" : resourceClass,
				"fields":coll_inputs}
            var c_props = {
				"sender_id":this.doc_id,
				"c_type":"container",
				"c_resourceclass":"modDocument",
				"chunks":[chunk_props]};
            c_props = JSON.encode([c_props]);
            var input = $('doc_coll_container');
            
            if (input) {
                input.set('value', c_props);
            }
            else {
                input = new Element('input', {
                    id: 'doc_coll_container',
                    'type': 'hidden',
                    'name': 'coll_container',
                    'value': c_props
                });
                input.inject(form, 'top');
            }

            
            //Set the options of the form's Request handler. 
            //("this" refers to the $('myForm') element).
            form.set('send', {
                onComplete: function(response){
                    //log.removeClass('ajax-loading');
                    //$('message').set('html', response);
                    notification.alert('Formular wurde gespeichert', 'Sende die komplette Seite');
                    brunoclass.saveBlox();
                }
            });
            //Send the form.
            form.send();
        }
        else {
            brunoclass.saveBlox();
        }
        
    },
    saveBlox: function(){
        var brunoclass=this;
        var containers = $$('.bloxcontainer');
        var theDump = brunoclass.collectContainers('saveblox',containers);
        var brunoarrayJSON = JSON.encode(theDump);
        //var ajax_url="index.php?id=54";
        //console.log(brunoarrayJSON);
        //console.log(brunoclass.postfields);
		//console.log('url:'+brunoclass.ajax_url);
        var req = new Request({
            method: 'post',
            //url: "index.php?id=54",
            url: brunoclass.ajax_url,
            onRequest: function(){
                //$('message').toggleClass('ml_ajax_wait');
            },
            onComplete: function(responseText){
                //var log = $('message');
                //log.removeClass('ml_ajax_wait');
				notification.alert('Seite wurde gespeichert','Lade neuen Seiteninhalt');
                brunoclass.reload_wrapper(responseText)
             
            },
            onSuccess: function(responseText){
                //$('message').set('html', responseText);
           
            },
            onFailure: function(placeholder_translation){
                //console.log('klappt nciht');
            }
        }).send(brunoclass.postfields + '&editx=' + brunoarrayJSON);
    },
    refresh_wrapper: function(responseText){
        var brunoclass=this; 
        var ajax_response = new Element('div', {
            id: 'ajax_response',
            'style': 'display:none'
        });
		$('wrapper').set('id','old_wrapper');
        ajax_response.inject(document.body,'bottom').set('html', responseText);
		var ajax_response = $('ajax_response');
        var response_wrapper = ajax_response.getElement('#wrapper');
        response_wrapper.set('id', 'response_wrapper');
		$('old_wrapper').set('id','wrapper');
        $('wrapper').set('html', response_wrapper.get('html'));
        //response_wrapper.dispose();
		ajax_response.destroy();
        //log.empty();
        startXtools(brunoclass.ajax_url,brunoclass.doc_id);
    },
    reload_wrapper: function(responseText){
        //brunoclass.docid = '160';
		var brunoclass=this;
		var edited=(this.edited_blox!==0)?'&edited_blox='+this.edited_blox:'';
		var req = new Request({
            method: 'post',
            url: brunoclass.ajax_url,
            onRequest: function(){
                //$('message').toggleClass('ml_ajax_wait');
            },
            onComplete: function(responseText){
                //var log = $('message');
                //log.removeClass('ml_ajax_wait');
				notification.alert('Seiteninhalt wurde geladen');
                brunoclass.refresh_wrapper(responseText)
				brunoclass.edited_blox=0;
            },
            onSuccess: function(responseText){
                //$('message').set('html', responseText);
            },
            onFailure: function(placeholder_translation){
                //console.log('klappt nicht');
            }
        }).send('reload=wrapper&docid='+brunoclass.doc_id+edited +'&docurl='+encodeURIComponent(document.URL));

    },

    loadTvTabs: function(sort){
        var rowid = sort.get('rowid');
		var tablename = sort.get('tablename')||'';
		var savemode = sort.get('savemode');
        var chunkname = sort.get('chunkname');
        var xedit_tabs = sort.get('xedit_tabs');
		var tpl = sort.get('tpl');
        var container = sort.getParent();
        var containertyp = container.get('c_type');
        var containerparent = container.get('c_parentid');
        var containerid = container.get('id');
		var brunoclass=this;
        this.editingElement=sort;
		this.edited_blox=rowid+':'+containerid;
			
		//this.postfields = '';
        var items = [];
        items.push(sort);
		//var fields=this.collectChunks(items, containerid);
		//fields=JSON.encode(fields);
        
        var containers = [];
        containers.push(container);		
		var containers=this.collectContainers('loadTvTabs',containers,items);
		this.coll_containers=JSON.encode(containers);
		// read querystring of savebutton
		var savebutton = sort.getElement('a.save');
		var queryfields='';
        if (savebutton) {
            queryfields = '&' + new URI(savebutton.get('href')).get('query')||queryfields;
        }

		
		//console.log(queryfields);
		//this.collectFields(editdiv, i, containerid, rowid);
		//var sort = sort;
		var xcc_container = $('xcc');
		//var published=sort.get('published');
        var req = new Request({
            method: 'post',
            url: brunoclass.ajax_url,
            onRequest: function(){
                //$('message').toggleClass('ml_ajax_wait');
            },
            onComplete: function(placeholder_translation){
                //$('message').removeClass('ml_ajax_wait');
                
            },
            onSuccess: function(responseText){
               
			    brunoclass.insertTvTabs(responseText);
				
            },
            onFailure: function(placeholder_translation){
                //console.log('klappt nicht');
            }
        }).send('get_tv_tabs=yes' + '&savemode=' + savemode + '&xedit_tabs=' + xedit_tabs + '&chunkname=' + chunkname + '&containertyp=' + containertyp+ '&tablename=' + tablename+ '&rowid=' + rowid + '&containerparent=' + containerparent + '&containerid=' + containerid + '&tpl=' + tpl + '&containers=' + this.coll_containers +this.postfields+queryfields);
        
        //console.log( sort.get('html') );
        //console.log( $('blox_29') );		
    
    },
    insertTvTabs: function(responseText){

				var editspans = this.editingElement.getElements('.xedit, .xedit_input');
				var blox_area_edit=$('xcc_blox_block_edit');
				if (blox_area_edit){
					blox_area_edit.dispose();
				}
			    
			    //marc
				var blox_area_edit = new Element('div').set('id','xcc_blox_block_edit');	// Tab Container für Chunkbearbeitung bauen
				var area_inner = $('xcc_area_blox_edit').getElement('div.xcc_area_inner');	// Ziel wählen
				blox_area_edit.inject($(area_inner,'top'));									// Nei mit dem Container....
				blox_area_edit.set('html',responseText);					   
 
				var tabs3 = new SimpleTabs(blox_area_edit.getElement('.tab_block'), { 						// Tabs einrichten
					selector: 'h4'
				});	
				var resize = new calc();
				var areas = $$('.xcc_area');
				resize.hide(areas);
				$('xcc_area_blox_edit').setStyle('display','block');						// (Verstecktes) Anzeigen um Höhe zu bestimmen
				var hoehe = resize.getheight($('xcc_area_blox_edit'));						// Höhe bestimmen
				//$('log').appendText(hoehe);

				var level1 = $$('.xcc_level1');
				this.bloxTab.setStyle('opacity',1);
				resize.toggleActive(level1,this.bloxTab);										// Aktiv setzen
				resize.resizeContainer($('xcc'),hoehe + 58);										// Ausfahren
				$('xcc_area_blox_edit').morph({												// Einblenden
						opacity: 1
					})
				$('xcc_area_blox_edit').addClass('active');
				$('xcc_area_blox_edit').store('height',hoehe);									// Aktiv setzen					
				var cookie = Cookie.write('xcc_area_blox_edit', hoehe, {duration: 30});
				this.bloxTab.morph({
				'width': 200, 																// Menüpunkt auf Breite ausfahren. Da innerwidth nicht aufrufbar (steckt ja im anderen Array, das mir hier zu blöd ist neu zu bauen. Wenn man das allerdings irgendwie "global" verfügbar machen kann, wäre das für viele Sachen gut...
				'background-position': '0px 0px'
				});
				$('minimize').morph({														// Minimier-Button einblenden
						opacity: 1
					}).addClass('minimize').removeClass('maximize');
						
                //ende marc
                /* 
			    var inp_chunkname = $('tv_input_chunkname');
                if (inp_chunkname) {
                    inp_chunkname.set('value', $('input_chunkname').get('value'));
                }
                */
                var bloxform = $('ajax');
				/*
				bloxform.store('containerid',containerid);
				bloxform.store('c_parentid',containerparent); 
				*/
                //hole evtl. geänderte Daten in inputs 
                /* ist das unter Umständen sinnvoll? 
                 * bei snippetaufrufen z.B. ists Panne, da dann der geparste Inhalt geladen wird
                 * manuell nachladbar machen, oder manuell den gespeicherten Inhalt ladbar machen?
                 * toggle zwischen gespeichertem und aktuellem Inhalt??
                 * oder Inhalte wie snippet-aufrufe, chunks usw dürfen einfach nicht in inline-bearbeitbare Bereiche!!
                 * wär ja auch quatsch, oder?
                 */
                editspans.each(function(el, i){ 
                    var tablename=el.get('tablename');
					var fieldname=el.get('fieldname');
					if (tablename){
						var rowid = el.get('rowid')
						prefix=tablename+'_'+rowid;
					}
					else{
						prefix='tv';
					}
					
					var inputid = prefix+'_input_' + fieldname;
					var input = $(inputid);
                    //var postname = fieldname + '_' + containerid + '_' + sort + '_' + rowid;
                     

					//directresize-div untersuchen auf das eigentliche img ungewollten Kram rausfummeln 
					//direct-resize ausschalten in der xedit KLasse ist gscheiter.
					//aber wir könnten hier schonmal das tn_ von den thumbs entfernen
					//und falls kein style="with vorhanden eins für DR einpflanzen"                    
                   var postvalue = brunoclass.getXeditValue(el);
                   /*
					var clone = el.clone();  
                   var imgs = clone.getElements('img'); 
         			if (imgs) {
						imgs.each(function(img, i){
							var source = img.get('src');
							source = source.replace('tn_', '');
							img.set('src',source);
							
						});
					}
					*/                    
					/*
					var drimgs = clone.getElements('.dr-image'); 
         			if (drimgs){
					drimgs.each(function(el, i){
						var img = el.getElement('img');
						if (img){
						el.set('html','');
						img.inject(el);								
						}

					});
					}
                    */
					//var postvalue = clone.get('html');		
					
                    if (input) {
                        input.set('value', postvalue);
                    }
                    else {
                        //hidden-inputs mit Werten aus Inhalt erstellen	
                        var input = new Element('input', {
                            id: inputid,
							'type' : 'hidden', 
                            'name': inputid,
							'value': postvalue,
							'tablename':tablename,
							'fieldname':fieldname,
							'rowid':rowid,
							'class':'bloxinput'
                        });
                        input.inject(bloxform,'top');
                    }
                   
                });

				var editor = null;
				var inputs=$$('.bloxinput');
				inputs.each(function(el, i){ 
				
				if (el.hasClass('richtext')){
					editor=CKEDITOR.instances[el.get('id')];
                    
					if (editor){
						CKEDITOR.remove(el);
					}
					CKEDITOR.replace( el );	
					
					
				}

                //date-picker               
                if (el.hasClass('fdp')) {
                    var dpid = el.get('id');
					var opts ={
                        formElements: JSON.decode('{"'+dpid+'":"d-dt-m-dt-y"}'),
						rangeLow:el.get('rangelow'),
						rangeHigh:el.get('rangehigh')
                    };

                    datePickerController.destroyDatePicker(dpid);
                    datePickerController.createDatePicker(opts);
   
                    
                }	
				
                //filemanager              
                
                if (el.hasClass('filemanager')) {
					var elid=el.get('id');
                    var button = $('button_'+elid);
                    //if (button) button.addEvent('click', manager1.show.bind(manager1));
                    if (button) 
                        button.removeEvents('click');
                    button.addEvent('click', function(e){
                        e.stop();
                        runfilemanager(el);
                        
                    });
                  
                }					
							
				});
		
				
				/*
                var input = new Element('input', {
                    id: 'coll_container',
                    'type': 'hidden',
                    'name': 'coll_container',
                    'value': brunoclass.coll_containers
                });
                input.inject($('ajax'), 'top');
			    */		
		
		
		
		
	},
    remove_xcc_area_blox_edit: function(reset_ed_el){
        // editarea entfernen, saveall-button neu belegen
		if (reset_ed_el) brunoclass.editingElement=null;
		var blox_area_edit = $('xcc_blox_block_edit');
        if (blox_area_edit) {
            blox_area_edit.dispose();
        }
    },
    removeThumbPrefix: function(el){
        var clone = el.clone();
        var imgs = clone.getElements('img');
        if (imgs) {
            imgs.each(function(img, i){
                var source = img.get('src');
                source = source.replace('tn_', '');
                img.set('src', source);
                
            });
        }
        return clone.get('html');
    }
    
});


Mif.xtools = new Class({
    Implements: [new Options],
    
    options: {
        duration: '1000',
        trash_class: '.xtrash',
        save_class: '.save',
        drag_class: 'span.drag',
        sort_class: '.blox',
        remove_class: '.remove',
        tools_class: '.xtools'
    },
    initialize: function(options){ // Hier muss man auch mal aufräumen. Die Klasse sollte nur das beinhalten was wirklich gebraucht wird.
        this.setOptions(options);
        var xtools = $$(this.options.tools_class);
        xtools.each(function(xtool){
            xtool.setOpacity(0);
            
        })
        
        var sortables = $$(this.options.sort_class);
        var xtools = this;
        sortables.each(function(sort, i){ // das sind halt mal pauschal alle.
            xtools.prepareSortable(sort);
            
        });
    },
	disposeSortable:function(){
    var lists = [];
    lists.push(this.getParent());	
	this.dispose();
    mySortables.checkChildCounts(lists);		
	},
    prepareSortable: function(sort){
        var xtools = this;
		var sortFx = new Fx.Morph(sort, {
            duration: 1000,
            transition: Fx.Transitions.Sine.easeOut
        });
        var xremove = sort.getElement(this.options.remove_class);
        if (xremove) {
            xremove.removeEvents('click'); 
            xremove.addEvents({
                click: function(e){
                    e.stop();
                    sortFx.start({
                        link: 'cancel',
                        'opacity': '0'
                    })
                    xtools.disposeSortable.delay(500, sort);

                }
            })
        }
        
        var xtrash = sort.getElement(this.options.trash_class);
        if (xtrash) {
			xtrash.removeEvents('click'); 
            xtrash.addEvents({
                click: function(e){
					e.stop();
                    var published = sort.get('published');
                    
                    if (published == '1') {
                        sort.set('published', '0');
                        sort.removeClass('published_1');
                        sort.addClass('published_0');
                    }
                    else {
                        sort.set('published', '1');
                        sort.removeClass('published_0');
                        sort.addClass('published_1');
                    }
                 }
            })
        }
        var xsave = sort.getElement(this.options.save_class);
        if (xsave) {
            xsave.removeEvents('click');
            xsave.addEvents({
                click: function(e){
					e.stop();
                    sort.highlight('#F3F865');
                    brunoclass.loadTvTabs(sort);
                }
            })
        }
        var el_xtools = sort.getElement(this.options.tools_class)
        if (el_xtools) {
            var xtoolsFx = new Fx.Morph(el_xtools, {
                duration: 500,
                transition: Fx.Transitions.Sine.easeOut
            });
            sort.addEvents({
                mouseenter: function(){
                    xtoolsFx.cancel();
                    xtoolsFx.start({
                        link: 'cancel',
                        'opacity': '1'
                    })
                },
                mouseleave: function(){
                    xtoolsFx.cancel();
                    xtoolsFx.start({
                        link: 'cancel',
                        'opacity': '0'
                    })
                }
            });
        }
    }
})
