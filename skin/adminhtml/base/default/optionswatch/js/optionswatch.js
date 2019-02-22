jQuery(document).ready(function(){
	/*-----------OPTION SWATCH UPLOAD PAGE----------------*/
	var attrOptionJson = jQuery('.optionswatch-adminhtml-swatch-edit #attributeOptionJson').val();
	var attrOptionArray = jQuery.parseJSON(attrOptionJson);
	jQuery('.optionswatch-adminhtml-swatch-edit #attribute_id').change(function(){
		var attributeId = jQuery(this).val();		
		var optionSelect = jQuery('.optionswatch-adminhtml-swatch-edit #option_id');
		optionSelect.empty();
		for(var val in attrOptionArray[attributeId]) {
			if( val!= "")
				jQuery('<option />', {value: val, text: attrOptionArray[attributeId][val]}).appendTo(optionSelect);
		}
	})
	
	/*-----------MEDIA UPLOADER LABEL DROPDOWN----------------
	jQuery('.adminhtml-catalog-product-edit .image-color-label').change(function(){
		var optionId = jQuery(this).val();		
		var imageFile = jQuery(this).attr('image');	
		var mediaGalleryContent = jQuery.parseJSON( jQuery('#media_gallery_content_save').val() );
		for (var i=0; i< mediaGalleryContent.length; i++) {
			if( mediaGalleryContent[i].file ==  imageFile){
				mediaGalleryContent[i].label = optionId;
				break;
			}
		}
		jQuery('#media_gallery_content_save').val(JSON.stringify(mediaGalleryContent));
	})	*/
	
	/*-----------MEDIA UPLOADER LABEL DROPDOWN PRESELECT----------------
	if(jQuery('#media_gallery_content_save').length) {
		var mediaGalleryInfo = jQuery.parseJSON( jQuery('#media_gallery_content_save').val() );
		for (var i=0; i< mediaGalleryInfo.length; i++) {
			var imageFileAttr = mediaGalleryInfo[i].file;
			var labelSelect = jQuery(".adminhtml-catalog-product-edit [image='"+imageFileAttr+"']");
			labelSelect.val(mediaGalleryInfo[i].label);
		}
	}
	*/
	
})

