var Weltpixel_Ajaxcompare = Weltpixel_Ajaxcompare || {};

Weltpixel_Ajaxcompare.ajaxCompare = function(url) {
    url = url.replace("catalog/product_compare/add","weltpixel_quickview/ajax_catalog_product/compare");
    weltpixel.lightbox.startLoader();
    jQuery.ajax( {
        url : url,
        dataType : 'json',
        success : function(data) {
            if(data.status == 'SUCCESS'){
                if(window.parent.jQuery('.mini-compare').length){                        
                    window.parent.jQuery('.mini-compare').html(data.menubar);                
                }
            }
                
            var responseDiv = new Element('div');
            responseDiv.id = 'wpqv-addtocart-response';
            responseDiv.update(data.message);

            weltpixel.lightbox.open(responseDiv);                
            weltpixel.lightbox.stopLoader();
                
        }
    });
}

jQuery(document).ready(function() {
    weltpixel.lightbox.init();
    jQuery('.wp-add-to-compare').bind('click', function(e) {
        e.preventDefault();
        var url = jQuery(this).attr('href');
        Weltpixel_Ajaxcompare.ajaxCompare(url);
    });
});    