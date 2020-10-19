cordova.define("webbridge", function(require, exports, module) {
    var exec = require("cordova/exec");
    var WebBridge = function() {};

    //-------------------------------------------------------------------
    WebBridge.prototype.shopInfo = function(successCallback,errorCallback,param) {

        if (typeof successCallback != "function") {
            console.log("WebBridge.shopInfo failure: success callback parameter must be a function");
            return
        }
        if (typeof errorCallback != "function") {
            console.log("WebBridge.shopInfo failure: success callback parameter must be a function");
            return
        }
        exec(successCallback, errorCallback, 'WebBridge', 'shopInfo', [param]);
    };

    WebBridge.prototype.debugId = function(successCallback,param) {
        if (typeof successCallback != "function") {
            console.log("WebBridge.shopInfo failure: success callback parameter must be a function");
            return
        }
        exec(successCallback, null, 'WebBridge', 'debugId',[param]);
    };

    WebBridge.prototype.callbackClass = function(callbackClassName, successCallback,errorCallback,param) {
        if (typeof successCallback != "function") {
            console.log("WebBridge.shopInfo failure: success callback parameter must be a function");
            return
        }
        if (typeof errorCallback != "function") {
            console.log("WebBridge.shopInfo failure: success callback parameter must be a function");
            return
        }
        exec(successCallback, null, 'WebBridge', callbackClassName,[param]);
    };


    var webBridge = new WebBridge();
    module.exports = webBridge;

});


