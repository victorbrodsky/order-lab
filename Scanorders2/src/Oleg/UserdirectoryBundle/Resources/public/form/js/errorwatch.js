
function isIE () {
    var myNav = navigator.userAgent.toLowerCase();
    return (myNav.indexOf('msie') != -1) ? parseInt(myNav.split('msie')[1]) : false;
}

window.onerror = function( msg, url, linenumber ){

    if( isIE() ) {
        if( isIE() <= 7 ) {
            // is IE version equal or less than 7
            var msg = "Warning! You are using an old version of browser Internet Explorer 7 or lower. \n\
                        Please upgrade the browser or use the modern browsers such as \n\
                        Firefox or Google Chrome to have a full features of this system.";
            $('.browser-notice').html(msg);
            $('.browser-notice').show();           
        } 
    } else {

        var newline = "\n";

          alert(    'Internal system error. Please reload the page by clicking "OK" button. ' + newline +
                    'Please e-mail us at slidescan@med.cornell.edu if the problem persists. ' + newline +
                    'Error message: ' + msg + newline +
                    ' URL: ' + url + newline +
                    ' Line Number: ' + linenumber
          );

        //location.reload();
    }

}
