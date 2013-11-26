/**
 * JS for slide coollections
 * Created with JetBrains PhpStorm.
 * User: oli2002
 * Date: 9/10/13
 * Time: 9:11 AM
 * To change this template use File | Settings | File Templates.
 */

////////////////// uses as generic collection field with + and - buttons ////////////////////////

//get input field only
function getCollField( ident, patient, procedure, accession, part, block, slide, coll, prefix ) {

    //console.log("coll field input:"+ident+"_"+patient+"_"+procedure+"_"+accession+"_"+part+"_"+block+"_"+slide+"_"+coll+"_"+prefix);
    //diffDisident_field_0_0_0_0_0_0_0_0_diffdiag_0
    var dataholder = "#form-prototype-data"; //fixed data holder

    if( !prefix ) {
        prefix = "";
    }

    var identLowerCase = ident.toLowerCase();
    //console.log(dataholder+", ident="+ident+", identLowerCase="+identLowerCase);
    var collectionHolder =  $(dataholder);
    var prototype = collectionHolder.data('prototype-'+prefix+identLowerCase);
    //console.log("prototype="+prototype);

    var newForm = prototype.replace(/__patient__/g, patient);
    newForm = newForm.replace(/__procedure__/g, procedure);
    newForm = newForm.replace(/__accession__/g, accession);
    newForm = newForm.replace(/__part__/g, part);
    newForm = newForm.replace(/__block__/g, block);
    newForm = newForm.replace(/__slide__/g, slide);

    var regex = new RegExp( '__' + identLowerCase + '__', 'g' );
    newForm = newForm.replace(regex, coll);

    //console.log("newForm="+newForm);
    return newForm;
}

//called by pressing + button attached to the field
//btnpos - position of the button element (used for text area when + button is placed at the bottom of the text area)
function addCollectionField( elem, btnpos ) {

    var element = $(elem);
    //console.log("element.class="+element.attr('class'));

    if( btnpos && btnpos == "bottom" ) {
        var parent = element.parent().parent().parent();
    } else {
        var parent = element.parent();
    }

    var elementInput = parent.find("input,textarea");

    //console.log("elementInput.class="+elementInput.attr('class')+", id="+elementInput.attr('id'));
    var inputId = elementInput.attr('id');
    //console.log("inputId="+inputId);

    var idsArr = inputId.split("_");

    var name = idsArr[idsArr.length-holderIndex];   //i.e. "patient"
    var fieldName = idsArr[idsArr.length-fieldIndex];

    //console.log("name="+name+",fieldName="+fieldName);

    var patient = idsArr[4];
    var procedure = idsArr[6];
    var accession = idsArr[8];
    var part = idsArr[10];

    if( inputId && inputId.indexOf("_slide_") != -1 ) {
        var block = idsArr[12];
        var slide = idsArr[14];
    } else {
        var block = 0;
        var slide = 0;
    }

    if( btnpos && btnpos == "bottom" ) {
        var elementHolder = element.parent().parent().parent();
    } else {
        var elementHolder = element.parent().parent().parent().parent().parent().parent();
    }

    //console.log("elementHolder id="+elementHolder.attr("id")+",class="+elementHolder.attr("class"));

    var collHolders = elementHolder.find('.row');
    var collHoldersCount = collHolders.length;  //TODO: this will be used as id of this element, however, the input field with this id might already exist (solution: get the max id for this element for all existing fields)
    var maxId = getMaxIdFromRows(collHolders,fieldName);
    //console.log("maxId="+maxId);

    var ident = name+fieldName;
    //console.log("ident=" + ident + ", collHoldersCount="+collHoldersCount );

    var prefix = "add"; //indicate that this field is added by + button, so we can get different data prototype

    //var newForm = getDiffdiagField( fieldName, type, patient, procedure, accession, part, block, slide, collInputsCount, false );
    var newForm = getCollField( ident, patient, procedure, accession, part, block, slide, maxId+1, prefix );
    //console.log("newForm="+newForm);

    var collInputs = elementHolder.find('input[type=text],textarea');
    var lastInput = collInputs.eq(collHoldersCount-1);
    //console.log("attach to el id="+lastInput.attr("id")+",class="+lastInput.attr("class"));

    //add to last input field
    var lastcollHolder = collHolders.last();    //eq(collHoldersCount-1);

    var btnDel = getDelBtn(btnpos);

    if( btnpos && btnpos == "bottom" ) {    //TEXTAREA with button at the bottom

        lastcollHolder.after(newForm);

        //add - button to + button if does not exists
        if( element.parent().find('.delbtnCollField').length == 0 ) {
            //console.log("add - button to textarea + button");
            element.after(btnDel);
        }

        expandTextarea();

    } else {    //INPUT with attached button

        lastcollHolder.after(newForm);

        //continue only for fields where button is attached to it as a component to each field

        //switch + for - : remove + and add -
        //remove + from the last element
        var delBtnEl = lastInput.parent().find('.addbtnCollField');
        //console.log("delBtnEl id="+delBtnEl.attr("id")+",class="+delBtnEl.attr("class"));
        delBtnEl.remove();
        //add - to the last element if not exists
        //console.log("count="+lastInput.parent().find('.delbtnCollField').length);
        if( lastInput.parent().find('.delbtnCollField').length == 0 ) {
            lastInput.after(btnDel);
        }
        //add - to created element (this element has + button)
        var addBtnElement = elementHolder.find('.addbtnCollField');   //find + button
        addBtnElement.before(btnDel);

    }

}

