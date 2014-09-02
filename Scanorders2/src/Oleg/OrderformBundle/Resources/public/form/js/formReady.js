/**
 * Created with JetBrains PhpStorm.
 * User: oli2002
 * Date: 2/27/14
 * Time: 12:55 PM
 * To change this template use File | Settings | File Templates.
 */

$(document).ready(function() {

    //idle timeout
    idleTimeout();

    setNavBar();

    initAdd();

    expandTextarea();

    fieldInputMask();

    setResearchEducational();   //init research and educational

    customCombobox();

    setResearch();  //set research listener

    setEducational(); //set educational listener

    //add diseaseType radio listener for new form
    diseaseTypeListener();
    //render diseaseType radio result for show form
    diseaseTypeRender();

    //take care of buttons for single form
    $("#orderinfo").hide();
    $("#optional_button").hide();

    //priority option
    priorityOption();

    //purpose option
    purposeOption();

    //multy form toggle button
    $('.form_body_toggle_btn').on('click', function(e) {
        var className = $(this).attr("class");
        var id = this.id;
        if( $(this).hasClass('glyphicon-folder-open') ) {
            $("#"+id).removeClass('glyphicon-folder-open');
            $("#"+id).addClass('glyphicon-folder-close');
        } else {
            $("#"+id).removeClass('glyphicon-folder-close');
            $("#"+id).addClass('glyphicon-folder-open');
        }
    });

    //multy form delete button
    $('.delete_form_btn').on('click', function(e) {
        var id = this.id;
        deleteItem(id);
    });

    //tooltip
    $(".element-with-tooltip").tooltip();

    initAllElements(); //init disable all fields

    attachResearchEducationalTooltip();

    changeInstitution();

    windowCloseAlert();

    initConvertEnterToTab();

    initDatetimepicker();

});


