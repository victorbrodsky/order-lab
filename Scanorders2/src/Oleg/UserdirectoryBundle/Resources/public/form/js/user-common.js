/**
 * Created by oli2002 on 9/3/14.
 */


var _sitename = "";

function trimWithCheck(val) {

    if(typeof String.prototype.trim !== 'function') {
        String.prototype.trim = function() {
            return this.replace(/^\s+|\s+$/g, '');
        }
    }

    if( val && typeof val != 'undefined' && val != "" ) {
        val = val.toString();
        val = val.trim();
    }
    return val;
}

//convert enter to tab behavior: pressing enter will focus the next input field
function initConvertEnterToTab() {
    $('body').on('keydown', 'input, select', function(e) {
        var self = $(this)
            , form = self.parents('form:eq(0)')
            , focusable
            , next
            ;
        if (e.keyCode == 13) {
            //focusable = form.find('input,a,select,button,textarea').filter(':visible');
            focusable = form.find('input,select').filter(':visible').not("[readonly]").not("[disabled]");
            next = focusable.eq(focusable.index(this)+1);
            //console.log('next.length='+next.length);
            if( next.length ) {
                //printF(next,'go next:');
                next.focus();
            } else {
                //form.submit();
            }
            return false;
        }
    });
}

function getCommonBaseUrl(link,sitename) {

    if( typeof sitename === 'undefined' ) {
        sitename = getSitename();
    }
    //console.log('sitename='+sitename);

    var prefix = sitename;  //"scan";
    var urlBase = $("#baseurl").val();
    if( typeof urlBase !== 'undefined' && urlBase != "" ) {
        urlBase = "http://" + urlBase + "/" + prefix + "/" + link;
    }
    //console.log("urlBase="+urlBase);
    return urlBase;
}

function getSitename() {

    //if( typeof _sitename != 'undefined' && _sitename != "" )
    //    return;

    var holder = '/order/';
    var sitename = '';
    var url = document.URL;
    var urlArr = url.split(holder);
    //get rid of app_dev.php
    var urlfullClean = urlArr[1].replace("app_dev.php/", "");
    var urlCleanArr =  urlfullClean.split("/");
    sitename =  urlCleanArr[0];

    _sitename = sitename;

    return sitename;
}

