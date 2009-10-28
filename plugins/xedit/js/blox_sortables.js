var blox_Sortables = new Class({

	Implements: [Events, Options],

	options: {/*
		onSort: $empty,
		onStart: $empty,
		onComplete: $empty,*/
		snap: 4,
		opacity: 1,
		clone: false,
		revert: false,
		handle: false,
		constrain: false
	},

	initialize: function(lists, options){
		this.setOptions(options);
		this.elements = [];
		this.lists = [];
		this.unremoveables = [];
		this.unfillables = [];
		this.idle = true;
		this.count = 0;
		this.dragmode = '';//'copy'
		this.mode = '';//'copy'
		
		this.addLists($$($(lists) || lists));
		this.addUnremoveables($$($(this.options.unremoveables) || this.options.unremoveables));
		this.addUnfillables($$($(this.options.unfillables) || this.options.unfillables));
		if (!this.options.clone) this.options.revert = false;
		if (this.options.revert) this.effect = new Fx.Morph(null, $merge({duration: 250, link: 'cancel'}, this.options.revert));
        this.checkChildCounts(this.lists);
	},

	attach: function(){
		this.addLists(this.lists);
		return this;
	},

	detach: function(){
		this.lists = this.removeLists(this.lists);
		return this;
	},

	addItems: function(){
		Array.flatten(arguments).each(function(element){
			this.elements.push(element);
			var start = element.retrieve('sortables:start', this.start.bindWithEvent(this, element));
			(this.options.handle ? element.getElement(this.options.handle) || element : element).addEvent('mousedown', start);
		}, this);
		return this;
	},

	addLists: function(){
		Array.flatten(arguments).each(function(list){
			this.lists.push(list);
			this.addItems(list.getChildren());
		}, this);
		return this;
	},
	addUnremoveables: function(){
		Array.flatten(arguments).each(function(list){
			this.unremoveables.push(list);
		}, this);
		return this;
	},
	addUnfillables: function(){
		Array.flatten(arguments).each(function(list){
			this.unfillables.push(list);
		}, this);
		return this;
	},

	removeItems: function(){
		var elements = [];
		Array.flatten(arguments).each(function(element){
			elements.push(element);
			this.elements.erase(element);
			var start = element.retrieve('sortables:start');
			(this.options.handle ? element.getElement(this.options.handle) || element : element).removeEvent('mousedown', start);
		}, this);
		return $$(elements);
	},

	removeLists: function(){
		var lists = [];
		Array.flatten(arguments).each(function(list){
			lists.push(list);
			this.lists.erase(list);
			this.removeItems(list.getChildren());
		}, this);
		return $$(lists);
	},

	getClone: function(event, element){
		if (!this.options.clone) return new Element('div').inject(document.body);
		if ($type(this.options.clone) == 'function') return this.options.clone.call(this, event, element, this.list);
		return element.clone(true).setStyles({
			'margin': '0px',
			'position': 'absolute',
			'visibility': 'hidden',
			'width': element.getStyle('width')
		}).position(this.element.getPosition(this.element.getOffsetParent()));
	},
    getDragElement: function(element){
        var dragelement = element.getElement('.dragelement');
        if (dragelement) {
			
        }else {
			dragelement = element;
		}
        
        return dragelement;		
	},

    getCopyClone: function(event, element){
		
            var clone = this.getDragElement(element).clone(true);
            clone.inject(this.list);
			//clone.inject($('left'));
			el_xtools=clone.getElement('.xtools');
            
            if (el_xtools) {
                el_xtools.setStyles({
                    'opacity': '1',
                    'visibility': 'visible'
                })
            }

		    if (this.mode == 'copy'){
				clone.set('savemode', 'copy');
			}
            clone.set('style', element.get('style'));
			clone.addClass('blox');
        
        return clone;
    },

	getDroppables: function(){
		var droppables = this.list.getChildren();
		if (!this.options.constrain) droppables = this.lists.concat(droppables).erase(this.list);
		return droppables.erase(this.clone).erase(this.element);
	},

	insert: function(dragging, element){

		//var sortelement = this.element;//im move-mode
		//var sortelement = (this.mode == 'copy')? this.copyclone:this.element;//im copy-mode

        if (this.unfillables.contains(element)) {

		}else{
		var where = 'inside';
		if (this.lists.contains(element)){
			this.list = element;
			this.drag.droppables = this.getDroppables();
			//where = this.sortelement.getAllPrevious().contains(element) ? 'before' : 'after';
		} else {
			where = this.sortelement.getAllPrevious().contains(element) ? 'before' : 'after';
		}
            //this.list = element;
            this.sortelement.inject(element, where);
            
            /*
             var mouse = this.drag.mouse;
             this.clone.setStyles({
             'margin': '0px',
             'position': 'absolute',
             'visibility': 'visible'
             }).inject(element);
             */
            this.fireEvent('sort', [this.sortelement, this.clone]);
        }

	},

	remove: function(dragging, element){

     //this.sortelement=this.sortelement.dispose();
	 //this.list = 'nowhere';

	},

    start: function(event, element){
        if (!this.idle) 
            return;
        if (element.hasClass('bloxdummy')) {
			return;
			}

            this.idle = false;				
			this.element = element;				
            this.opacity = element.get('opacity');
            this.list = element.getParent();
            this.startlist = element.getParent();
            //dragmodus: copy/move
            this.sortelement = this.element;//im move-mode		 
            this.clone = this.getClone(event, element)
            
            this.mode = this.dragmode;
            if (this.unremoveables.contains(element.getParent())) {
                this.mode = 'copy';
            }
            /*
             if (this.mode == 'copy'){
             this.copyclone = this.getCopyClone(event, element);//für copy-mode
             this.sortelement = this.copyclone;//im copy-mode
             this.clone = this.getClone(event, this.sortelement);
             this.sortelement = this.sortelement.dispose();
             }
             */
            this.copyclone = this.getCopyClone(event, element);//für copy-mode
            this.sortelement = this.copyclone;//im copy-mode	
            this.clone = this.getClone(event, this.sortelement);
            //this.sortelement = this.sortelement.dispose();
            
            this.drag = new Drag.Move(this.clone, {
                snap: this.options.snap,
                container: this.options.constrain && this.element.getParent(),
                droppables: this.getDroppables(),
                onSnap: function(){
                    event.stop();
                    this.clone.setStyle('visibility', 'visible');
                    this.sortelement.set('opacity', this.options.opacity || 0);
                    this.fireEvent('start', [this.element, this.clone]);
                }
    .bind(this)            ,
                onEnter: this.insert.bind(this),
				onLeave: this.remove.bind(this),
                onCancel: this.reset.bind(this),
                onComplete: this.end.bind(this)
            });
            
            this.clone.inject(this.element, 'before');
            this.drag.start(event);
     
        
    },

	end: function(){
		
		//var sortelement = (this.mode == 'copy')? this.copyclone:this.element;//im copy-mode
		
		this.drag.detach();
		this.sortelement.set('opacity', this.opacity);
		if (this.effect){
			var dim = this.sortelement.getStyles('width', 'height');
			var pos = this.clone.computePosition(this.sortelement.getPosition(this.clone.offsetParent));
			this.effect.element = this.clone;
			this.effect.start({
				top: pos.top,
				left: pos.left,
				width: dim.width,
				height: dim.height,
				opacity: 0.25
			}).chain(this.reset.bind(this));
		} else {
			this.reset();
		}
	},

	reset: function(){
		this.idle = true;
		this.clone.destroy();
		var lists = [];
		this.addItems(this.sortelement);
		if (this.mode == 'copy'||this.list == 'nowhere'){
			
		}else
		{
			
			this.element.highlight('#F3F865');
			this.element.dispose();
			lists.push(this.list);
		}

 
		lists.push(this.startlist);
		this.checkChildCounts(lists);
		this.fireEvent('complete', this.sortelement);
	},

	serialize: function(){
		var params = Array.link(arguments, {modifier: Function.type, index: $defined});
		var serial = this.lists.map(function(list){
			return list.getChildren().map(params.modifier || function(element){
				return element.get('id');
			}, this);
		}, this);
		
		var index = params.index;
		if (this.lists.length == 1) index = 0;
		return $chk(index) && index >= 0 && index < this.lists.length ? serial[index] : serial;
	},

    checkChildCounts: function(lists){
        lists.each(function(list){
            var dummy = list.getElement('.bloxdummy');
            if (dummy) {
                if (list.getChildren().length <= 1) {
                    dummy.removeClass('hidden');
                }
                else {
                    dummy.addClass('hidden');
                }
            }
        })
    }

});