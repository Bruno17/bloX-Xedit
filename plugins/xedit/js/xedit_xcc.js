/**
 * @author marc
 */
		//window.addEvent('domready', function() {
		var startxcc = function(ajax_url,doc_id){
		var tabs1 = new SimpleTabs('xcc_blox_block', { 					// erst Tabs einrichten, damit die Höhe für das Auswählen stimmt
			selector: 'h4'
		});	

		var tabs2 = new SimpleTabs($('xcc_settings_block').getElement('.tab_block'), { 				// erst Tabs einrichten, damit die Höhe für das Auswählen stimmt
			selector: 'h4'
		});	

		//Bruno - hier noch ein extra div tab_block eingebaut sonst zerhauts das Formular
		
		var tabs3 = new SimpleTabs($('xcc_tvs_block').getElement('.tab_block'), { 					// erst Tabs einrichten, damit die Höhe für das Auswählen stimmt
			selector: 'h4'
		});	
		
		//Bruno - das anders lösen(alle brunoclass-funktionen in die Klasse xcc packen???)
        var brunoclass = new Mif.brunoclass({
            ajax_url: ajax_url,
            doc_id: doc_id
        });
													
		var xcc_start = new xcc({
            brunoclass: brunoclass
        });
		
		var rtAreas = $$('.richtext');	
		rtAreas.each(function(el,i){
			CKEDITOR.replace( el );
		});		
		
		

		
		};

		var notification = new Roar({
		position: 'lowerRight',
		duration: 4200 // 5 seconds until message fades out
	    });

		
