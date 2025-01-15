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
 * Created by oli2002 on 9/3/14.
 */

//var _tenantprefix = $("#tenantprefix").val();
var _cycleShow = false;
var _sitename = "";
var asyncflag = true;
var combobox_width = '100%'; //'element'

var urlBase = $("#baseurl").val();
var cycle = $("#formcycle").val();
var user_name = $("#user_name").val();
var user_id = $("#user_id").val();
var _authuser_id = $("#authuser_id").val();

if( !user_id ) {
    user_id = 'undefined';
}

//Window.prototype.setCicleShow = setCicleShow;
//Window.prototype.getSitename  = getSitename;
//Window.prototype.fieldInputMask = fieldInputMask;
// export default function(setCicleShow) {
//     return setCicleShow();
// };

function setCicleShow() {
    //console.log("setCicleShow: cycle="+cycle);
    //console.log("setCicleShow: cycle.indexOf="+cycle.indexOf("show"));
    if( cycle && (cycle.indexOf("show") != -1 || cycle.indexOf("review") != -1) ) {
        _cycleShow = true;
        //console.log("setCicleShow: true");
    } else {
        //console.log("setCicleShow: false");
    }
}
//decalre globally
// setCicleShow = function() {
//     console.log("setCicleShow: cycle="+cycle);
//     //console.log("setCicleShow: cycle.indexOf="+cycle.indexOf("show"));
//     if( cycle && (cycle.indexOf("show") != -1 || cycle.indexOf("review") != -1) ) {
//         _cycleShow = true;
//         //console.log("setCicleShow: true");
//     } else {
//         //console.log("setCicleShow: false");
//     }
// }

function isIE() {
  var myNav = navigator.userAgent.toLowerCase();
  return (myNav.indexOf('msie') != -1) ? parseInt(myNav.split('msie')[1]) : false;
}

function checkBrowserComptability() {
    //console.log('IE='+isIE());
    if( isIE() && isIE() <= 7 ) {        
        // is IE version equal or less than 7
        var msg = "Warning! You are using an old version of browser Internet Explorer 7 or lower. \n\
                    Please upgrade the browser or use the modern browsers such as \n\
                    Firefox or Google Chrome to have full features of this system.";
        $('.browser-notice').html(msg);
        $('.browser-notice').show();           
    } 
}

function initTooltips() {

    //tooltip always
    $(".element-with-tooltip-always").tooltip();
    attachTooltipToSelectCombobox('.element-with-select2-tooltip-always',null);

    //Tooltips in button groups, input groups, and tables require special setting
    //you'll have to specify the option container: 'body' (documented below) to avoid
    //unwanted side effects (such as the element growing wider and/or losing its rounded corners when the tooltip is triggered).
    $(".element-table-with-tooltip-always").tooltip({container: 'body'});

    //element-with-select2-tooltip-always-when-readonly
    //console.log('cycle='+cycle);
//    if( cycle && cycle.indexOf("show") == -1 ) {
//        $('.element-with-select2-tooltip-always-when-readonly').each( function(){
//            //printF($(this),'element-with-select2-tooltip-always-when-readonly:');
//            if( $(this).hasClass('select2-container-disabled') ) {
//                attachTooltipToSelectCombobox('.element-with-select2-tooltip-always-when-readonly',$(this));
//            }
//
//            //add on enable/disable event
//        });
//    }

    var userPreferencesTooltip = $("#user-preferences-tooltip").val();
    if( userPreferencesTooltip == 0 ) {
        return false;
    }

    //tooltip if user preferences is set
    $(".element-with-tooltip").tooltip();
    attachTooltipToSelectCombobox('.element-with-select2-tooltip',null);
}

function attachTooltipToSelectCombobox( comboboxSelector, comboboxEl ) {
    //console.log('attachTooltipToSelectCombobox; comboboxSelector='+comboboxSelector);
    if( comboboxEl == null ) {
        comboboxEl = $(comboboxSelector);
    }
    //console.log(comboboxEl);
    comboboxEl.parent().tooltip({
        title: function() {
            var titleText = $(this).find('select,input'+comboboxSelector).attr('title');
            //console.log('titleText='+titleText);
            return titleText;
        }
    });
}

function regularCombobox(holder) {
    
    //console.log('IE='+isIE());
    //console.log('regularCombobox');

    if( isIE() && isIE() <= 7 ) {
        // is IE version equal or less than 7
        checkBrowserComptability();
        return;
    } 
    
    var targetid = "select.combobox";

    targetid = getElementTargetByHolder(holder,targetid);

    if( $(targetid).length == 0 ) {
        return;
    }

    $(targetid).each( function() {
        specificRegularCombobox( $(this) )
    });
}
function specificRegularCombobox( comboboxEl ) {
    //return; //testing
    //console.log("specificRegularCombobox");
    //console.log('comboboxEl:');
    //console.log(comboboxEl);

    if( comboboxEl && comboboxEl.length ) {
        //ok
    } else {
        return;
    }

    var comboboxWidth = combobox_width;
    if( comboboxEl.hasClass('combobox-no-width') ) {
        comboboxWidth = null;
    }

    comboboxEl.select2({
        width: comboboxWidth,
        dropdownAutoWidth: true,
        placeholder: "Select an option",
        allowClear: true,
        selectOnBlur: false,
        matcher: select2Matcher
        //containerCssClass: 'combobox-width'
    });

    // comboboxEl.select2({
    //     width: comboboxWidth,
    //     dropdownAutoWidth: true,
    //     placeholder: "Select an option",
    //     allowClear: true,
    //     selectOnBlur: false,
    //     matcher: select2Matcher
    // });

    if (comboboxEl.attr("readonly")) {
        comboboxEl.select2("readonly", true);
    }

    if( comboboxEl.hasClass('other-status') ) {
        listenerComboboxStatusField(comboboxEl);
    }

//        if( comboboxEl.hasClass('element-with-select2-tooltip') ) {
//            console.log('regularCombobox: add tooltip to id='+comboboxEl.attr('id'));
//            console.log('title tooltip='+comboboxEl.attr('title'));
//            var parent = comboboxEl.parent().tooltip({
//                title: comboboxEl.attr('title')
//            });
//            //comboboxEl.tooltip();
//        }

    //Disabling Chrome Autofill (autocomplete="new-password" should work as well)
    $('input.select2-input').attr('autocomplete', "xxxxxxxxxxx");
}


function listenerComboboxStatusField() {
    return;
}

function getElementTargetByHolder(holder,target) {
    if( holder && typeof holder !== 'undefined' && holder.length > 0 ) {
        target = holder.find(target);
    }

    return target;
}

//Generic ajax combobox
function getComboboxGeneric(holder,name,globalDataArray,multipleFlag,urlprefix,sitename,force,placeholder,thisAsyncflag) {

    //console.log('get Combobox Generic: name='+name);

    var targetid = ".ajax-combobox-"+name;
    targetid = getElementTargetByHolder(holder,targetid);

    if( typeof placeholder === 'undefined' || placeholder == null ) {
        placeholder = "Select an option or type in a new value";
    }

    if( typeof force === 'undefined' || force == null ) {
        force = false;
    }

    if( typeof thisAsyncflag === 'undefined' || thisAsyncflag == null ) {
        thisAsyncflag = asyncflag;
    }

    if( !force && $(targetid).length == 0 ) {
        return;
    }

    if( typeof urlprefix === 'undefined' || urlprefix == null ) {
        urlprefix = "generic/";
    }

    if( typeof sitename === 'undefined' || sitename == null ) {
        sitename = "employees";
    }

    if( typeof cycle === 'undefined' ) {
        cycle = 'new';
    }
    var cycleStr = "?cycle="+cycle;

    var sitenameStr = getSitename();
    if( sitenameStr ) {
        sitenameStr = "&sitename="+sitenameStr;
    }

    //path: '/common/generic/{name}'
    //var url = getCommonBaseUrl("util/common/"+urlprefix+name+cycleStr+sitenameStr,sitename);
    var url = null;
    if( sitename == "employees" ) {
        if (urlprefix === "generic/") {
            url = Routing.generate('employees_get_generic_select2', {
                name: name,
                cycle: cycle
            });
        } else if( urlprefix === "genericusers/") {
            url = Routing.generate('employees_get_genericusers', {
                name: name,
                cycle: cycle
            });
        } else {
            url = getCommonBaseUrl("util/common/"+urlprefix+name+cycleStr+sitenameStr,sitename);
            // url = Routing.generate('employees_get_special_select2', {
            //     //urlprefix: urlprefix,
            //     name: name,
            //     cycle: cycle,
            //     sitename: sitenameStr
            // });
        }
    } else if( sitename == "scan" ) {
        url = getCommonBaseUrl("util/common/"+urlprefix+name+cycleStr+sitenameStr,sitename);
    } else {
        console.log('Invalid sitename='+url);
    }

    console.log('get Combobox Generic: url='+url);

    if( globalDataArray.length == 0 ) {
        $.ajax({
            url: url,
            timeout: _ajaxTimeout,
            async: thisAsyncflag
        }).done(function(data) {
            $.each(data, function(key, val) {
                //console.log("val="+val);
                globalDataArray.push(val);
                //console.log(data);
            });
            populateSelectCombobox( targetid, globalDataArray, placeholder, multipleFlag );
        });
    } else {
        populateSelectCombobox( targetid, globalDataArray, placeholder, multipleFlag );
    }

    //console.log("EOF getComboboxGeneric");
}

