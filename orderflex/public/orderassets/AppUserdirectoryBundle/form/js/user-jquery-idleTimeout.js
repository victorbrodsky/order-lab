/*
 * Copyright (c) 2017 Cornell University
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */

/**
 * Created with JetBrains PhpStorm.
 * User: oli2002
 * Date: 2/10/14
 * Time: 10:42 AM
 * To change this template use File | Settings | File Templates.
 */

//based on JillElaine https://github.com/JillElaine/jquery-idleTimeout
// and Marcus Westin https://github.com/marcuswestin/store.js
//alternative: https://openbase.com/js/jquery-idleTimeout-plus
//https://github.com/marcuswestin/store.js


//jquery ui modal class modified = ui-dialog ui-widget ui-widget-content ui-corner-all ui-front ui-dialog-buttons ui-draggable ui-resizable

var _idleAfter = 0; //in seconds
var _ajaxTimeout = 300000;  //15000 => 15 sec, 180000 => 180 sec
var _maxIdleTime = $("#maxIdleTime").val(); //in seconds. set on login
var _siteEmail = $("#siteEmail").val();
//var _serverActive = false;
//_countdownDialog = $("#dialog-countdown");
//var _lastActiveTime = Date.now();


$(document).ready(function() {

    //testing
    // var urlIdleTimeoutLogout = getCommonBaseUrl("idle-log-out");
    // $.get(urlIdleTimeoutLogout).done(function () {
    //     alert("success");
    //     console.log("url ok");
    // }).fail(function () {
    //     console.log("url error");
    //     alert("url error");
    // });

    // Prevent Dropzone from auto discovering this element
    if( typeof Dropzone !== 'undefined' ) {
        Dropzone.autoDiscover = false;
    }

    //console.log('idleTimeout0 cycle=('+cycle+')');

    if( typeof cycle === "undefined" ) {
        //try to get cycle again
        var cycle = $("#formcycle").val();
    }

    if( typeof cycle === "undefined" ) {
        var cycle = 'show';
    }

    var disableIdleTimeout = null;
    if( $("#disableIdleTimeout") ) {
        disableIdleTimeout = $("#disableIdleTimeout").val();
    }

    var enableIdleTimeout = true;

    //console.log('idleTimeout cycle=('+cycle+')');
    if( cycle == 'download' ) {
        enableIdleTimeout = false;
    }
    if( disableIdleTimeout && disableIdleTimeout == '1' ) {
        enableIdleTimeout = false;
    }
    console.log('enableIdleTimeout='+enableIdleTimeout);
    //if( cycle !== 'download' && idleTimeout !== 1 ) {
    if( enableIdleTimeout ) {
        // var idleTimeout = new idleTimeoutClass();
        // idleTimeout.init();
        // idleTimeout.checkIdleTimeout();

        var delayInMilliseconds = 2000; //1 second
        setTimeout(function(){
            //console.debug("initIdleTimeout after "+delayInMilliseconds+" milliseconds");
            initIdleTimeout();
        }, delayInMilliseconds);
    }

});

function initIdleTimeout() {
    var idleTimeout = new idleTimeoutClass();
    idleTimeout.init();
    idleTimeout.checkIdleTimeout();
}

function idleTimeoutClass() { }

idleTimeoutClass.prototype.init = function () {
    
    this.employees_sitename = "employees";   //"{{ employees_sitename|escape('js') }}";
    // cache a reference to the countdown element so we don't have to query the DOM for it on each ping.
    //this.countdownDialog = $("#dialog-countdown");
    //this.urlCommonIdleTimeout = getCommonBaseUrl("common/keepalive",this.employees_sitename);
    //var tenantprefix = $('#tenantprefix').val();
    //console.log('tenantprefix='+tenantprefix);
    //this.urlCommonIdleTimeout = Routing.generate('keepalive', {'_locale': tenantprefix});
    this.urlCommonIdleTimeout = Routing.generate('keepalive');
    console.log("urlCommonIdleTimeout="+this.urlCommonIdleTimeout);

    this.setMaxIdletime();
    
    //this.setActive();
};

