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
 * Created with JetBrains PhpStorm.
 * User: oli2002
 * Date: 9/11/13
 * Time: 5:17 PM
 * To change this template use File | Settings | File Templates.
 * JS for diseaseType group
 */

var choice_selector_str = 'input:radio';

function diseaseTypeListener() {

    //add listener for all visible radio for diseaseType
    $(".diseaseType").find(choice_selector_str).on('change', function(){
        //access value of changed radio group with $(this).val()
        var checkedValue = $(this).val();
        //console.log("checkedValue="+checkedValue);

        var parent = $(this).closest('.partdiseasetype');
        var originradio = parent.find('.originradio');
        var primaryorganradio = parent.find('.primaryorganradio');
        //console.log("originradio id="+originradio.attr("id")+", class="+originradio.attr("class"));

        if( checkedValue == "Neoplastic" ) {

            originradio.collapse('show');

            //add listener for child radio, because it is visible now
            originradio.find(choice_selector_str).on('change', function(){
                var checkedValueOrigin = $(this).val();
                //console.log("checkedValueOrigin="+checkedValueOrigin);
                if( checkedValueOrigin == "Metastatic" ) {
                    primaryorganradio.collapse('show');
                } else {
                    if( primaryorganradio.is(':visible') ) {
                        primaryorganradio.collapse('hide');
                        clearPrimaryOrgan($(this));
                    }
                }
            });

        } else {

            hideDiseaseTypeChildren($(this));

        }


    });

}

function diseaseTypeRender() {

    function checkDiseaseType() {
        var radioElement = $(this);
        var radioElementValue = radioElement.val();

        if( radioElement.is(':checked') && radioElementValue == "Neoplastic" ) {
            //console.log("checked id="+radioElement.attr("id"));

            var parent = radioElement.closest('.partdiseasetype');
            var originradio = parent.find('.originradio');

            originradio.collapse('show');

            //console.log("originradio id="+originradio.attr("id")+",class="+originradio.attr("id"));

            originradio.find(choice_selector_str).each(function() {

                var originElement = $(this);
                //console.log("originElement id="+originElement.attr("id")+", value="+originElement.val());

                if( originElement.is(':checked') && originElement.val() == "Metastatic" ) {

                    var primaryorganradio = parent.find('.primaryorganradio');
                    primaryorganradio.collapse('show');
                }

            });

        }

    }

    $(".diseaseType").find(choice_selector_str).each(checkDiseaseType);
}

//render diseaseType by AJAX from checkForm
function diseaseTypeRenderCheckForm( element, origin, primaryorgan ) {

    if( !element.attr("class") || element.attr("class").indexOf('diseaseType') == -1 ) {
        return;
    }

    function checkDiseaseType() {
        var radioElement = $(this);
        var radioElementValue = radioElement.val();

        if( radioElement.is(':checked') && radioElementValue == "Neoplastic" ) {
            //console.log("checked id="+radioElement.attr("id"));

            var parent = radioElement.closest('.partdiseasetype');
            var originradio = parent.find('.originradio');

            originradio.collapse('show');

            //console.log("originradio id="+originradio.attr("id")+",class="+originradio.attr("id"));

            originradio.find(choice_selector_str).each(function() {

                var originElement = $(this);
                var originElementValue = originElement.val();
                //console.log("originElement id="+originElement.attr("id")+", value="+originElementValue);

                if( origin == "Metastatic" && originElementValue == "Metastatic" ) {
                    //console.log("Check "+origin);
                    originElement.prop('checked',true);

                    var primaryorganradio = parent.find('.primaryorganradio');
                    primaryorganradio.collapse('show');

                    //set ajax-combobox-organ
                    var primaryOrgan = primaryorganradio.find(".ajax-combobox-organ");
                    primaryOrgan.select2('data', {id: primaryorgan, text: primaryorgan});
                } else {
                    originElement.attr("disabled", true);
                }

            });

        }

    }

    if( element ) {
        element.find(choice_selector_str).each(checkDiseaseType);
    } else {
        $(".diseaseType").find(choice_selector_str).each(checkDiseaseType);
    }
}

//hide children of DiseaseType
function hideDiseaseTypeChildren( element ) {

    //console.log("hide disease children, id="+element.attr("id")+",class="+element.attr("class"));

    //if( !element.attr("class") || element.attr("class").indexOf('diseaseType') == -1 ) {
    //    return;
    //}

    //console.log("hide Disease Type Children");
    var originradio = element.closest('.partdiseasetype').find(".originradio");
    if( originradio.is(':visible') ) {
        originradio.collapse('hide');
        clearOrigin(element);
    }

    var primaryorganradio = element.closest('.partdiseasetype').find(".primaryorganradio");
    //console.log("primaryorganradio id="+primaryorganradio.attr("id")+",primaryorganradio="+primaryorganradio.attr("class"));
    if( primaryorganradio.is(':visible') ) {
        primaryorganradio.collapse('hide');
        clearPrimaryOrgan(element);
    }

    //remove disable attr
    //console.log("disable and uncheck all in 'Origin'");
    originradio.find("input").removeAttr("disabled");
    originradio.find("input").prop('checked',false);

}

function clearOrigin( elem ) {
    //console.log("clear orig, id="+elem.attr("id")+",class="+elem.attr("class"));
    elem.closest('.partdiseasetype').find('.originradio').find('.radio_inline').each(function() {
        //console.log("clear radio_inline, id="+$(this).attr("id")+",class="+$(this).attr("id"));
        $(this).find("input").prop('checked',false);
    });
}

function clearPrimaryOrgan( elem ) {
    //console.log("clear orig, id="+elem.attr("id")+",class="+elem.attr("class"));
    elem.closest('.partdiseasetype').find('.ajax-combobox-organ').select2('data', null);
}