var xcc = new Class({
	Implements: [Options],
	options:{

	},
	initialize: function(options){  								// Das eingefahrene StandardPanel initialisieren
		this.setOptions(options);
		var xcc_container = $('xcc');
		var level1 = $$('.xcc_level1');								// Alle Level1 Menüpunkte
		var areas = $$('.xcc_area');								// Alle Areas. Sinnvoll ist, dass gleich alle angelegt sind, wenn auch leer zur Not.
        var brunoclass = options.brunoclass;
		var collectAreas = new calc();
		area_ids = new Array();
		area_heights = new Array();
		var allAreasHeight = collectAreas.getAllAreas(areas);		// Alle Höhen der Areas speichern
		// alert(allAreas['xcc_settings_area']); 					// GEIL! klappt.
		collectAreas.hide(areas); 									// Initial sind alle zu. Vielleicht kann/sollte man den Initial-Kram auch noch auslagern. Stefan?
		el_innerWidth = Array(); 									// Array, um die Breiten der inneren Elemente des ersten Levels zu sammeln.
		//active_area = 0;
        active_area = false;		
		/* Menüfunktionen erste Ebene */
		level1.each(function(el,i){
			el.setStyle('opacity',0);
			if (el.hasClass('xcc_level1_active')){
				var area_active = areas[i];							// Korrespondierende aktive Area 
			}
			el_inner = el.getChildren('div.level1_inner')[0]; 		// Inneres Element			
			el_innerWidth[i] = el_inner.getSize().x;         	 	// Breite inneres Element
			if(! el.hasClass('xcc_level1_active')){                 // wenn nicht aktiv zusammenschieben auf Icon
			el.setStyles({
				'width': '45px'
			})
			}
			var elFx = new Fx.Morph(el, {duration: 300, transition: Fx.Transitions.Sine.easeOut});
			el.addEvents({
				mouseenter: function(){				
					elFx.cancel();
					elFx.start({
						'width': el_innerWidth[i], 					// Mouseover auf Breite ausfahren
						'background-position': '0px 0px'
					});
				},
				mouseleave: function(){
					elFx.cancel();
					if (!el.hasClass('xcc_level1_active')) {
						elFx.start.delay(300, elFx, { 				// Mouseout wieder zusammenfahren
							'width': '45px',
							'background-position': '0px 60px'
						}); 
					}
				},
				click: function(e){	
				    e.stop();								// nicht vergessen: Wenn der User was editiert hat, ne Abfrage einblenden ob er wirklich wechseln will. Obwohl, die Eingaben bleiben ja in der Area. Hmmm...
					collectAreas.toggleActive(level1,el);           // Alle Menüpunkte inaktiv setzen, aktuellen aktiv
					var area_id = areas[i].get('id'); 				// ID der korrespondierenden Area
					//var area_height = Cookie.read(area_id); 
					var area_height = $(area_id).retrieve('height') ; 	// Auslesen des Cookies (falls Tab-Höhe geändert wurde, siehe Tab-Script
					areas.each(function(area,u){	       			// Areas durchlaufen und die (bisher) aktive ausblenden.
					if (area.hasClass('active')){                   // Bruno: Reihenfolge vertauscht, da sonst das aktive ausgeblendet, wenn erneut geklickt
						area.removeClass('active');
						
						area.morph({
							opacity: 0,
							onComplete: function(){
								area.setStyles({
									'display': 'none'
								})
							}
						})
						
					}
					})					
					//area_height = allAreasHeight[area_id] + 72;
					if (!area_height) { area_height = allAreasHeight[area_id] ; }
					areas[i].setStyles({
						'display': 'block'
					})
					collectAreas.resizeContainer(xcc_container, area_height+ 72);		// Containerhöhe anpassen									
					areas[i].morph({
						display: 'block',
						opacity: 1
					})
                    /*
					if(active_area){
						active_area.morph({
							opacity: 0
                     */

					$('minimize').morph({
						opacity: 1
					}).addClass('minimize').removeClass('maximize');
					active_area = areas[i];							// aktivierte Area als active setzen
					areas[i].addClass('active');					
					}
			})
		})
		$('minimize').setStyle('opacity',0);
		$('minimize').addEvents({
			click: function(){
				if ($('minimize').hasClass('minimize')) {
					xcc_container.morph({
						'height': '58px'
					});
					$('minimize').addClass('maximize').removeClass('minimize');
				}
				else {
					if (active_area) {
						var area_id = active_area.get('id');
						var area_height = Cookie.read(area_id); 		// Auslesen des Cookies (falls Tab-Höhe geändert wurde, siehe Tab-Script
					if (!area_height) { area_height = allAreasHeight[area_id] + 58; }
						xcc_container.morph({
							'height': area_height
						});
						$('minimize').addClass('minimize').removeClass('maximize');
					}
				}
			}
		})
		xcc_container.setStyle('opacity',1);
		level1.each(function(el,i){
			if (!el.hasClass('xcc_area_blox_edit')) {// Chunk-Bearbeitung brauchen wir nicht zeigen initial
				var fadeIn = new Fx.Tween(el, {
					property: 'opacity',
					duration: 1200
				});
				fadeIn.start(1);
			}
		})
		/*
		$('chunkedittest').addEvents({
			click: function(){
				collectAreas.loadTvTabs(1,1);
			}
		})
		*/
		/* Cancel Button
		 * kann evtl. auch hier weg in ne eigene Klasse
		 */
		
		$('xcc_edit_cancel').addEvents({
			click: function(){
				Sexy.confirm('<h1>Bearbeitung Abbrechen?</h1><p>Sind Sie sicher, dass Sie die Berbeitung abbrechen wollen?</p>', {
				  textBoxBtnOk: 'Ja!',
				  textBoxBtnCancel: 'Nein!', 
				  onComplete: 
			          function(returnvalue) { 
			            if(returnvalue)
			            {
							// Abrechen, Bearbeitungsblock leeren und zusammenfahren. 
							brunoclass.bloxTab.morph({
								opacity: 0
							, onComplete: function(){
								//$('xcc_area_blox_edit').getElement('div.xcc_area_inner').set('html',''); // mal auf die Schnelle
								brunoclass.remove_xcc_area_blox_edit(true);
								notification.alert('Bearbeitung abgebrochen','Um den Block wieder zu bearbeiten, bitte auf den Editier-Button klicken.');
							}	
							})
							xcc_container.morph({
								height: '60px'
							})
							xcc.initialize;			// zurück auf los.      
			            }
			            else
			            {
			            	// nix tun, weiter gehts
							// notification.alert('Erfolgreich!','Block wurde gespeichert');
			            }
			          }
				});
			}		
		});
        var xccspace = new Element('div', {
            id: 'xcc_space',
            'style': 'height:100px;clear:both;'
        });
        xccspace.inject($(document.body), 'bottom');
		
		
	}
	
	

})

