/**
 * Created by oli2002 on 9/3/14.
 */


var _cycleShow = false;
var _sitename = "";
var asyncflag = true;
var combobox_width = '100%'; //'element'

var urlBase = $("#baseurl").val();
var cycle = $("#formcycle").val();
var user_name = $("#user_name").val();
var user_id = $("#user_id").val();

if( !user_id ) {
    user_id = 'undefined';
}

function setCicleShow() {
    //console.log("setCicleShow: cycle="+cycle);
    //console.log("setCicleShow: cycle.indexOf="+cycle.indexOf("show"));
    if( cycle && cycle.indexOf("show") != -1 ) {
        _cycleShow = true;
        //console.log("setCicleShow: true");
    } else {
        //console.log("setCicleShow: false");
    }
}

function isIE() {
  var myNav = navigator.userAgent.toLowerCase();
  return (myNav.indexOf('msie') != -1) ? parseInt(myNav.split('msie')[1]) : false;
}

function checkBrowserComptability() {
    console.log('0 IE='+isIE());
    if( isIE() && isIE() <= 7 ) {
        console.log('1 IE='+isIE());
        // is IE version equal or less than 7
        var msg = "Warning! You are using an old version of browser Internet Explorer 7 or lower. \n\
                    Please upgrade the browser or use the modern browsers such as \n\
                    Firefox or Google Chrome to have a full features of this system.";
        $('.browser-notice').html(msg);
        $('.browser-notice').show();           
    } 
}

function initTooltips() {

    //tooltip always
    $(".element-with-tooltip-always").tooltip();
    attachTooltipToSelectCombobox('.element-with-select2-tooltip-always',null);

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
    
    if( isIE() && isIE() <= 7 ) {
        // is IE version equal or less than 7
        checkBrowserComptability();
//        var msg = "Warning! You are using an old version of browser Internet Explorer 7 or lower. \n\
//                    Please upgrade the browser or use the modern browsers such as \n\
//                    Firefox or Google Chrome to have a full features of this system.";
//        $('.browser-notice').html(msg);
//        $('.browser-notice').show();
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

function getElementTargetByHolder(holder,target) {
    if( holder && typeof holder !== 'undefined' && holder.length > 0 ) {
        target = holder.find(target);
    }
    return target;
}

//Generic ajax combobox
function getComboboxGeneric(holder,name,globalDataArray,multipleFlag,urlprefix,sitename,force) {

    //console.log('get Combobox Generic: name='+name);

    var targetid = ".ajax-combobox-"+name;
    targetid = getElementTargetByHolder(holder,targetid);

    if( typeof force === 'undefined' ) {
        force = false;
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
        data = [];  //new Array();
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

    //filter disbaled options from data
    data = filterDisabledOptions(data,target);

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
            orientation: "auto top",
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

    //http://www.jacklmoore.com/autosize/ v.2
    //for downlaod it append lots of white space on the bottom
//    if( cycle != 'download' ) {
//        autosize(document.querySelectorAll('textarea'));
////        $('textarea').each(function(){
////            //$(this).autosize();  //v1
////            autosize($(this));  //v2
////        });
////        $('textarea').autosize();
//    }

    var elements = $('.textarea');
    for (var i = 0; i < elements.length; ++i) {
        var element = elements[i];

        //resize text area to fit the current text. It cause freeze html to pdf converter when downloading report.
        //exception to resize textarea
        var resize = true;
        var full = window.location.pathname;
        if( full.indexOf("/event-log") !== -1 ) {
            resize = false;
        }
        if( cycle != 'download' && resize ) {
            //console.log('resize textarea');
            $(element).height($(element).prop('scrollHeight'));
        }

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



//confirm modal: modified from http://www.petefreitag.com/item/809.cfm
function generalConfirmAction() {

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


        $('#generalDataConfirmModal').find('.modal-body').text( $(this).attr('general-data-confirm') );

        var callbackfn = $(this).attr('general-data-callback');

        if( callbackfn ) {
            var onclickStr = callbackfn+'("'+href+'")';
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

        return false;
    });

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
        updateFellAppTotalRank(holder);
    });
}
function updateFellAppTotalRank(holder) {
    var totalRank = 0;

    var academicRank = getValueFromRankString(holder,'.interview-academicRank');
    var personalityRank = getValueFromRankString(holder,'.interview-personalityRank');
    var potentialRank = getValueFromRankString(holder,'.interview-potentialRank');

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
function getValueFromRankString(holder,identifierName) {

    var rankEl = holder.find(identifierName);
    if( !rankEl ) {
        return null;
    }

    var rankData = rankEl.select2('data');
    if( !rankData || !rankData.text ) {
        return null;
    }

    var rankText = rankData.text;
    if( rankText.textContent ) {
      rankText = rankText.textContent;
    } else {
      rankText = rankText.innerText;
    }

    var rank = rankText.split(" ")[0];
    //console.log("rank="+rank+" => Number(rank)="+Number(rank));
    return Number(rank);
}