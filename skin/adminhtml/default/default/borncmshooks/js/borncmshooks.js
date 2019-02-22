function typeFetcher(link){
    var type = window.document.getElementById('cms_content_type');
    var sel_type = type.selectedIndex;
    var sel_type_node = type.options[sel_type];
    var sel_type_value = sel_type_node.getAttribute('value');
    
    
    var target = document.getElementById('cms_content_target');
    var controller_url = link;
    new Ajax.Request(controller_url , {
        method: 'post',
        parameters: {"type" : sel_type_value},
        onComplete: function(borncmshooks){
            Element.hide('loading-mask');
            target.innerHTML = borncmshooks.responseText;
        }
    });
}

function saveSection(save_link, get_link, hook_id,section_id){
    section_id = typeof section_id !== 'undefined' ? section_id : false;
    section_name = document.getElementById('ajax_section_name').value;
    section_order = document.getElementById('ajax_section_order').value;
    section_status = document.getElementById('ajax_section_status');
    section_status_value = section_status.options[section_status.selectedIndex].value;
    if(section_name == ''){
        alert('Enter A Section Name');
    }else{
        var controller_url = save_link;
        new Ajax.Request(controller_url , {
        method: 'post',
        parameters: {'hook_id' : hook_id, 'section':section_name, 'section_order':section_order ,'section_status':section_status_value, 'section_id' : section_id},
        onComplete: function(borncmshooks){
            Element.hide('loading-mask');
            form_link = borncmshooks.responseText;
            var response = eval("(" + borncmshooks.responseText + ")");
            getSections(get_link,hook_id, true);
            getFields(''+response.show_form+'',''+response.get_fields+'',response.section_id);
            forms = document.getElementById('form_region_form');
            forms.innerHTML = '';
        }
        });
    }
}
function getSections(link, hook_id, remove_children){
    remove_children = typeof remove_children !== 'undefined' ? remove_children : false;
    var sections = document.getElementById('section_region_sections');
    var controller_url = link;
        new Ajax.Request(controller_url , {
        method: 'post',
        parameters: {"hook_id" : hook_id},
        onComplete: function(borncmshooks){
            sections.innerHTML = borncmshooks.responseText;
            console.log(link);
            console.log(hook_id);
            console.log(remove_children);
            if(remove_children == false){
            document.getElementById('field_region_field').innerHTML = '';
            document.getElementById('form_region_form').innerHTML = '';
            }
        }
    });
}

function saveField(save_link,get_link,hook_id,field_id){
    field_id = typeof field_id !== 'undefined' ? field_id : false;
    field_name = document.getElementById('ajax_field_name').value;
    field_order = document.getElementById('ajax_field_order').value;
    field_section = document.getElementById('ajax_field_section');
    field_section_value = field_section.options[field_section.selectedIndex].value;
    field_status = document.getElementById('ajax_field_status');
    field_status_value = field_status.options[field_status.selectedIndex].value;
    if(field_name == ''){
        alert('Enter A Field Name');
    }else{
        var controller_url = save_link;
        new Ajax.Request(controller_url , {
        method: 'post',
        parameters: {'hook_id' : hook_id, 
                     'field':field_name, 
                     'field_order':field_order,
                     'field_section':field_section_value,
                     'field_status':field_status_value, 
                     'field_id' : field_id},
        onComplete: function(borncmshooks){
            Element.hide('loading-mask');
            var response = eval("(" + borncmshooks.responseText + ")");
            getFields(false,''+response.get_fields+'',response.section_id);
            getForms(''+response.show_form+'',''+response.get_forms+'',response.field_id);
            }
        });
    }
}
function getFields(form_link,link, section_id, remove_children){
    remove_children = typeof remove_children !== 'undefined' ? remove_children : false;
    var fields = document.getElementById('field_region_field');
    var controller_url = link;
        new Ajax.Request(controller_url , {
        method: 'post',
        parameters: {"section_id" : section_id},
        onComplete: function(borncmshooks){
            fields.innerHTML = borncmshooks.responseText;
            if(form_link == false){
                document.getElementById('form-container').innerHTML = '';
            }else{
                showEditContentForm(form_link, 'section', section_id);
                
            }
        }
    });
    
}
function getFieldsForDropdown(get_link, row){
    row = typeof row !== 'undefined' ? row : false;
    if(row == true){
        var section = document.getElementById('row_section');
    }else{
        var section = document.getElementById('ajax_form_section');
    }
    
    var section_index = section.selectedIndex;
    var selected_section = section.options[section.selectedIndex].value;
    if(section_index != 0){
        var controller_url = get_link;
        new Ajax.Request(controller_url, {
            method: 'post',
            parameters: {'section_id' : selected_section},
            onComplete: function(borncmshooks){
                if(row == true){
                    var fields = document.getElementById('row_field');
                }else{
                    var fields = document.getElementById('ajax_form_field');
                }
                fields.disabled = false;
                fields.innerHTML = borncmshooks.responseText;
            }
        });
    }else{
        if(row == true){
            var fields = document.getElementById('row_field');
            var forms = document.getElementById('row_form');
            forms.value = '';
            forms.disabled = true;
        }else{
            var fields = document.getElementById('ajax_form_field');
        }
        fields.value = '';
        fields.disabled = true;
    }
}

