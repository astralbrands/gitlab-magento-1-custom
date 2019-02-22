try {
    Event.observe(window, 'load', function() {
        weltpixel.quickview.inIframe = true;
        setTimeout(parent.weltpixel.quickview.adjustIframe(), 5000);
        //parent.weltpixel.quickview.adjustIframe();
    });                                           
} catch (e) {
    console.log(e);
}
