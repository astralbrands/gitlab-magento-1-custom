/*
	By Osvaldas Valutis, www.osvaldas.info
	Available for use under the MIT License
*/



;(function( $, window, document, undefined )
{
	$.fn.doubleTapToGo = function( params )
	{
		if( !( 'ontouchstart' in window ) &&
			!navigator.msMaxTouchPoints &&
			!navigator.userAgent.toLowerCase().match( /windows phone os 7/i ) ) return false;

		this.each( function()
		{
			var curItem = false;

			$( this ).on( 'click', function( e )
			{
				var item = $( this );
                                if (!(item).parent().is('#wpmm-nav') && jQuery('body').hasClass('wpmm-desktop')) {
                                   return ;
                                }
				if( item[ 0 ] != curItem[ 0 ] )
				{
                                    //var subcategs = jQuery(this).find('.wrapper-nav-columns');
                                    //if (!subcategs.length || !jQuery(subcategs[0]).hasClass('open') || !jQuery('body').hasClass('wpmm-desktop') ) {
                                        e.preventDefault();
                                        curItem = item;
                                    //}
				}
			});

			$( document ).on( 'click touchstart MSPointerDown', function( e )
			{
				var resetItem = true,
					parents	  = $( e.target ).parents();

				for( var i = 0; i < parents.length; i++ )
					if( parents[ i ] == curItem[ 0 ] )
						resetItem = false;

				if( resetItem )
					curItem = false;
			});
		});
		return this;
	};
})( jQuery, window, document );
jQuery(document).ready(function() {
    jQuery("#wpmm-nav li:has(ul)").doubleTapToGo();
});