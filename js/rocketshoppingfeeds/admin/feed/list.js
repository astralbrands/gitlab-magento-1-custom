function setCookie(cname, cvalue, exdays) {
    var d = new Date();
    d.setTime(d.getTime() + (exdays*24*60*60*1000));
    document.cookie = cname + "=" + cvalue + "; expires=" + d.toUTCString() + "; path=/";
}

function getCookie(cname) {
    var name = cname + "=";
    var ca = document.cookie.split(';');
    for(var i=0; i<ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0)==' ') c = c.substring(1);
        if (c.indexOf(name) == 0) return c.substring(name.length,c.length);
    }
    return null;
}

Event.observe(window, 'load', function () {
    if (typeof rocketshoppingfeeds_feed_gridJsObject != 'undefined') {
        var timeout = null;
        var isIdle = true;
        $('grid_autorefresh_check').observe('change', function(){
            setCookie('grid_autorefresh_check', this.checked);
        });

        $(document).on('mousemove', function() {
            isIdle = false;
            clearTimeout(timeout);
            timeout = setTimeout(function() {isIdle = true;}, 5000);
        });

        setInterval(function () {
            if ($('grid_autorefresh_check').checked && isIdle === true) {
                rocketshoppingfeeds_feed_gridJsObject.doFilter();
            }
        }, 15000);
    }
});