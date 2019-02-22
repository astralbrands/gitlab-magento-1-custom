if(typeof String.prototype.trim !== 'function') {
  String.prototype.trim = function() {
    return this.replace(/^\s+|\s+$/g, ''); 
  }
}

var CookieManager = CookieManager || {}

CookieManager.init = function(cookieLifeTime) {
    this.cookieLifeTime = cookieLifeTime;
    this.pageCounter = 'weltpixel_pagecounter';
}

CookieManager.setCookie = function(cname,cvalue) {
    var d = new Date();
    d.setTime(d.getTime()+(this.cookieLifeTime*24*60*60*1000));
    var expires = "expires="+d.toGMTString();
    document.cookie = cname + "=" + cvalue + "; " + expires + ";path=" + Mage.Cookies.path; //last part adds to all the domain the cookie, it can be removed + ";path=/"
}

CookieManager.getCookie = function(cname)
{
    var name = cname + "=";
    var ca = document.cookie.split(';');
    for(var i=0; i<ca.length; i++) 
    {
        var c = ca[i].trim();
        if (c.indexOf(name)==0) return c.substring(name.length,c.length);
    }
    return "";
}

CookieManager.checkCookie = function(cname)
{
    var cValue = this.getCookie(cname);
    if (cValue != "")
    {
        return true;
    }
    else 
    {
        return false;
    }
}

CookieManager.countPages = function() {
    this.setCookie(this.pageCounter, this.getCookie(this.pageCounter) - 0 + 1);
}

CookieManager.getPageCount = function() {
    return this.getCookie(this.pageCounter) - 0;
}