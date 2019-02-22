"use strict";

/**
 * Sets a cookie
 * @param string cName  Name of cookie
 * @param string cValue Cookies Value
 * @param string exDays Expiration in days for the cookie
 */
function setCookie(cName, cValue, exDays) {
    var date = new Date();
    date.setTime(date.getTime() + (exDays*1000*60*60*24));
    var expires = "expires=" + date.toGMTString();
    return window.document.cookie = cName+"="+cValue+"; "+expires;
}

/**
 * Get a cookie
 * @param string cName, cookie name
 * @return string String, cookie value
 */
function getCookie(cName) {
    var name = cName + "=";
    var cArr = window.document.cookie.split(";");
    var i, cookie;
    for(i=0; i<cArr.length; i++) {
        cookie = cArr[i].trim();
        if (cookie.indexOf(name) === 0) {
            return cookie.substring(name.length, cookie.length);
        }
    }
    return;
}

/**
 * Gets a url param from the url NOTE: This function will not work for array
 * params
 * @param  string urlParamName [description]
 * @return object Return the param object if it is found,
 * otherwise returns false
 */
function getURLParam(urlParamName) {
    var queryString =  window.location.search.slice(1);
    var paramObj = {};
    if (queryString) {
        //Remove # anchors
        queryString = queryString.split("#")[0];
        //Check if urlParam is set in current url
        if(queryString.indexOf(urlParamName) !== -1){
            var queryArr = queryString.split("&");
            var i, param;
            for(i = 0; i<queryArr.length; i++) {
                param = queryArr[i].split("=");
                if(param[0] === urlParamName) {
                    paramObj[param[0]] = param[1];
                    return paramObj;
                }
            }
        } else {
            return;
        }
    }
    return;
}

/**
 * Check if a query Param is set in the URL and if it is sets the CJ cookie
 * @param string cjevent Number to be stored to identify user (Optional)
 */
function setCJEvent(cjevent) {
    if (!cjevent) {
        var urlParam = getURLParam("cjevent");
        if(urlParam) {
            cjevent = urlParam.cjevent;
        } else {
            return;
        }
    }
    setCookie("cjevent", cjevent, 45);
}


/**
 * Returns the CJCookie if it exists or false if it does not
 * @return string | boolean Return the cjevent cookie value if it exists,
 * otherwise it will return false.
 */
function getCJEvent(){
    return getCookie("cjevent");
}