//elements is a row element
function getMaxIdFromRows( elements, field ) {
    var maxId = 0;
    //console.log("elements.length="+elements.length + ", field="+field);
    for( var i = 0; i < elements.length; i++ ) {

        var element = elements.eq(i);
        var inputField = element.find('input,textarea');
        var fieldId = inputField.attr("id");
        //console.log("get Max: inputField id="+fieldId+",class="+inputField.attr("class"));
        var idArr = fieldId.split("_"+field+"_");
        var idValueStr = idArr[1].split("_")[0];
        var idValue = parseInt(idValueStr);
        //console.log( "idValue=" + idValue );
        //console.log( idValue+"=?"+maxId );
        if( idValue > maxId ) {
            maxId = idValue;
        }
    }
    return maxId;
}

//get input field only
function getDiffdiagField( name, type, patient, procedure, accession, part, block, slide, diffdiag, noDelBtn ) {

    //inputGroupId_patient_0_procedure_0_accession_0_part_0_diffDisident_0_diffDisident
    var ending = "_" + name + "_" + diffdiag + "_" + name;

    var dataholder = "#form-prototype-data"; //fixed data holder
    //console.log(dataholder);
    var collectionHolder =  $(dataholder);

    if( name == "diffDisident" ) {
        var prototype = collectionHolder.data('prototype-diffdisident');
        //console.log("diffDisident prototype="+prototype);
        var newForm = prototype.replace(/__patient__/g, patient);
        newForm = newForm.replace(/__procedure__/g, procedure);
        newForm = newForm.replace(/__accession__/g, accession);
        newForm = newForm.replace(/__part__/g, part);
        newForm = newForm.replace(/__partdiffdisident__/g, diffdiag);
    }

    if( name == "relevantScans" ) {
        var prototype = collectionHolder.data('prototype-relevantscans');
        //console.log("prototype="+prototype);
        var newForm = prototype.replace(/__patient__/g, patient);
        newForm = newForm.replace(/__procedure__/g, procedure);
        newForm = newForm.replace(/__accession__/g, accession);
        newForm = newForm.replace(/__part__/g, part);
        newForm = newForm.replace(/__block__/g, block);
        newForm = newForm.replace(/__slide__/g, slide);
        newForm = newForm.replace(/__relevantScans__/g, diffdiag);
    }

    var inputGroupId = 'inputGroupId_patient_'+patient+'_procedure_'+procedure+'_accession_'+accession+'_part_'+part+'_block_'+block+'_slide_'+slide+ending;
    //console.log("inputGroupId="+inputGroupId);

    var header = '<div class="input-group" id="'+inputGroupId+'">';

    var btnAdd = getAddBtn();

    var btnDel = "";
    if( noDelBtn != true ) {
        btnDel = getDelBtn();
    }

    var footer = '</div>';

    newForm = header + newForm + btnDel + btnAdd + footer;

    //console.log("newForm="+newForm);
    return newForm;
}

//delete input field and modify +/- buttons accordingly:
function delCollectionField( elem, btnpos ) {

    var element = $(elem);

    if( btnpos && btnpos == "bottom" ) {
        var parent = element.parent().parent().parent(); //field parent i.e. slidespecialstains
        var elementHolder = parent.find(".row").last();
        var holder = parent;
    } else {
        var parent = element.parent();
        var elementHolder = element.parent().parent().parent().parent().parent();
        var holder = elementHolder.parent();
    }

    //console.log("holder id="+holder.attr("id")+", class="+holder.attr("class"));

    //console.log("elementHolder id="+elementHolder.attr("id")+", class="+elementHolder.attr("class"));

    elementHolder.remove();

    var elementsEnabled = holder.find('input[type=text]:enabled:not([readonly]),textarea:enabled:not([readonly])');

    //var elementsCount = elements.length;
    var elementsEnabledCount = elementsEnabled.length;
    //console.log("elementsEnabledCount="+elementsEnabledCount);

    if( !btnpos || btnpos != "bottom" ) { //only for attached field-button
        //add '+' button if the last visible field doesn't have it
        var lastEnabledEl = elementsEnabled.last();
        //console.log("lastEnabledEl id="+lastEnabledEl.attr("id")+", class="+lastEnabledEl.attr("class"));
        if( lastEnabledEl.parent().find('.addbtnCollField').length == 0 ) {
            //console.log("add + btn");
            var delBtnEl = lastEnabledEl.parent().find('.delbtnCollField');
            delBtnEl.after(getAddBtn());
        }
    }

    //remove '-' button if only one visible field left
    if( elementsEnabledCount == 1 ) {
        //console.log('remove - btn, count='+elementsEnabledCount);
        if( btnpos && btnpos == "bottom" ) {
            element.remove();
        } else {
            elementsEnabled.first().parent().find('.delbtnCollField').remove();
        }
    }

    return false;
}

function getAddBtn() {
    //var addbtnId = 'addbtn_patient_'+patient+'_procedure_'+procedure+'_accession_'+accession+'_part_'+part+'_block_'+block+'_slide_'+slide+'_'+ident+'_'+collInt+'_'+ident;
    var btn = '<span onClick="addCollectionField(this)"'+
        'class="input-group-addon btn addbtnCollField" data-toggle="datepicker" type="button"><i class="glyphicon glyphicon-plus-sign"></i></span>';
    return btn;
}

function getDelBtn(btnpos) {
    if( btnpos && btnpos == "bottom" ) {
        var btn = '&nbsp;<button onClick="delCollectionField(this,\''+btnpos+'\')" class="btn btn-sm btn-danger delbtnCollField" type="button">-</button>';
    } else {
        var btn = '<span onClick="delCollectionField(this)"'+
            'class="input-group-addon btn delbtnCollField" data-toggle="datepicker" type="button"><i class="glyphicon glyphicon-minus-sign"></i></span>';
    }

    return btn;
}
