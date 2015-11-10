/**
 * Created with JetBrains PhpStorm.
 * User: oli2002
 * Date: 2/10/14
 * Time: 10:42 AM
 * To change this template use File | Settings | File Templates.
 */

//TODO: rewrite: extend it from userdirectory idleTimeout

$(document).ready(function() {

    // Prevent Dropzone from auto discovering this element
    if( typeof Dropzone !== 'undefined' ) {
        Dropzone.autoDiscover = false;
    }

    //overwrite
    idleTimeoutClass.prototype.onTimeout = function() {
        //console.log("onTimeout: scan");
        keepWorking();
        tryToSubmitForm();
    }

    //overwrite
    idleTimeoutClass.prototype.onAbort = function() {
        //console.log("onAbort: scan");
        tryToSubmitForm();
        idlelogout();
        var par = 'par!!!';
    }

//    idleTimeoutClass.prototype.testfunc = function() {
//        console.log("testfunc: scan test !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!");
//        //alert("testfunc: scan test !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!");
//    }

    var idleTimeout = new idleTimeoutClass();

    idleTimeout.init();
    //idleTimeout.setMaxIdletime();
    idleTimeout.checkIdleTimeout();

});


function tryToSubmitForm() {
    $('#save_order_onidletimeout_btn').show();
    console.log("try To Submit Form: on timeout. len="+$('#save_order_onidletimeout_btn').length);

    if( $('#save_order_onidletimeout_btn').length > 0 &&
        ( cycle == "new" || cycle == "edit" || cycle == "amend" ) &&
        checkIfOrderWasModified()
        ) {
        console.log("try To Submit Form: save!!!!!!!!!!!");
        //save if all fields are not empty; don't validate
        $('#save_order_onidletimeout_btn').trigger('click');
    } else {
        idlelogout();
    }
}

//check if the order is empty:
function checkIfOrderWasModified() {

    var modified = false;

    //if at least one keyfield is not empty, then the form was modified
    $('.checkbtn').each(function() {
        if( modified )
            return true;
        var btnObj = new btnObject( $(this) );
        if( btnObj.key != "" ) {
            //console.log("at least one keyfield is not empty");
            modified = true;
            return true;
        }
    });

    if( modified ) return true;

    //if at least one button is checked (was pressed), then form was modified
    var btnsRemove = $('.removebtn');
    if( btnsRemove.length > 0 ) {
        //console.log("at least one button is checked (was pressed)");
        modified = true;
        return true;
    }

    if( modified ) return true;

    //if at least one input field (input,textarea,select) is not empty, then form was modified (this is slide input fields check)
    $(":input").each(function(){

        if( modified )
            return true;

        var id = $(this).attr('id');
        if( !id || typeof id === "undefined" || id.indexOf("_slide_") === -1 )
            return true;

        //ignore slide type (preselected)
        if( $(this).hasClass('combobox') )
            return true;

        //ignore stain (preselected)
        if( $(this).hasClass('ajax-combobox-stain') )
            return true;

        //ignore magnification (preselected)
        //if( $(this).hasClass('horizontal_type') )
        if( $(this).is(':radio') )
            return true;

        //ignore scanregion (preselected)
        if( $(this).hasClass('ajax-combobox-scanregion') )
            return true;

        if( !$(this).is('[readonly]') && $(this).val() != "" ) {    //&& !$(this).hasClass('ajax-combobox-staintype')
            //console.log($(this));
            //console.log("at least one input field (input,textarea,select) is not empty");
            modified = true;
            return true;
        }

    });

    if( modified ) return true;

    //console.log("not modified");
    return false;
}