idleTimeoutClass.prototype.setMaxIdletime = function () {
    
    if( _maxIdleTime ) {
        //console.log("_maxIdleTime is set = " + _maxIdleTime);
        _idleAfter = _maxIdleTime;
        return;
    }
    
    //get max idle time from server by ajax
    $.ajax({
        //url: getCommonBaseUrl("common/getmaxidletime",this.employees_sitename),
        url: Routing.generate('getmaxidletime'),
        type: 'GET',
        //contentType: 'application/json',
        dataType: 'json',
        async: false,
        timeout: _ajaxTimeout,
        success: function (data) {
            //console.debug("data="+data);
            //console.log("idletime="+data.maxIdleTime);
            //console.debug("maint="+data.maintenance);
            _idleAfter = data.maxIdleTime;
        },
        error: function ( x, t, m ) {
            if( t === "timeout" ) {
                getAjaxTimeoutMsg();
            }
            //console.debug("get max idletime: error data="+data);
            _idleAfter = 0;
        }
    });
};

//var _idleTimeout = null;
idleTimeoutClass.prototype.checkIdleTimeout = function () {
    console.log( "############# checkIdleTimeout, testvar="+this.testvar+"; " + "_idleAfter="+_idleAfter);
    //var urlIdleTimeoutLogout = getCommonBaseUrl("idle-log-out");
    //var urlIdleTimeoutLogout = Routing.generate('employees_idlelogout');
    //http://127.0.0.1/order/index_dev.php/directory/idle-log-out
    //http://127.0.0.1/order/index_dev.php/directory/idle-log-out
    //console.log("checkIdleTimeout urlIdleTimeoutLogout="+urlIdleTimeoutLogout);

    //var sessionKeepAliveTimer = Math.round(_idleAfter/3); //call server before idle timer expired
    var sessionKeepAliveTimer = 180; //180 sec => 3 min
    if( sessionKeepAliveTimer <= 120 ) {
        sessionKeepAliveTimer = 120;
    }

    var idleCheckHeartbeat = 5; //sec
    var dialogDisplayLimit = 60; //sec

    /////// TESTING PARAMETERS ///////
    _idleAfter = 25; //sec testing
    sessionKeepAliveTimer = 3; //false; //testing
    dialogDisplayLimit = 15; //sec
    urlIdleTimeoutLogout = false; //testing
    /////// EOF TESTING PARAMETERS ///////

    //var thisUrl = window.location.href; // http://127.0.0.1/order/index_dev.php/directory/
    var thisUrl = window.location.pathname; // /order/index_dev.php/directory/
    //console.log("thisUrl="+thisUrl);
    //convert url: replace '/' to '-'
    //thisUrl = thisUrl.toString().replaceAll("/","_");
    thisUrl = thisUrl.replace(/\//g,"_"); //result: _index_dev.php_c_lmh_pathology_directory_
    //console.log("2 thisUrl="+thisUrl);
    // console.log(
    //     "checkIdleTimeout (in sec)" +
    //     ", _idleAfter="+_idleAfter+
    //     ", sessionKeepAliveTimer="+sessionKeepAliveTimer+
    //     ", idleCheckHeartbeat="+idleCheckHeartbeat+
    //     ", thisUrl="+thisUrl
    // );

    //testing
    //var keepaliveUrl = Routing.generate('keepalive');
    //https://stackoverflow.com/questions/29937114/how-to-pass-locale-using-fosjsroutingbundle
    //https://stackoverflow.com/questions/25842418/symfony-fos-js-routing-and-problems-with-locale
    //var keepaliveUrl = Routing.generate('keepalive',{tenantprefix: 'pathology'});
    //console.log("testing keepaliveUrl="+keepaliveUrl);

    //var tenantprefix = $('#tenantprefix').val();
    //console.log('Routing.generate tenantprefix='+tenantprefix);

    //var urlIdleTimeoutLogout = Routing.generate('employees_idlelogout_ref',{'_locale': tenantprefix, url: thisUrl});
    var urlIdleTimeoutLogout = Routing.generate('employees_idlelogout_ref',{url: thisUrl});
    //http://127.0.0.1/order/index_dev.php/directory/idle-log-out
    //http://127.0.0.1/order/index_dev.php/directory/idle-log-out
    console.log("checkIdleTimeout urlIdleTimeoutLogout="+urlIdleTimeoutLogout);

    //{tenantprefix} cause error: Uncaught Error: The route "setserveractive" requires the parameter "tenantprefix"
    //var sessionKeepAliveUrl = Routing.generate('setserveractive',{'_locale': tenantprefix, url: thisUrl}); //window.location.href
    //var sessionKeepAliveUrl = Routing.generate('setserveractive',{tenantprefix: 'pathology', url: thisUrl}); //window.location.href
    var sessionKeepAliveUrl = Routing.generate('setserveractive',{url: thisUrl}); //window.location.href
    console.log("sessionKeepAliveUrl="+sessionKeepAliveUrl);

    //var sessionKeepAliveUrl = getCommonBaseUrl("setserveractive"); //working, but modify to pass {setserveractive} from session
    //sessionKeepAliveUrl = sessionKeepAliveUrl + "/" + thisUrl;
    //console.log("2 sessionKeepAliveUrl="+sessionKeepAliveUrl);

    var dialogText = "For security reasons, you are about to be logged out due to inactivity.";// +
        //"Please move the mouse, press any key, or press the button below to stay logged in.";

    var dialogText2 = "Please move the mouse, press any key, or press the button below to stay logged in.";

    //https://github.com/JillElaine/jquery-idleTimeout/blob/master/example.html
    //var idleTimeout
    //_idleTimeout
    var idleTimeout = $(document).idleTimeout({
        redirectUrl: urlIdleTimeoutLogout, // redirect to this url on logout. Set to "redirectUrl: false" to disable redirect
        //redirectUrl: false,

        // idle settings
        idleTimeLimit: _idleAfter,                 // 'No activity' time limit in seconds. 1200 = 20 Minutes
        idleCheckHeartbeat: idleCheckHeartbeat,    // Frequency to check for idle timeouts in seconds

        // optional custom callback to perform before logout
        //customCallback: false,
        customCallback: userCheckIfConnected,       // set to false for no customCallback

        // customCallback:    function () {    // define optional custom js function
        //     // perform custom action before logout
        //     var urlIdleTimeoutLogout = getCommonBaseUrl("idle-log-out1");
        //     //var urlIdleTimeoutLogout = this.urlIdleTimeoutLogout;
        //
        //     //var idleTimeout = _idleTimeout;
        //
        //     $.get(urlIdleTimeoutLogout).done(function () {
        //         //alert("success");
        //         console.log("url ok");
        //     }).fail(function () {
        //         console.log("url error");
        //         this.redirectUrl = null;
        //         //console.log("dialogTitle="+$.fn.idleTimeout().dialogTitle);
        //         var warningMessage = "A network connection interruption was detected and " +
        //             "your account was logged out due to inactivity. To continue, please " +
        //             "make sure you are connected to the network (and to the VPN, if applicable) " +
        //             "and log in again.";
        //         alert(warningMessage);
        //
        //     });
        // },

        // configure which activity events to detect
        // http://www.quirksmode.org/dom/events/
        // https://developer.mozilla.org/en-US/docs/Web/Reference/Events
        activityEvents: 'click keypress scroll wheel mousewheel mousemove', // separate each event with a space

        // warning dialog box configuration
        enableDialog: true,           // set to false for logout without warning dialog
        dialogDisplayLimit: dialogDisplayLimit,       // 20 seconds for testing. Time to display the warning dialog before logout (and optional callback) in seconds. 180 = 3 Minutes
        dialogTitle: 'You are about to be signed out of this application!', // also displays on browser title bar
        dialogText: dialogText, //'Because you have been inactive, your session is about to expire.',
        dialogText2: dialogText2,
        dialogTimeRemaining: 'Time remaining',
        dialogStayLoggedInButton: 'Keep me logged in',
        dialogLogOutNowButton: 'Log Out Now',

        // error message if https://github.com/marcuswestin/store.js not enabled
        errorAlertMessage: 'Please disable "Private Mode", or upgrade to a modern browser.', //Or perhaps a dependent file missing. Please see: https://github.com/marcuswestin/store.js',

        // server-side session keep-alive timer
        sessionKeepAliveTimer: sessionKeepAliveTimer,   // ping the server at this interval in seconds. 600 = 10 Minutes. Set to false to disable pings
        sessionKeepAliveUrl: sessionKeepAliveUrl // set URL to ping - does not apply if sessionKeepAliveTimer: false
    });
};


function getAjaxTimeoutMsg() {
    //alert("Could not communicate with server: no answer after " + _ajaxTimeout/1000 + " seconds.");
    var msg = "Could not communicate with server: no answer after " + _ajaxTimeout/1000 + " seconds. " +
        "The server appears unreachable. Please check your Internet connection, VPN connection (if applicable), "+
        "or contact the system administrator "+_siteEmail+". "+
        "You may be logged out in "+_maxIdleTime+" minutes and entered data may be lost if the connection is not restored.";

    alert(msg);

    return false;
}

//HaProxy scheme (used http instead of https):
//Error: Blocked loading mixed active content “http://view.online/c/wcm/pathology/directory/login”
function userCheckIfConnected_ORIG() {
    //console.log("userCheckIfConnected");

    //event.stopPropagation();
    //console.log("window.onbeforeunload = null");
    window.onbeforeunload = null;
    //return;

    //check whether internet connection is present AND ping the server to make sure VPN is still connected
    //if no network connection is present OR the server is not pingable,
    // (a) change “Auto-log out” modal title to “Network connection interrupted”,
    // (b) change the modal body to “A network connection interruption was detected and
    // your account was logged out due to inactivity.
    // To continue, please make sure you are connected to the network (and to the VPN,
    // if applicable) and log in again.”
    // (c) Change the button text from “Keep me logged in” to “Ok”

    //var urlIdleTimeoutLogout = getCommonBaseUrl("idle-log-out");
    var urlIdleTimeoutLogout = Routing.generate('employees_idlelogout');
    console.log("userCheckIfConnected urlIdleTimeoutLogout="+urlIdleTimeoutLogout);

    urlIdleTimeoutLogout.replace('http', 'https');

    //var idleTimeout = _idleTimeout;

    $.get(urlIdleTimeoutLogout).done(function () {
        //alert("success");
        console.log("url ok");
    }).fail(function () {
        console.log("url error");
        console.log("url urlIdleTimeoutLogout="+urlIdleTimeoutLogout);
        //console.log("dialogTitle="+$.fn.idleTimeout().dialogTitle);
        //alert("failed.");

        var warningMessage = "A network connection interruption was detected and " +
            "your account was logged out due to inactivity. To continue, please " +
            "make sure you are connected to the network (and to the VPN, if applicable) " +
            "and log in again.";
        alert(warningMessage);

        //var currentConfig = idleTimeout.currentConfig;
        //$.fn.idleTimeout().redirectUrl = false;
        //$.fn.idleTimeout().dialogTitle = "Network connection interrupted";
        //$.fn.idleTimeout().dialogText = "A network connection interruption was detected and your account " +
        //    "was logged out due to inactivity. To continue, please make sure you are connected " +
        //    "to the network (and to the VPN, if applicable) and log in again.";
        //$.fn.idleTimeout().dialogLogOutNowButton = "OK";

        //destroyWarningDialog();
        //$.fn.idleTimeout().destroyWarningDialog();
        //$.fn.idleTimeout().stopDialogTimer();
        //$.fn.idleTimeout().startIdleTimer();
    });

    //if() {
    //}


    //not connected
    //idleTimeout.redirectUrl = false;
}

function userCheckIfConnected() {

    //event.stopPropagation();
    //console.log("window.onbeforeunload = null");
    //window.onbeforeunload = null;

    var urlIdleTimeoutLogout = Routing.generate('employees_idlelogout');
    //urlIdleTimeoutLogout = 'https://view.online/c/wcm/pathology/directory/idle-log-out';
    console.log("userCheckIfConnected urlIdleTimeoutLogout="+urlIdleTimeoutLogout);

    //urlIdleTimeoutLogout.replace('http', 'https');

    $.ajax({
        url: urlIdleTimeoutLogout,
        timeout: 3000,
        async: false,
    }).success(function(data) {
        console.log("userCheckIfConnected: success");
    }).done(function() {
        console.log("userCheckIfConnected: url ok");
    }).fail(function(jqXHR, textStatus, errorThrown) {
        console.log("url error");
        console.log("url urlIdleTimeoutLogout="+urlIdleTimeoutLogout);

        var failMsg = "userCheckIfConnected fail. jqXHR.status="+jqXHR.status+", textStatus="+textStatus+", errorThrown="+errorThrown;
        console.log(failMsg);

        var warningMessage = "A network connection interruption was detected and " +
            "your account was logged out due to inactivity. To continue, please " +
            "make sure you are connected to the network (and to the VPN, if applicable) " +
            "and log in again.";
        alert(warningMessage);
    });
}

destroyWarningDialog = function () {
    console.log("my destroyWarningDialog");
    $("#idletimer_warning_dialog").dialog('destroy').remove();
    //document.title = origTitle;

    //if (currentConfig.sessionKeepAliveTimer) {
        //startKeepSessionAlive();
    //}
};









// //NOT USED
// idleTimeoutClass.prototype.checkIdleTimeout1_Orig = function () {
//     //console.log( "############# checkIdleTimeout, testvar="+this.testvar+"; " + "_idleAfter="+_idleAfter);
//     console.log( "############# checkIdleTimeout" + ", _idleAfter="+_idleAfter);
//     // start the idle timer plugin; all times are in seconds
//     var pollingIntervalShift = 50;
//     //var pollingIntervalShift = 10;
//     var idleTimeout =
//     $.idleTimeout('#idle-timeout', '#idle-timeout-keepworking', {
//         AJAXTimeout: null,
//         failedRequests: 1,
//         idleAfter: _idleAfter,
//         warningLength: 30,
//         pollingInterval: (_idleAfter-pollingIntervalShift),
//         keepAliveURL: this.urlCommonIdleTimeout,
//         serverResponseEquals: 'OK',
//         onTimeout: function(){
//             //fired on idle timeout from server: server response is not equal to the expected
//             console.log("onTimeout: logout");
//             //alert("onTimeout: logout");
//             idleTimeoutClass.prototype.onTimeout();
//         },
//         onIdle: function(){
//             //fired on no activity on the page
//             console.log("on idle");
//             //alert("on idle");
//             //$('#idle-timeout').modal('show');
//             idleTimeoutClass.prototype.isServerActive();
//         },
//         onCountdown: function(counter){
//             console.log("on Countdown");
//             _countdownDialog.html(counter); // update the counter
//         },
//         onAbort: function(){
//             console.log("onAbort: logout");
//             //alert("onAbort: logout");
//             idleTimeoutClass.prototype.onAbort();
//         }
//     });
//
//
// };
//
// //NOT USED
// idleTimeoutClass.prototype.isServerActive = function () {
//     //check if the other page is active
//     var url = Routing.generate('keepalive');
//     //console.log("isServerActive url="+url);
//     $.ajax({
//         url: url,
//         //type: 'GET',
//         //dataType: 'json',
//         async: true,
//         timeout: _ajaxTimeout,
//         success: function (data) {
//             if( data.indexOf("show_idletimeout_modal") !== -1 ) {
//                 console.log("show timeout dialog modal: data="+data);
//                 $('#idle-timeout').modal('show');
//             } else {
//                 console.log("OK data="+data+" => force to close timeout dialog modal");
//                 //$("#idle-timeout-keepworking").trigger('click');
//             }
//         },
//         error: function ( x, t, m ) {
//             //console.debug("isserveractive error???");
//             if( t === "timeout" ) {
//                 //console.debug("isserveractive timeout???");
//                 getAjaxTimeoutMsg();
//             }
//         }
//     });
//
//     //console.debug("active="+active);
//     //return active;
// };
//
// //NOT USED
// idleTimeoutClass.prototype.onTimeout = function () {
//     //console.log("onTimeout: user");
//     idlelogout();
// };
// //NOT USED
// idleTimeoutClass.prototype.onAbort = function () {
//     //console.log("onAbort: user");
//     //getAjaxTimeoutMsg();
//     idlelogout();
// };
//
// //idleTimeoutClass.prototype.testfunc = function() {
// //    //console.log("testfunc: user test!");
// //    //alert("testfunc: user test!");
// //}
//
// //NOT USED
// idleTimeoutClass.prototype.setActive = function () {
//     //console.log("setActive");
//     //return;
//
//     var timerIdleTime = 60 * 1000; //1 min = 60s * 1000 milliseconds
//     //console.log("event active idleTimer timerIdleTime="+timerIdleTime);
//
//     _lastActiveTime = Date.now();
//
//     //sets the activity variable on the server to 1 every minute if there was any activity
//     setInterval(function(){
//
//         //alert("Hello");
//         var getLastActiveTimeDiff = Date.now() - _lastActiveTime; //in milliseconds
//         //console.log("getLastActiveTimeDiff="+getLastActiveTimeDiff/1000+" sec");
//         //console.log("getLastActiveTimeDiff="+getLastActiveTimeDiff+" < "+timerIdleTime);
//
//         if( getLastActiveTimeDiff < timerIdleTime ) {
//             //console.log("event setserveractive:  getLastActiveTimeDiff="+getLastActiveTimeDiff/1000+" sec");
//             //var url = getCommonBaseUrl("common/setserveractive","employees");
//             //var currentUrl = window.location.href;
//             var url = Routing.generate('setserveractive');
//             //console.log("url="+url);
//             $.ajax({
//                 url: url,
//                 //type: 'GET',
//                 //contentType: 'application/json',
//                 //dataType: 'json',
//                 //data: {url: currentUrl},
//                 async: true,
//                 timeout: _ajaxTimeout,
//                 success: function (data) {
//                     //console.debug("data="+data+"; timerIdleTime="+timerIdleTime);
//                 },
//                 //success: this.maxIdleTimeMethod,
//                 error: function ( x, t, m ) {
//                     if( t === "timeout" ) {
//                         getAjaxTimeoutMsg();
//                     }
//                 }
//             });
//         }
//
//     }, timerIdleTime);
//
//     function resetTimer(){
//         //console.log('reset lastActiveTime');
//         _lastActiveTime = Date.now();
//     }
// //    $( document ).on( "mousemove, keydown, click", function( event ) {
// //        resetTimer();
// //    });
//     $(document).mousemove(function (e) {
//         resetTimer();
//     });
//     $(document).keypress(function (e) {
//        resetTimer();
//     });
//     $(document).click(function (e) {
//        resetTimer();
//     });
// };
//
//
// //////////////////// Common Timeout Function //////////////////////////
//
//
//
// //NOT USED
// function keepWorking() {
//     //console.log("keep working: hide modal");
//     $('#idle-timeout').modal('hide');
// }
//
// //NOT USED
// function logoff() {
//     //return; //testing
//     //console.log("logoff");
//     window.onbeforeunload = null;
//     var urlRegularLogout = getCommonBaseUrl("idle-log-out");
//     window.location = urlRegularLogout;
// }
//
// //NOT USED
// //redirect to /idlelogout controller => logout with message of inactivity
// function idlelogout() {
//     console.log('idlelogout')
//     return; //testing
//     window.onbeforeunload = null;
//     var urlIdleTimeoutLogout = getCommonBaseUrl("idle-log-out");
//     window.location = urlIdleTimeoutLogout;
// }
//
// //////////////////// EOF Common Timeout Function //////////////////////////