/**
 * Created with JetBrains PhpStorm.
 * User: oli2002
 * Date: 2/10/14
 * Time: 10:42 AM
 * To change this template use File | Settings | File Templates.
 */

var _idleAfter = 0;

//https://github.com/ehynds/jquery-idle-timeout
function idleTimeout() {

    //get max idle time from server by ajax
    $.ajax({
        url: "http://"+urlBase+"/getmaxidletime/",
        type: 'GET',
        //contentType: 'application/json',
        //dataType: 'json',
        async: false,
        success: function (data) {
            console.debug("data="+data);
            _idleAfter = data;
        },
        error: function () {
            console.debug("error data="+data);
            _idleAfter = 0;
        }
    });

    // cache a reference to the countdown element so we don't have to query the DOM for it on each ping.
    var $countdown = $("#dialog-countdown");

    var urlCommonIdleTimeout = "http://"+urlBase+"/keepalive/";
    //var urlIdleTimeoutLogout = "http://"+urlBase+"/idlelogout";

    //pollingInterval: 7200 sec, //how often to call keepalive. If set to some big number (i.e. 2 hours) then we will not notify kernel to update session getLastUsed()
    //idleAfter: 1800 sec => 30min*60sec =
    //failedRequests: 1     //if return will no equal 'OK', then failed requests counter will increment to one and compare to failedRequests. If more, then page will forward to urlIdleTimeoutLogout

    // start the idle timer plugin
    $.idleTimeout('#idle-timeout', '#idle-timeout-keepworking', {
        AJAXTimeout: null,
        failedRequests: 1,
        idleAfter: _idleAfter,
        warningLength: 30,
        pollingInterval: 7200,
        keepAliveURL: urlCommonIdleTimeout,
        serverResponseEquals: 'OK',
        onTimeout: function(){
            //$('#next_button_multi').trigger('click');
            keepWorking();
            $('#save_order_onidletimeout_btn').show();
            //console.log("on timeout. len="+$('#save_order_onidletimeout_btn').length);

            if( $('#save_order_onidletimeout_btn').length > 0 &&
                ( cicle == "new" || cicle == "amend" || cicle == "edit" )
            ) {
                //console.log("save!!!!!!!!!!!");
                //save if all fields are not empty; don't validate
                $('#save_order_onidletimeout_btn').trigger('click');
            } else {
                //console.log("logout");
                idlelogout();
            }
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
    var urlRegularLogout = "http://"+urlBase+"/logout";
    window.location = urlRegularLogout;
}

function idlelogout() {
    //console.log("logoff");
//    var str = "";
//    if( saveorder && saveorder == 'saveorder'  ) {
//        str = "?opt=saveorder";
//    }
    var urlIdleTimeoutLogout = "http://"+urlBase+"/idlelogout"; //+str;
    //console.log("urlIdleTimeoutLogout="+urlIdleTimeoutLogout);
    window.location = urlIdleTimeoutLogout;
}
