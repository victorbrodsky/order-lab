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
        //access value of changed radio group with $(this).val()
        var checkedValue = $(this).val();
        console.log("checkedValue="+checkedValue);

        var boxChecked = false;
        if( $(this).is(':checked') ) {
            boxChecked = true;
        }
        console.log("boxChecked="+boxChecked);

        var parent = $(this).closest('.partdiseasetype');
        var originradio = parent.find('.originradio');
        var primaryorganradio = parent.find('.primaryorganradio');
        //console.log("originradio id="+originradio.attr("id")+", class="+originradio.attr("class"));

        if( checkedValue == "Neoplastic" ) {

            if( boxChecked ) {
                originradio.collapse('show');
                //add listener for child radio, because it is visible now
                originradio.find(choice_selector_str).on('change', function(){
                    var checkedValueOrigin = $(this).val();
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
                    if( boxChecked && checkedValueOrigin == "Unspecified" ) {
                        console.log("uncheck all children boxes");
                        var parent = $(this).closest('.origin-checkboxes');
                        primaryorganradio.find(choice_selector_str).attr('checked',false);
                    }
                });
            } else {
                hideDiseaseTypeChildren($(this));
            }

        }

        if( boxChecked && (checkedValue == "None" || checkedValue == "Unspecified") ) {
            console.log("uncheck all boxes");
            var parent = $(this).closest('.partdiseasetype');
            parent.find(choice_selector_str).attr('checked',false);
        }


    });

}

function diseaseTypeRender() {

    function checkDiseaseType() {
        var radioElement = $(this);
        var radioElementValue = radioElement.val();

        var boxChecked = false;
        if( $(this).is(':checked') ) {
            boxChecked = true;
        }

//        if( radioElementValue == "Metastatic" ) {
//
//            if( boxChecked ) {
//                var primaryorganradio = parent.find('.primaryorganradio');
//                primaryorganradio.collapse('show');
//            } else {
//                var primaryorganradio = parent.find('.primaryorganradio');
//                primaryorganradio.collapse('hide');
//            }
//
//
//        }

        if( boxChecked && radioElementValue == "Neoplastic" ) {
            //console.log("checked id="+radioElement.attr("id"));

            var parent = radioElement.closest('.partdiseasetype');
            var originradio = parent.find('.originradio');

            originradio.collapse('show');

            //console.log("originradio id="+originradio.attr("id")+",class="+originradio.attr("id"));

            originradio.find(choice_selector_str).each(function() {

                var originElement = $(this);
                //console.log("originElement id="+originElement.attr("id")+", value="+originElement.val());

                if( boxChecked && originElement.val() == "Metastatic" ) {

                    var primaryorganradio = parent.find('.primaryorganradio');
                    primaryorganradio.collapse('show');
                }

                if( boxChecked && (radioElementValue == "Unspecified") ) {
                    console.log("uncheck all children boxes");
                    var parent = radioElement.closest('.origin-checkboxes');
                    parent.find(choice_selector_str).attr('checked',false);
                }

            });

        }

//        if( boxChecked && (radioElementValue == "None" || radioElementValue == "Unspecified") ) {
        if( boxChecked && radioElementValue == "None" ) {
            console.log("uncheck all boxes");
            var parent = radioElement.closest('.partdiseasetype');
            parent.find(choice_selector_str).attr('checked',false);
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