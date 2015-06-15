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
    //access value of changed radio group with $(this).val()
    //var checkedValue = $(this).val();
    var label = $("label[for='"+checkbox.attr("id")+"']");
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
                //var checkedValueOrigin = checkbox.val();
                var label = $("label[for='"+this.id+"']");
                var checkedValueOrigin = label.text();
                var boxChecked = false;
                if( checkbox.is(':checked') ) {
                    boxChecked = true;
                }
                //console.log("checkedValueOrigin="+checkedValueOrigin);
                if( checkedValueOrigin == "Metastatic" ) {
                    if( boxChecked ) {
                        primaryorganradio.collapse('show');
                    } else {
                        if( primaryorganradio.is(':visible') ) {
                            primaryorganradio.collapse('hide');
                            clearPrimaryOrgan(checkbox);
                        }
                    }
                }
                if( checkedValueOrigin == "Unspecified" ) {
                    //console.log("uncheck all children boxes");
                    var parent = checkbox.closest('.origin-checkboxes');
                    parent.find(choice_selector_str).not(this).each(function(){
                        if( boxChecked ) {
                            checkbox.attr('checked',false);
                            checkbox.attr('disabled',true);
                            if( primaryorganradio.is(':visible') ) {
                                primaryorganradio.collapse('hide');
                                clearPrimaryOrgan(checkbox);
                            }
                        } else {
                            checkbox.attr('disabled',false);
                        }
                    });
                }
            });
        } else {
            hideDiseaseTypeChildren(checkbox);
        }

    }

    if( checkedValue == "None" || checkedValue == "Unspecified" ) {
        //console.log("uncheck all boxes");
        var parent = checkbox.closest('.partdiseasetype');
        parent.find(choice_selector_str).not(this).each(function(){
            if( boxChecked ) {
                checkbox.attr('checked',false);
                checkbox.attr('disabled',true);
                hideDiseaseTypeChildren(checkbox);
            } else {
                checkbox.attr('disabled',false);
            }
        });

    }
}

//render checkboxes and its children when page is rendered from server
function diseaseTypeRender() {

    function checkDiseaseType() {
        diseaseTypeSingleRender($(this))
    }

    $(".diseaseType").find(choice_selector_str).each(checkDiseaseType);
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

            var originElement = $(this);
            var label = $("label[for='"+this.id+"']");
            var elementValue = label.text();
            //console.log("origin elementValue="+elementValue);

            if( boxChecked && elementValue == "Metastatic" ) {
                var parent = checkbox.closest('.partdiseasetype');
                var primaryorganradio = parent.find('.primaryorganradio');
                primaryorganradio.collapse('show');
            }

            if( boxChecked && (elementValue == "Unspecified") ) {
                //console.log("uncheck all children boxes");
                var parent = checkbox.closest('.origin-checkboxes');
                parent.find(choice_selector_str).attr('checked',false);
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
                var indexMetastatic = -1;
                var indexPrimary = -1;
                var indexUnspecified = -1;
                for( var ii = 0; ii < diseaseorigins.length; ii++ ) {
                    //console.log("data check :" + diseaseorigins[ii]['name'] + ", originElementValue=" + originElementValue );
                    if( diseaseorigins[ii]['name'] == originElementValue ) {
                        index = ii;
                        break;
                    }
//                    if( diseaseorigins[ii]['name'] == "Metastatic" ) {
//                        indexMetastatic = ii;
//                        break;
//                    }
//                    if( diseaseorigins[ii]['name'] == "Primary" ) {
//                        indexPrimary = ii;
//                        break;
//                    }
//                    if( diseaseorigins[ii]['name'] == "Unspecified" ) {
//                        indexUnspecified = ii;
//                        break;
//                    }
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
//                if( indexPrimary > -1 && originElementValue == "Primary" ) {
//                    originElement.prop('checked',true);
//                }
//                if( indexUnspecified > -1 && originElementValue == "Unspecified" ) {
//                    originElement.prop('checked',true);
//                }

                //if( origin == "Metastatic" && originElementValue == "Metastatic" ) {
//                if( index > -1 && originElementValue == "Metastatic" ) {
//                    //console.log("Check originElementValue="+originElementValue);
//                    originElement.prop('checked',true);
//
//                    var primaryorganradio = parent.find('.primaryorganradio');
//                    primaryorganradio.collapse('show');
//
//                    //set ajax-combobox-organ
//                    var primaryOrgan = primaryorganradio.find(".ajax-combobox-organ");
//                    primaryOrgan.select2('data', {id: primaryorgan, text: primaryorgan});
//                }
                //else {
                    //originElement.attr("disabled", true);
                //}

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