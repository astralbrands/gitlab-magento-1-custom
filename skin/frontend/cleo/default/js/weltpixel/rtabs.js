

(function ( $ ) {

    $.fn.rtabs = function () {
        var i = 0;
        var links = $(this).find('.tabs li');
        var contents = this.children('.tabs-content').children('div');

        contents.each(function() {
            $(this).addClass('tab-'+i);
            i++;
        });

        i = 0;

        links.each(function() {
            $(this).addClass('tab-'+i);
            i++;
        }).click(function() {
            //$(this).attr('class')
            links.removeClass('active');
            var currentClass = $(this).attr('class');

            $(this).addClass('active');

            contents.hide();

            contents.each(function() {
                if($(this).hasClass(currentClass)) {
                    $(this).show();
                }
            });

        }).first().click();

    }
})( jQuery );

