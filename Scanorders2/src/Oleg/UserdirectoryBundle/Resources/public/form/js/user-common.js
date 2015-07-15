/**
 * Created by oli2002 on 9/3/14.
 */


var _sitename = "";
var asyncflag = true;
var combobox_width = '100%'; //'element'

var urlBase = $("#baseurl").val();
var cycle = $("#formcycle").val();
var user_name = $("#user_name").val();
var user_id = $("#user_id").val();


function regularCombobox(holder) {
    var targetid = "select.combobox";
    if( $(targetid).length == 0 ) {
        return;
    }
    if( typeof holder !== 'undefined' && holder.length > 0 ) {
        targetid = holder.find(targetid);
        if( targetid.length == 0 ) {
            return;
        }
    }
    $(targetid).each( function() {
        specificRegularCombobox( $(this) )
    });
}
function specificRegularCombobox( comboboxEl ) {
    comboboxEl.select2({
        width: combobox_width,
        dropdownAutoWidth: true,
        placeholder: "Select an option",
        allowClear: true,
        selectOnBlur: false
        //containerCssClass: 'combobox-width'
    });

    if( comboboxEl.attr("readonly") ) {
        comboboxEl.select2("readonly", true);
    }

//        if( comboboxEl.hasClass('element-with-select2-tooltip') ) {
//            console.log('regularCombobox: add tooltip to id='+comboboxEl.attr('id'));
//            console.log('title tooltip='+comboboxEl.attr('title'));
//            var parent = comboboxEl.parent().tooltip({
//                title: comboboxEl.attr('title')
//            });
//            //comboboxEl.tooltip();
//        }
}

//Generic ajax combobox
function getComboboxGeneric(holder,name,globalDataArray,multipleFlag,urlprefix,sitename,force) {

    //console.log('get Combobox Generic: name='+name);

    var targetid = ".ajax-combobox-"+name;

    if( typeof force === 'undefined' ) {
        force = false;
    }

    if( !force && $(targetid).length == 0 ) {
        return;
    }

    if( typeof holder !== 'undefined' && holder && holder.length > 0 ) {
        targetid = holder.find(targetid);

        if( !force && targetid.length == 0 )
            return;
    }

    if( typeof urlprefix === 'undefined' || urlprefix == null ) {
        urlprefix = "generic/";
    }

    if( typeof sitename === 'undefined' || sitename == null ) {
        sitename = "employees";
    }

    var url = getCommonBaseUrl("util/common/"+urlprefix+name,sitename);
    //console.log('get Combobox Generic: url='+url);

    if( globalDataArray.length == 0 ) {
        $.ajax({
            url: url,
            timeout: _ajaxTimeout,
            async: asyncflag
        }).success(function(data) {
            $.each(data, function(key, val) {
                globalDataArray.push(val);
                //console.log(data);
            });
            populateSelectCombobox( targetid, globalDataArray, "Select an option or type in a new value", multipleFlag );
        });
    } else {
        populateSelectCombobox( targetid, globalDataArray, "Select an option or type in a new value", multipleFlag );
    }

}

