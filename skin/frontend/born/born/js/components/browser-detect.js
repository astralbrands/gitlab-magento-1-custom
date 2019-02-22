define([], function() {

  var BrowserDetect = {
    init: function () {
      this.browser = this.searchString(this.dataBrowser) || "Other";
      this.version = this.searchVersion(navigator.userAgent) || this.searchVersion(navigator.appVersion) || "Unknown";
    },
    searchString: function (data) {
      for (var i = 0; i < data.length; i++) {
        var dataString = data[i].string;
        this.versionSearchString = data[i].subString;

        if (dataString.indexOf(data[i].subString) !== -1) {
          return data[i].identity;
        }
      }
    },
    searchVersion: function (dataString) {
      var index = dataString.indexOf(this.versionSearchString);
      if (index === -1) {
        return;
      }

      var rv = dataString.indexOf("rv:");
      if (this.versionSearchString === "Trident" && rv !== -1) {
        return parseFloat(dataString.substring(rv + 3));
      } else {
        return parseFloat(dataString.substring(index + this.versionSearchString.length + 1));
      }
    },

    dataBrowser: [
      {string: navigator.userAgent, subString: "Edge", identity: "ms_edge"},
      {string: navigator.userAgent, subString: "MSIE", identity: "ie"},
      {string: navigator.userAgent, subString: "Trident", identity: "ie"},
      {string: navigator.userAgent, subString: "Firefox", identity: "firefox"},
      {string: navigator.userAgent, subString: "Opera", identity: "opera"},
      {string: navigator.userAgent, subString: "OPR", identity: "opera"},

      {string: navigator.userAgent, subString: "Chrome", identity: "chrome"},
      {string: navigator.userAgent, subString: "Safari", identity: "safari"}
    ]
  };

  BrowserDetect.init();

  return {
    browser: BrowserDetect.browser,
    version: BrowserDetect.version
  };
});
