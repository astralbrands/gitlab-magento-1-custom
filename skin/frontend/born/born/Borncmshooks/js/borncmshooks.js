function borncmshooksGetAllData(page_code){
	var controller_url = "http://palacio.bornlocal.com/borncmshooks/index/getalldata/";
    new Ajax.Request(controller_url , {
    method: 'post',
    parameters: {'page_code':page_code},
    onComplete: function(borncmshooks){
    	var response = eval("(" + borncmshooks.responseText + ")");
        console.log(response);
    }});
}

function borncmshooksGetSection(page_code, section_code){
	var controller_url = "http://palacio.bornlocal.com/borncmshooks/index/getsection/";
    new Ajax.Request(controller_url , {
    method: 'post',
    parameters: {'page_code':page_code,'section_code':section_code},
    onComplete: function(borncmshooks){
    	var response = eval("(" + borncmshooks.responseText + ")");
        console.log(response);
    }});
}

function borncmshooksGetField(page_code, field_code){
	var controller_url = "http://palacio.bornlocal.com/borncmshooks/index/getfield/";
    new Ajax.Request(controller_url , {
    method: 'post',
    parameters: {'page_code':page_code,'field_code':field_code},
    onComplete: function(borncmshooks){
    	var response = eval("(" + borncmshooks.responseText + ")");
        console.log(response);
    }});
}

function borncmshooksGetRow(page_code, row_code){
	var controller_url = "http://palacio.bornlocal.com/borncmshooks/index/getrow/";
    new Ajax.Request(controller_url , {
    method: 'post',
    parameters: {'page_code':page_code,'row_code':row_code},
    onComplete: function(borncmshooks){
    	var response = eval("(" + borncmshooks.responseText + ")");
        console.log(response);
    }});
}