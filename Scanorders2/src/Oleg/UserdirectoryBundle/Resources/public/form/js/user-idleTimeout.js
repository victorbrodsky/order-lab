/**
 * Created with JetBrains PhpStorm.
 * User: oli2002
 * Date: 2/10/14
 * Time: 10:42 AM
 * To change this template use File | Settings | File Templates.
 */

var _idleAfter = 0;
var _ajaxTimeout = 20000;  //15000 => 15 sec

//https://github.com/ehynds/jquery-idle-timeout
function idleTimeout() {

    //get max idle time from server by ajax
    $.ajax({
        url: getCommonBaseUrl("getmaxidletime/"),	//urlBase+"getmaxidletime/",
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
        },
        error: function ( x, t, m ) {
            if( t === "timeout" ) {
                getAjaxTimeoutMsg();
            }
            //console.debug("get max idletime: error data="+data);
            _idleAfter = 0;
        }
    });

    // cache a reference to the countdown element so we don't have to query the DOM for it on each ping.
    var $countdown = $("#dialog-countdown");

    var urlCommonIdleTimeout = getCommonBaseUrl("keepalive");

    //pollingInterval: 7200 sec, //how often to call keepalive. If set to some big number (i.e. 2 hours) then we will not notify kernel to update session getLastUsed()
    //idleAfter: 1800 sec => 30min*60sec =
    //failedRequests: 1     //if return will no equal 'OK', then failed requests counter will increment to one and compare to failedRequests. If more, then page will forward to urlIdleTimeoutLogout

    // start the idle timer plugin
    $.idleTimeout('#idle-timeout', '#idle-timeout-keepworking', {
        AJAXTimeout: null,
        failedRequests: 1,
        idleAfter: _idleAfter,
        warningLength: 30,
        pollingInterval: _idleAfter-50,
        keepAliveURL: urlCommonIdleTimeout,
        serverResponseEquals: 'OK',
        onTimeout: function(){
            //console.log("onTimeout: logout");
            keepWorking();
            //tryToSubmitForm();
        },
        onIdle: function(){
            //console.log("on idle");
            $('#idle-timeout').modal('show');
        },
        onCountdown: function(counter){
            //console.log("on Countdown");
            $countdown.html(counter); // update the counter
        },
        onAbort: function(){
            //console.log("onAbort: logout");
            //tryToSubmitForm();
            idlelogout();
        }
    });
}

function keepWorking() {
    //console.log("keep working");
    $('#idle-timeout').modal('hide');
}

function logoff() {
    //console.log("logoff");
    window.onbeforeunload = null;
    var urlRegularLogout = getCommonBaseUrl("idlelogout");	//urlBase+"logout";
    window.location = urlRegularLogout;
}

//redirect to /idlelogout controller => logout with message of inactivity
function idlelogout() {
    //console.log("idlelogout");
    window.onbeforeunload = null;
    var urlIdleTimeoutLogout = getCommonBaseUrl("idlelogout");	//urlBase+"idlelogout";
    window.location = urlIdleTimeoutLogout;
}
