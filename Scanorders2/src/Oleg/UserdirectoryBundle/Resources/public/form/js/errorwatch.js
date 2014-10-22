
function isIE () {
    var myNav = navigator.userAgent.toLowerCase();
    return (myNav.indexOf('msie') != -1) ? parseInt(myNav.split('msie')[1]) : false;
}

window.onerror = function( msg, url, linenumber ){

    if( isIE() ) {
        //
    } else {

        var newline = "";   //"\n";

          alert(    'Internal system error. Please reload the page by clicking "OK" button. ' + newline +
                    'Please e-mail us at slidescan@med.cornell.edu if the problem persists. ' + newline +
                    'Error message: ' + msg + newline +
                    'URL: ' + url + newline +
                    'Line Number: ' + linenumber
          );

        //location.reload();
    }

}
