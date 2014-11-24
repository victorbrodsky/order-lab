/**
 * Created by oli2002 on 9/3/14.
 */


var _sitename = "";
var asyncflag = true;
var combobox_width = '100%'; //'element'

var urlBase = $("#baseurl").val();
var cicle = $("#formcicle").val();
var user_name = $("#user_name").val();
var user_id = $("#user_id").val();


function regularCombobox() {

    var selectboxes = $("select.combobox");

    selectboxes.each( function() {
        $(this).select2({
            width: combobox_width,
            dropdownAutoWidth: true,
            placeholder: "Select an option",
            allowClear: true,
            selectOnBlur: false
            //containerCssClass: 'combobox-width'
        });
        if( $(this).attr("readonly") ) {
            $(this).select2("readonly", true);
        }
    });
}

//target - class or id of the target element
function populateSelectCombobox( target, data, placeholder, multipleFlag ) {

    //console.log("target="+target);

    //clear the value if it is not set (What is the point to do so if it is empty?!)
//    var value = $(target).val();
//    console.log("target="+target+", value1="+value);
//    if( !value || value == "" ) {
//        var value2 = $(target).select2('val');
//        console.log("target="+target+", value2="+value2);
//        if( !value2 || value2 == "" ) {
//            console.log('clear value!');
//            $(target).select2('val','');
//        }
//    }

    if( placeholder ) {
        var allowClear = true;
    } else {
        var allowClear = false;
    }

    if( multipleFlag ) {
        var multiple = true;
    } else {
        var multiple = false;
    }

    if( !data ) {
        data = new Array();
    }

    var createSearchChoice = function(term, data) {
        //if( term.match(/^[0-9]+$/) != null ) {
        //    //console.log("term is digit");
        //}
        return {id:term, text:term};
    };

    if( $(target).hasClass('combobox-without-add') ) {
        createSearchChoice = null;
    }

    $(target).select2({
        placeholder: placeholder,
        allowClear: allowClear,
        width: combobox_width,
        dropdownAutoWidth: true,
        selectOnBlur: false,
        dataType: 'json',
        quietMillis: 100,
        multiple: multiple,
        data: data,
        createSearchChoice:createSearchChoice
    });

    if( $(target).attr("readonly") ) {
        $(this).select2("readonly", true);
    }
}


