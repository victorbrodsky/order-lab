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

//consider https://github.com/JillElaine/jquery-idleTimeout
//https://openbase.com/js/jquery-idleTimeout-plus

var _idleAfter = 0; //in seconds
var _ajaxTimeout = 300000;  //15000 => 15 sec, 180000 => 180 sec
var _maxIdleTime = $("#maxIdleTime").val(); //in seconds
var _siteEmail = $("#siteEmail").val();
//var _serverActive = false;
_countdownDialog = $("#dialog-countdown");
var _lastActiveTime = Date.now();


$(document).ready(function() {

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

    //console.log('idleTimeout cycle=('+cycle+')');
    if( cycle !== 'download' ) {
        console.log('init idleTimeout');
        var idleTimeout = new idleTimeoutClass();

        idleTimeout.init();
        //idleTimeout.setMaxIdletime();
        idleTimeout.checkIdleTimeout();
    }

});



function idleTimeoutClass() { }

idleTimeoutClass.prototype.init = function () {
    
    this.employees_sitename = "employees";   //"{{ employees_sitename|escape('js') }}";
    // cache a reference to the countdown element so we don't have to query the DOM for it on each ping.
    //this.countdownDialog = $("#dialog-countdown");
    this.urlCommonIdleTimeout = getCommonBaseUrl("common/keepalive",this.employees_sitename);

    this.setMaxIdletime();
    
    this.setActive();

};

