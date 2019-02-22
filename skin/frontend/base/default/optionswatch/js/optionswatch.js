var app = new Object();       
    function getProductBySku(displayedSku){
        var sku = displayedSku;
        jQuery.each(productObject,function(index, value ) {
            if(displayedSku == index){
                jQuery.each(value, function( index1, value1 ) {
                    jQuery.each(value1, function( index2, value2 ) {
                    });             
                }); 
            }
        });
    }

jQuery( document ).ready(function() {
    jQuery('.sliderv').slick('destroy');
    jQuery('.sliderh').slick('destroy');
    jQuery('.sliderv').slick({infinite: false,vertical:true,verticalSwiping:true,slidesToShow: 5,slidesToScroll: 1});
    jQuery('.sliderh').slick({infinite: false,vertical:false,verticalSwiping:false,slidesToShow: 1,slidesToScroll: 1});
    var mediaObject = new Object();

    mediaObject.reloadMedia = function(){
        jQuery('.sliderv').slick('destroy');
        jQuery('.sliderh').slick('destroy');
        var furl;
        var pdo_media_images = product_media_object.mediaImages;
            if(product_type == 'simple'){
            var image_array = pdo_media_images['product_'+product_media_object.sku]; 
            }else if(product_type == 'bundle'){
            var image_array = pdo_media_images['product_'+product_media_object.sku]; 
            }else if(product_type == 'configurable'){
            sku = swatches[checkedId].sku;
            var image_array = pdo_media_images['product_'+sku];
            var option_select = document.getElementById('attribute184');
            option_select.querySelector('option[value="'+checkedId+'"]').selected = true;
            }
        var pocount = image_array.length;
        app.i = 0;            
            jQuery.each(image_array, function( index, value ) {
                if(app.i == 0){
                var topObject = jQuery('#default-media-image-slot-0');
                jQuery('#media-image-vertical-slider-panel').html('');
                jQuery('#media-image-horizontal-slider-panel').html('');  
                var splash = jQuery('#image-main');
                    if(product_type == 'configurable'){
                        furl = skin_url+'catalog/product'+value.file;
                    }else if(product_type == 'simple'){
                        furl = value.file;
                    }else if(product_type == 'bundle'){
                        furl = value.file;
                    }else{
                        furl = value.file;
                    }

                    if(product_type == 'configurable'){
                    topObject.find('#media-image-0').attr('src',skin_url+'catalog/product'+value.file);
                    furl = skin_url+'catalog/product'+value.file;
                    }else{
                    topObject.find('#media-image-0').attr('src',value.file);
                    furl = value.file;
                    }
                splash.attr('src',furl);
                } 
            var adji = app.i + 1;
            var o = jQuery('#default-media-image-slot-0');
            var j = o.clone(); 
            var h = o.clone();
            j.attr('id', 'media-image-slot-'+app.i);
            j.find('.media-image-link').attr('id','media-image-link-'+app.i);
            j.find('.media-image-link').attr('data-slide-index',app.i);
            j.find('.media-image-link').attr('data-image-id','999');
            j.find('.media-image-link').attr('data-label','999');
            j.find('.media-image').attr('id','media-image-'+app.i);
                var url = value.file;
                var exists = url.indexOf('media/catalog/product');
                if(exists != -1){
                    j.find('.media-image').attr('src',value.file);
                }else {
                    j.find('.media-image').attr('src',skin_url+'catalog/product'+value.file);
                }
            h.attr('id', 'media-image-slot-'+app.i);
            h.find('.media-image-link').attr('id','splash-media-image-link-'+app.i);
            h.find('.media-image-link').attr('class','splash-media-image-link');
            h.find('.splash-media-image-link').attr('data-slide-index',app.i);
            h.find('.splash-media-image-link').attr('data-image-id','999');
            h.find('.splash-media-image-link').attr('data-label','999');
            h.find('.media-image').attr('id','splash-media-image-'+app.i);
            h.find('.media-image').attr('class','splash-media-image');
                if(exists != -1){
                    h.find('.splash-media-image').attr('src',value.file);
                }else {
                    h.find('.splash-media-image').attr('src',skin_url+'catalog/product'+value.file);
                }


            j.appendTo("#media-image-vertical-slider-panel"); 
            h.appendTo("#media-image-horizontal-slider-panel");        
            app.i++;
            });
            jQuery('.sliderv').slick({infinite: false,vertical:true,verticalSwiping:true,slidesToShow: 5,slidesToScroll: 1});
            jQuery('.sliderh').slick({infinite: false,vertical:false,verticalSwiping:false,slidesToShow: 1,slidesToScroll: 1});
            jQuery('.sliderv').find('.slick-prev').addClass('slick-prev-up');
            jQuery('.sliderv').find('.slick-next').addClass('slick-next-down');
            jQuery('.sliderh').find('.slick-prev').addClass('slick-prev-left');
            jQuery('.sliderh').find('.slick-next').addClass('slick-next-right');            
        return this;                                        
    };

    mediaObject.reloadMedia();
        
        jQuery('#media-image-vertical-panel').click(function(e){
        e.preventDefault();
        var splash = jQuery('#image-main');
        splash.attr('src',e.target.src);    
        });        
        jQuery('#media-image-horizontal-panel').click(function(e){
        e.preventDefault();
        });  
        jQuery('.splash-media-image-link').click(function(e){
        e.preventDefault();
        });

    jQuery('.swatch-container-link').click(function(e){
    var option_select = document.getElementById('attribute184');
    var checked = jQuery(e.currentTarget).hasClass('checked');
    var option_id = jQuery(e.currentTarget.innerHTML).attr('data-node');
        if(!checked){
            jQuery('#swatch-container-image-'+checkedId).removeClass('checked');
            jQuery(e.currentTarget).addClass('checked');
            checkedId = option_id;
        }
    option_select.querySelector('option[value="'+checkedId+'"]').selected = true;
    e.preventDefault();
    mediaObject.reloadMedia();
    });

    jQuery('.media-image-link').click(function(e){
        console.log(e);
        e.preventDefault();
    });
        jQuery('#media-image-vertical-slider-panel').click(function(e){
        e.preventDefault();
        var splash = jQuery('#image-main');
        splash.attr('src',e.target.src);    
        });

        jQuery('div.product-shop').click(function(e){
        var obj = e.target;
        var sku = e.target.parentElement.dataset.swatch;        
            if(typeof sku !== "undefined"){
            mediaObject.reloadMedia(sku);
            } 
 
        }); 
});