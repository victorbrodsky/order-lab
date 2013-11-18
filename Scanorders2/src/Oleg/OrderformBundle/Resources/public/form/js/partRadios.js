/**
 * Created with JetBrains PhpStorm.
 * User: oli2002
 * Date: 9/11/13
 * Time: 5:17 PM
 * To change this template use File | Settings | File Templates.
 * JS for diseaseType group
 */

function diseaseTypeListener() {

    //add listener for all visible radio for diseaseType
    $(".diseaseType").find('input:radio').on('change', function(){
        //access value of changed radio group with $(this).val()
        var checkedValue = $(this).val();
        //console.log("checkedValue="+checkedValue);

        var parent = $(this).parent().parent();
        var originradio = parent.find('.originradio');
        var primaryorganradio = parent.find('.primaryorganradio');
        //console.log("originradio id="+originradio.attr("id")+", class="+originradio.attr("class"));

        if( checkedValue == "Neoplastic" ) {

            originradio.collapse('show');

            //add listener for child radio, because it is visible now
            originradio.find('input:radio').on('change', function(){
                var checkedValueOrigin = $(this).val();
                //console.log("checkedValueOrigin="+checkedValueOrigin);
                if( checkedValueOrigin == "Metastatic" ) {
                    primaryorganradio.collapse('show');
                }
                if( checkedValueOrigin == "Primary" ) {
                    if( primaryorganradio.is(':visible') ) {
                        primaryorganradio.collapse('hide');
                    }
                }
            });

        }
        if( checkedValue == "Non-Neoplastic" || checkedValue == "None" ) {
            //originradio.collapse('hide');
            if( originradio.is(':visible') ) {
                originradio.collapse('hide');
            }
        }


    });

}

function diseaseTypeRender() {

    function checkDiseaseType() {
        var radioElement = $(this);
        var radioElementValue = radioElement.val();

        if( radioElement.is(':checked') && radioElementValue == "Neoplastic" ) {
            //console.log("checked id="+radioElement.attr("id"));

            var parent = radioElement.parent().parent();
            var originradio = parent.find('.originradio');

            originradio.collapse('show');

            //console.log("originradio id="+originradio.attr("id")+",class="+originradio.attr("id"));

            originradio.find('input:radio').each(function() {

                var originElement = $(this);
                //console.log("originElement id="+originElement.attr("id"));

                if( originElement.is(':checked') && originElement.val() == "Metastatic" ) {

                    var primaryorganradio = parent.find('.primaryorganradio');
                    primaryorganradio.collapse('show');
                }

            });

        }

    }

    $(".diseaseType").find('input:radio').each(checkDiseaseType);
}