idleTimeoutClass.prototype.setMaxIdletime = function () {
    
    if( _maxIdleTime ) {
        //console.log("_maxIdleTime is set = " + _maxIdleTime);
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
            //console.log("_idleAfter="+_idleAfter);
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
    //console.log( "############# checkIdleTimeout, testvar="+this.testvar+"; " + "_idleAfter="+_idleAfter);
    console.log( "############# user-idleTimeout checkIdleTimeout" + ", _idleAfter="+_idleAfter);
    // start the idle timer plugin; all times are in seconds
    var pollingIntervalShift = 50;
    //var pollingIntervalShift = 10;
    var idleTimeout =
    $.idleTimeout('#idle-timeout', '#idle-timeout-keepworking', {
        AJAXTimeout: null,
        failedRequests: 1,
        idleAfter: _idleAfter,
        warningLength: 30,
        pollingInterval: (_idleAfter-pollingIntervalShift),
        keepAliveURL: this.urlCommonIdleTimeout,
        serverResponseEquals: 'OK',
        onTimeout: function(){
            //fired on idle timeout from server: server response is not equal to the expected
            console.log("onTimeout: logout");
            //alert("onTimeout: logout");
            idleTimeoutClass.prototype.onTimeout();
        },
        onIdle: function(){
            //fired on no activity on the page
            console.log("on idle");
            //alert("on idle");
            //$('#idle-timeout').modal('show');
            idleTimeoutClass.prototype.isServerActive();
        },
        onCountdown: function(counter){
            console.log("on Countdown");
            _countdownDialog.html(counter); // update the counter             
        },
        onAbort: function(){
            console.log("onAbort: logout");
            //alert("onAbort: logout");
            idleTimeoutClass.prototype.onAbort();
        }
    });
    
      
};

idleTimeoutClass.prototype.isServerActive = function () {
    //check if the other page is active
    var url = Routing.generate('keepalive');
    //console.log("isServerActive url="+url);
    $.ajax({
        url: url,
        //type: 'GET',
        //dataType: 'json',
        async: true,
        timeout: _ajaxTimeout,
        success: function (data) {
            if( data.indexOf("show_idletimeout_modal") !== -1 ) {
                console.log("show timeout dialog modal: data="+data);
                $('#idle-timeout').modal('show');
            } else {
                console.log("OK data="+data+" => force to close timeout dialog modal");
                //$("#idle-timeout-keepworking").trigger('click');
            }
        },
        error: function ( x, t, m ) {
            //console.debug("isserveractive error???");
            if( t === "timeout" ) {
                //console.debug("isserveractive timeout???");
                getAjaxTimeoutMsg();
            }
        }
    });
    
    //console.debug("active="+active);
    //return active;
};


idleTimeoutClass.prototype.onTimeout = function () {
    //console.log("onTimeout: user");
    idlelogout();
};

idleTimeoutClass.prototype.onAbort = function () {
    //console.log("onAbort: user");
    //getAjaxTimeoutMsg();
    idlelogout();
};

//idleTimeoutClass.prototype.testfunc = function() {
//    //console.log("testfunc: user test!");
//    //alert("testfunc: user test!");
//}


idleTimeoutClass.prototype.setActive = function () {
    //console.log("setActive");
    //return;

    var timerIdleTime = 60 * 1000; //1 min = 60s * 1000 milliseconds
    //console.log("event active idleTimer timerIdleTime="+timerIdleTime);
    
    _lastActiveTime = Date.now();
    
    //sets the activity variable on the server to 1 every minute if there was any activity
    setInterval(function(){ 
        
        //alert("Hello"); 
        var getLastActiveTimeDiff = Date.now() - _lastActiveTime; //in milliseconds
        //console.log("getLastActiveTimeDiff="+getLastActiveTimeDiff/1000+" sec");
        //console.log("getLastActiveTimeDiff="+getLastActiveTimeDiff+" < "+timerIdleTime);
        
        if( getLastActiveTimeDiff < timerIdleTime ) {
            //console.log("event setserveractive:  getLastActiveTimeDiff="+getLastActiveTimeDiff/1000+" sec");
            var url = getCommonBaseUrl("common/setserveractive","employees");
            console.log("getCommonBaseUrl url="+url);
            //var currentUrl = window.location.href;
            var url = Routing.generate('setserveractive');
            console.log("Routing url="+url);
            $.ajax({
                url: url,
                //type: 'GET',
                //contentType: 'application/json',
                //dataType: 'json',
                //data: {url: currentUrl},
                async: true,
                timeout: _ajaxTimeout,
                success: function (data) {
                    //console.debug("data="+data+"; timerIdleTime="+timerIdleTime);               
                },
                //success: this.maxIdleTimeMethod,
                error: function ( x, t, m ) {
                    if( t === "timeout" ) {
                        getAjaxTimeoutMsg();
                    }                  
                }
            });
        }
        
    }, timerIdleTime);
    
    function resetTimer(){
        //console.log('reset lastActiveTime');
        _lastActiveTime = Date.now();
    }    
//    $( document ).on( "mousemove, keydown, click", function( event ) {       
//        resetTimer();
//    });
    $(document).mousemove(function (e) {
        resetTimer();
    });
    $(document).keypress(function (e) {
       resetTimer();
    });
    $(document).click(function (e) {
       resetTimer();
    });
};


//////////////////// Common Timeout Function //////////////////////////

function getAjaxTimeoutMsg() {
    //alert("Could not communicate with server: no answer after " + _ajaxTimeout/1000 + " seconds.");   
    var msg = "Could not communicate with server: no answer after " + _ajaxTimeout/1000 + " seconds. " +
            "The server appears unreachable. Please check your Internet connection, VPN connection (if applicable), "+
            "or contact the system administrator "+_siteEmail+". "+
            "You may be logged out in "+_maxIdleTime+" minutes and entered data may be lost if the connection is not restored.";
    
    alert(msg);
    
    return false;
}

function keepWorking() {
    //console.log("keep working: hide modal");
    $('#idle-timeout').modal('hide');
}

function logoff() {
    //return; //testing
    console.log("logoff");
    window.onbeforeunload = null;
    var urlRegularLogout = getCommonBaseUrl("idle-log-out");
    window.location = urlRegularLogout;
}

//redirect to /idlelogout controller => logout with message of inactivity
function idlelogout() {
    console.log('idlelogout')
    return; //testing
    window.onbeforeunload = null;
    var urlIdleTimeoutLogout = getCommonBaseUrl("idle-log-out");
    window.location = urlIdleTimeoutLogout;
}

//////////////////// EOF Common Timeout Function //////////////////////////