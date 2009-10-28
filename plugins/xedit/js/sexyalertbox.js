/**
 * Sexy Alert Box - for mootools 1.2 - jQUery 1.3
 * @name sexyalertbox.v1.2.js
 * @author Eduardo D. Sada - http://www.coders.me/web-js-html/javascript/sexy-alert-box
 * @version 1.2.2
 * @date 25-May-2009
 * @copyright (c) 2009 Eduardo D. Sada (www.coders.me)
 * @license MIT - http://es.wikipedia.org/wiki/Licencia_MIT
 * @example http://www.coders.me/ejemplos/sexy-alert-box/
 * @based in <PBBAcpBox> (Pokemon_JOJO, <http://www.mibhouse.org/pokemon_jojo>)
 * @thanks to Pokemon_JOJO!
 * @features:
 * * Chain Implemented (Cola de mensajes)
 * * More styles (info, error, alert, prompt, confirm)
 * * ESC would close the window
 * * Focus on a default button
*/

/*
Class: SexyAlertBox
	Clone class of original javascript function : 'alert', 'confirm' and 'prompt'

Arguments:
	options - see Options below

Options:
	name - name of the box for use different style
	zIndex - integer, zindex of the box
	onReturn - return value when box is closed. defaults to false
	onReturnFunction - a function to fire when return box value
	BoxStyles - stylesheets of the box
	OverlayStyles - stylesheets of overlay
	showDuration - duration of the box transition when showing (defaults to 200 ms)
	showEffect - transitions, to be used when showing
	closeDuration - Duration of the box transition when closing (defaults to 100 ms)
	closeEffect - transitions, to be used when closing
	onShowStart - a function to fire when box start to showing
	onCloseStart - a function to fire when box start to closing
	onShowComplete - a function to fire when box done showing
	onCloseComplete - a function to fire when box done closing
*/

