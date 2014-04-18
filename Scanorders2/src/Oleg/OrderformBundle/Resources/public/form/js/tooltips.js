/**
 * Created with JetBrains PhpStorm.
 * User: oli2002
 * Date: 4/18/14
 * Time: 2:02 PM
 * To change this template use File | Settings | File Templates.
 */


function attachTooltip( element, flag, fieldParentName ) {

    var userPreferencesTooltip = $("#user-preferences-tooltip").val();
    //console.log('userPreferencesTooltip='+userPreferencesTooltip);

    if( userPreferencesTooltip == 0 ) {
        return false;
    }

    //var elementClass = element.attr('class');
    //if( elementClass && elementClass.indexOf("ajax-combobox") == -1 ) {
    if( element.hasClass('ajax-combobox') ) {
        //console.log( "select2!" );
        element = element.parent();
    }

    if( flag ) {

        var name = getObjectName(fieldParentName);

        if( element.hasClass('keyfield') ) {
            var title = "To enter another "+name+", click [X] button to clear information about this one";
        } else {
            var title = "Please enter "+name+" and/or press the [Check] button to access this field";
        }

        //printF(element,'create:');
        element.tooltip({
            'title': title
        });

        //highlight the button element and keyfield
        highlightBtnAndKey(element);

    } else {

        //printF(element,'destroy:');
        element.tooltip('destroy');

    }

}

function highlightBtnAndKey( element ) {

    var parent = getButtonElementParent(element);
    //console.log( "id="+parent.attr('id') );
    //printF(element,"input:");
    //printF(parent,"parent:");

    var btn = parent.find('.checkbtn,.removebtn');
    var keyfield = parent.find('.keyfield');

    element.on('show.bs.tooltip', function () {
        //printF(element,"showing input:");
        //printF(parent,"showing parent:");
        btn.removeClass('btn-default');
        btn.addClass('btn-info');
        keyfield.addClass('alert-info');
    })

    element.on('hide.bs.tooltip', function () {
        //printF(element,"hiding input:");
        //printF(parent,"hiding parent:");
        btn.removeClass('btn-info');
        btn.addClass('btn-default');
        keyfield.removeClass('alert-info');
    })

}

function getObjectName( inname ) {

    var name = '';

    switch(inname)
    {
        case 'patient':
            name = 'MRN';
            break;
        case 'accession':
            name = 'Accession';
            break;
        case 'part':
            name = 'Part';
            break;
        case 'block':
            name = 'Block';
            break;
        default:
            name = 'keyfield';
    }

    return name;
}

