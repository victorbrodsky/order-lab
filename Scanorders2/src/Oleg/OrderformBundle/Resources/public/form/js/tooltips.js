/**
 * Created with JetBrains PhpStorm.
 * User: oli2002
 * Date: 4/18/14
 * Time: 2:02 PM
 * To change this template use File | Settings | File Templates.
 */


function attachScanMagTooltip() {

    var msg = "Scanning at 40X magnification is done Friday to Monday. " +
        "Some of the slides (about 7% of the batch) may have to be rescanned the following week in order to obtain sufficient image quality. " +
        "We will do our best to expedite the process.";



}


function attachPatientNameSexAgeLockedTooltip() {

    var userPreferencesTooltip = $("#user-preferences-tooltip").val();
    if( userPreferencesTooltip == 0 ) {
        return false;
    }

    var sexname = "Accession";
    if( orderformtype == "single") {
        sexname = "Procedure";
    }

    //patient's sex
    var patsex = $('.patientsex').find('.well');
    patsex.tooltip({
        'title': "This is the current sex of the patient (if known). To enter a new sex, use the field \"Patient's Sex (at the time of encounter)\" in the "+sexname+" section."
    });
    highlightProcedureSexElement( patsex, '.proceduresex-field' );

    //patient's age
    var patage = $('.patientage').find('.well');
    patage.tooltip({
        'title': "This is the current age of the patient (if known). To enter a new age, use the field \"DOB\"."
    });
    highlightProcedureAgeElement( patage.parent(), '.patientdob-mask' );

    //patient's name
    var patname = $('.patientname').find('.well');
    patname.tooltip({
        'title': "This is the current name of the patient (if known). To enter a new name, use the field \"Patient's [Last, First, Middle] Name (at the time of encounter)\" in the "+sexname+" section."
    });
    highlightProcedureNameElement( patname, '.procedure-lastName', '.procedure-firstName', '.procedure-middleName' );

}

function highlightProcedureAgeElement( element, parentTarget) {
    element.on('show.bs.tooltip', function () {
        $(parentTarget).css("background-color","#d9edf7");
    });
    element.on('hide.bs.tooltip', function () {
        $(parentTarget).css("background-color","");
    });
}

function highlightProcedureNameElement( element, parentTarget1, parentTarget2, parentTarget3 ) {
    element.on('show.bs.tooltip', function () {
        $(parentTarget1).css("background-color","#d9edf7");
        $(parentTarget2).css("background-color","#d9edf7");
        $(parentTarget3).css("background-color","#d9edf7");
    });
    element.on('hide.bs.tooltip', function () {
        $(parentTarget1).css("background-color","");
        $(parentTarget2).css("background-color","");
        $(parentTarget3).css("background-color","");
    });
}

function highlightProcedureSexElement( element, parentTarget ) {
    element.on('show.bs.tooltip', function () {
        var parent = $(parentTarget);
        parent.addClass("alert-info");
    });
    element.on('hide.bs.tooltip', function () {
        var parent = $(parentTarget);
        parent.removeClass("alert-info");
    });
}

////////////////// tooltip for research and educational //////////////////
function attachResearchEducationalTooltip() {

    var userPreferencesTooltip = $("#user-preferences-tooltip").val();
    if( userPreferencesTooltip == 0 ) {
        return false;
    }

    //research
    attachResEduOnMouseEvent(
                                '.combobox-research-projectTitle',
                                '.combobox-research-setTitle',
                                '.ajax-combobox-optionaluser-research',
                                "Please enter the Research Project Title to access this field"
                            );

    //educational
    attachResEduOnMouseEvent(
                                '.combobox-educational-courseTitle',
                                '.combobox-educational-lessonTitle',
                                '.ajax-combobox-optionaluser-educational',
                                "Please enter the Course Title to access this field"
    );
    //attachResEduOnMouseEvent( '.ajax-combobox-optionaluser-educational', '.combobox-educational-courseTitle', "Please enter the Course Title to access this field" );

}