function getFormsForDropdown(get_link){
    var field = document.getElementById('row_field');
    var field_index = field.selectedIndex;
    var selected_field = field.options[field.selectedIndex].value;
    if(field_index != 0){
        var controller_url = get_link;
        new Ajax.Request(controller_url, {
            method: 'post',
            parameters: {'field_id' : selected_field},
            onComplete: function(borncmshooks){
                var forms = document.getElementById('row_form');
                forms.disabled = false;
                forms.innerHTML = borncmshooks.responseText;
            }
        });
    }else{
        var forms = document.getElementById('row_form');
        forms.value = '';
        forms.disabled = true;
    }
}
function saveForm(save_link,get_link,hook_id,form_id){
    form_id = typeof form_id !== 'undefined' ? form_id : false;
    form_name = document.getElementById('ajax_form_name').value;
    form_description = document.getElementById('ajax_form_description').value;
    form_section = document.getElementById('ajax_form_section');
    form_section_value = form_section.options[form_section.selectedIndex].value;
    form_status = document.getElementById('ajax_form_status');
    form_status_value = form_status.options[form_status.selectedIndex].value;
    if(form_name == ''){
        alert('Enter A Field Name');
    }else{
        if(form_section.selectedIndex == 0){
            alert('Select a section for this form');
        }else{
            form_field = document.getElementById('ajax_form_field');
            if(form_field.selectedIndex == 0){
                alert('Select a field for this form');
            }else{
                form_field_value = form_field.options[form_field.selectedIndex].value;
                var controller_url = save_link;
                new Ajax.Request(controller_url , {
                method: 'post',
                parameters: {'hook_id' : hook_id, 
                             'form':form_name, 
                             'form_description':form_description,
                             'form_section':form_section_value,
                             'form_field':form_field_value,
                             'form_status':form_status_value, 
                             'form_id' : form_id},
                onComplete: function(borncmshooks){
                    Element.hide('loading-mask');
                    var response = eval("(" + borncmshooks.responseText + ")");
                    getFields(false,''+response.get_fields+'', form_section_value);
                    getForms(false,get_link,form_field_value);
                    getForm(''+response.show_form+'',''+response.get_forms+'',response.form_id);
                    }
                });
            }
        }
    }
}
function getForms(form_link,link, field_id){
        var forms = document.getElementById('form_region_form');
        var controller_url = link;
        new Ajax.Request(controller_url , {
        method: 'post',
        parameters: {"field_id" : field_id},
        onComplete: function(borncmshooks){
            forms.innerHTML = borncmshooks.responseText;
            if(form_link != false){
                showEditContentForm(form_link, 'field', field_id);
            }
        }
    });
}
function getForm(form_link,get_forms_link,form_id){
    showEditContentForm(form_link, 'form', form_id);
}
function deleteContent(link, type, type_id){
            switch(type){
               case 'section':
                   var controller_url = link;
                    new Ajax.Request(controller_url , {
                    method: 'post',
                    parameters: {'type': type, 'type_id' : type_id},
                    onComplete: function(borncmshooks){
                        Element.hide('loading-mask');
                        var response = eval("(" + borncmshooks.responseText + ")");
                        getSections(''+response.get_sections+'',response.hook_id);
                        if(document.getElementById('form-container')){
                            edit_form = document.getElementById('form-container');
                            edit_form.innerHTML = '';
                        }
                    }});
               break;
               
               case 'field':
                   var controller_url = link;
                    new Ajax.Request(controller_url , {
                    method: 'post',
                    parameters: {'type': type, 'type_id' : type_id},
                    onComplete: function(borncmshooks){
                        Element.hide('loading-mask');
                        var response = eval("(" + borncmshooks.responseText + ")");
                        getFields(''+response.show_form+'',''+response.get_fields+'',response.section_id);
                        if(document.getElementById('form-container')){
                            edit_form = document.getElementById('form-container');
                            edit_form.innerHTML = '';
                        }
                    }});
               break;
               
               case 'form':
                   var controller_url = link;
                    new Ajax.Request(controller_url , {
                    method: 'post',
                    parameters: {'type': type, 'type_id' : type_id},
                    onComplete: function(borncmshooks){
                        Element.hide('loading-mask');
                        var response = eval("(" + borncmshooks.responseText + ")");
                        getForms(''+response.show_form+'',''+response.get_forms+'',response.field_id);
                        if(document.getElementById('form-container')){
                            edit_form = document.getElementById('form-container');
                            edit_form.innerHTML = '';
                        }
                    }});
               break;
               
               case 'element':
                    var controller_url = link;
                    new Ajax.Request(controller_url , {
                    method: 'post',
                    parameters: {'type': type, 'type_id' : type_id},
                    onComplete: function(borncmshooks){
                        Element.hide('loading-mask');
                        var response = eval("(" + borncmshooks.responseText + ")");
                        getForm(''+response.show_form+'',''+response.get_forms+'',response.form_id);
                    }});
               break;
            }
                    
}
function showNewContentForm(new_form_link, type, hook_id){
    var controller_url = new_form_link;
    new Ajax.Request(controller_url , {
    method: 'post',
    parameters: {"type" : type, 'hook_id': hook_id},
    onComplete: function(borncmshooks){
        document.getElementById('form-container').innerHTML = borncmshooks.responseText;
    }});
}
function showEditContentForm(edit_form_link, type, type_id){
    var controller_url = edit_form_link;
    new Ajax.Request(controller_url , {
    method: 'post',
    parameters: {"type" : type, 'type_id': type_id},
    onComplete: function(borncmshooks){
        document.getElementById('form-container').innerHTML = borncmshooks.responseText;
    }
    });
}