//target - class or id of the target element
function populateSelectCombobox( target, data, placeholder, multipleFlag ) {

    //console.log("populateSelectCombobox");
    //console.log("target="+target);
    //printF(target,'populate combobox target: ');

    //printF($('#oleg_fellappbundle_fellowshipapplication_user_infos_0_firstName'),'populate combobox target1: ');
    //printF(document.getElementById('oleg_fellappbundle_fellowshipapplication_user_infos_0_firstName'),'populate combobox target2: ');
    //printF(document.getElementById('sssssss'),'populate combobox target3: ');

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
        //console.log('allowClear true');
    } else {
        var allowClear = false;
        //console.log('allowClear false');
    }
    //allowClear = true;
    //console.log('allowClear='+allowClear);

    if( multipleFlag ) {
        //console.log('multiple true');
        var multiple = true;
    } else {
        //console.log('multiple false');
        var multiple = false;
    }

    if( !data ) {
        data = [];  //new Array();
    }

    var createSearchChoice = function(term, data) {
        //if( term.match(/^[0-9]+$/) != null ) {
        //    //console.log("term is digit");
        //}
        //for select 3.* multiple values are separated by comma, so do not allow comma
        term = term.replace(/,/g, '');
        //console.log("term="+term);
        return {id:term, text:term};
    };



    if( $(target).hasClass('combobox-without-add') ) {
        createSearchChoice = null;
    }

    //filter disbaled options from data
    data = filterDisabledOptions(data,target);

    var comboboxWidth = combobox_width;
    if( $(target).hasClass('combobox-no-width') ) {
        comboboxWidth = null;
    }

    $(target).select2({
        placeholder: placeholder,
        allowClear: allowClear,
        width: comboboxWidth,
        dropdownAutoWidth: true,
        selectOnBlur: false,
        dataType: 'json',
        quietMillis: 100,
        multiple: multiple,
        data: data,
        createSearchChoice: createSearchChoice,
        matcher: select2Matcher
    });

    if( $(target).attr("readonly") ) {
        $(this).select2("readonly", true);
    }

    if( $(target).hasClass('other-status') ) {
        listenerComboboxStatusField($(target));
    }

    //console.log("EOF populateSelectCombobox");
}

//Select2 V3
var select2Matcher = function(term, text, opt) {
    //console.log("term="+term);
    //console.log("text="+text);
    var textStr = text.toString().toUpperCase().replace(/[\. ,\/:;-]+/g, "");
    var termStr = term.toString().toUpperCase().replace(/[\. ,\/:;-]+/g, "");
    return textStr.indexOf(termStr)>=0;
}
//Select2 V4 (https://select2.org/searching function matchCustom(params, data))
var select2Matcher_V4 = function(params, data) {

    // If there are no search terms, return all of the data
    if ($.trim(params.term) === '') {
        return data;
    }

    // Do not display the item if there is no 'text' property
    if (typeof data.text === 'undefined') {
        return null;
    }

    var textStr = data.text.toString().toUpperCase().replace(/[\. ,\/:;-]+/g, "");
    var termStr = params.term.toString().toUpperCase().replace(/[\. ,\/:;-]+/g, "");

    if( textStr.indexOf(termStr)>=0 ) {
        var modifiedData = $.extend({}, data, true);
        //modifiedData.text += ' (matched)';
        // You can return modified objects from here
        return modifiedData;
    }

    // Return `null` if the term should not be displayed
    return null;
}

var filterDisabledOptions = function(data,target) {

    function checkRemove(dataOption,selectedId) {
        var remove = false;
        if( dataOption.disabled == true ) {
            //remove this option because it is disabled or draft
            //console.log(selectedId + '?=> remove disbaled ################# ' + dataOption.id);
            if( selectedId ) {
                if( selectedId == dataOption.id ) {
                    remove = false;
                } else {
                    remove = true;
                }
            } else {
                remove = true;
            }
        } //if

        return remove;
    }

    var selectedId = $(target).val();
//    console.log('selected selectedId='+selectedId);
//    console.log($(target));
//    console.log(data);

    //filter: data is new array
//    data = data.filter(function( dataOption ) {
//        var remove = checkRemove(dataOption,selectedId);
//        return !remove;
//    });

    for (var i = 0; i < data.length; i++) {

        var remove = checkRemove(data[i],selectedId);

        if( remove ) {
            //console.log('remove index:' + i + "=>" + data[i].id);
            data.splice(i,1);
            //console.log('after data clean:');
            //console.log(data);
        }
    }

//    if( selectedId == 142 ) {
//        console.log('after data clean:');
//        console.log(data);
//    }

    return data;
};


