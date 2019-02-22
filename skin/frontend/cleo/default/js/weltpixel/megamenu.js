
jQuery(document).ready(function(){
	jQuery('.header-settings').click(function() {
		jQuery('.header-options').toggleClass('open');
		jQuery(this).toggleClass('rotated');
	});
	
});
jQuery(window).on("resize", function(event){
  if( jQuery(this).width() > 768 ) {
  	jQuery('.header-options').removeClass('open');
	jQuery(this).removeClass('rotated');
  };
});
document.observe('dom:loaded', function() {

    var head = document.getElementsByTagName('head')[0],
        body = document.getElementsByTagName('body')[0],
        threshold = $('wpmm-width-threshold'),
        widthThreshold = (threshold) ? Math.abs(parseInt(threshold.value)) : 960,
        meta = document.getElementsByName('viewport'),
        speed = 0.5;

    var nav = $('wpmm-nav-container'),
        viewport = $('wpmm-nav-viewport'),
        overlay = $('wpmm-overlay'),
        opener = $('wpmm-opener'),
        closer = $('wpmm-closer'),
        subnavs = $$('#wpmm-nav .wpmm-nav-content'),
        toggles = $$('#wpmm-nav .subnav-toggle'),
        navWidth,
        navPosition;

    var debug = false;
    function echo(s)
    {
        if (debug) {
            console.log(s);
        }
    }

    /*
     * add viewport meta tag on mobiles if not exists already
     */

    if (meta.length == 0) {
        meta = document.createElement('meta');
        meta.name = 'viewport';
        meta.content = '';
        head.appendChild(meta);
    }

    function isMobile()
    {
        return (window.innerWidth < widthThreshold);
    }

    function updateDOM()
    {
        if (isMobile()) {
            body.removeClassName('wpmm-desktop');
            meta.content = 'width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, user-scalable=no';
        } else {
            body.addClassName('wpmm-desktop');
        }
    }

    function closeSubnavs()
    {
        echo('closeSubnavs()');
        subnavs.invoke('removeClassName', 'open');
        toggles.invoke('removeClassName', 'subnav-toggle-open');
    }

    function hOpen(e, ee)
    {
        ee.cancelBubble = true;
        Event.stop(ee);

        navWidth = nav.getWidth();
        navPosition = nav.positionedOffset();

        if (navPosition.left == 0)
        {
            new Effect.Move(nav, {
                x : (-1) * navWidth,
                y : 0,
                mode : 'absolute',
                duration : speed,
                beforeStart: function() {
                    new Effect.Fade(overlay, {
                        duration : speed,
                        from : 1,
                        to : 0
                    });
                },
                afterFinish: function() {
                    body.removeClassName('wpmm-mobile');
                    nav.setAttribute('style', '');
                    overlay.setAttribute('style', '');
                }
            });
        }
        else
        {
            new Effect.Move(nav, {
                x : 0,
                y : 0,
                mode : 'absolute',
                duration : speed,
                beforeStart: function() {
                    body.addClassName('wpmm-mobile');
                    nav.setStyle({
                        'left' : (-1) * nav.getWidth() + 'px'
                    });
                    overlay.setStyle({
                        display : 'block',
                        opacity : 0
                    });
                    new Effect.Fade(overlay, {
                        duration : speed,
                        from : 0,
                        to : 1
                    });
                }
            });
        }
    }

    function hToogle(e, ee)
    {
        ee.cancelBubble = true;
        e.toggleClassName('subnav-toggle-open');
        e.previous('div').toggleClassName('open');
    }
      
     function hLink(e, ee)
    {
        //ee.cancelBubble = true;          
        var subnav = e.next('div');
        if (subnav) {
            var subnavIsVisible = subnav.hasClassName('open');
            echo(subnavIsVisible);
            if (subnavIsVisible) {
                echo('subnavIsVisible');
                return false;
            }        
            if ( ! isMobile()) {
                echo('! isMobile');
                closeSubnavs();
            }
//            Event.stop(ee);
            if (!jQuery('body').hasClass('wpmm-desktop')) {
                subnav.toggleClassName('open');
            }            
            var subnavToggle = e.next('.subnav-toggle');
            if (subnavToggle) {
                subnavToggle.toggleClassName('subnav-toggle-open');
            }
            echo('fin hLink');
        }       
        return false;
    }

    [opener, closer, overlay].each(function(e) {
        if (e)
        {
            e.observe('click', function(ee) {
                echo('opener | closer / click');
                hOpen(e, ee);
            });
//            e.observe('touchstart', function(ee) {
//                echo('opener | closer / touchstart');
//                hOpen(e, ee);
//            });
        }
    });
    
    /*
     * toggle menus and sub-menus via toggles
     */

    $$('#wpmm-nav .subnav-toggle').each(function(e) {
        // touchstart
        e.observe('click', function(ee) {
            echo('.subnav-toggle / click');
            hToogle(e, ee);
        });
//        e.observe('touchstart', function(ee) {
//            echo('.subnav-toggle / touchstart');
//            e.stopObserving('click');
//            hToogle(e, ee);
//        });
    });

    /*
     * toggle menus and sub-menus via links
     */

    $$('#wpmm-nav .nav-link').each(function(e) {
        // touchstart
        e.observe('click', function(ee) {
            echo('.nav-link / click');
            hLink(e, ee);
        });
//        e.observe('touchstart', function(ee) {
//            echo('.nav-link / touchstart');
//            //e.stopObserving('click');
//            hLink(e, ee);
//        });
    });

    /*
     * ...
     */

    Event.observe(document, 'touchstart', function(e) {
        // echo('document / touchstart');
        var subnavsVisible = subnavs.findAll(function(subnav) {
                return subnav.hasClassName('open');
            });
        if (subnavsVisible.size()) {
            e.cancelBubble = true;
            subnavs.invoke('removeClassName', 'open');
            Event.stop(e);
        }
    });

    if (overlay) {
        Event.observe(overlay, 'touchstart', function(e) {
            // echo('overlay / touchstart');
            e.cancelBubble = true;
        });
    }

    if (nav) {
        Event.observe(nav, 'touchend', function(e) {
            // echo('nav / touchend');
            e.cancelBubble = true;
        });
        Event.observe(nav, 'touchstart', function(e) {
            // echo('nav / touchstart');
            e.cancelBubble = true;
        });
    }

    /* ---------------------------------------------------------------------- */

    updateDOM();

    Event.observe(window, 'orientationchange', function() {
        updateDOM();
        closeSubnavs();
    });

    Event.observe(window, 'resize', function() {
        updateDOM();
        closeSubnavs();
    });

});
