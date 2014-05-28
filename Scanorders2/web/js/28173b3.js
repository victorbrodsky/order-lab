
function isIE () {
    var myNav = navigator.userAgent.toLowerCase();
    return (myNav.indexOf('msie') != -1) ? parseInt(myNav.split('msie')[1]) : false;
}

window.onerror=function(msg, url, linenumber){

    if( isIE() ) {

    } else {
        alert(  'Internal system error. Please reload the page by clicking "OK" button.\n' +
            'Please e-mail us at slidescan@med.cornell.edu if the problem persists.\n\n'+
            'Error message: '+msg+'\nURL: '+url+'\nLine Number: '+linenumber   );
        //location.reload();
    }

}


