/**
 * SimpleTabs - Unobtrusive Tabs with Ajax
 *
 * @example
 *
 *	var tabs = new SimpleTabs($('tab-element'), {
 * 		selector: 'h2.tab-tab'
 *	});
 *
 * @version		1.0
 *
 * @license		MIT License
 * @author		Harald Kirschner <mail [at] digitarald.de>
 * @copyright	2007 Author
 */


var SimpleTabs = new Class({

	Implements: [Events, Options],

	/**
	 * Options
	 */
	options: {
		selector: '.tab-tab',
		classWrapper: 'tab-wrapper',
		classMenu: 'tab-menu',
		classContainer: 'tab-container',
		onSelect: function(toggle, container, index, start) {	
			// alert(start);									//muss prüfen ob initiales "Selecten"
			toggle.addClass('tab-selected');
			container.setStyle('opacity',0);
			container.setStyles({
				'display': 'block',
				'position': 'relative'
				});
			container.fade('in');
			var containerHeight = container.getSize();
			var calcHeight = new calc();
			if (start == 'aktiv') {
				var area = container.getParent('.xcc_area').get('id');
                container.getParent('.tab-wrapper').setStyle('height',container.getSize().y + 30);				
				//$('log').appendText('Area: ' + area);
				calcHeight.resizeContainer($('xcc'), containerHeight.y + 120);
				// calcHeight.saveHeight(area, containerHeight.y + 120);
				// test[area] = containerHeight.y +120;	
				var cookie = Cookie.write(area, containerHeight.y +120, {duration: 30});
				$(area).store('height',containerHeight.y + 50);
						
			}
		},
		onDeselect: function(toggle, container, index) {
			toggle.removeClass('tab-selected');
			container.setStyle('position','absolute');			
			container.fade('out');
			container.setStyles.delay(500, container, {'display': 'none', 'position':'relative'});
			var formelements = container.getElements('.xcc_formelement');
			formelements.each(function(el){
			var box = el.getElement('.xcc_formelement_box');
			box.setStyle('display','none').setStyle('opacity', 0);
			box.removeClass('active').removeClass('resized');
			box.getParent('.xcc_formelement').removeClass('active');
			})			
		},
		onRequest: function(toggle, container, index) {
			container.addClass('tab-ajax-loading');
		},
		onComplete: function(toggle, container, index) {
			container.removeClass('tab-ajax-loading');
		},
		onFailure: function(toggle, container, index) {
			container.removeClass('tab-ajax-loading');
		},
		OnAsk: function(level1){
			// $('log').appendText('Asked: ' + level1);
		},
		onAdded: Class.empty,
		getContent: null,
		ajaxOptions: {},
		cache: true
	},

	/**
	 * Constructor
	 *
	 * @param {Element} The parent Element that holds the tab elements
	 * @param {Object} Options
	 */
	initialize: function(element, options) {
		this.element = $(element);
		if (this.element){
		var areaid = this.element.getParent('.xcc_area').get('id');
		this.showcookie = areaid + '-selectedTab';				
		}
		// $('log').appendText(' - Area für Tabs: ' + areaid + ', Cookiename: ' + this.showcookie);	
		this.setOptions(options);
		var selectedTab = Cookie.read(areaid + '-selectedTab'); 
		if (!selectedTab) { 
		    selectedTab = 0;
		}
		this.show = selectedTab; 
		this.selected = null;
		this.build();
	},

	build: function() {
		this.tabs = [];
		this.menu = new Element('ul', {'class': this.options.classMenu});
		this.wrapper = new Element('div', {'class': this.options.classWrapper});
        if (this.element) {
			this.element.getElements(this.options.selector).each(function(el){
				var content = el.get('href') || (this.options.getContent ? this.options.getContent.call(this, el) : el.getNext());
				this.addTab(el.innerHTML, el.title || el.innerHTML, content);
			}, this);
			this.element.empty().adopt(this.menu, this.wrapper);
		}
		
		if (this.tabs.length){
			if (this.show > this.tabs.length)this.show=0;
			this.select(this.show,'inaktiv');
		} 
	},

	/**
	 * Add a new tab at the end of the tab menu
	 *
	 * @param {String} inner Text
	 * @param {String} Title
	 * @param {Element|String} Content Element or URL for Ajax
	 */
	addTab: function(text, title, content) {
		var grab = $(content);
		var container = (grab || new Element('div'))
			.setStyle('display', 'none')
			.addClass(this.options.classContainer)
			.addClass('clearfix');
			// Build columns for form elements
			var formelements = container.getElements('.xcc_formelement');  	// Alle Formularelemente
			var lastelement = formelements.getLast();						// Letztes Element
			var lastnumber = formelements.indexOf(lastelement) + 1;			// Index letztes Element (damit Anzahl Elemente)
			var screenWidth = $('xcc').getSize().x;							// Breite Panel
			var maxColumns = Math.floor((screenWidth-60)/280);					// Spalten Tab
			if (lastnumber >= maxColumns){									// Wieviel Spalten erstellen
				columns = maxColumns;
			} else {
				columns = lastnumber;
			}
			var minItemsPerColumn = Math.floor(lastnumber/columns);
			var rest = lastnumber % columns;
			if (rest) { var maxItemsPerColumn = minItemsPerColumn + 1 }
			// $('log').appendText(' Last Number: ' + lastnumber + ' breite: ' + screenWidth + 'Maximale Spalten: ' + maxColumns
			// + 'Columns: ' + columns + ' Min Items: ' + minItemsPerColumn + ' Max Items: ' + maxItemsPerColumn + ' Rest: ' + rest);
			var items_in_column = 1; 										// noch keine Elemente in Spalten
			var arrays_created = 0;											// Noch keine Spaltenarrays angelegt			
			var current_item = 0;											// wo simma grad
			var ColumnsArray = [];											// Arrays für die einzelnen Spalten
			var counter = 0;
			if (lastnumber != 1 && lastnumber != -1) {						// Nur wenn Spalten Sinn machen
				while (arrays_created < maxColumns) {
					if (rest > 0) { // wieviele Items in die Spalte?
						var anzahl = maxItemsPerColumn;			
					}
					else {
						var anzahl = minItemsPerColumn;
					}
					var spalte = new Element('div').addClass('xcc_tabs_column').inject(container, 'bottom');
					if(maxColumns - arrays_created == 1){
						spalte.addClass('xcc_last');
					}
					//$('log').appendText(arrays_created + '. Spalte angelegt. Elemente: ');
					while (items_in_column <= anzahl) {
						// $('log').appendText(counter + ', ');
						spalte.adopt(formelements[counter]);
						if(items_in_column == anzahl && $defined(formelements[counter])){
							formelements[counter].addClass('xcc_last_item');
						 }						
						items_in_column++;
						counter++;
					}
					items_in_column = 1;
					arrays_created++;
					rest--;
				}
			}
			container.inject(this.wrapper);
			this.prepareForms(formelements);			
		var pos = this.tabs.length;
		var evt = (this.options.hover) ? 'mouseenter' : 'click';
		var tab = {
			container: container,
			toggle: new Element('li').grab(new Element('a', {
				href: '#',
				title: title
			}).grab(
				new Element('span', {html: text})
			)).addEvent(evt, this.onClick.bindWithEvent(this, [pos])).inject(this.menu)
		};
		if (!grab && $type(content) == 'string') tab.url = content;
		this.tabs.push(tab);
		return this.fireEvent('onAdded', [tab.toggle, tab.container, pos]);
	},

	onClick: function(evt, index) {
		this.select(index,'aktiv');
		return false;
	},

	/**
	 * Select the tab via tab-index
	 *
	 * @param {Number} Tab-index
	 */
	select: function(index,start) {
		if (this.selected === index || !this.tabs[index]) return this;
		var cookie = Cookie.write(this.showcookie, index, {duration: 30});
		if (this.ajax) this.ajax.cancel().removeEvents();
		var tab = this.tabs[index];
		var params = [tab.toggle, tab.container, index, start];
		if (this.selected !== null && Number(this.selected) !== Number(index)) {
			var current = this.tabs[this.selected];
			if (this.ajax && this.ajax.running) this.ajax.cancel();
			params.extend([current.toggle, current.container, this.selected]);
			this.fireEvent('onDeselect', [current.toggle, current.container, this.selected]);
		}
			this.fireEvent('onSelect', params);
		if (tab.url && (!tab.loaded || !this.options.cache)) {
			this.ajax = this.ajax || new Request.HTML();
			this.ajax.setOptions({
				url: tab.url,
				method: 'get',
				update: tab.container,
				onFailure: this.fireEvent.pass(['onFailure', params], this),
				onComplete: function(resp) {
					tab.loaded = true;
					this.fireEvent('onComplete', params);
				}.bind(this)
			}).setOptions(this.options.ajaxOptions);
			this.ajax.send();
			this.fireEvent('onRequest', params);
		}
		this.selected = index;
		return this;
	},
	prepareForms: function(formelements){
		formelements.each(function(element,v){
			form_value = '';
			if(element.getElement('input')){
				if (element.getElement('input').get('type') == 'checkbox' || element.getElement('input').get('type') == 'radio') {
					// erstmal schauen was wir haben. Aufbau: <input type="checkbox".../><span class="label">Label</span>
					form_type = 'multiple';
					var inputs = element.getElements('input');
					var fieldvalues = new Element('div').addClass('fieldvalues').inject(element, 'bottom');
					inputs.each(function(input){
						var input_label = input.getNext('span');
						var clearer = new Element('div').addClass('clearfix');
						clearer.adopt(input, input_label);
						fieldvalues.adopt(clearer);
					// .wraps(input);
					// clearer.wraps(input);
					// clearer.adopt(input_label);
					})
					var form_handler = fieldvalues;
					var form_value = readValues(inputs);
				}
				else {
					form_type = 'input_text';
					var form_handler = element.getElement('input');
					form_value = form_handler.get('value'); // Inhalt auslesen
				}
			}
			
			if (element.getElement('textarea')) {
				form_type = 'textarea';
				var form_handler = element.getElement('textarea');
				form_value = form_handler.get('value');								// Inhalt auslesen
			}
			
			if (element.getElement('select')) {
				form_type = 'select';
				var form_handler = element.getElement('select');
				var selects = form_handler.getElements('option');
				form_value = readValues(selects);											// kommt noch.
			}
		// $('log').appendText(v + '. Formtyp: ' + form_type + ' | ')
		var form_box = new Element('div').addClass('xcc_formelement_box').addClass('inactive');			// Optik-Schnickschnack bauen
		var form_top = new Element('div').addClass('xcc_formelement_top');
		var form_all = new Element('div').addClass('xcc_formelement_all');
		var form_bottom = new Element('div').addClass('xcc_formelement_bottom');
		var form_submit = new Element('span').addClass('xcc_form_submit');
		var form_label = element.getElement('label');
		var label_value = new Element('span').addClass('value').set('text',form_value);
		form_box.wraps(form_handler);
		form_top.inject(form_box, 'top');
		form_submit.inject(form_box, 'top');
		form_bottom.inject(form_box, 'bottom');
		form_all.wraps(form_handler);
		form_box.setStyles({
			'display':'none',
			'opacity': 0
			});
		label_value.inject(element.getElement('label'));
		if($defined(element.getParent('.xcc_tabs_column'))){						// falls letzte Spalte wird die Formbox links angezeigt.
			//$('log').appendText('huhu ');
			if(element.getParent('.xcc_tabs_column').hasClass('xcc_last')){
				//$('log').appendText('hier! ');
				form_box.addClass('xcc_left');
			}
		}
		form_label.addEvents({
			click:function(){
				removeBoxes(form_box,formelements);				
				showBox(form_type,form_box,element,formelements);
			}
		})
		
		/* Submit-Button jeder Formbox */
		form_box.getElement('.xcc_form_submit').addEvents({
			click: function(){
				removeBox(form_box);
			}
		})
		
		
		if(form_type == 'input_text' || form_type == 'textarea'){  // Änderung gleich live in Vorschau (label) Ã¼bernehmen
			form_handler.addEvents({
				keyup:function(){
					new_value = form_handler.get('value');
					label_value.set('text',new_value);
					if(! label_value.hasClass('edited')){
						label_value.addClass('edited');
					}
				}
			})
		}
		if (form_type == 'multiple') { // Änderung gleich live in Vorschau (label) übernehmen für Checkboxen und Options
			var inputs = form_handler.getElements('input');
			inputs.each(function(input){
				input.addEvents({
					click: function(){
						var form_value = readValues(inputs);
						label_value.set('text',form_value)
					}
				})
				
			})
		}
		if (form_type == 'select') { // Änderung gleich live in Vorschau (label) übernehmen für Checkboxen und Options
			var inputs = form_handler.getElements('option');
			inputs.each(function(input){
				input.addEvents({
					click: function(){
						var form_value = readValues(inputs);
						label_value.set('text',form_value)
					}
				})
				
			})
		}
		})
	}

});

