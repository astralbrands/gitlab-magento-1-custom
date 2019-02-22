function ampromo_hide_all()
{
    ampromo_hide();

    $('promo_catalog_edit_tabs_ampromo_items_price').up('li').hide();
    if ($('promo_catalog_edit_tabs_ampromo_product_page_banners')) {
        $('promo_catalog_edit_tabs_ampromo_product_page_banners').up('li').hide();
    }

    if ('ampromo_items' == $('rule_simple_action').value || 'ampromo_eachn' == $('rule_simple_action').value){
        $('promo_catalog_edit_tabs_ampromo_items_price').up('li').show();
        if ($('promo_catalog_edit_tabs_ampromo_product_page_banners')) {
            $('promo_catalog_edit_tabs_ampromo_product_page_banners').up('li').show();
        }
    } else if  ('ampromo_product' == $('rule_simple_action').value){
        $('promo_catalog_edit_tabs_ampromo_items_price').up('li').show();
        if ($('promo_catalog_edit_tabs_ampromo_product_page_banners')) {
            $('promo_catalog_edit_tabs_ampromo_product_page_banners').up('li').show();
        }
    } else if ('ampromo_cart'  == $('rule_simple_action').value || 'ampromo_spent'  == $('rule_simple_action').value) {
        $('promo_catalog_edit_tabs_ampromo_items_price').up('li').show();
    }
}

document.observe("dom:loaded", function() {
    ampromo_hide_all();

    if ($('edit_form'))
        $('edit_form').setAttribute('enctype', 'multipart/form-data');
});
