/**
 * Created with JetBrains PhpStorm.
 * User: oli2002
 * Date: 2/10/14
 * Time: 10:42 AM
 * To change this template use File | Settings | File Templates.
 */

var _idleAfter = 0;
var _ajaxTimeout = 20000;  //15000 => 15 sec
var _maxIdleTime = $("#maxIdleTime").val();
var _siteEmail = $("#siteEmail").val();
var _serverActive = false;
_countdownDialog = $("#dialog-countdown");


$(document).ready(function() {

    // Prevent Dropzone from auto discovering this element
    if( typeof Dropzone !== 'undefined' ) {
        Dropzone.autoDiscover = false;
    }

    var idleTimeout = new idleTimeoutClass();

    idleTimeout.init();
    //idleTimeout.setMaxIdletime();
    idleTimeout.checkIdleTimeout();

});


function idleTimeoutClass() { }

idleTimeoutClass.prototype.init = function () {
    
    this.employees_sitename = "employees";   //"{{ employees_sitename|escape('js') }}";
    // cache a reference to the countdown element so we don't have to query the DOM for it on each ping.
    //this.countdownDialog = $("#dialog-countdown");
    this.urlCommonIdleTimeout = getCommonBaseUrl("common/keepalive",this.employees_sitename);
    
    
    this.setMaxIdletime();
    
    //Centralize Idle Timeout Logout for multiple tabs/browser windows via setInterval js function that sets the activity variable on the server to 1 every minute if there was any activity
    //https://github.com/thorst/jquery-idletimer
    // timeout is in milliseconds
    var timerIdleTime = 60000; //1 min = 60000 milliseconds
    console.debug("event active idleTimer timerIdleTime="+timerIdleTime);
    $( document ).idleTimer( timerIdleTime );
     
    //$( document ).on( "idle.idleTimer", function(event, elem, obj){
        // function you want to fire when the user goes idle
    //});

    $( document ).on( "active.idleTimer", function(event, elem, obj, triggerevent){
        // function you want to fire when the user becomes active again
        console.debug("event active idleTimer _idleAfter="+_idleAfter);
        $.ajax({
            url: getCommonBaseUrl("common/setserveractive",this.employees_sitename),
            type: 'GET',
            //contentType: 'application/json',
            dataType: 'json',
            async: false,
            timeout: _ajaxTimeout,
            success: function (data) {
                console.debug("data="+data+"; _idleAfter="+_idleAfter);               
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
    });
    
    //how to notify the server on activity every 1 minutes:
    // get time last active event fired
    // returns: number
    // $( document ).idleTimer("getLastActiveTime");
    
};

idleTimeoutClass.prototype.setMaxIdletime = function () {
    
    if( _maxIdleTime ) {
        console.log("_maxIdleTime is set = " + _maxIdleTime);
        _idleAfter = _maxIdleTime;
        return;
    }
    
    //get max idle time from server by ajax
    $.ajax({
        url: getCommonBaseUrl("common/getmaxidletime",this.employees_sitename),
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
            //fired on idle timeout from server: server response is not equal to the expected
            console.log("onTimeout: logout");
            idleTimeoutClass.prototype.onTimeout();
        },
        onIdle: function(){
            //fired on no activity on the page
            console.log("on idle");
//            _serverActive = idleTimeoutClass.prototype.isServerActive();
            $('#idle-timeout').modal('show');
//            if( _serverActive ) {              
//                //$(this).dialog('close');
//                //$(this).data('idletimeout').resume.trigger('click');              
//                //keepWorking();                              
//                return false;
//            } else {
//                $('#idle-timeout').modal('show');
//            }                

        },
        onCountdown: function(counter){
            console.log("on Countdown");
            
            //theoretically this shoud not have happened, but just in case check the server before start counter
//            _serverActive = idleTimeoutClass.prototype.isServerActive();
//            if( _serverActive ) {
//                $("#idle-timeout-keepworking").trigger('click');
//                return;
//            }
                       
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

idleTimeoutClass.prototype.isServerActive = function () {
    //check if the other page is active
    var active = false;
    $.ajax({
        url: getCommonBaseUrl("common/isserveractive",this.employees_sitename),
        type: 'GET',
        //contentType: 'application/json',
        dataType: 'json',
        async: false,
        timeout: _ajaxTimeout,
        success: function (data) {
            console.debug("data="+data);
            if( data == "OK" ) {
                console.debug("OK data="+data);
                active = true;
            }
        },
        //success: this.maxIdleTimeMethod,
        error: function ( x, t, m ) {
            console.debug("isserveractive error???");
            if( t === "timeout" ) {
                console.debug("isserveractive timeout???");
                getAjaxTimeoutMsg();
            }
            //console.debug("get max idletime: error data="+data);
            _idleAfter = 0;
        }
    });
    
    console.debug("active="+active);
    return active;
};


idleTimeoutClass.prototype.onTimeout = function () {
    console.log("onTimeout: user");
    idlelogout();
};

idleTimeoutClass.prototype.onAbort = function () {
    console.log("onAbort: user");
    //getAjaxTimeoutMsg();
    idlelogout();
};

//idleTimeoutClass.prototype.testfunc = function() {
//    //console.log("testfunc: user test!");
//    //alert("testfunc: user test!");
//}




//////////////////// Common Timeout Function //////////////////////////

function getAjaxTimeoutMsg() {
    //alert("Could not communicate with server: no answer after " + _ajaxTimeout/1000 + " seconds.");   
    var msg = "The server appears unreachable. Please check your Internet connection, VPN connection (if applicable), "+
            "or contact the system administrator "+_siteEmail+". "+
            "You may be logged out in "+_ajaxTimeout/60+" minutes and entered data may be lost if the connection is not restored.";
    
    alert(msg);
    
    return false;
}

function keepWorking() {
    //console.log("keep working");
    $('#idle-timeout').modal('hide');
}

function logoff() {
    //return; //testing
    //console.log("logoff");
    window.onbeforeunload = null;
    var urlRegularLogout = getCommonBaseUrl("idlelogout");
    window.location = urlRegularLogout;
}

//redirect to /idlelogout controller => logout with message of inactivity
function idlelogout() {
    //return; //testing
    window.onbeforeunload = null;
    var urlIdleTimeoutLogout = getCommonBaseUrl("idlelogout");
    window.location = urlIdleTimeoutLogout;
}

//////////////////// EOF Common Timeout Function //////////////////////////