function initDatetimepicker() {

    var datetimepicker = $('.form_datetime');
    //console.log('initDatetimepicker');
    if( datetimepicker.length ) {
        printF(datetimepicker,"init:");
        datetimepicker.datetimepicker({
            format: 'mm-dd-yyyy hh:i'
        });
    }
}


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
        //console.log('init convert enter to tab');
        if( $(this).hasClass('submit-on-enter-field') ) {
            //console.log('submit-on-enter-field !');
            return;
        }
        //console.log('continue convert enter to tab');

        var self = $(this)
            , form = self.parents('form:eq(0)')
            , focusable
            , next
            ;
        if( e.keyCode == 13 ) {
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

    if( sitename == "employees" ) {
        sitename = "pathology-employees";
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

    //scan or employees
    return sitename;
}

function collpaseAll(holder) {
    if( typeof holder === 'undefined' ) {
        $('.panel-collapse').collapse('hide');
    } else {
        console.log("holder id="+$(holder).attr('id'));
        console.log($(holder));
        $(holder).find('.panel-collapse').collapse('hide');
    }

}

function extendAll(holder) {
    if( typeof holder === 'undefined' ) {
        $('.panel-collapse').collapse('show');
    } else {
        $(holder).find('.panel-collapse').collapse('show');
    }
}


function initDatepicker() {

    if( cicle != "show" ) {

        //console.log("init Datepicker");

        var regularDatepickers = $('.input-group.date.regular-datepicker').not('.allow-future-date');
        initSingleDatepicker( regularDatepickers );

        var scandateDatepickers = $('.input-group.date.allow-future-date');
        initSingleDatepicker( scandateDatepickers );

        //make sure the masking is clear when input is cleared by datepicker
        regularDatepickers.datepicker().on("clearDate", function(e){
            var inputField = $(this).find('input');
            //printF(inputField,"clearDate input:");
            clearErrorField( inputField );
        });

    }

}

function initSingleDatepicker( datepickerElement ) {

    //printF(datepickerElement,'datepicker element:');

    var endDate = new Date(); //use current date as default

    if( datepickerElement.hasClass('allow-future-date') ) {
        endDate = false;//'End of time';
    }
    //console.log('endDate='+endDate);

    //to prevent datepicker clear on Enter key, use the version from https://github.com/eternicode/bootstrap-datepicker/issues/775
    datepickerElement.datepicker({
        autoclose: true,
        clearBtn: true,
        todayBtn: "linked",
        todayHighlight: true,
        endDate: endDate
    });
}

function expandTextarea() {
    //var elements = document.getElementsByClassName('textarea');
    var elements = $('.textarea');

    for (var i = 0; i < elements.length; ++i) {
        var element = elements[i];
        //element.addEventListener('keyup', function() {
        addEvent('keyup', element, function() {
            this.style.overflow = 'hidden';
            this.style.height = 0;
            var newH = this.scrollHeight + 10;
            //console.log("cur h="+this.style.height+", newH="+newH);
            this.style.height = newH + 'px';
        }, false);
    }
}

//Internet Explorer (up to version 8) used an alternate attachEvent method.
// The following should be an attempt to write a cross-browser addEvent function.
function addEvent(event, elem, func) {
    if (elem.addEventListener)  // W3C DOM
        elem.addEventListener(event,func,false);
    else if (elem.attachEvent) { // IE DOM
        //elem.attachEvent("on"+event, func);
        elem.attachEvent("on" + event, function() {return(func.call(elem, window.event));});
    }
    else { // No much to do
        elem[event] = func;
    }
}

function setNavBar() {

    $('ul.li').removeClass('active');

    var full = window.location.pathname;

    var id = 'userhome';

    //Admin
    if( full.indexOf("/user/listusers") !== -1 ) {
        id = 'admin';
    }
    if( full.indexOf("/admin/") !== -1 ) {
        id = 'admin';
    }
    if( full.indexOf("/access-requests") !== -1 ) {
        id = 'admin';
    }
    if( full.indexOf("/account-requests") !== -1 ) {
        id = 'admin';
    }
    if( full.indexOf("/listusers") !== -1 ) {
        id = 'admin';
    }
    if( full.indexOf("/users/") !== -1 ) {
        id = 'admin';
    }
    if( full.indexOf("/event-log") !== -1 ) {
        id = 'admin';
    }
    if( full.indexOf("/settings") !== -1 ) {
        id = 'admin';
    }
//    if( full.indexOf("/user-directory") !== -1 ) {
//        id = 'admin';
//    }

    if( full.indexOf("/users/new") !== -1 ) {
        id = 'adduser';
    }
    if( full.indexOf("/locations/new") !== -1 ) {
        id = 'addlocation';
    }

    if( full.indexOf("/user-directory") !== -1 ) {
        id = 'userlist';
    }

    if( full.indexOf("/user-directory/previous") !== -1 ) {
        id = 'userlist-previous';
    }

    if( full.indexOf("/users/") !== -1 || full.indexOf("/edit-user-profile/") !== -1 ) {
        if( $('#nav-bar-admin').length > 0 ) {
            //id = 'admin';
        } else {
            id = 'user';
        }
    }

    //console.log("id="+id);
    //console.info("full="+window.location.pathname+", id="+id + " ?="+full.indexOf("multi/clinical"));

    $('#nav-bar-'+id).addClass('active');
}




//Helpers
function capitaliseFirstLetter(string)
{
    return string.charAt(0).toUpperCase() + string.slice(1);
}

function printF(element,text) {
    var str = "id="+element.attr("id") + ", class=" + element.attr("class")
    if( text ) {
        str = text + " : " + str;
    }
    console.log(str);
}

function inArrayCheck( arr, needle ) {
    //console.log('len='+arr.length+", needle: "+needle+"?="+parseInt(needle));

    if( needle == '' ) {
        return -1;
    }

    if( needle == parseInt(needle) ) {
        return needle;
    }

    for( var i = 0; i < arr.length; i++ ) {
        //console.log(arr[i]['text']+'?='+needle);
        if( arr[i]['text'] === needle ) {
            return arr[i]['id'];
        }
    }
    return -1;
}

function isInt(value) {
    return !isNaN(value) &&
        parseInt(Number(value)) == value &&
        !isNaN(parseInt(value, 10));
}