function initDatetimepicker() {

    $('.form_datetime').each( function() {

        //printF($(this),"init:");
        $(this).datetimepicker({
            format: 'mm/dd/yyyy hh:i',
            autoclose: true,
            todayBtn: true
        });

    });
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

//TODO: replace by Routing.generate('my-route-name');
function getCommonBaseUrl(link,sitename) {
    //console.log('getCommonBaseUrl: sitename='+sitename);

    if( typeof sitename === 'undefined' ) {
        sitename = getSitename();
    }

    if( sitename == "employees" ) {
        sitename = "directory";
    }

    if( typeof sitename === 'undefined' ) {
        sitename = "directory";
    }
    if( sitename ) {
        //OK
    } else {
        sitename = "directory";
    }

    //console.log('sitename='+sitename);

    var scheme = "http:";
    var url = window.location.href;
    console.log('window.location.href url='+url);

    var urlArr = url.split("/");
    if( urlArr.length > 0 ) {
        scheme = urlArr[0];
    }
    //console.log('scheme='+scheme);

    //get tenantprefix from container
    //_tenantprefix = ''; //testing
    //console.log("_tenantprefix="+_tenantprefix);
    //Get the tenantprefix from the URL
    // /order/index_dev.php/c/lmh/pathology/fellowship-applications/interview-modal/1575
    //Or get it using ajax call to the server to get tenantprefix from the container
    //var tenantprefix = 'c/lmh/pathology/';

    var prefix = sitename;  //"scan";
    var urlBase = $("#baseurl").val();
    if( typeof urlBase !== 'undefined' && urlBase != "" ) {
        //if( _tenantprefix ) {
        //    urlBase = scheme + "//" + urlBase + "/" + _tenantprefix + "/" + prefix + "/" + link;
        //} else {
            urlBase = scheme + "//" + urlBase + "/" + prefix + "/" + link;
        //}
        //urlBase = scheme + "//" + urlBase + "/" + _tenantprefix + prefix + "/" + link;
    }

    //url might be in the form of:
    //urlBase=/order/index_dev.php/c/wcm/pathology/directory/util/common/generic/residencytracks

    console.log("urlBase="+urlBase);
    //alert("urlBase="+urlBase);
    return urlBase;
}

function getSitename() {
    //console.log("getSitename");
    //if( typeof _sitename != 'undefined' && _sitename != "" )
    //    return;

    // var holder = '/order/';
    // var sitename = '';
    // var url = document.URL;
    //console.log("url="+url);
    // if( url.indexOf(holder) !== -1 ) {
    //     var urlArr = url.split(holder);
    //     var urlSite = urlArr[1];
    // } else {
    //     //https://css-tricks.com/example/index.html?s=flexbox
    //     //window.location.host = "css-tricks.com"
    //     //window.location.pathname = "example/index.html"
    //     var urlArr = window.location.pathname.split('/');
    //     var urlSite = urlArr[0];
    // }

    var sitename = siteNameMapper();
    if( sitename ) {
        return sitename;
    }

    //https://css-tricks.com/example/index.html?s=flexbox
    //window.location.host = "css-tricks.com"
    //window.location.pathname = "example/index.html"
    //console.log("window.location.pathname="+window.location.pathname);

    var urlArr = window.location.pathname.split('/');
    var urlSite = urlArr[0];
    //console.log("urlSite="+urlSite);

    //get rid of app_dev.php
    var urlfullClean = urlSite.replace("app_dev.php/", "");
    var urlfullClean = urlSite.replace("index_dev.php/", "");
    //console.log("urlfullClean="+urlfullClean);

    var urlCleanArr =  urlfullClean.split("/");
    var sitename =  urlCleanArr[0];

    sitename = sitename.replace("/", "");

    _sitename = sitename;

    //scan or employees
    return sitename;
}

function siteNameMapper() {
    var url = window.location.pathname;

    var sitename = 'directory';

    if( url.indexOf("directory") !== -1 ) {
        sitename = 'directory';
    }
    if( url.indexOf("call-log-book") !== -1 ) {
        sitename = 'call-log-book';
    }
    if( url.indexOf("critical-result-notifications") !== -1 ) {
        sitename = 'critical-result-notifications';
    }
    if( url.indexOf("fellowship-applications") !== -1 ) {
        sitename = 'fellowship-applications';
    }
    if( url.indexOf("residency-applications") !== -1 ) {
        sitename = 'residency-applications';
    }
    if( url.indexOf("time-away-request") !== -1 ) {
        sitename = 'time-away-request';
    }
    if( url.indexOf("translational-research") !== -1 ) {
        sitename = 'translational-research';
    }
    if( url.indexOf("deidentifier") !== -1 ) {
        sitename = 'deidentifier';
    }
    if( url.indexOf("scan") !== -1 ) {
        sitename = 'scan';
    }

    return sitename;
}

function collapseThis(link) {
    //console.log('collapse This');
    var holder = $(link).closest('.panel');
    holder.find('.panel-collapse').collapse('toggle');
}

function collapseAll(holder) {
    console.log('collapse All');
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
    console.log('extend All');
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
    //console.log("init Datepicker cycle="+cycle);
    if( cycle != "show" ) {

        //console.log("init Datepicker");
        //console.log(holder);

        if( typeof holder !== 'undefined' && holder && holder.length > 0 ) {
            //var targets = holder.find('.input-group.date').not('.form_datetime, .datepicker-ignore');
            var targets = holder.find('.input-group.date:not(.form_datetime, .datepicker-ignore)');
        } else {
            //var targets = $('.input-group.date').not('.form_datetime, .datepicker-ignore');
            var targets = $('.input-group.date:not(.form_datetime, .datepicker-ignore)');
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
    //console.log("1 initSingleDatepicker:",datepickerElement);
    if( datepickerElement.hasClass('datepicker-ignore') ) {
        printF(datepickerElement,'Ignore:');
        return;
    }

    //disable datepickers with readonly attributes
    var inputField = datepickerElement.find('input.datepicker, input.datepicker-exception');
    //var inputField = datepickerElement.find('input.datepicker');
    //printF(inputField,'inputField:');

    if( inputField.hasClass('datepicker-ignore') ) {
        printF(inputField,'Ignore:');
        return;
    }

    //console.log("2 initSingleDatepicker:");
    //printF(datepickerElement,'datepicker element:');
    //console.log(datepickerElement);

    var calendarIconBtn = datepickerElement.find('.calendar-icon-button');
    //console.log("initSingleDatepicker: calendarIconBtn:",calendarIconBtn);
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
            //console.log('allow future date');
            endDate = false;//'End of time';
        }
        //console.log('endDate='+endDate);

        var datepickertodayBtn = "linked";
        var datepickerFormat = "mm/dd/yyyy";
        //var datepickerStartView = "month";
        var datepickerMinViewMode = "days";
        var datepickerViewMode = null;  //"days";
        var datepickerMultidate = false;
        if( datepickerElement.hasClass('datepicker-only-month-year') ) {
            datepickertodayBtn = false;
            datepickerFormat = "mm/yyyy";
            //datepickerStartView = "month";
            datepickerMinViewMode = "months";
            //datepickerViewMode = "months";
            //console.log('datepickerFormat='+datepickerFormat);
            //console.log(datepickerElement);
        }
        if( datepickerElement.hasClass('datepicker-only-day-month') ) {
            datepickertodayBtn = false;
            datepickerFormat = "dd/mm";
            //datepickerStartView = "month";
            datepickerMinViewMode = "days";
            //datepickerViewMode = "months";
            //console.log('datepickerFormat='+datepickerFormat);
            //console.log(datepickerElement);
        }
        if( datepickerElement.hasClass('datepicker-only-year') ) {
            datepickertodayBtn = false;
            datepickerFormat = " yyyy";
            datepickerMinViewMode = "years";
            datepickerViewMode = "years";
        }
        if( datepickerElement.hasClass('datepicker-multidate') ) {
            datepickerMultidate = true;
        }

        //calendarStartDate calendarEndDate
        //printF(datepickerElement,"datepickerElement:");
        //console.log('datepickerElement:',datepickerElement);
        var calendarStartDate = datepickerElement.find('input').data("calendarstartdate");
        //console.log('calendarStartDate='+calendarStartDate);
        //calendarStartDate = new Date(); //testing
        //calendarStartDate.setDate(calendarStartDate.getDate() - 2); //testing
        var startDate = false;
        if( calendarStartDate ) {
            startDate = calendarStartDate;
        }
        var calendarEndDate = datepickerElement.find('input').data("calendarenddate");
        //console.log('endDate='+endDate);
        //endDate = new Date(); //testing
        //endDate.setDate(endDate.getDate() + 2); //testing
        if( calendarEndDate ) {
            endDate = calendarEndDate;
        }

        //console.log('endDate',endDate);

        //to prevent datepicker clear on Enter key, use the version from https://github.com/eternicode/bootstrap-datepicker/issues/775
        datepickerElement.datepicker({
            autoclose: true,
            clearBtn: true,
            todayBtn: datepickertodayBtn,
            todayHighlight: true,
            startDate: startDate,
            endDate: endDate,
            orientation: "auto", //"auto top"
            ////minDate: new Date(1902, 1, 1)   //null
            format: datepickerFormat,
            minViewMode: datepickerMinViewMode,
            viewMode: datepickerViewMode,
            multidate: datepickerMultidate,
        });

        calendarIconBtn.prop('disabled', false);

        //fix bug: https://github.com/eternicode/bootstrap-datepicker/issues/978
        //datepickerElement.datepicker().on('hide.bs.collapse', function(event) {
        datepickerElement.on('hide.bs.collapse', function(event) {
            // prevent datepicker from firing bootstrap modal "show.bs.modal"
            event.stopPropagation();
            //console.log('hide.bs.collapse element id='+$(this).attr("id"));
            //printF($(this).find('input.datepicker'),"hide.bs.collapse element:");
            $(this).find('input.datepicker').removeClass('datepicker-status-open');
        });
        //datepickerElement.datepicker().on('shown.bs.collapse', function(event) {
        datepickerElement.on('shown.bs.collapse', function(event) {
            // prevent datepicker from firing bootstrap modal "show.bs.modal"
            event.stopPropagation();
        });

        //datepickerElement.datepicker().on("clearDate", function(e){
        datepickerElement.on("clearDate", function (e) {
            var inputField = $(this).find('input.datepicker, input.datepicker-exception');
            console.log('on clear Date');
            //printF(inputField, "clearDate input:");
            clearErrorField(inputField); //callback  - clear masking field in user-masking.js
            customClearDatepickerFunction(inputField); //callback - use other custom function

            //if( inputField.hasClass('datepicker-onclear-cleartooltip') ) {
            //    console.log("clear tooltip!!!!!!");
            //    console.log($(this));
            //    //$(this).closest('.datepicker').tooltip('destroy');
            //    //$(this).tooltip('destroy');
            //    $(this).tooltip({
            //        title: function() {
            //            return "";
            //        }
            //    });
            //}

        });

        //open/close the date picker on click icon (calendar-icon-button) or body
        calendarIconBtn.on( "click", function(event) {
            event.stopPropagation();
            //console.log( "user-common: click calendar icon" );
            var inputField = $(this).closest('.input-group').find('input.datepicker');
            if( inputField.hasClass("datepicker-status-open") ) {
                //console.log( "hide datepicker" );
                //$('body').off('click');
                //$('body').click();
                $(".datepicker-dropdown").remove();
                inputField.removeClass("datepicker-status-open");
            } else {
                inputField.addClass("datepicker-status-open");
            }

        });

        if(0) {
            //fiddle: http://jsfiddle.net/oab6eyv1/230/
            //datepickerElement.find('input.datepicker').on("click", function (event) {
            datepickerElement.on("click", function (event) {
                event.stopPropagation();
                //console.log("click datepicker body");

                var inputField = $(this).find('input.datepicker');

                if (inputField.hasClass("datepicker-status-open")) {
                //if( inputField.hasClass('focus.inputmask') ) {
                    //console.log("hide datepicker");
                    //$(".datepicker-dropdown").remove();
                    //$(document).click();
                    //$('html,body').click();
                    $(".datepicker-dropdown").remove();
                    inputField.removeClass("datepicker-status-open");
                    inputField.removeClass("focus.inputmask");
                    //$(this).find('.calendar-icon-button').click();
                    $(this).find('.calendar-icon-button').focus();
                    $(document).click();
                    //printF($(this).parent().find('.calendar-icon-button'), "calendar:");
                } else {
                    inputField.addClass("datepicker-status-open");
                }
            });
        }

        //datepickerElement.on('keyup', function(){
        //    console.log("datepicker on keyup value="+$(this).val());
        //    if( $(this).val() == datepickerFormat ){
        //        // this only happens when a key is released and no valid value is in the field. Eg. when tabbing into the field, we'll make sure the datepicker plugin does not get '__/__/____' as a date value
        //        $(this).val('');
        //    }
        //});

    }

    return;
}

//This call function can be overriden
function customClearDatepickerFunction(inputField) {
    //console.log('customClearDatepickerFunction');
    return;
}

// //Testing
// function clickCalendarButton() {
//     //event.stopPropagation();
//     //console.log( "user-common: clickCalendarButton: click calendar icon" );
//     var inputField = $(this).closest('.input-group').find('input.datepicker');
//     if( inputField.hasClass("datepicker-status-open") ) {
//         //console.log( "hide datepicker" );
//         //$('body').off('click');
//         //$('body').click();
//         $(".datepicker-dropdown").remove();
//         inputField.removeClass("datepicker-status-open");
//     } else {
//         inputField.addClass("datepicker-status-open");
//     }
// }
// Window.prototype.clickCalendarButton  = clickCalendarButton;
// Window.prototype.initSingleDatepicker  = initSingleDatepicker;


function expandTextarea(holder) {
    var targetid = ".textarea";
    var targetidHeight = [];

    targetid = getElementTargetByHolder(holder,targetid);
    //console.log("expandTextarea: targetid="+targetid);

    if( $(targetid).length == 0 ) {
        //console.log('no textarea => return');
        return;
    }

    var onchangeFunction = function(domElement) {
        //var originalReadonly = domElement.readOnly;
        //console.log("originalReadonly="+originalReadonly);
        //domElement.readOnly = true; //to get correct height make it readonly

        //expand if hidden (collapsed)
        if(0) {
            var originalHidden = false;
            var panelEl = $(domElement).closest('.panel-collapse');
            console.log("panelEl-Class=" + panelEl.attr('class') + "panelEl-ID=" + panelEl.attr('id'));
            //console.log(panelEl);
            if (panelEl) {
                if (panelEl.hasClass("in")) {
                    //opened
                    console.log("panelEl has Class in=[" + panelEl.attr('class') + "], panelEl-ID=[" + panelEl.attr('id') + "]");
                } else {
                    //hidden
                    console.log("show originalHidden=" + originalHidden);
                    originalHidden = true;
                    panelEl.collapse('show');
                }
            }
            console.log("originalHidden=" + originalHidden);
        }

        // if( domElement.id in targetidHeight && targetidHeight[domElement.id] ) {
        //     if( targetidHeight[domElement.id] > 40 ) {
        //         console.log("already set="+domElement.id+", h="+targetidHeight[domElement.id]);
        //         return null;
        //     }
        // }

        domElement.style.overflow = 'hidden';
        domElement.style.height = 0;
        var newH = domElement.scrollHeight + 10;
        //console.log("onchange Function: cur h="+domElement.style.height+", newH="+newH+", ID="+domElement.id);
        domElement.style.height = newH + 'px';
        //domElement.readOnly = originalReadonly; //to get correct height make it readonly

        targetidHeight[domElement.id] = newH;

        //close if panel was hidden
        if(0) {
            if (panelEl) {
                if (originalHidden) {
                    //console.log("hide originalHidden="+originalHidden);
                    //panelEl.collapse('hide');
                    //panelEl.removeClass('in');
                }
            }
        }
    }; //EOF onchangeFunction

    //for (var i = 0; i < elements.length; ++i) {
    //  var element = elements[i];
    $(targetid).each( function() {
        var element = $(this);

        //resize text area to fit the current text. It cause freeze html to pdf converter when downloading report.
        //exception to resize textarea
        var resize = true;
        var full = window.location.pathname;
        if( full.indexOf("/event-log") !== -1 ) {
            resize = false;
        }
        //console.log('resize='+resize);

        if( cycle != 'download' && resize ) {
            //console.log('resize textarea');

            //ver1
            //var height = $(element).prop('scrollHeight');
            //console.log('height='+height);
            //$(element).height(height);

            //ver2
            onchangeFunction(this);
        }

        //this does not work anymore (5 July 2017) => changed to on('input'
        //addEvent('keyup', element, function() {
        //    this.style.overflow = 'hidden';
        //    this.style.height = 0;
        //    var newH = this.scrollHeight + 10;
        //    console.log("event keyup: cur h="+this.style.height+", newH="+newH);
        //    this.style.height = newH + 'px';
        //}, false);

        $(element).on('input mouseenter',function(e){
            //e.target.value
            //console.log("on input");
            onchangeFunction(this);
        });

        // $(element).on('mouseenter',function(e){
        //     //e.target.value
        //     console.log("on mouseenter!!!!!!!!!!!!!!!!!!!!!");
        //     onchangeFunction(this);
        // });


        // $(element).on('blur change click dblclick error focus focusin focusout hover keydown keypress keyup load mousedown mouseenter mouseleave mousemove mouseout mouseover mouseup resize scroll select submit', function(){
        //     console.log("on everything !!!!!!!!!!!!!!!!!!!!!");
        //     onchangeFunction(this);
        // });

    });

}

//Internet Explorer (up to version 8) used an alternate attachEvent method.
// The following should be an attempt to write a cross-browser addEvent function.
//function addEvent(event, elem, func) {
//    if (elem.addEventListener) {  // W3C DOM
//        console.log('W3C DOM addEventListener');
//        elem.addEventListener(event, func, false);
//    } else if (elem.attachEvent) { // IE DOM
//        console.log('IE DOM attachEvent');
//        //elem.attachEvent("on"+event, func);
//        elem.attachEvent("on" + event, function() {return(func.call(elem, window.event));});
//    }
//    else { // No much to do
//        console.log('No much to do');
//        elem[event] = func;
//    }
//}

//Helpers
function capitaliseFirstLetter(string)
{
    return string.charAt(0).toUpperCase() + string.slice(1);
}

function printF(element,text) {
    if( element && $(element).length > 0 ) {
        //return null;

        //var str = "id=" + element.attr("id") + ", class=" + element.attr("class");
        var str = "id=" + $(element).attr("id") + ", class=" + $(element).attr("class")
        if (text) {
            str = text + " : " + str;
        }
        console.log(str);
    }
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



//confirm modal: modified from http://www.petefreitag.com/item/809.cfm
function generalConfirmAction() {

    //console.log("generalConfirmAction");

    //$('a[general-data-confirm], button[general-data-confirm]')
    $('a[general-data-confirm]').click(function(ev) {

        var href = $(this).attr('href');

        if( !$('#generalDataConfirmModal').length ) {
            var modalHtml =
                '<div id="generalDataConfirmModal" class="modal fade general-data-confirm-modal">' +
                    '<div class="modal-dialog">' +
                    '<div class="modal-content">' +
                    '<div class="modal-header text-center">' +
                    '<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>' +
                    '<h3 id="dataConfirmLabel">Confirmation</h3>' +
                    '</div>' +
                    '<div class="modal-body text-center">' +
                    '</div>' +
                    '<div class="modal-footer">' +
                    '<button class="btn btn-primary general-data-confirm-cancel" data-dismiss="modal" aria-hidden="true">Cancel</button>' +
                    '<a class="btn btn-primary general-data-confirm-ok" id="dataConfirmOK">OK</a>' +
                    '<button style="display: none;" class="btn btn-primary data-comment-ok">OK</button>' +
                    '</div>' +
                    '</div>' +
                    '</div>' +
                    '</div>';

            $('body').append(modalHtml);
        }

        $('#generalDataConfirmModal').find('.modal-body').html( $(this).attr('general-data-confirm') );

        var callbackfn = $(this).attr('general-data-callback'); //for example: "refreshpage"

        if( callbackfn ) {
            var onclickStr = callbackfn+'("'+href+'"'+',this'+')';
            $('#dataConfirmOK').attr('onclick',onclickStr);
        } else {
            $('#dataConfirmOK').attr('href', href);
        }

//        /////////////// add comment /////////////////////
//        if( $(this).hasClass("status-with-comment") ) {
//            //do it by listening .general-data-confirm-ok
//            //console.log('add comment!');
//            var commentHtml = '<br><br>Please provide a comment:' +
//                '<p><textarea id="'+$(this).attr('id')+'" name="addcomment" type="textarea" class="textarea form-control addcomment_text" maxlength="5000" required></textarea></p>';
//            $('#generalDataConfirmModal').find('.modal-body').append(commentHtml);
//            //replace href link <a> with button
//            $('.general-data-confirm-ok').hide();
//            $('.data-comment-ok').show();
//        } else {
//            //$('#dataConfirmOK').attr('href', href); //do it automatically
//        }
//        /////////////// EOF add comment /////////////////////

        ////////// assign correct confirmation text and button's text //////////
        var okText = $(this).attr('data-ok');
        var cancelText = $(this).attr('data-cancel');
        if( typeof okText === 'undefined' ){
            okText = 'OK';
        }
        if( typeof cancelText === 'undefined' ){
            cancelText = 'Cancel';
        }
        $('#generalDataConfirmModal').find('.general-data-confirm-cancel').text( cancelText );

        //console.log('okText='+okText);
        if( okText != 'hideOkButton' ) {
            $('#generalDataConfirmModal').find('.general-data-confirm-ok').text( okText );
            $('.general-data-confirm-ok').show();
        } else {
            $('.general-data-confirm-ok').hide();
        }
        ////////// EOF of assigning text //////////       

        $('#generalDataConfirmModal').modal({show:true});

        //post process function, for example click all buttons on transres review page to update specific fields
        var generalDataConfirmBtn = $(this);
        var postprocessfn = generalDataConfirmBtn.attr('general-post-process'); //general-post-process=transresUpdateProjectSpecificBtn

        //add listnere to ok button to "Please wait ..." and disable button on click
        $('.general-data-confirm-ok').on('click', function(event){
            //console.log("on modal js: dataConfirmOK clicked. postprocessfn="+postprocessfn);
            //alert("on modal js: dataConfirmOK clicked. postprocessfn="+postprocessfn);

            var footer = $(this).closest('.modal-footer');
            footer.html('Please wait ...');
            //alert('111');

            //post process function, for example click all buttons on transres review page to update specific fields
            if(1) {
                if( postprocessfn ) {

                    // if ($.isFunction(window[postprocessfn])) {
                    //     alert("Exists: postprocessfn="+postprocessfn);
                    // } else {
                    //     alert("Does not exist: postprocessfn="+postprocessfn);
                    // }

                    //var fn = window[postprocessfn];
                    //console.log("postprocessfn="+postprocessfn);
                    //fn(generalDataConfirmBtn);

                    if ($.isFunction(window[postprocessfn])) {
                        //execute it
                        console.log("function exists: postprocessfn="+postprocessfn);
                        //alert("Exists: postprocessfn="+postprocessfn);
                        var fn = window[postprocessfn];
                        fn(generalDataConfirmBtn);
                    } else {
                        //alert("Does not exist: postprocessfn="+postprocessfn);
                        console.log("function "+postprocessfn+" does not exist");
                    }
                }
            }
        });

        return false;
    }); //general-data-confirm click

}


function listenerFellAppRank(holder) {

    var rankHolder = $('.interview-rank');
    if( !rankHolder ) {
        return;
    }

    if( holder ) {
        rankHolder = holder.find('.interview-rank');
    }

    rankHolder.on("change", function(e) {
        //console.log("interview-rank on change");
        var holder = $(this).closest('.user-collection-holder');
        updateInterviewAppTotalRank(holder,"oleg_fellappbundle_");
    });
}
function listenerResAppRank(holder) {

    var rankHolder = $('.interview-rank');
    if( !rankHolder ) {
        return;
    }

    if( holder ) {
        rankHolder = holder.find('.interview-rank');
    }

    rankHolder.on("change", function(e) {
        //console.log("interview-rank on change");
        var holder = $(this).closest('.user-collection-holder');
        updateInterviewAppTotalRank(holder,"oleg_resappbundle_");
    });
}
function updateInterviewAppTotalRank(holder,bundlePrefix) {
    var totalRank = 0;

    var academicRank = getValueFromRankString(holder,'.interview-academicRank',bundlePrefix);
    var personalityRank = getValueFromRankString(holder,'.interview-personalityRank',bundlePrefix);
    var potentialRank = getValueFromRankString(holder,'.interview-potentialRank',bundlePrefix);

    if( academicRank ) {
        totalRank = totalRank + academicRank;
    }

    if( personalityRank ) {
        totalRank = totalRank + personalityRank;
    }

    if( potentialRank ) {
        totalRank = totalRank + potentialRank;
    }

    holder.find('.interview-totalRank').val(totalRank);

}
function getValueFromRankString( holder, identifierName, bundlePrefix) {
    
    
    String.prototype.xSplit = function(_regEx)
    {
       // Most browsers can do this properly, so let them -- they'll do it faster
       if ('a~b'.split(/(~)/).length === 3) { 
           return this.split(_regEx); 
       }

       if (!_regEx.global)
          { _regEx = new RegExp(_regEx.source, 'g' + (_regEx.ignoreCase ? 'i' : '')); }

       // IE (and any other browser that can't capture the delimiter)
       // will, unfortunately, have to be slowed down
       var m, str = '', arr = [];
       var i, len = this.length;
       for (i = 0; i < len; i++)
       {
          str += this.charAt(i);
          m = str.match(_regEx);
          if (m)
          {
             arr.push(str.replace(m[0], ''));
             arr.push(m[0]);
             str = '';
          }
       }

       if (str != '') arr.push(str);

       return arr;
    }
    
    var rankEl = holder.find(identifierName);
    if( !rankEl ) {
        return null;
    }

//    var rankData = rankEl.select2('data');
//    if( !rankData || !rankData.text ) {
//        return null;
//    }
    //var rankText = String(rankData.text)+""; 
    //var rankText = rankData.text + ""; 
    
    if( isIE() && isIE() <= 7 ) {
        //id=oleg_fellappbundle_interview_academicRank
        identifierName = identifierName.replace("-","_");
        identifierName = identifierName.replace(".","");

        //identifierName = "oleg_fellappbundle_" + identifierName.replace("-","_");
        identifierName = bundlePrefix + identifierName.replace("-","_");

        //console.log("identifierName="+identifierName);
        rankEl = document.getElementById(identifierName);
    }
    var rankText = getSelect2Text(rankEl);
    if( !rankText ) {
        //console.log("rankText is null => return");
        return null;
    } 
    //console.log("rankText="+rankText);

    //var rank = rankText.split(" ")[0];
    var rank = rankText;
    if( rankText.indexOf(" ") != -1 ) {
        var rankArr = rankText.xSplit(" ");
        //console.log("rankArr.length="+rankArr.length);
        if( rankArr.length > 1 ) {
            rank = rankArr[0];
        }
    }
    //console.log("rank="+rank+" => Number(rank)="+Number(rank));
    
    rank = Number(rank);
    //console.log("rank="+rank);
    
    return rank;
}

function getSelect2Text(element) {
    var res = null;
    if( !element ) {
        //console.log("element is null => return");
        return null;
    }
    
    if( isIE() && isIE() <= 7 ) {
        if( element.selectedIndex ) {
            res = element.options[element.selectedIndex].text+"";
        }
    } else {
        var elementData = element.select2('data');
        if( elementData && elementData.text ) {
            res = elementData.text;
        }
    }
    return res;
}


function userTeamTwigMacro(myteamurl,btnTargetId,replaceTargetId) {
    //console.log("myteamurl="+myteamurl);

    var btn = document.getElementById(btnTargetId);
    var lbtn = Ladda.create(btn);
    lbtn.start();

    $.ajax({
        url: myteamurl,
        timeout: _ajaxTimeout,
        type: "GET",
        //type: "POST",
        //data: {id: userid },
        //dataType: 'json',
        async: asyncflag
    }).done(function(response) {
        //console.log(response);
        //var template = response;
        $('#' + replaceTargetId).html(response); //Change the html of the div with the id = "your_div"
        $.bootstrapSortable(true);
    }).always(function() {
        lbtn.stop();
    }).error(function(jqXHR, textStatus, errorThrown) {
        console.log('Error : ' + errorThrown);
    });
}

function userWrapperAjax( userid, btnTargetId, replaceTargetId, cycle ) {
    //console.log("userid="+userid);

    var btn = document.getElementById(btnTargetId);
    var lbtn = Ladda.create(btn);
    lbtn.start();

    var url = Routing.generate('employees_user_wrapper_ajax');

    $.ajax({
        url: url,
        timeout: _ajaxTimeout,
        type: "GET",
        //type: "POST",
        data: {userid: userid, cycle: cycle },
        //dataType: 'json',
        async: asyncflag
    }).done(function(response) {
        //console.log(response);
        //var template = response;
        $('#'+replaceTargetId).html(response); //Change the html of the div with the id = "your_div"
        regularCombobox( $('#'+replaceTargetId) );
    }).always(function() {
        lbtn.stop();
    }).error(function(jqXHR, textStatus, errorThrown) {
        console.log('Error : ' + errorThrown);
    });
}

//options in php getFlashBag: pnotify, pnotify-error:
//$this->get('session')->getFlashBag()->add(
//    'pnotify',
//    "message here ..."
//);
function userPnotifyDisplay() {
    //console.log("user Pnotify Display");
    if( !$('#pnotify-notice') ) {
        //console.log("user Pnotify Display: does not exists");
        return;
    }
    var text = $('#pnotify-notice').val();
    //console.log("user Pnotify Display: text="+text);
    if( text ) {
        var type = 'info';
        var hide = false;
        if( $('#pnotify-notice').hasClass('pnotify-notice-success') ) {
            type = 'success';
        }
        if( $('#pnotify-notice').hasClass('pnotify-notice-error') ) {
            type = 'error';
            hide = false;
        }
        new PNotify({
            icon: false,
            //title: 'Regular Notice',
            text: text,
            type: type,
            hide: hide
        });
    }
}

function userPlayroomHeaderInit() {

    //$(".headroom").headroom({
    //    "offset": 205,
    //    "tolerance": 5,
    //    "classes": {
    //        "initial": "animated",
    //        "pinned": "slideDown",
    //        "unpinned": "slideUp"
    //    }
    //});

    //var headerElem = document.getElementById("user-headroom-header");
    //if( headerElem ) {
    //    var headroom = new Headroom(headerElem, {
    //        "offset": 10,
    //        "tolerance": 5,
    //        "classes": {
    //            "initial": "animated",
    //            //"pinned": "slideDown",
    //            //"unpinned": "slideUp"
    //            "unpinned": "slideDown",
    //            "pinned": "slideUp"
    //        }
    //    });
    //    headroom.init();
    //}

    //http://jsfiddle.net/wdDsk/100/
    //show hidden header by id='user-headroom-header' when scroll down after 10 pixels
    var headerEl = $('#user-headroom-header');
    if( headerEl ) {
        var ost = 10;
        $(window).scroll(function () {
            var cOst = $(this).scrollTop();
            //console.log("cOst="+cOst);

            if( cOst < ost ) {
                headerEl.addClass('headroom-hidden').removeClass('headroom-shown');
            } else {
                //show it only if title is not null
                if( headerEl.text() ) {
                    headerEl.addClass('headroom-shown').removeClass('headroom-hidden');
                }
            }

            //ost = cOst;
        });
    }
}

//**********************************************************************
// function waitfor - Wait until a condition is met
//
// Needed parameters:
//    test: function that returns a value
//    expectedValue: the value of the test function we are waiting for
//    msec: delay between the calls to test
//    callback: function to execute when the condition is met
// Parameters for debugging:
//    count: used to count the loops
//    source: a string to specify an ID, a message, etc
// The same function existed in slide-return-request-table.js, handsontable_scanorder.js
//**********************************************************************
function waitfor(test, expectedValue, msec, count, source, callback) {
    //console.log('waitfor, count='+count);

    //if( count > 200 ) {
    //    //console.log('waitfor, exit on count');
    //    return;
    //}

    // Check if condition met. If not, re-check later (msec).
    while( test() !== expectedValue ) {
        count++;
        setTimeout(function() {
            waitfor(test, expectedValue, msec, count, source, callback);
        }, msec);
        return;
    }
    // Condition finally met. callback() can be executed.
    if(source) {
        console.log(source + ': ' + test() + ', expected: ' + expectedValue + ', ' + count + ' loops.');
    }
    callback();
}


//click on "Add New" button
function constructNewUserModal(btnDom, sitename, otherUserParam, comboboxValue) {
    //1) get html form from add-new-user-modal-prototype
    //var modalHtml = $("#add-new-user-modal-prototype").text();
    var modalDiv = document.getElementById('add-new-user-modal-prototype');
    var modalHtml = modalDiv.innerHTML;

    //console.log("modalHtml="+modalHtml);
    //console.log($(btnDom));
    var holder = $(btnDom).closest('.row');
    //console.log(holder);

    //2) get fieldId and otherUserParam
    //2a) get otherUserParam
    if( otherUserParam === undefined || !otherUserParam ) {
        //console.log(holder);
        otherUserParam = holder.find('select.add-new-user-on-enter').data("otheruserparam");
        if (otherUserParam === undefined) {
            otherUserParam = holder.find('select.add-new-user-on-enter').data("otheruserparam");
        }
    }
    //console.log("otherUserParam="+otherUserParam);

    //2b) get field id (assume select box)
    var comboboxEl = holder.find('select.combobox');
    var fieldId = comboboxEl.attr('id');
    //console.log("fieldId="+fieldId);

    //3a) replace fieldId and otherUserParam
    modalHtml = modalHtml.replace("[[fieldId]]",fieldId);
    modalHtml = modalHtml.replace("[[otherUserParam]]",otherUserParam);
    modalHtml = modalHtml.replace("user-add-new-user","user-add-new-user-instance");
    //console.log("modalHtml="+modalHtml);

    //3b) replace lastName by comboboxValue
    if( comboboxValue !== undefined ) {
        modalHtml = modalHtml.replace("[[lastName]]",comboboxValue);
    } else {
        modalHtml = modalHtml.replace("[[lastName]]","");
    }

    //4) show modal
    $('body').append(modalHtml);


    getComboboxGeneric($('#user-add-new-user-instance'), 'administrativetitletype', _addmintitles, false);
    getComboboxCompositetree($('#user-add-new-user-instance'));
    if( $('#user-add-new-user-instance').length ) {
        regularCombobox($('#user-add-new-user-instance'));
    }

    $('#user-add-new-user-instance').modal(
        {
            show:true,
            keyboard: false,
            backdrop: 'static'
        }
    );

    $("#user-add-new-user-instance").on('hidden.bs.modal', function () {
        //console.log("hidden.bs.modal");
        $('#user-add-new-user-instance').find( '.modal' ).modal( 'hide' ).data( 'bs.modal', null );

        $('#user-add-new-user-instance').find( '.modal' ).remove();
        $('#user-add-new-user-instance').find( '.modal-backdrop' ).remove();
        $('#user-add-new-user-instance').remove();
        $('body').removeClass( "modal-open" );
    });


    //console.log("before setKeytypeByEmailListener");
    setKeytypeByEmailListener($('#user-add-new-user-instance'));

}

//btnDom - is the 'this' button attached to the field where a new user is to be created
//otherUserParam - ap-cp, hematopathology
function addNewUserOnFly( btnDom, sitename, otherUserParam ) {
    //console.log("Add New User on Fly");
    constructAddNewUserModalByAjax(btnDom,sitename,otherUserParam);
}
function constructAddNewUserModalByAjax(btnDom,sitename,otherUserParam,selectElement) {

    //Show temp modal "Please wait..."
    var tempModalHtml = '<div id="new-user-temp-modal" class="modal fade" role="dialog">'+
                        '<div class="modal-dialog">'+
                            '<div class="modal-content">'+
                                '<div class="modal-header">'+
                                    '<h4 class="modal-title">Loading ...</h4>'+
                                '</div>'+
                            '</div>'+
                        '</div>'+
                    '</div>';
    //console.log("Create temp modal tempModalHtml="+tempModalHtml);
    $('body').append(tempModalHtml);

    var url = Routing.generate('employees_new_simple_user');

    if( otherUserParam === undefined ) {
        var holder = $(btnDom).closest('.row');
        //console.log(holder);
        var otherUserParam = holder.find('select.add-new-user-on-enter').data("otheruserparam");
        if (otherUserParam === undefined) {
            otherUserParam = holder.find('select.add-new-user-on-enter').data("otheruserparam");
        }
    }
    //console.log("otherUserParam="+otherUserParam);
    
    var comboboxValue = null;
    if( selectElement !== undefined ) {
        comboboxValue = selectElement.value;
        //console.log("selectElement exists: comboboxValue="+comboboxValue);
    }
    //console.log("comboboxValue="+comboboxValue);
    
    $.ajax({
        url: url,
        timeout: _ajaxTimeout,
        type: "GET",
        //type: "POST",
        data: {comboboxValue: comboboxValue},
        //dataType: 'json',
        async: asyncflag
    }).done(function(response) {
        //console.log(response);
        //Remove temp modal addNewUserOnFly
        $('.modal').modal('hide').data('bs.modal', null );
        $('.modal').remove();
        $("#new-user-temp-modal").remove();
        getAddNewUserModalByForm(btnDom,sitename,otherUserParam,response)
    }).always(function() {
        //lbtn.stop();
    }).error(function(jqXHR, textStatus, errorThrown) {
        console.log('Error : ' + errorThrown);
    });
}
//employees_new_simple_user
function getAddNewUserModalByForm(btnDom,sitename,otherUserParam,newUserFormHtml) {
    //console.log("construct modal");

    //get field id (assume select box)
    var comboboxEl = $(btnDom).closest('.row').find('select.combobox');
    var fieldId = comboboxEl.attr('id');
    //console.log("fieldId="+fieldId);
    //console.log("sitename="+sitename);

    constructAddNewUserModalByForm(newUserFormHtml,fieldId,sitename,otherUserParam,'body');

    var newUserEl = $('#user-add-new-user');
    getComboboxGeneric(newUserEl,'administrativetitletype',_addmintitles,false);
    getComboboxCompositetree(newUserEl);

    if( newUserEl.length ) {
        regularCombobox(newUserEl);
    }

    newUserEl.modal(
        {
            show:true,
            keyboard: false,
            backdrop: 'static'
        }
    );

    // $('#user-add-new-user').on('shown.bs.modal', function () {
    //     $('#oleg_userdirectorybundle_user_primaryPublicUserId').focus();
    // });

    //$('#user-add-new-user')
    newUserEl.on('hidden.bs.modal', function () {
        //console.log("hidden.bs.modal");
        $( '.modal' ).modal( 'hide' ).data( 'bs.modal', null );

        $( '.modal' ).remove();
        $( '.modal-backdrop' ).remove();
        $( 'body' ).removeClass( "modal-open" );
    });

    return false;
}
function constructAddNewUserModalByForm(newUserFormHtml,fieldId,sitename,otherUserParam,appendHolder) {
    fieldId = "'"+fieldId+"'";
    sitename = "'"+sitename+"'";
    otherUserParam = "'"+otherUserParam+"'";
    var modalHtml =
        '<div id="user-add-new-user" class="modal fade">' +
        '<div class="modal-dialog">' +
        '<div class="modal-content">' +
        '<div class="modal-header text-center">' +
        '<button id="user-add-btn-dismiss" type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>' +
        '<h3 id="dataConfirmLabel">Add name and contact info for an unlisted person</h3>' +
        '</div>' +
        '<div class="modal-body text-center">' +
        newUserFormHtml +
        '<div id="add-user-danger-box" class="alert alert-danger" style="display: none;"></div>' +
        '</div>' +
        '<div class="modal-footer">' +
        '<button id="user-add-btn-cancel" class="btn btn-primary" data-dismiss="modal" aria-hidden="true">Cancel</button>' +
        '<a class="btn btn-primary add-user-btn-add" id="add-user-btn-add" onclick="addNewUserAction(this,'+fieldId+','+sitename+','+otherUserParam+')">Add</a>' +
        '</div>' +
        '</div>' +
        '</div>' +
        '</div>';

    if( appendHolder === undefined ) {
        appendHolder = 'body';
    }

    $(appendHolder).append(modalHtml);
}
function populateUserFromLdap(searchBtn,inputType) {
    //var btn =

    var modalHolder = $(searchBtn).closest(".modal");
    modalHolder.find('#add-user-danger-box').hide();
    modalHolder.find('#add-user-danger-box').html(null);

    var lbtn = Ladda.create( searchBtn );
    lbtn.start();

    var formholder = $(searchBtn).closest(".modal");
    var holder = $(searchBtn).closest(".input-group");
    var inputEl = holder.find("input");
    var searchvalue = inputEl.val();

    if( inputEl.hasClass('user-email') ) {
        if( validateEmail(searchvalue) == false ) {
            var errorMsg = "Please enter a valid user's email address";
            modalHolder.find('#add-user-danger-box').html(errorMsg);   //"Please enter a new user email address");
            modalHolder.find('#add-user-danger-box').show(errorMsg);
            lbtn.stop();
            return false;
        }
    }
    //var email = holder.find("#oleg_userdirectorybundle_user_infos_0_email").val();

    if( !searchvalue ) {
        lbtn.stop();
        return false;
    }

    var url = Routing.generate('employees_search_user_ldap_ajax');
    $.ajax({
        url: url,
        timeout: _ajaxTimeout,
        type: "GET",
        data: {
            searchvalue: searchvalue,
            type: inputType
        },
        dataType: 'json',
        async: asyncflag
    }).done(function(response) {
        //console.log(response);

        if( response ) {
            //var company = response.company;
            //if( company ) {
            //    formholder.find("#oleg_userdirectorybundle_user_administrativeTitles_0_institution").select2('val',company);
            //}

            //var displayName = response.displayName;

            var givenName = response.givenName;
            //console.log("givenName="+givenName);
            //if( givenName ) {
                formholder.find("#oleg_userdirectorybundle_user_infos_0_firstName").val(givenName);
            //}

            var lastName = response.lastName;
            //console.log("lastName="+lastName);
            //if( lastName ) {
                formholder.find("#oleg_userdirectorybundle_user_infos_0_lastName").val(lastName);
            //}

            if(inputType != "email") {
                var mail = response.mail;
                //if( mail ) {
                    formholder.find("#oleg_userdirectorybundle_user_infos_0_email").val(mail);
                //}
            }

            var telephoneNumber = response.telephoneNumber;
            //if( telephoneNumber ) {
                formholder.find("#oleg_userdirectorybundle_user_infos_0_preferredPhone").val(telephoneNumber);
            //}

            var title = response.title;
            //console.log("title="+title);
            //if( title ) {
                formholder.find("#oleg_userdirectorybundle_user_administrativeTitles_0_name").select2('val',title);
            //}

            if(inputType != "primaryPublicUserId") {
                var primaryPublicUserId = response.primaryPublicUserId;
                //if( primaryPublicUserId ) {
                    formholder.find("#oleg_userdirectorybundle_user_primaryPublicUserId").val(primaryPublicUserId);
                //}
            }

            setKeytypeByEmail(formholder,response.mail);

        } else {
            //console.log("no response");
        }

    }).always(function() {
        lbtn.stop();
    }).error(function(jqXHR, textStatus, errorThrown) {
        //console.log('Error : ' + errorThrown);
        lbtn.stop();
    });

    return false;
}
//TODO: get corresponding keytype (PrimaryPublicUserIdType) from email extension
var emailUsernametypeMap = [];
function setKeytypeByEmailListener(modalHtml) {
    //console.log("setKeytypeByEmailListener");

    //1) set map array "email extension" - "keytype id" (AppUserdirectoryBundle:UsernameType)
    if( emailUsernametypeMap.length == 0 ) {
        var url = Routing.generate('employees_get_map_email_usernametype');
        $.ajax({
            url: url,
            timeout: _ajaxTimeout,
            type: "GET",
            //type: "POST",
            async: asyncflag
        }).done(function (response) {
            //console.log(response);
            emailUsernametypeMap = response;
            //console.log("emailUsernametypeMap:");
            //console.log(emailUsernametypeMap);
        }).always(function () {
            //
        }).error(function (jqXHR, textStatus, errorThrown) {
            console.log('Error : ' + errorThrown);
        });
    }

    // //var holder = $('#user-add-new-user-instance');
    // var emailField = $('#oleg_userdirectorybundle_user_infos_0_email');
    // emailField.on("input", function(e) {
    //     console.log("1 email changed");
    //     $('#oleg_userdirectorybundle_user_keytype').select2('val',1);
    // });

    //2) set listener
    var emailField = modalHtml.find(".user-email");
    emailField.on("input", function(e) {
        //console.log("2 email changed");
        var email = $(this).val();
        setKeytypeByEmail(modalHtml,email);
    });
    // emailField.on("change", function(e) {
    //     console.log("3 email changed");
    //     var email = $(this).val();
    //     setKeytypeByEmail(modalHtml,email);
    // });
}
function setKeytypeByEmail(modalHtml,email) {
    //console.log("2 email changed");
    //var email = $(this).val();
    if( email && emailUsernametypeMap && emailUsernametypeMap.length > 0 ) {
        var sEmails = email.split("@");
        if( sEmails.length == 2 ) {
            //var use=sEmails[0];
            var domain = sEmails[1];
            var keytype = emailUsernametypeMap[domain];
            //console.log("domain=" + domain + "; keytype=" + keytype);
            modalHtml.find('#oleg_userdirectorybundle_user_keytype').select2('val', keytype);
        }
    }
}

function addNewUserAction( addUserBtn, fieldId, sitename, otherUserParam ) {

    var holder = $(addUserBtn).closest(".modal");

    holder.find('#add-user-danger-box').hide();
    holder.find('#add-user-danger-box').html(null);

    var btn = document.getElementById("add-user-btn-add");
    //var btn = btnEl.get(0);
    var lbtn = Ladda.create( btn );
    lbtn.start();
    holder.find("#user-add-btn-dismiss").hide();
    holder.find("#user-add-btn-cancel").hide();

    var transTime = 500;

    //console.log("add New UserAction: Add New User Ajax");

    var keytype = holder.find('#oleg_userdirectorybundle_user_keytype').select2('val');

    var cwid = holder.find("#oleg_userdirectorybundle_user_primaryPublicUserId").val();
    //console.log("cwid="+cwid);
    //var userid = $("#add-new-user-userid").val();
    //console.log("userid="+userid);
    var email = holder.find("#oleg_userdirectorybundle_user_infos_0_email").val();
    //console.log("email="+email);
    //var displayname = $("#add-new-user-displayname").val();
    //console.log("displayname="+displayname);
    var firstname = holder.find("#oleg_userdirectorybundle_user_infos_0_firstName").val();
    //console.log("firstname="+firstname);
    var lastname = holder.find("#oleg_userdirectorybundle_user_infos_0_lastName").val();
    //console.log("lastname="+lastname);
    var phone = holder.find("#oleg_userdirectorybundle_user_infos_0_preferredPhone").val();
    //console.log("phone="+phone);
    var administrativetitle = holder.find("#oleg_userdirectorybundle_user_administrativeTitles_0_name").select2('val');
    //console.log("administrativetitle="+administrativetitle);
    var institution = holder.find("#oleg_userdirectorybundle_user_administrativeTitles_0_institution").select2('val');
    //console.log("institution="+institution);

    var errorMsg = null;

    //1) validate email
    if( !email ) {
        errorMsg = "Please enter a new user's email address";
    } else {
        if( validateEmail(email) == false ) {
            errorMsg = "Please enter a valid user's email address";
        }
    }
    if( !firstname ) {
        errorMsg = "Please enter a new user's first name";
    }
    if( !lastname ) {
        errorMsg = "Please enter a new user's last name";
    }

    if( errorMsg ) {
        holder.find('#add-user-danger-box').html(errorMsg);   //"Please enter a new user email address");
        holder.find('#add-user-danger-box').show(transTime);

        lbtn.stop();
        holder.find("#user-add-btn-dismiss").show();
        holder.find("#user-add-btn-cancel").show();

        return false;
    }

    //console.log("add New UserAction: call ajax to check if user exists");

    //2) try to create a new user
    var url = Routing.generate(sitename+'_add_new_user_ajax');
    $.ajax({
        url: url,
        timeout: _ajaxTimeout,
        //type: "GET",
        type: "POST",
        data: {
            fieldId: fieldId,
            sitename: sitename,
            otherUserParam: otherUserParam,
            cwid: cwid,
            email: email,
            //displayname: displayname,
            firstname: firstname,
            lastname: lastname,
            phone: phone,
            administrativetitle: administrativetitle,
            institution: institution,
            keytype: keytype
        },
        dataType: 'json',
        async: asyncflag
    }).done(function(response) {
        //console.log(response);

        if( response.flag == "NOTOK" ) {
            //console.log('NOTOK');
            lbtn.stop();
            holder.find("#user-add-btn-dismiss").show();
            holder.find("#user-add-btn-cancel").show();

            holder.find('#add-user-danger-box').html(response.error);
            holder.find('#add-user-danger-box').show(transTime);
        } else {
            //console.log('OK');
            updateUserComboboxes(response,fieldId);
            //$("#user-add-btn-dismiss").click();
            //document.getElementById("user-add-btn-dismiss").click();
            holder.find("#user-add-btn-dismiss").click();
        }

    }).always(function() {
        lbtn.stop();
        holder.find("#user-add-btn-dismiss").show();
        holder.find("#user-add-btn-cancel").show();
    }).error(function(jqXHR, textStatus, errorThrown) {
        //console.log('jqXHR:');
        //console.log(jqXHR);
        console.log('Error: ' + errorThrown);
        //console.log('errorThrown: ' + errorThrown);
        lbtn.stop();
        holder.find("#user-add-btn-dismiss").show();
        holder.find("#user-add-btn-cancel").show();

        holder.find('#add-user-danger-box').html('Error : ' + errorThrown);
        holder.find('#add-user-danger-box').show(transTime);
    });

}

//simple validation in the form of: anystring@anystring.anystring
// function validateEmail(email)
// {
//     //var re = /\S+@\S+\.\S+/;
//     var re = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/;
//     return re.test(email);
// }
function validateEmail(inputText)
{
    //console.log('Validation inputText='+inputText);
    var mailformat = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/;
    if(inputText.match(mailformat))
    {
        //alert("Valid email address!");
        //document.form1.text1.focus();
        return true;
    }
    return false;
}

function updateUserComboboxes(response,fieldId) {
    //console.log("update user comboboxes; response:");
    //console.log(response);

    var userId = response.userId;
    var userName = response.userName;
    //console.log("userId="+userId+"; userName="+userName);

    $("select.add-new-user-on-enter").each(function(){

        if( $(this).find("option[value='" + userId + "']").length ) {
            //$("#state").val(userId).trigger("change");
        } else {

            //console.log("fieldId="+fieldId+"=?="+$(this).attr('id'));
            if( fieldId == $(this).attr('id') ) {
                //console.log("set this user fieldId="+fieldId);
                var newOption = new Option(userName, userId, true, true);
            } else {
                //console.log("just add this user fieldId="+fieldId);
                var newOption = new Option(userName, userId, false, false);
            }
            $(this).append(newOption).trigger('change');

        }

    });

}

//app_translationalresearchbundle_project_billingContact
function selectExistingUserComboboxes(clickedDomEl, userId, fieldId) {
    $('#'+fieldId).val(userId).trigger("change");
    var holder = $(clickedDomEl).closest(".modal");
    //document.getElementById("user-add-btn-dismiss").click();
    holder.find("#user-add-btn-dismiss").click();
}
