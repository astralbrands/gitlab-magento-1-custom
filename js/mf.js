function ajr(lnk, sd, dv) {
	if (window.XMLHttpRequest) { // code for IE7+, Firefox, Chrome, Opera, Safari
		xr = new XMLHttpRequest();
	} else { // code for IE6, IE5
		xr = new ActiveXObject("Microsoft.XMLHTTP");
	}
	if (sd == "p") xr.open("POST", lnk, false);
	if (sd == "p") xr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	if (sd != "p") xr.open("GET", lnk, false);
	xr.send(dv);
	if (sd == "x") ds = xr.responseXML;
	if (sd != "x") ds = xr.responseText;
	xr = "";
	return ds;
}

function dg(t, dx) {
	rdt = "";
	if (dx == "0") dx = new Date();
	if (dx != "0") dx = new Date(dx);
	nd = new Number(24 * 60 * 60 * 1000);
	if (t == "1") rdt = (dx.getMonth() + 1) + "/" + dx.getDate() + "/" + dx.getFullYear();
	if (t == "4") rdt = (dx.getMonth() + 1) + "/" + dx.getDate() + "/" + dx.getFullYear();
	if (t == "S") rdt = dx.getDate() + "" + dx.getMonth() + "" + dx.getFullYear() + "" + dx.getHours();
	if (t == "3") rdt = dx;
	if (t == "y") rdt = dx.getFullYear();
	if (t == "10") rdt = Date.parse(dx);
	if (t == "11") rdt = new Number(Date.parse(dx)) + nd;
	if (t == "h") rdt = dx.getHours();
	if (t == "mn") rdt = dx.getMinutes();
	return rdt;
}

function rq(q) {
	try {
		ls = window.top.location.search;
	} catch (Error) {
		ls = location.search;
	}
	switch (cta(ls, q)) {
		case true:
			nsv = new String(ls);
			qvs = new RegExp(q + "=");
			var sa1 = nsv.split(qvs);
			sm = new String(sa1[1]);
			var sa2 = sm.split("&");
			rv = unescape(sa2[0]);
			break;
		default:
			rv = "";
	}
	if (rv == "undefined") rv = "";
	return rv;
}

function cta(st, cv) {
	rv = new RegExp(cv, "gi");
	return rv.test(st);
}

function cCky(name, value, days) {
	if (days) {
		var date = new Date();
		date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
		var expires = "; expires=" + date.toGMTString();
	} else var expires = "";
	document.cookie = name + "=" + value + expires + "; path=/";
}

function rCky(name) {
	var nameEQ = name + "=";
	var ca = document.cookie.split(';');
	for (var i = 0; i < ca.length; i++) {
		var c = ca[i];
		while (c.charAt(0) == ' ') c = c.substring(1, c.length);
		if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
	}
	return null;
}

function eCky(name) {
	cCky(name, "", -1);
}


function chgimg(idv, lk) {
	try {
		document.getElementById(idv).src = lk;
	} catch (Error) {

	}
}

function gspu(idv) {
	try {
		cv = document.getElementById(idv).innerHTML;
	} catch (Error) {
		cv = "";
	}
	return cv;
}

function udpu(idv, udv) {
	try {
		cv = 1;
		document.getElementById(idv).innerHTML = udv;
	} catch (Error) {
		cv = "0";
	}
	return cv;
}

function gclu(idv, nm) {
	try {
		cv = document.getElementsByClassName(idv)[nm].innerHTML
	} catch (Error) {
		cv = "";
	}
	return cv;
}

function udpc(idv, nm, udv) {
	try {
		cv = 1;
		document.getElementsByClassName(idv)[nm].innerHTML = udv;
	} catch (Error) {
		cv = "0";
	}
	return cv;
}

