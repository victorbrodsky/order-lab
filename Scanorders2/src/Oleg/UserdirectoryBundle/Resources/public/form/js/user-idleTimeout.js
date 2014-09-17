/**
 * Created with JetBrains PhpStorm.
 * User: oli2002
 * Date: 2/10/14
 * Time: 10:42 AM
 * To change this template use File | Settings | File Templates.
 */

var _idleAfter = 0;
var _ajaxTimeout = 20000;  //15000 => 15 sec

_countdownDialog = $("#dialog-countdown");


$(document).ready(function() {

    var idleTimeout = new idleTimeoutClass();

    idleTimeout.init();
    idleTimeout.setMaxIdletime();
    idleTimeout.checkIdleTimeout();

});


function idleTimeoutClass() { }

idleTimeoutClass.prototype.init = function () {
    this.employees_sitename = "employees";   //"{{ employees_sitename|escape('js') }}";
    // cache a reference to the countdown element so we don't have to query the DOM for it on each ping.
    //this.countdownDialog = $("#dialog-countdown");
    this.urlCommonIdleTimeout = getCommonBaseUrl("keepalive",this.employees_sitename);
};

idleTimeoutClass.prototype.setMaxIdletime = function () {
    //get max idle time from server by ajax
    $.ajax({
        url: getCommonBaseUrl("getmaxidletime",this.employees_sitename),
        type: 'GET',
        //contentType: 'application/json',
        dataType: 'json',
        async: false,
        timeout: _ajaxTimeout,
        success: function (data) {
            //console.debug("data="+data);
            //console.debug("idletime="+data.maxIdleTime);
            //console.debug("maint="+data.maintenance);
            _idleAfter = data.maxIdleTime;
            //idleTimeoutClass.prototype.testfunc();
        },
        //success: this.maxIdleTimeMethod,
        error: function ( x, t, m ) {
            if( t === "timeout" ) {
                getAjaxTimeoutMsg();
            }
            //console.debug("get max idletime: error data="+data);
            _idleAfter = 0;
        }
    });
};

idleTimeoutClass.prototype.checkIdleTimeout = function () {
    //console.log( "############# checkIdleTimeout, testvar="+this.testvar );
    // start the idle timer plugin
    $.idleTimeout('#idle-timeout', '#idle-timeout-keepworking', {
        AJAXTimeout: null,
        failedRequests: 1,
        idleAfter: _idleAfter,
        warningLength: 30,
        pollingInterval: _idleAfter-50,
        keepAliveURL: this.urlCommonIdleTimeout,
        serverResponseEquals: 'OK',
        onTimeout: function(){
            console.log("onTimeout: logout");
            idleTimeoutClass.prototype.onTimeout();
        },
        onIdle: function(){
            console.log("on idle");
            $('#idle-timeout').modal('show');
        },
        onCountdown: function(counter){
            console.log("on Countdown");
            //$("#dialog-countdown").html(counter); // update the counter
            _countdownDialog.html(counter); // update the counter
            //this.countdownDialog.html(counter); // update the counter
        },
        onAbort: function(){
            console.log("onAbort: logout");
            idleTimeoutClass.prototype.onAbort();
        }
    });
};

idleTimeoutClass.prototype.onTimeout = function () {
    console.log("onTimeout: user");
    idlelogout();
};

idleTimeoutClass.prototype.onAbort = function () {
    console.log("onAbort: user");
    idlelogout();
};

//idleTimeoutClass.prototype.testfunc = function() {
//    //console.log("testfunc: user test!");
//    //alert("testfunc: user test!");
//}




//////////////////// Common Timeout Function //////////////////////////

function getAjaxTimeoutMsg() {
    alert("Could not communicate with server: no answer after " + _ajaxTimeout/1000 + " seconds.");
    return false;
}

function keepWorking() {
    //console.log("keep working");
    $('#idle-timeout').modal('hide');
}

function logoff() {
    //console.log("logoff");
    window.onbeforeunload = null;
    var urlRegularLogout = getCommonBaseUrl("idlelogout");
    window.location = urlRegularLogout;
}

//redirect to /idlelogout controller => logout with message of inactivity
function idlelogout() {
    window.onbeforeunload = null;
    var urlIdleTimeoutLogout = getCommonBaseUrl("idlelogout");
    window.location = urlIdleTimeoutLogout;
}

//////////////////// EOF Common Timeout Function //////////////////////////