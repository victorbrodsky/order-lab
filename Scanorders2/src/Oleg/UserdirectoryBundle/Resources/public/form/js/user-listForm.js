/**
 * Created with JetBrains PhpStorm.
 * User: oli2002
 * Date: 4/9/14
 * Time: 4:58 PM
 * To change this template use File | Settings | File Templates.
 */

$(document).ready(function() {


    if( $('.select2-list-type').select2('val') == "default" ) {
        $(".select2-list-original").select2("readonly", true);
        $('.select2-list-original').select2('val',null);
    }

    $('.select2-list-type').on("change", function(e) {

        //console.log("type change listener, val="+$('.select2-list-type').select2('val'));
        if( $('.select2-list-type').select2('val') == "default" ) {
            //console.log("default");
            $(".select2-list-original").select2("readonly", true);
            $('.select2-list-original').select2('val',null);
        } else {
            $(".select2-list-original").select2("readonly", false);
        }

    });

});