// mal ein Versuch, die Größenberechnung von Elementen auszugliedern. Okay, klappt ganz gut.
var calc = new Class({
	Implements: [Options],
	options:{
		height: '50px'
	},
	initialize: function(options){
		this.setOptions(options);
	},
	getheight: function(el) {
		var hoehe = el.getSize();
		return hoehe.y;
	},
	getwidth: function(el) {
		var breite = el.getSize();
		return breite.x;
	},
	
	/* okay, das ist eine wilde Idee: Die Bearbeitungsflächen 
	 * anhand ihrer ID sammeln und die Höhen in assoziatives Array schreiben.
	 * Zumindest wild für mich */
	
	getAllAreas: function(areas){ 				// So ne Art Reset, um alle Areas einzulesen.
		areas.each(function(area,i){
			area_ids[i] = area.get('id');
			area_heights[i] = area.getSize().y;
		//	$('log').appendText(area_ids[i] + ': ' + area_heights[i] + '<br>');
		})
		return area_heights.associate(area_ids); // oha, jetzt bin ich gespannt. YES!
	},
	saveHeight: function(area,height){						// speichert einzelne Höhe einer Area in das Array
		allAreasHeight[area] = height;
		// $('log').appendText(area + 'Höhe: ' + height);
	},
	hide: function(array){
		array.each(function(element){
			if (!element.hasClass('xcc_area_active')) {
				element.setStyles({
					'display': 'none',
					'overflow': 'hidden',
					'opacity': 0
				})
			}
			})
		
	},
	toggleActive: function(array,el){
		array.each(function(element){
			if (element != el) {
				var elFx = new Fx.Morph(element, {
					duration: 300,
					transition: Fx.Transitions.Sine.easeOut
				});
				elFx.start({ 								// Effekt
					'width': '45px',
					'background-position': '0px 60px'
				});
				element.removeClass('xcc_level1_active'); 	// Alle aktiven inaktiv setzen
			}
		})
		el.removeClass('xcc_level1_inactive'); 				// aktuelle aktiv setzen
		el.addClass('xcc_level1_active');
	},
	resizeContainer: function(container,height){	
		container.morph({
						'height': height
					});
	}
	/* Marc, hab Deinen Teil mal in meine funktion übernommen.
	,
	loadTvTabs: function(sort,brunoclass){
	*/					// Bruno, ich versuch mal Deine Klasse zu nehmen, zumindest die Struktur. Sollte dann für Dich anpassbar sein. Wichtig ist ja nur der Responsetext. Der fängt mit <h4> an, also das pure Harald-Dings.
     /*   var docid = sort.get('docid');
        var chunkname = sort.get('chunkname');
        var container = sort.getParent();
        var containertyp = container.get('c_type');
        var containerparent = container.get('c_parentid');
        var containerid = container.get('id');
        var editspans = sort.getElements('.xedit');
        var sort = sort; */
		//var published=sort.get('published');
        /*
	    var req = new Request({
			method: 'post',
			url: 'ajax_forms.php',
			onRequest: function(){
				$('log').appendText('aufruf! ');
			},
			onComplete: function(placeholder_translation){
				$('log').appendText('feddich! ');
				
			},
			onSuccess: function(responseText){
				var blox_area_edit = new Element('div').set('id','xcc_blox_block_edit');	// Tab Container für Chunkbearbeitung bauen
				var area_inner = $('xcc_area_blox_edit').getElement('div.xcc_area_inner');	// Ziel wählen
				blox_area_edit.inject($(area_inner,'top'));									// Nei mit dem Container....
				blox_area_edit.set('html',responseText);									// Und mit Brunos funky Ajax Response
				
				
				
				
				var tabs3 = new SimpleTabs('xcc_blox_block_edit', { 						// Tabs einrichten
					selector: 'h4'
				});	
				var resize = new calc();
				var areas = $$('.xcc_area');
				resize.hide(areas);
				$('xcc_area_blox_edit').setStyle('display','block');						// (Verstecktes) Anzeigen um Höhe zu bestimmen
				var hoehe = resize.getheight($('xcc_area_blox_edit'));						// Höhe bestimmen
				$('log').appendText(hoehe);
				var level1 = $$('.xcc_level1');												// Das nervt mich an JS, muss alles neu deklarieren... Oder?
				level1[3].setStyle('opacity',1);
				resize.toggleActive(level1,level1[3]);										// Aktiv setzen
				resize.resizeContainer($('xcc'),hoehe + 58);										// Ausfahren
				$('xcc_area_blox_edit').morph({												// Einblenden
						opacity: 1
					})
				var cookie = Cookie.write('xcc_area_blox_edit', hoehe, {duration: 30});
				level1[3].morph({
				'width': 200, 																// Menüpunkt auf Breite ausfahren. Da innerwidth nicht aufrufbar (steckt ja im anderen Array, das mir hier zu blöd ist neu zu bauen. Wenn man das allerdings irgendwie "global" verfügbar machen kann, wäre das für viele Sachen gut...
				'background-position': '0px 0px'
				});
				$('minimize').morph({														// Minimier-Button einblenden
						opacity: 1
					}).addClass('minimize').removeClass('maximize');
			}
		}).send();
	}
	*/
})