function chng(str, pv, nm, lnk) {

	st = new String(gclu(pv, nm));
	h2t = new String(gclu("slot-title", nm));

	st = st.replace(h2t, str)
	st = st.replace("Learn More", "Shop Now")
	st = st.replace("onclick", "href=\"" + lnk + "\" bg")
	udpc(pv, nm, st);
}


function txtChange(pv, str, nm, lk) {
	st = new String(gclu(pv, nm));
	sw = screen.width;
	evt = (sw < 600) ? "onClick" : "onMouseOver";
	ecb = (sw < 600) ? "onClick" : "onMouseOut";
	swv = "onClick=\"chng('" + str + "','" + pv + "','" + nm + "','" + lk + "' )\" data=";
	st = st.replace("href=", swv);
	udpc(pv, nm, st);
}


function cartf() {
	try {
		sv = new String(gclu("subscription", 0));
		var sn = sv.length;
		if (sn > 0) {
			st = new String(gclu("grand-total-cart", 0));
			st = st.replace("Grand Total", "");
			st = st.replace("$0.00", "");
			st = st.replace("Regular Payment", "Payment Owed");
			st = st.replace("Subscription", "Pre Sale");
			udpc("grand-total-cart", 0, st)
		}
	} catch (Error) {
		//alert("none")
	}
}

function himgs(cl, im, lk, tv, im2, lk2, tv2) {
	str = "<a href=\"" + lk + "\" title=\"" + tv + "\"><img src=\"" + im + "\" style=\"width:100%\" /></a>";
	if (im2 !== "" && lk2 !== "" && tv2 !== "") {
		str = str + "<a href=\"" + lk2 + "\" title=\"" + tv2 + "\"><img src=\"" + im2 + "\" style=\"width:100%\" /></a>";
	}
	udpc(cl, 0, str);
}


function presell() {
	try {
		nv = new String(gspu("product-options-wrapper"));
		var sn = nv.search("Send my first shipment on");
		if (sn > -1) {
			nv = nv.replace("Send my first shipment on", "Send my pre-sale shipment on");
			udpu("product-options-wrapper", nv);
			nv = new String(gclu("subtotal", 0));
			var sn = nv.search("0.00");
			if (sn > 0) udpc("subtotal", 0, "");
		}
	} catch (Error) {
		//alert("none");
	}
}

function gtfv(idv) {
	try {
		rtv = document.getElementById(idv).value;
	} catch (Error) {
		rtv = "";
	}
	return rtv;
}

function stfv(idv, pv) {
	rtvc = "";
	if (pv == "undefined" || pv == undefined) pv = "";
	try {
		document.getElementById(idv).value = pv;
	} catch (Error) {
		rtvc = "";
	}
	return rtvc;
}

function usv() {
	var ib = {
		type: '1'
	};
	ib.firstName = gtfv("billing:firstname");
	ib.lastName = gtfv("billing:lastname");
	ib.email = gtfv("billing:email");
	ib.street1 = gtfv("billing:street1");
	ib.street2 = gtfv("billing:street2");
	ib.city = gtfv("billing:city");
	ib.region = gtfv("billing:region_id");
	ib.country = gtfv("billing:country_id");
	ib.telephone = gtfv("billing:telephone");
	ib.bmonth = gtfv("billing:month");
	ib.zip = gtfv("billing:postcode");
	ib.bday = gtfv("billing:day");
	ib.byear = gtfv("billing:year");
	ib.pwd = gtfv("billing:customer_password");
	localStorage.usv = JSON.stringify(ib);
}

function saveBilling() {
	pw = gtfv("billing:customer_password");
	if (pw == "") {
		pw = dg("10", "0");
		stfv("billing:customer_password", pw);
		stfv("billing:confirm_password", pw);
	}
	usv();
	billing.save();
}

function sff(id, pv) {
	var x = document.getElementById(id);
	var txt = "";
	for (var i = 0; i < x.length; i++) {
		if (x.options[i].value == pv) x.options[i].selected = "true";
	}
	return txt;
}