function showAddElementForm(add_element_form_link,hook_id,section_id,field_id,form_id, element_id){
    element_id = typeof element_id !== 'undefined' ? element_id : false;
    var controller_url = add_element_form_link;
    if(element_id == false){
        new Ajax.Request(controller_url , {
        method: 'post',
        parameters: {'hook_id' : hook_id, 
                    'section_id': section_id,
                    'field_id': field_id,
                    'form_id': form_id},
        onComplete: function(borncmshooks){
            if(document.getElementById('borncmshooks_elementform') == null){
                document.getElementById('borncmshooks_formelements').innerHTML += borncmshooks.responseText;
            }
        }});
    }else{
        new Ajax.Request(controller_url , {
        method: 'post',
        parameters: {'hook_id' : hook_id, 
                    'section_id': section_id,
                    'field_id': field_id,
                    'form_id': form_id,
                    'element_id':element_id},
        onComplete: function(borncmshooks){
                if(document.getElementById('borncmshooks_elementform') != null){
                    div = document.getElementById('borncmshooks_elementform');
                    div.parentNode.removeChild(div);
                    document.getElementById('borncmshooks_formelements').innerHTML += borncmshooks.responseText;
                }else{                 
                   document.getElementById('borncmshooks_formelements').innerHTML += borncmshooks.responseText;
                }
            }});
    }
}
function applyElementToForm(save_link, hook_id, section_id, field_id, form_id, element_id){
    element_id = typeof element_id !== 'undefined' ? element_id : false;
    var controller_url = save_link;
    element_type = document.getElementById('element_type');
    if(element_type.selectedIndex == 0){
        alert('Please Select an Element Type')
    }else{
        element_type_value = element_type.options[element_type.selectedIndex].value;
        element_config = document.getElementById('element_config').value;
        element_label = document.getElementById('element_label').value;
        element_order = document.getElementById('element_order').value;
        if(element_id != false){
            new Ajax.Request(controller_url , {
            method: 'post',
            parameters: {'hook_id' : hook_id, 
                        'section_id': section_id,
                        'field_id': field_id,
                        'form_id': form_id,
                        'element_id':element_id,
                        'element_type': element_type_value,
                        'element_config':element_config,
                        'element_label':element_label,
                        'element_order':element_order},
            onComplete: function(borncmshooks){
                var response = eval("(" + borncmshooks.responseText + ")");
                getForm(''+response.show_form+'',''+response.get_forms+'',response.form_id);
            }});
        }else{
            new Ajax.Request(controller_url , {
            method: 'post',
            parameters: {'hook_id' : hook_id, 
                        'section_id': section_id,
                        'field_id': field_id,
                        'form_id': form_id,
                        'element_type': element_type_value,
                        'element_config':element_config,
                        'element_label':element_label,
                        'element_order':element_order},
            onComplete: function(borncmshooks){
                var response = eval("(" + borncmshooks.responseText + ")");
                getForm(''+response.show_form+'',''+response.get_forms+'',response.form_id);
            }});
        }
    }
}

