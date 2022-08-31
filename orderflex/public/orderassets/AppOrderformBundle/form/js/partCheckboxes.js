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

var choice_selector_str = 'input:checkbox';

function diseaseTypeListener() {

    //add listener for all visible radio for diseaseType
    $(".diseaseType").find(choice_selector_str).on('change', function(){
        diseaseTypeProcessing($(this));
    });

}
function diseaseTypeProcessing(checkbox) {
    //console.log("this.id="+this.id+", checkbox.attr(id)="+checkbox.attr("id"));
    var labelSelector = "label[for='"+checkbox.attr('id')+"']";
    //console.log('labelSelector='+labelSelector);
    var label = $(labelSelector);
    var checkedValue = label.text();
    //console.log("checkedValue="+checkedValue);

    var boxChecked = false;
    if( checkbox.is(':checked') ) {
        boxChecked = true;
    }
    //console.log("checkedValue="+checkedValue+", boxChecked="+boxChecked);

    var parent = checkbox.closest('.partdiseasetype');
    var originradio = parent.find('.originradio');
    var primaryorganradio = parent.find('.primaryorganradio');
    //console.log("originradio id="+originradio.attr("id")+", class="+originradio.attr("class"));

    if( checkedValue == "Neoplastic" ) {

        if( boxChecked ) {

            originradio.collapse('show');

            //add listener for child radio, because it is visible now
            originradio.find(choice_selector_str).on('change', function(){
                var label = $("label[for='"+this.id+"']");
                var checkedValueOrigin = label.text();
                var boxChecked = false;
                if( $(this).is(':checked') ) {
                    boxChecked = true;
                }
                //console.log("checkedValueOrigin="+checkedValueOrigin);
                if( checkedValueOrigin == "Metastatic" ) {
                    if( boxChecked ) {
                        primaryorganradio.collapse('show');
                    } else {
                        if( primaryorganradio.is(':visible') ) {
                            primaryorganradio.collapse('hide');
                            clearPrimaryOrgan($(this));
                        }
                    }
                }

                //unchecked "Unspecified" if Primary or Metastatic checked
                if( checkedValueOrigin == "Primary" || checkedValueOrigin == "Metastatic" ) {
                    if( boxChecked ) {
                        var parent = $(this).closest('.origin-checkboxes');
                        parent.find(choice_selector_str).not(this).each(function(){
                            var labelSelector = "label[for='"+$(this).attr('id')+"']";
                            var label = $(labelSelector);
                            var labelText = label.text();
                            if( labelText == "Unspecified" ) {
                                $(this).attr('checked',false);
                            }
                        });
                    }
                }

                if( checkedValueOrigin == "Unspecified" ) {
                    //console.log("uncheck all children boxes");
                    var parent = $(this).closest('.origin-checkboxes');
                    parent.find(choice_selector_str).not(this).each(function(){
                        if( boxChecked ) {
                            $(this).attr('checked',false);
                            //$(this).attr('disabled',true);
                            if( primaryorganradio.is(':visible') ) {
                                primaryorganradio.collapse('hide');
                                clearPrimaryOrgan($(this));
                            }
                        } else {
                            //$(this).attr('disabled',false);
                        }
                    });
                }
            }); //on change
        } else {
            hideDiseaseTypeChildren(checkbox);
        }

    }

    //unchecked "None" and "Unspecified" if Neoplastic or Non-Neoplastic checked
    if( checkedValue == "Neoplastic" || checkedValue == "Non-Neoplastic" ) {
        if( boxChecked ) {
            var parent = checkbox.closest('.diseaseType');
            parent.find(choice_selector_str).not(checkbox).each(function(){
                var labelSelector = "label[for='"+$(this).attr('id')+"']";
                var label = $(labelSelector);
                var labelText = label.text();
                if( labelText == "None" || labelText == "Unspecified" ) {
                    $(this).attr('checked',false);
                }
            });
        }
    }

    if( checkedValue == "None" || checkedValue == "Unspecified" ) {
        //console.log("uncheck all boxes");
        var parent = checkbox.closest('.diseaseType');
        parent.find(choice_selector_str).not(checkbox).each(function(){
            //printF($(this),'uncheck:');
            if( boxChecked ) {
                $(this).attr('checked',false);
                //$(this).attr('disabled',true);
                hideDiseaseTypeChildren($(this));
            } else {
                if( cycle != "show" ) {
                    //$(this).attr('disabled',false);
                }
            }
        });

    }
}

