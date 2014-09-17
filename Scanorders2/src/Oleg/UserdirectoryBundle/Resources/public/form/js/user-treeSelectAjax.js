/**
 * Created by oli2002 on 9/17/14.
 */

function initTreeSelect() {

    $('.ajax-combobox-institution').on('change', function(e){
        getComboboxTreeByPid($(this),'ajax-combobox-department');
    });

    $('.ajax-combobox-department').on('change', function(e){
        //console.log('change!!!', e);
        getComboboxTreeByPid($(this),'ajax-combobox-division');
    });

    $('.ajax-combobox-division').on('change', function(e){
        getComboboxTreeByPid($(this),'ajax-combobox-service');
    });

}

function getComboboxTreeByPid(parentElement,fieldClass) {

    console.log( "onchange=" + fieldClass );

    var holder = parentElement.closest('.user-collection-holder');
    var parentid = parentElement.select2('val');
    var targetId = '#' + holder.find("."+fieldClass).not("*[id^='s2id_']").attr('id');
    console.log( "parentid="+parentid );

    if( parentid ) {

        var fieldName = fieldClass.replace("ajax-combobox-", "");
        console.log( "fieldName="+fieldName+", parentid="+parentid );
        var url = getCommonBaseUrl("util/"+fieldName);
        url = url + "?pid="+parentid;
        $.ajax({
            url: url,
            timeout: _ajaxTimeout,
            async: asyncflag
        }).success(function(data) {
            populateSelectCombobox( targetId, data, "Select an option or type in a new value" );
            $(targetId).select2("readonly", false);
        });

    } else {
        console.log( "clear combobox, targetId="+targetId);

        //clear combobox
        populateSelectCombobox( targetId, null, "Select an option or type in a new value" );
        $(targetId).select2("readonly", true);
        
        clearChildren(holder,fieldClass);
    }
}

function clearChildren(holder,fieldClass) {
    //var holder = parentElement.closest('.user-collection-holder');
    var childrenTargetClass = null;

    if( fieldClass == "ajax-combobox-institution" ) {
        childrenTargetClass = "ajax-combobox-department";
    }
    if( fieldClass == "ajax-combobox-department" ) {
        childrenTargetClass = "ajax-combobox-division";
    }
    if( fieldClass == "ajax-combobox-division" ) {
        childrenTargetClass = "ajax-combobox-service";
    }

    if( childrenTargetClass ) {

        console.log( "clear Children="+childrenTargetClass );
        var childrenTargetId = '#' + holder.find("."+childrenTargetClass).not("*[id^='s2id_']").attr('id');

        populateSelectCombobox( childrenTargetId, null, "Select an option or type in a new value" );
        $(childrenTargetId).select2("readonly", true);

        clearChildren(holder,childrenTargetClass);

    }

}