var showBox = function(form_type,form_box,element,formelements){
		if (form_box.hasClass('inactive')) {
			form_box.setStyle('display', 'block');
			form_box.getParent('.xcc_formelement').addClass('active');
			var tab_inner = form_box.getParent('.tab-container');
			var tab_outer = form_box.getParent('.tab-wrapper');
			var form_size = form_box.getSize().y;
			var body_size = window.getSize().y;
			var position = form_box.getPosition().y;
            var inner_area_coordinates = tab_inner.getCoordinates();
			var outer_area_coordinates = tab_outer.getCoordinates();
			//$('log').appendText(' Body:' + body_size + ' | hoehe: ' + form_size + ' | hoehe inner_area: ' + inner_area_coordinates.height+ ' | hoehe outer_area: ' + outer_area_coordinates.height + ' | Position: ' + position + ' | Position area: ' + inner_area_coordinates.top);
			
			// okay, jetzt fängt der Hinse nachts um 4 mit Mathe an...
	
			var nuffmit = new calc();
			if(form_size + position > body_size) {													// Muss ausfahren: wenn höhe Formbox + y-Postition größer als der Body ist...
          		if (outer_area_coordinates.height - 30  > form_size){			 							// aber das Ding noch reinpassen würde...
					form_box.setStyle('top','-' + (form_size + position - body_size) + 'px');  // muha, so ungefähr halt ;-)
				} else {
					form_box.setStyle('top','-' + (position - inner_area_coordinates.top - 6) + 'px'); 			// Form ganz nach oben
					nuffmit.resizeContainer($('xcc'),form_size + 120);
					var area = form_box.getParent('.xcc_area').get('id'); 
					// var cookie = Cookie.write(area + '_container', outer_area_coordinates.height +120, {duration: 30});
					tab_outer.morph({		
						'height': form_size
					})	
					form_box.addClass('resized');								
				}
			}	
			if (form_size + position < body_size) {										// kann auch sein, dass wir es zusammenfahren müssen
				if(outer_area_coordinates.height - 30 > form_size){
					tab_outer.morph({										// und Tab-Container anpassen
						'height': inner_area_coordinates.height + 100
					})
					if(form_size >= inner_area_coordinates.height){			// schauen wohin resizen, auf Tab Höhe oder Formelement
						resizeHeight = form_size;
					} else {
						resizeHeight = inner_area_coordinates.height;
					}
					nuffmit.resizeContainer($('xcc'),resizeHeight + 120);
				}
			}	
			form_box.morph({
				'opacity': 1,
				onComplete: function(){
					form_box.removeClass('inactive').addClass('active');
				}
			});
			var active_input = form_box.getElement('input,textarea,select');
			//$('log').appendText('tag ist: ' + active_input.get('tag'));
																	// Focus auf Input setzen... Klappt aber nicht.					
			active_input.select();
			//focus(form_box);
			if(form_box.hasClass('active')){											// wenn was offen ist, Tab-Funktion implementieren. Hmmm, wird das jetzt rekursiv?
			//active_input.setCaretPosition('end');
			active_input.focus();

			active_input.addEvents({
				keydown: function(event){
					if (event.key == "tab") {
						index = formelements.indexOf(element);
						if (!event.shift) {
							new_index = index+1;
						} else {
							new_index = index-1;
						}
						if ($defined(formelements[new_index])) {
							var activeForm = form_box.getParent('xcc_form_element')
							var new_form_box = formelements[new_index].getElement('.xcc_formelement_box');
							removeBoxes(new_form_box, formelements);
							showBox(form_type,new_form_box, formelements[new_index], formelements);
							//$('log').appendText(' ' + index);
							var active_input = new_form_box.getElement('input,textarea,select');
							//active_input.setCaretPosition('end');
							active_input.focus();												// Focus auf Input setzen... Klappt aber nicht.												
							//$('log').appendText(form_type);
							return false;
						}
					}
					
					if (event.key=="enter"){				//muss man noch textareas ausschließen, das klappt aber grad nicht.
						removeBox(form_box);
						//$('log').appendText(form_type);
					}
				}			
			});
		}
		} else {
			form_box.morph({
				'opacity': 0
			})
			form_box.addClass('inactive');
			form_box.removeClass('active');
			form_box.getParent('.xcc_formelement').removeClass('active');
			form_box.setStyles.delay(1000,form_box,{
				'display':'none'
				});
		}
}
var removeBoxes = function(form_box,formelements){
		formelements.each(function(el){
			var box = el.getChildren('.xcc_formelement_box.active');
			if ($defined(box)) {
				box.morph({
					'opacity': 0
				})
				if(box.hasClass('resized') && (box == form_box)){				// wenn nur die aktuelle ausgeblendet wird
					var nunnamit = new calc();
					var area_id = form_box.getParent('.xcc_area').get('id');
					var area_height = Cookie.read(area_id);
					var tab_inner_height = form_box.getParent('.tab-container').getSize().y;					
					nunnamit.resizeContainer($('xcc'), area_height);
					form_box.getParent('.tab-wrapper').morph({
						'height':tab_inner_height + 30
						})
					box.removeClass('resized');
				}
				box.addClass.delay(100, box, 'inactive');
				if (form_box == box){
					box.addClass('current');
				}
				box.removeClass('active');
				box.getParent('.xcc_formelement').removeClass('active');
				box.setStyles.delay(1000, form_box, {
					'display': 'none'
				});
			}
		})
}

var readValues = function(inputs){
					form_value = '';
	 				inputs.each(function(input){
					var input_value = input.get('value');
						if (input.checked || input.selected){
						form_value = form_value + ',' + input_value;
						}
					})
					if (form_value == '') {
						form_value = 'Nichts ausgewählt'
					} else {
						form_value = form_value.substr(1, form_value.length);
					}
					return form_value;
}

var removeBox = function(form_box){
	form_box.morph({
			'opacity': 0
		})
	form_box.addClass('inactive');
	form_box.setStyles.delay(1000,form_box,{
		'display':'none'
	});
	if(form_box.hasClass('resized')){
			var nunnamit = new calc();
			var area_id = form_box.getParent('.xcc_area').get('id');
			var area_height = Cookie.read(area_id);
			nunnamit.resizeContainer($('xcc'), area_height);
	}
}