//render checkboxes and its children when page is rendered from server
function diseaseTypeRender() {

    function checkDiseaseType() {

        diseaseTypeProcessing($(this));

        diseaseTypeSingleRender($(this));

        //console.log('cycle='+cycle);
        if( cycle == "show" ) {
            $(this).attr("disabled", true);
        }

    }

    $(".diseaseType").find(choice_selector_str).each(checkDiseaseType);

    //checkbox has collapse element: fix bug: https://github.com/eternicode/bootstrap-datepicker/issues/978
    $('.originradio,.primaryorganradio').on('hide.bs.collapse', function (event) {
        event.stopPropagation();
    });
}
function diseaseTypeSingleRender(checkbox) {

    var label = $("label[for='"+checkbox.attr("id")+"']");
    var elementValue = label.text();

    var boxChecked = false;
    if( checkbox.is(':checked') ) {
        boxChecked = true;
    }
    //console.log("elementValue="+elementValue+", boxChecked="+boxChecked);

    if( boxChecked && elementValue == "Neoplastic" ) {
        //console.log("checked id="+checkbox.attr("id"));

        var parent = checkbox.closest('.partdiseasetype');
        var originradio = parent.find('.originradio');

        originradio.collapse('show');

        //console.log("originradio id="+originradio.attr("id")+",class="+originradio.attr("id"));

        originradio.find(choice_selector_str).each(function() {

            var label = $("label[for='"+this.id+"']");
            var elementValue = label.text();
            //console.log("origin elementValue="+elementValue);

            if( boxChecked && elementValue == "Metastatic" ) {
                var parent = checkbox.closest('.partdiseasetype');
                var primaryorganradio = parent.find('.primaryorganradio');
                primaryorganradio.collapse('show');
            }

        });

    }

}

//render diseaseType by AJAX from checkForm
function diseaseTypeRenderCheckForm( element, diseasetypes, diseaseorigins, primaryorgan ) {

    if( !element.attr("class") || element.attr("class").indexOf('diseaseType') == -1 ) {
        return;
    }

    function checkDiseaseType() {
        var radioElement = $(this);
        //var radioElementValue = radioElement.val();

        var label = $("label[for='"+this.id+"']");
        var radioElementValue = label.text();

        //console.log("checkDiseaseType: radioElementValue="+radioElementValue);

        if( radioElement.is(':checked') && radioElementValue == "Neoplastic" ) {
            //console.log("checked id="+radioElement.attr("id"));

            var parent = radioElement.closest('.partdiseasetype');
            var originradio = parent.find('.originradio');

            originradio.collapse('show');

            //console.log("originradio id="+originradio.attr("id")+",class="+originradio.attr("id"));

            originradio.find(choice_selector_str).each(function() {

                var originElement = $(this);
                //var originElementValue = originElement.val();
                var label = $("label[for='"+this.id+"']");
                var originElementValue = label.text();
                //console.log("originElement id="+originElement.attr("id")+", value="+originElementValue);

                //check if current checkbox value is in array of data
                var index = -1;
                for( var ii = 0; ii < diseaseorigins.length; ii++ ) {
                    //console.log("data check :" + diseaseorigins[ii]['name'] + ", originElementValue=" + originElementValue );
                    if( diseaseorigins[ii]['name'] == originElementValue ) {
                        index = ii;
                        break;
                    }
                }

                if( index > -1 ) {

                    originElement.prop('checked',true);

                    if( originElementValue == "Metastatic" ) {
                        //console.log("Check originElementValue="+originElementValue);
                        //originElement.prop('checked',true);

                        var primaryorganradio = parent.find('.primaryorganradio');
                        primaryorganradio.collapse('show');

                        //set ajax-combobox-organ
                        var primaryOrgan = primaryorganradio.find(".ajax-combobox-organ");
                        primaryOrgan.select2('data', {id: primaryorgan, text: primaryorgan});
                    }

                }

                originElement.attr("disabled", true);

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