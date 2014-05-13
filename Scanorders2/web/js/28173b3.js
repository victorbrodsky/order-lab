
window.onerror=function(msg, url, linenumber){
    alert(  'Internal system error. Please reload the page by clicking "OK" button.\n' +
            'Please e-mail us at slidescan@med.cornell.edu if the problem persists.\n\n'+
            'Error message: '+msg+'\nURL: '+url+'\nLine Number: '+linenumber   );
    location.reload();
}