function removeAddElementForm(){
    form = document.getElementById('borncmshooks_elementform');
    form.parentNode.removeChild(form);
}

function saveContent(save_link,type,get_link,hook_id,type_id){
    type_id = typeof type_id !== 'undefined' ? type_id : false;
    if(type_id === false){
       type_id = null;
    }
    switch(type){
        case 'section':
            saveSection(save_link, get_link, hook_id,type_id);
        break;
        
        case 'field':
            saveField(save_link, get_link, hook_id,type_id);
        break;
        
        case 'form':
            saveForm(save_link, get_link, hook_id,type_id);
        break;
    }
}

function removeMiniform(button){
    $miniform = button.parentNode;
    $miniform.innerHTML = '';
}

function setTabToActive(){
    $('borncmshooks_tabs_section_manage_section_content').style.display = 'block';
    $('borncmshooks_tabs_form_section_content').style.display = 'none';
    $('borncmshooks_tabs_form_grid_content').style.display = 'none';
    $('borncmshooks_tabs_form_section').removeClassName('active');
    $('borncmshooks_tabs_form_grid').removeClassName('active');
    $('borncmshooks_tabs_section_manage_section').addClassName('active');
}

function reloadEditor () {
    
     var location = document.location.origin;

wysiwygpage_content = new tinyMceWysiwygSetup("page_content", {
  "enabled":true,
  "hidden":false,
 "use_container":false,
 "add_variables":true,
 "add_widgets":true,
 "no_display":false,
  "translator":{},
  "encode_directives":true,
 "directives_url": location+"\/index.php\/borncmshooks\/cms_wysiwyg\/directive\/key\/3615fa92c6434208891002438144587a8afd5d2f4f179770a53c733c54b62b21\/",
 "popup_css": location+"\/js\/mage\/adminhtml\/wysiwyg\/tiny_mce\/themes\/advanced\/skins\/default\/dialog.css",
 "content_css": location+"\/js\/mage\/adminhtml\/wysiwyg\/tiny_mce\/themes\/advanced\/skins\/default\/content.css",
  "width":"100%",
  "plugins":[
    {
     "name":"magentovariable",
     "src": location+"\/js\/mage\/adminhtml\/wysiwyg\/tiny_mce\/plugins\/magentovariable\/editor_plugin.js",
     "options":{
       "title":"Insert Variable...",
       "url": location+"\/index.php\/borncmshooks\/system_variable\/wysiwygPlugin\/key\/2ca0bce05fac65132b2b205005ff054cb1a2a29c8e7a36b6898ff36cb4b56732\/",
     "onclick": {
        "search": ["html_id"],
        "subject":"MagentovariablePlugin.loadChooser('"+location+"\/index.php\/borncmshooks\/system_variable\/wysiwygPlugin\/key\/2ca0bce05fac65132b2b205005ff054cb1a2a29c8e7a36b6898ff36cb4b56732\/', '{{html_id}}');"
        },
      "class":"add-variable plugin"
      }
    }
  ],
 "directives_url_quoted": location+"\/index\\.php\/borncmshooks\/cms_wysiwyg\/directive\/key\/3615fa92c6434208891002438144587a8afd5d2f4f179770a53c733c54b62b21\/",
  "add_images":true,
 "files_browser_window_url": location+"\/index.php\/admin\/cms_wysiwyg_images\/index\/",
  "files_browser_window_width":1000,
 "files_browser_window_height":600,
 "widget_plugin_src": location+"\/js\/mage\/adminhtml\/wysiwyg\/tiny_mce\/plugins\/magentowidget\/editor_plugin.js",
 "widget_images_url": location+"\/skin\/adminhtml\/default\/enterprise\/images\/widget\/",
 "widget_placeholders": ["catalog__category_widget_link.gif","catalog__product_widget_link.gif","catalog__product_widget_new.gif","cms__widget_block.gif","cms__widget_page_link.gif","default.gif","enterprise_banner__widget_banner.gif","enterprise_catalogevent__widget_lister.gif","enterprise_cms__widget_menu.gif","enterprise_cms__widget_node.gif","enterprise_cms__widget_pagination.gif","reports__product_widget_compared.gif","reports__product_widget_viewed.gif"],
 "widget_window_url": location+"\/index.php\/borncmshooks\/widget\/index\/key\/ca3b0784865ff57eb8d508eb10d2563d6862c48849b71b130e691e5f0d4e58e6\/","firebug_warning_title":"Warning","firebug_warning_text":"Firebug is known to make the WYSIWYG editor slow unless it is turned off or configured properly.","firebug_warning_anchor":"Hide"});

wysiwygpage_content.setup();


closeEditorPopup = function(name) {
                    if ((typeof popups != "undefined") && popups[name] != undefined && !popups[name].closed) {
                        popups[name].close();
                   }
                };

Event.observe("togglepage_content", "click", wysiwygpage_content.toggle.bind(wysiwygpage_content));
}