function attachResEduOnMouseEvent( parentTarget, elementTarget1, elementTarget2,  title ) {

    setResEduTooltip( parentTarget, elementTarget1, title );
    setResEduTooltip( parentTarget, elementTarget2, title );

    $(parentTarget).on("change", function(e) {
        //console.log('on change');

        if( $(elementTarget1).hasClass('select2-container-disabled') ) {
            setResEduTooltip( parentTarget, elementTarget1, title );
        } else {
            $(elementTarget1).parent().tooltip('destroy');
        }

        if( $(elementTarget2).hasClass('select2-container-disabled') ) {
            setResEduTooltip( parentTarget, elementTarget2, title );
        } else {
            $(elementTarget2).parent().tooltip('destroy');
        }

    });
}

function setResEduTooltip( parentTarget, elementTarget, title ) {
    $(elementTarget).parent().tooltip({
        'title': title
    });
    highlightResEduParentElement( elementTarget, parentTarget );
}


function highlightResEduParentElement( elementTarget, parentTarget ) {

    $(elementTarget).parent().on('show.bs.tooltip', function () {
        var parent = $(parentTarget).parent();
        var inputEl = parent.find('.select2-chosen');
        //inputEl.addClass("highlightSelect");
        inputEl.addClass("alert-info");
    });

    $(elementTarget).parent().on('hide.bs.tooltip', function () {
        var parent = $(parentTarget).parent();
        var inputEl = parent.find('.select2-chosen');
        //inputEl.removeClass("highlightSelect");
        inputEl.removeClass("alert-info");
    });

}
////////////////// EOF tooltip for research and educational //////////////////


////////////////// tooltip for scan order form //////////////////
function attachTooltip( element, flag, fieldParentName ) {

    var userPreferencesTooltip = $("#user-preferences-tooltip").val();
    //console.log( 'id='+element.attr('id')+', class='+element.attr('class')+', flag='+flag+', userPreferencesTooltip='+userPreferencesTooltip + ", fieldParentName="+fieldParentName);

    if( userPreferencesTooltip == 0 ) {
        return false;
    }

    var name = getObjectName(fieldParentName);

    if( element.hasClass('keyfield') ) {
        var title = "To enter another "+name+", press the [X] button to clear information about this one";
    } else {
        var title = "Please enter "+name+" and/or press the [Check] button to access this field";
    }

    //check if this is a keytype input
    if( element.hasClass('mrntype-combobox') || element.hasClass('accessiontype-combobox') ) {
        return false;   //don't process mrn and accession type fields
    }

    //check if this is a keyfield related input
    if( element.hasClass('keyfield') ) {
        var keyfieldFlag = true;
    } else {
        var keyfieldFlag = false;
    }

    var elementObj = new keyAndBtnObject(element,fieldParentName);

    //replace select2 (disabled) element with its parent to attach a tooltip (use parent as a wrapper). This wrap is required by bootstrap tooltip.
    if( element.hasClass('ajax-combobox') ) {
        //console.log( "select2!" );
        element = element.parent();
    }

    //console.log( 'val='+elementObj.keyvalue + ', readonly=' + elementObj.readonly + ', keyfieldFlag=' + keyfieldFlag );

    if( elementObj.keyvalue != '' && elementObj.readonly && !keyfieldFlag ) {
        //console.log('keyfield is locked and has a tooltip => return!');
        element.tooltip('destroy');
        return false;
    }

    if( flag ) {

        //printF(element,'create:');
        element.tooltip({
            'title': title
        });

        //highlight the button element and keyfield
        highlightBtnAndKey(element,fieldParentName);

        //tooltip for type
        var keytype = elementObj.keytype;
        if( keytype && keyfieldFlag ) {
            //console.log("set keytype tooltip, name="+name);
            var keytypeTitle = "To enter another "+name+" Type, press the [x] button to clear information about this "+name;
            keytype.tooltip({
                'title': keytypeTitle
            });
            highlightBtnAndKey(keytype,fieldParentName);
        }

    } else {

        //printF(element,'destroy:');
        element.tooltip('destroy');
        var keytype = elementObj.keytype;
        if( keytype && keyfieldFlag ) {
            //printF(keytype, "destroy keytypeHolder tooltip:");
            keytype.tooltip('destroy');
        }

    }

}