var SexyAlertBox = new Class({
  Implements: [Options, Chain],
	getOptions: function() {
		return {
			name            : 'SexyAlertBox',
			zIndex          : 65555,
			onReturn        : false,
			onReturnFunction: $empty,
			BoxStyles       : { 'width': 500 },
			OverlayStyles   : { 'background-color': '#000', 'opacity': 0.7 },
			showDuration    : 200,
			showEffect      : Fx.Transitions.linear,
      closeDuration   : 100,
			closeEffect     : Fx.Transitions.linear,
			moveDuration    : 500,
			moveEffect      : Fx.Transitions.Back.easeOut,
			onShowStart     : $empty,
			onShowComplete  : $empty,
			onCloseStart    : $empty,
			onCloseComplete : function(properties) {
				this.options.onReturnFunction(this.options.onReturn);
			}.bind(this)
		};
	},

	initialize: function(options) {
    this.i=0;
    
		this.setOptions(this.getOptions(), options);

		this.Overlay = new Element('div', {
			'id'    : 'BoxOverlay',
			'styles': {
          'display'           : 'none',
          'position'          : 'absolute',
          'top'               : '0',
          'left'              : '0',
          'opacity'           : 0,
          'z-index'           : this.options.zIndex,
          'background-color'  : this.options.OverlayStyles['background-color'],
          'height'            : window.getScrollHeight() + 'px',
          'width'             : window.getScrollWidth() + 'px'
			}
		});

		this.Content = new Element('div', {
			'id': this.options.name + '-BoxContenedor'
		});

    this.Contenedor = new Element('div', {
      'id': this.options.name + '-BoxContent'
    }).adopt(this.Content);

		this.InBox = new Element('div', {
			'id': this.options.name + '-InBox'
		}).adopt(this.Contenedor);
		
		this.Box = new Element('div', {
			'id': this.options.name + '-Box',
			'styles': {
				'display': 'none',
				'z-index': this.options.zIndex + 2,
				'position': 'absolute',
				'top': '0',
				'left': '0',
				'width': this.options.BoxStyles['width'] + 'px'
			}
		}).adopt(this.InBox);

    this.Overlay.injectInside(document.body);
    this.Box.injectInside(document.body);

    this.preloadImages();
    
		window.addEvent('resize', function() {
			if(this.options.display == 1) {
				this.Overlay.setStyles({
					'height': window.getScrollHeight() + 'px',
					'width': window.getScrollWidth() + 'px'
				});
				this.replaceBox();
			}
		}.bind(this));
		
		this.Box.addEvent('keydown', function(event) {
        if (event.key == 'esc'){
          this.options.onReturn = false;
          this.display(0);
        }
    }.bind(this));
		
		window.addEvent('scroll', this.replaceBox.bind(this));
	},

  preloadImages: function() {
    var img = new Array(2);
    img[0] = new Image();img[1] = new Image();img[2] = new Image();
    img[0].src = this.Box.getStyle('background-image').replace(new RegExp("url\\('?([^']*)'?\\)", 'gi'), "$1");
    img[1].src = this.InBox.getStyle('background-image').replace(new RegExp("url\\('?([^']*)'?\\)", 'gi'), "$1");
    img[2].src = this.Contenedor.getStyle('background-image').replace(new RegExp("url\\('?([^']*)'?\\)", 'gi'), "$1");
  },


  togFlashObjects: function(state) {
    var hideobj=new Array("embed", "iframe", "object");
    for (y = 0; y < hideobj.length; y++) {
     var objs = document.getElementsByTagName(hideobj[y]);
     for(i = 0; i < objs.length; i++) {
      objs[i].style.visibility = state;
     }
    }
  },


	/*
	Property: display
		Show or close box
		
	Argument:
		option - integer, 1 to Show box and 0 to close box (with a transition).
	*/	
	display: function(option){
		if(this.Transition)
			this.Transition.cancel();				

		// Show Box	
		if(this.options.display == 0 && option != 0 || option == 1) {

      if(Browser.Engine.trident4)
        $$('select', 'object', 'embed').each(function(node){ node.style.visibility = 'hidden' });
        
      this.togFlashObjects('hidden');

			this.Overlay.setStyle('display', 'block');
			this.options.display = 1;
			this.fireEvent('onShowStart', [this.Overlay]);

			this.Transition = new Fx.Tween(this.Overlay,
				{
          property: 'opacity',
					duration: this.options.showDuration,
					transition: this.options.showEffect,
					onComplete: function() {

						sizes = window.getSize();
						scrollito = window.getScroll();

						this.Box.setStyles({
							'display': 'block',
							'left': (scrollito.x + (sizes.x - this.options.BoxStyles['width']) / 2).toInt()
						});

						this.replaceBox();
						this.fireEvent('onShowComplete', [this.Overlay]);
					}.bind(this)
				}
			).start(this.options.OverlayStyles['opacity']);

		}
		// Close Box
		else {

      if(Browser.Engine.trident4)
        $$('select', 'object', 'embed').each(function(node){ node.style.visibility = 'visible' });

      this.togFlashObjects('visible');

      this.queue.delay(500,this);

			this.Box.setStyles({
				'display': 'none',
				'top': 0
			});
			this.Content.empty();
			this.options.display = 0;

			this.fireEvent('onCloseStart', [this.Overlay]);

      if(this.i==1) {
        this.Transition = new Fx.Tween(this.Overlay,
          {
            property: 'opacity',
            duration: this.options.closeDuration,
            transition: this.options.closeEffect,
            onComplete: function() {
                this.fireEvent('onCloseComplete', [this.Overlay]);
            }.bind(this)
          }
        ).start(0);
      }

		}
	},

	/*
	Property: replaceBox
		Move Box in screen center when brower is resize or scroll
	*/
	replaceBox: function() {
		if(this.options.display == 1) {
			sizes = window.getSize();
      scrollito = window.getScroll();

			if(this.MoveBox)
				this.MoveBox.cancel();
			
			this.MoveBox = new Fx.Morph(this.Box, {
				duration: this.options.moveDuration,
				transition: this.options.moveEffect
			}).start({

				'left': (scrollito.x + (sizes.x - this.options.BoxStyles['width']) / 2).toInt(),
				'top': (scrollito.y + (sizes.y - this.Box.offsetHeight) / 2).toInt()

			});
			
      this.focusin.delay(this.options.moveDuration,this);
			
		}
	},

  focusin: function() {
    if ($chk($('BoxAlertBtnOk'))) {
      $('BoxAlertBtnOk').focus();
    } else if ($chk($('BoxPromptInput'))) {
        $('BoxPromptInput').focus();
    } else if ($chk($('BoxConfirmBtnOk'))) {
      $('BoxConfirmBtnOk').focus();
    }
  },

	queue: function() {
		this.i--;
		this.callChain();
	},


	/*
	Property: messageBox
		Core system for show all type of box
		
	Argument:
		type - string, 'alert' or 'confirm' or 'prompt'
		message - text to show in the box
		properties - see Options below
		input - text value of default 'input' when prompt
		
	Options:
		textBoxBtnOk - text value of 'Ok' button
		textBoxBtnCancel - text value of 'Cancel' button
		onComplete - a function to fire when return box value
	*/	
	messageBox: function(type, message, properties, input) {

		this.chain(function () {

      properties = $extend({
        'textBoxBtnOk': 'OK',
        'textBoxBtnCancel': 'Cancelar',
        'textBoxInputPrompt': null,
        'password': false,
        'onComplete': $empty
      }, properties || {});


      this.options.onReturnFunction = properties.onComplete;

      this.ContenedorBotones = new Element('div', {
        'id': this.options.name + '-Buttons'
      });
      

      if(type == 'alert' || type == 'info' || type == 'error')
      {
          this.AlertBtnOk = new Element('input', {
            'id': 'BoxAlertBtnOk',
            'type': 'submit',
            'value': properties.textBoxBtnOk,
            'styles': {
              'width': '70px'
            }
          });
          
          this.AlertBtnOk.addEvent('click', function() {
            this.options.onReturn = true;
            this.display(0);
          }.bind(this));
        
          if(type == 'alert')
            this.clase = 'BoxAlert';
          else if(type == 'error')
            this.clase = 'BoxError';
          else if(type == 'info')
            this.clase = 'BoxInfo';
        
          this.Content.setProperty('class',this.clase).set('html',message);

          this.AlertBtnOk.injectInside(this.ContenedorBotones);
          
          this.ContenedorBotones.injectInside(this.Content);
          this.display(1);
      }
      else if(type == 'confirm')
      {
          this.ConfirmBtnOk = new Element('input', {
            'id': 'BoxConfirmBtnOk',
            'type': 'submit',
            'value': properties.textBoxBtnOk,
            'styles': {
              'width': '70px'
            }
          });

          this.ConfirmBtnCancel = new Element('input', {
            'id': 'BoxConfirmBtnCancel',
            'type': 'submit',
            'value': properties.textBoxBtnCancel,
            'styles': {
              'width': '70px'
            }
          });

          this.ConfirmBtnOk.addEvent('click', function() {
            this.options.onReturn = true;
            this.display(0);
          }.bind(this));

          this.ConfirmBtnCancel.addEvent('click', function() {
            this.options.onReturn = false;
            this.display(0);
          }.bind(this));

          this.Content.setProperty('class','BoxConfirm').set('html',message);

          this.ConfirmBtnOk.injectInside(this.ContenedorBotones);
          this.ConfirmBtnCancel.injectInside(this.ContenedorBotones);
          
          this.ContenedorBotones.injectInside(this.Content);
          this.display(1);
      }
      else if(type == 'prompt')
      {
          this.PromptBtnOk = new Element('input', {
            'id': 'BoxPromptBtnOk',
            'type': 'submit',
            'value': properties.textBoxBtnOk,
            'styles': {
              'width': '70px'
            }
          });

          this.PromptBtnCancel = new Element('input', {
            'id': 'BoxPromptBtnCancel',
            'type': 'submit',
            'value': properties.textBoxBtnCancel,
            'styles': {
              'width': '70px'
            }
          });
          
          type = properties.password ? 'password' : 'text';
          this.PromptInput = new Element('input', {
            'id': 'BoxPromptInput',
            'type': type,
            'value': input,
            'styles': {
              'width': '250px'
            }
          });

          this.PromptBtnOk.addEvent('click', function() {
            this.options.onReturn = this.PromptInput.value;
            this.display(0);
          }.bind(this));

          this.PromptBtnCancel.addEvent('click', function() {
            this.options.onReturn = false;
            this.display(0);
          }.bind(this));

          this.Content.setProperty('class','BoxPrompt').set('html',message + '<br />');
          this.PromptInput.injectInside(this.Content);
          new Element('br').injectInside(this.Content);
          this.PromptBtnOk.injectInside(this.ContenedorBotones);
          this.PromptBtnCancel.injectInside(this.ContenedorBotones);


          this.ContenedorBotones.injectInside(this.Content);

          this.display(1);
      }
      else
      {
          this.options.onReturn = false;
          this.display(0);		
      }

    });

		this.i++;

		if(this.i==1) this.callChain();

	},

	/*
	Property: alert
		Shortcut for alert
		
	Argument:
		properties - see Options in messageBox
	*/		
	alert: function(message, properties){
		this.messageBox('alert', message, properties);
	},

	/*
	Property: info
		Shortcut for alert info
		
	Argument:
		properties - see Options in messageBox
	*/		
	info: function(message, properties){
		this.messageBox('info', message, properties);
	},

	/*
	Property: error
		Shortcut for alert error
		
	Argument:
		properties - see Options in messageBox
	*/		
	error: function(message, properties){
		this.messageBox('error', message, properties);
	},

	/*
	Property: confirm
		Shortcut for confirm
		
	Argument:
		properties - see Options in messageBox
	*/
	confirm: function(message, properties){
		this.messageBox('confirm', message, properties);
	},

	/*
	Property: prompt
		Shortcut for prompt
		
	Argument:
		properties - see Options in messageBox
	*/	
	prompt: function(message, input, properties){
		this.messageBox('prompt', message, properties, input);
	}
});

SexyAlertBox.implement(new Events, new Options);

window.addEvent('domready', function() {
  Sexy = new SexyAlertBox();
});