//target - class or id of the target element
function populateSelectCombobox( target, data, placeholder, multipleFlag ) {

    //console.log("target="+target);
    //printF(target,'populate combobox target: ');

//    //clear the value if it is not set (What is the point to do so if it is empty?!)
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
    //console.log('initDatetimepicker datetimepicker.length='+datetimepicker.length);
    if( datetimepicker.length > 0 ) {
        //printF(datetimepicker,"init:");
        datetimepicker.datetimepicker({
            //format: 'mm-dd-yyyy hh:i'
            format: 'mm/dd/yyyy hh:i'
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
        sitename = "directory";
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

function collapseThis(link) {
    //console.log('collapse This');
    var holder = $(link).closest('.panel');
    holder.find('.panel-collapse').collapse('toggle');
}

function collapseAll(holder) {
    //console.log('collapse All');
    if( typeof holder === 'undefined' ) {
        $('.panel-collapse').collapse('hide');
    } else {
        $(holder).find('.panel-collapse').collapse('hide');
    }

//    if( $(holder).is(":visible") ) {
//        $(holder).collapse('hide');
//    }
}

function extendAll(holder) {
    //console.log('extend All');
    if( typeof holder === 'undefined' ) {
        $('.panel-collapse').collapse('show');
    } else {
        $(holder).find('.panel-collapse').collapse('show');
    }

    if( !$(holder).is(":visible") ) {
        $(holder).collapse('show');
    }
}


//function initDatepicker_orig( holder ) {
//
//    if( cycle != "show" ) {
//
//        //console.log("init Datepicker");
//        //console.log(holder);
//
//
//        if( typeof holder !== 'undefined' && holder && holder.length > 0 ) {
//            var target1 = holder.find('.input-group.date.regular-datepicker').not('.allow-future-date');
//            var target2 = holder.find('.input-group.date.allow-future-date');
//            var target3 = holder.find('.input-group.date.datepicker-only-month-year');
//        } else {
//            var target1 = $('.input-group.date.regular-datepicker').not('.allow-future-date');
//            var target2 = $('.input-group.date.allow-future-date');
//            var target3 = $('.input-group.date.datepicker-only-month-year');
//        }
//
//        processAllDatepickers( target1 );
//        processAllDatepickers( target2 );
//        processAllDatepickers( target3 );
//
////        //make sure the masking is clear when input is cleared by datepicker
////        regularDatepickers.datepicker().on("clearDate", function(e){
////            var inputField = $(this).find('input');
////            //printF(inputField,"clearDate input:");
////            clearErrorField( inputField );
////        });
//
//    }
//
//}

function initDatepicker( holder ) {

    if( cycle != "show" ) {

        //console.log("init Datepicker");
        //console.log(holder);

        if( typeof holder !== 'undefined' && holder && holder.length > 0 ) {
            var targets = holder.find('.input-group.date');
        } else {
            var targets = $('.input-group.date');
        }

        processAllDatepickers( targets );

    }

}

function processAllDatepickers( targets ) {

    targets.each( function() {

        initSingleDatepicker( $(this) );

    });

}

//Note: for bootstrap's "hide.bs.collapse" event use datepicker fix https://github.com/eternicode/bootstrap-datepicker/issues/978
function initSingleDatepicker( datepickerElement ) {

    //console.log("initSingleDatepicker:");
    //printF(datepickerElement,'datepicker element:');
    //console.log(datepickerElement);

    //disable datepickers with readonly attributes
    var inputField = datepickerElement.find('input.datepicker, input.datepicker-exception');
    //var inputField = datepickerElement.find('input.datepicker');
    //printF(inputField,'inputField:');

    var calendarIconBtn = datepickerElement.find('.calendar-icon-button');
    //console.log("calendarIconBtn:");
    //console.log(calendarIconBtn);

    if( inputField.is('[readonly]') || inputField.is('[disabled]') ) {

        //console.log('datepicker input field is readonly');
        //console.log(inputField);
        datepickerElement.datepicker("remove");

        //calendarIconBtn.off();
        calendarIconBtn.prop('disabled', true);

    } else {

        //console.log('datepicker input field is active !!!!!!!!!!!!!!!!!!!!!!');

        var endDate = new Date(); //use current date as default
        if( datepickerElement.hasClass('allow-future-date') ) {
            endDate = false;//'End of time';
        }
        //console.log('endDate='+endDate);

        var datepickertodayBtn = "linked";
        var datepickerFormat = "mm/dd/yyyy";
        //var datepickerStartView = "month";
        var datepickerMinViewMode = "days";
        if( datepickerElement.hasClass('datepicker-only-month-year') ) {
            datepickertodayBtn = false;
            datepickerFormat = "mm/yyyy";
            //datepickerStartView = "month";
            datepickerMinViewMode = "months";
            //console.log('datepickerFormat='+datepickerFormat);
            //console.log(datepickerElement);
        }

        //to prevent datepicker clear on Enter key, use the version from https://github.com/eternicode/bootstrap-datepicker/issues/775
        datepickerElement.datepicker({
            autoclose: true,
            clearBtn: true,
            todayBtn: datepickertodayBtn,
            todayHighlight: true,
            endDate: endDate,
            ////minDate: new Date(1902, 1, 1)   //null
            format: datepickerFormat,
            minViewMode: datepickerMinViewMode
        });

        calendarIconBtn.prop('disabled', false);

        //fix bug: https://github.com/eternicode/bootstrap-datepicker/issues/978
        datepickerElement.datepicker().on('hide.bs.collapse', function(event) {
            // prevent datepicker from firing bootstrap modal "show.bs.modal"
            event.stopPropagation();
        });
        datepickerElement.datepicker().on('shown.bs.collapse', function(event) {
            // prevent datepicker from firing bootstrap modal "show.bs.modal"
            event.stopPropagation();
        });

        datepickerElement.datepicker().on("clearDate", function(e){
            var inputField = $(this).find('input.datepicker, input.datepicker-exception');
            //console.log('on clear Date');
            printF(inputField,"clearDate input:");
            clearErrorField( inputField );
        });
    }

    return;
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


//set geo location
function setGeoLocation( holder, data ) {
    var street1 = null;
    var street2 = null;
    var city = null;
    var state = null;
    var county = null;
    var country = null;
    var zip = null;

    //console.log(data);

    if( data ) {
        street1 = data.street1;
        street2 = data.street2;
        city = data.city;
        state = data.state;
        county = data.county;
        country = data.country;
        zip = data.zip;
    }

    holder.find('.geo-field-street1').val(street1);
    holder.find('.geo-field-street2').val(street2);
    holder.find('.geo-field-city').val(city);
    holder.find('.geo-field-county').val(county);
    holder.find('.geo-field-state').select2('val',state);
    holder.find('.geo-field-country').select2('val',country);
    holder.find('.geo-field-zip').val(zip);
}