function highlightBtnAndKey( element, fieldParentName ) {

    var elementObj = new keyAndBtnObject(element,fieldParentName);

    if( orderformtype == "single") {
        if( fieldParentName != 'patient' ) {
            var btn = $('#remove_single_btn');
        } else {
            var btn = elementObj.btn;
        }
    } else {
        var btn = elementObj.btn;
    }

    var keyfield = elementObj.keyfield;

    elementObj.element.on('show.bs.tooltip', function () {
        //printF(element,"showing input:");
        //printF(parent,"showing parent:");
        btn.removeClass('btn-default');
        btn.addClass('btn-info');
        keyfield.addClass('alert-info');
    })

    elementObj.element.on('hide.bs.tooltip', function () {
        //printF(element,"hiding input:");
        //printF(parent,"hiding parent:");
        btn.removeClass('btn-info');
        btn.addClass('btn-default');
        keyfield.removeClass('alert-info');
    })

}

function setTypeTooltip( keytypeElement ) {

    var userPreferencesTooltip = $("#user-preferences-tooltip").val();

    if( userPreferencesTooltip == 0 ) {
        return false;
    }

    var keytypeText = keytypeElement.select2('data').text;
    //console.log('keytypeText='+keytypeText);

    keytypeElement = keytypeElement.parent().find('div').first();

    if( keytypeText == 'Specify Another Specimen ID Issuer' || keytypeText == 'Specify Another Patient ID Issuer' ) {

        var keytypeTitle = "Please enter a new issuer's name";

        if( keytypeElement.hasClass('mrntype-combobox') ) {
            var keytypeTitle = "Please enter the new Patient ID issuer's name";
        }

        if( keytypeElement.hasClass('accessiontype-combobox') ) {
            var keytypeTitle = "Please enter the new Specimen ID issuer's name";
        }

        keytypeElement.tooltip({
            'title': keytypeTitle
        });

    } else {
        //printF(keytypeElement,"destroy tooltip:")
        keytypeElement.tooltip('destroy');

    }
}

//element: input element field in the object. i.e. clinical history input field.
//output: object: btn - check button, keyfield - keyfield input field, keytype - keytype input field (null if not existed)
function keyAndBtnObject( element, fieldParentName ) {

    this.element = element;
    this.btn = null;
    this.keyfield = null;
    this.keytype = null;
    this.readonly = null;

    this.parent = getButtonElementParent(element);

    //get name
//    //printF(element);
//    //console.log('id='+element.attr('id'));
//    var idsArr = element.attr('id').split("_");
//    this.name = idsArr[idsArr.length-holderIndex];       //i.e. "patient"
    this.name = fieldParentName;

    //get button element
    if( orderformtype == "single") {
        //console.log('this.name='+this.name);
        if( this.name == 'patient' )
            this.btn = $('.patientmrnbtn');
        if( this.name == 'accession' )
            this.btn = $('.accessionbtn');
        if( this.name == 'part' )
            this.btn = $('.partbtn');
        if( this.name == 'block' )
            this.btn = $('.blockbtn');
    } else {
        this.btn = this.parent.find('.checkbtn,.removebtn');
    }


    var btnObj = new btnObject(this.btn);

    this.keyfield = btnObj.element;
    this.keyvalue = btnObj.key;
    this.keytype = btnObj.typeelement;

    if( element.hasClass('ajax-combobox') ) {
        var parentWithDisableInfo = element.parent().find('.ajax-combobox').first();
        //printF(parentWithDisableInfo,"parentWithDisableInfo:");
        if( parentWithDisableInfo.hasClass('select2-container-disabled') ) {
            this.readonly = true;
        } else {
            this.readonly = false;
        }
    } else {
        //printF(this.keyfield,'regular input keyfield:');
        var readonly = this.keyfield.attr("readonly");
        if( readonly && readonly.toLowerCase()!=='false' ) {
            this.readonly = true;
        } else {
            this.readonly = false;
        }
    }

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
////////////////// EOF tooltip for scan order form //////////////////
