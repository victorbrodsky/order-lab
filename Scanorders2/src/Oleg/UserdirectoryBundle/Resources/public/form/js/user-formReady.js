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

    fieldInputMask();

    setResearchEducational();   //init research and educational

    setResearch();  //set research listener

    setEducational(); //set educational listener

    //tooltip
    $(".element-with-tooltip").tooltip();

    attachResearchEducationalTooltip();

    windowCloseAlert();

    initConvertEnterToTab();

    //initDatetimepicker();

    regularCombobox();

    initDatepicker();

    expandTextarea();

    getComboboxInstitution();

});


