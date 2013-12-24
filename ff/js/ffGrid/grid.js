(function( $ ){

	var defaults = {
		padding: 20,
		mouseOverColor : '#000000',
		mouseOutColor : '#ffffff',
		onSomeEvent: function() {}
	};
				
  var methods = {
  	
		init : function( options ) {

			var options =  $.extend(defaults, options);
			
			return this.each(function(){

				var o = options;
				var $this = $(this),
				data = $this.data('ffGrid'),
				ffGrid = $('<div />', {
					text : $this.attr('title')
				});

				// If the plugin hasn't been initialized yet
				if ( ! data ) {

					/*
					Do more setup stuff here
					*/

					$(this).data('ffGrid', {
						target : $this,
						ffGrid : ffGrid
					});

				}
			});
		},
		destroy : function( ) {

			return this.each(function(){

				var $this = $(this),
				data = $this.data('tooltip');

				// Namespacing FTW
				$(window).unbind('.tooltip');
				data.tooltip.remove();
				$this.removeData('tooltip');

			})

		},
		reposition : function( ) {  },
		show : function( ) {  },
		hide : function( ) {  },
		update : function( content ) { }
  };

  $.fn.ffGrid = function( method ) {
    
    if ( methods[method] ) {
      return methods[method].apply( this, Array.prototype.slice.call( arguments, 1 ));
    } else if ( typeof method === 'object' || ! method ) {
      return methods.init.apply( this, arguments );
    } else {
      $.error( 'Method ' +  method + ' does not exist on jQuery.ffGrid' );
    }    
  
  };

})( jQuery );