function showFormElements(get_link, hook_id){

    section = document.getElementById('row_section');
    field = document.getElementById('row_field');
    form = document.getElementById('row_form');
    
    section_value = section.options[section.selectedIndex].value;
    field_value = field.options[field.selectedIndex].value;
    form_value = form.options[form.selectedIndex].value;

    if(form.selectedIndex === 0){
        document.getElementById('borncmshooksrow_tabs_form_section_content').innerHTML = '';
        $('borncmshooksrow_tabs_row_section').addClassName('active');
        $('borncmshooksrow_tabs_form_section').removeClassName('active');
        $('borncmshooksrow_tabs_row_section_content').style.display = 'block';
        $('borncmshooksrow_tabs_form_section_content').style.display = 'none';
    }else{
        var controller_url = get_link;
        new Ajax.Request(controller_url , {
        method: 'post',
        parameters: {'hook_id': hook_id, 'section_id' : section_value, 'field_id': field_value, 'form_id': form_value},
        onComplete: function(borncmshooks){
            child = borncmshooks.responseText;
            document.getElementById('borncmshooksrow_tabs_form_section_content').innerHTML = child;
            $('borncmshooksrow_tabs_row_section').removeClassName('active');
            $('borncmshooksrow_tabs_form_section').addClassName('active');
            $('borncmshooksrow_tabs_row_section_content').style.display = 'none';
            $('borncmshooksrow_tabs_form_section_content').style.display = 'block';
            if (document.getElementById('togglepage_content')) {
                reloadEditor();
            }
        }});
    reloadEditor();
    }
}
    
    function showNewFormElements(get_link, hook_id){

    form = document.getElementById('row_form');
    
    section_value = form.options[form.selectedIndex].getAttribute('data-section');
    field_value = form.options[form.selectedIndex].getAttribute('data-field');
    form_value = form.options[form.selectedIndex].value;

    if(form.selectedIndex === 0){
        document.getElementById('borncmshooksrow_tabs_form_section_content').innerHTML = '';
        $('borncmshooksrow_tabs_row_section').addClassName('active');
        $('borncmshooksrow_tabs_form_section').removeClassName('active');
        $('borncmshooksrow_tabs_row_section_content').style.display = 'block';
        $('borncmshooksrow_tabs_form_section_content').style.display = 'none';
    }else{
        var controller_url = get_link;
        new Ajax.Request(controller_url , {
        method: 'post',
        parameters: {'hook_id': hook_id, 'section_id' : section_value, 'field_id': field_value, 'form_id': form_value},
            onComplete: function(borncmshooks){

            child = borncmshooks.responseText;
            document.getElementById('borncmshooksrow_tabs_form_section_content').innerHTML = child;
            $('borncmshooksrow_tabs_row_section').removeClassName('active');
            $('borncmshooksrow_tabs_form_section').addClassName('active');
            $('borncmshooksrow_tabs_row_section_content').style.display = 'none';
            $('borncmshooksrow_tabs_form_section_content').style.display = 'block';
            if (document.getElementById('togglepage_content')) {
                reloadEditor();
            }
        }});
    reloadEditor();

    }
}