function bldf() {
	sb = JSON.parse(localStorage.usv);
	stfv("billing:firstname", sb.firstName);
	stfv("billing:lastname", sb.lastName);
	stfv("billing:email", sb.email);
	stfv("billing:street1", sb.street1);
	stfv("billing:street2", sb.street2);
	stfv("billing:city", sb.city);
	stfv("billing:telephone", sb.telephone);
	stfv("billing:postcode", sb.zip);
	sff("billing:region_id", sb.region);
	stfv("billing:customer_password", sb.pwd);
	stfv("billing:confirm_password", sb.pwd);
}

function chout() {
	svf = new String(localStorage.usv);
	hdv = "style=\"display:none\"";
	udpc("page-title", 0, "<h1>Checkout</h1><div id=\"chobox\"></div>")
	nv = new String(gclu("step-title", 0));
	nv = nv.replace("<h2>Checkout Method</h2>", "<h2 style=\"cursor:pointer;\">Have an account? Sign In</h2>");
	udpc("step-title", 0, nv)
	nv = "Sign in to speed up your checkout process. <span onClick=\"checkout.setMethod()\" style=\"cursor:pointer;text-decoration:underline;font-weight:bold;\">Click here</span> to exit this screen and return to checkout.";
	udpc("description", 1, nv)
	bb = new String(gspu("billing-buttons-container"));
	bb = bb.replace("billing.save()", "saveBilling()");
	udpu("billing-buttons-container", bb);
	//document.getElementById("nav").style.display = "none";
	document.getElementById("login:register").checked = true;
	crtv = new String(gspu("popup-minicart"));
	crtv = crtv.replace("class=\"promo\"", hdv);
	crtv = crtv.replace("class=\"subtotal\"", "style=\"font-size:13pt;margin-top:3px;margin-bottom:4px;font-weight:bold;\"");
	crtv = crtv.replace("class=\"summary-items\"", "style=\"margin-bottom:5px;font-weight:bold;\"");
	crtv = crtv.replace("class=\"go-to-cart btn\"", hdv);
	crtv = crtv.replace(/class=\"product-image\"/g, hdv);
	crtv = crtv.replace(/class=\"product-details\"/g, "style=\"margin-bottom:3px;border-bottom: 1px solid;font-size:10pt;padding-bottom:3px;\"");
	//crtv = crtv.replace(/<a/g,"<a style=\"margin-top:3px;border-top: 1px solid;\" ");
	crtv = crtv.replace("mini-products-list", "");
	crtv = crtv.replace("Shopping Bag", "<div class=\"block-title\"><strong><span>Your Shopping Bag</span></strong></div>");
	crtv = "<div class=\"block block-progress opc-block-progress opc-block-progress-step-login\">" + crtv + "</div>";
	rtpg = gspu("checkout-progress-wrapper");
	//udpu("checkout-progress-wrapper", crtv + rtpg);
	document.getElementsByClassName("col-1")[0].style.display = "none";
	document.getElementsByClassName("col-2")[0].style.float = "left";
	//udpc("addshoppers_b_modal",0,"..");
	checkout.setMethod();
	if (svf.length > 15) bldf();
}

function loginJS() {
	lnk = window.location.protocol + "//" + window.location.hostname + "/customer/account/login/";
	dv = "login[username]=" + gtfv("login-email") + "&login[password]=" + gtfv("login-password");
	rv = new String(ajr(lnk, "p", dv));
}

function signinp() {
	stv = new String(gclu("col-2", 0));
	stv = stv.replace("<a", "<a style=\"font-size:9pt;text-decoration:underline;\" class=\"f-left\" onClick=\"cancelreg()\">Cancel and Return to Checkout</a><br /><a");
	stv = stv.replace("onepageLogin(this)", "loginJS()")
	regs1 = "<div style=\"max-width:330px\">" + stv + "</div>";
	udpu("chobox", regs1);
	document.getElementById("checkoutSteps").style.display = "none";
}