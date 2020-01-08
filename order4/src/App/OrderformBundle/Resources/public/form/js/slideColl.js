/*
 * Copyright (c) 2017 Cornell University
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */

/**
 * JS for slide coollections
 * Created with JetBrains PhpStorm.
 * User: oli2002
 * Date: 9/10/13
 * Time: 9:11 AM
 * To change this template use File | Settings | File Templates.
 */

////////////////// uses as generic collection field with + and - buttons ////////////////////////

//var findCollectionStr = 'input[type=text],.ajax-combobox-staintype';
var findCollectionStr = '.ajax-combobox-staintype, .partdiffdisident-field, .sliderelevantscan-field';

//var findCollectionSpecialStr = 'input[type=text],.ajax-combobox-staintype';
var findCollectionEnabledStr = 'input[type=text]:enabled:not([readonly])';  //,.ajax-combobox-staintype:enabled:not([readonly])';

//get input field only
function getCollField( ident, patient, encounter, procedure, accession, part, block, slide, coll, prefix ) {

    //console.log("coll field input:"+ident+"_"+patient+"_"+encounter+"_"+procedure+"_"+accession+"_"+part+"_"+block+"_"+slide+"_"+coll+"_"+prefix);
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
    newForm = newForm.replace(/__encounter__/g, encounter);
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

    //make sure to get only one element with correct id containing patient, encounter, procedure ... indexes
    var elementInputFind = element.closest('.fieldInputColl').find(findCollectionStr).not("*[id^='s2id_']");

    var elementInput =  elementInputFind.first();
    for( var i = 0; i < elementInputFind.length; i++ ) {
        //console.log("id="+elementInputFind.eq(i).attr('id')+", class="+elementInputFind.eq(i).attr('class'));
        if( elementInputFind.eq(i).attr('id') && elementInputFind.eq(i).attr('id').indexOf("_patient_") != -1 ) {
            elementInput = elementInputFind.eq(i);
            break;
        }
    }

    //console.log("elementInput.class="+elementInput.attr('class')+", id="+elementInput.attr('id'));
    var inputId = elementInput.attr('id');
    //console.log("inputId="+inputId+", elementInput.length="+elementInput.length);

    var idsArr = inputId.split("_");

    var name = idsArr[idsArr.length-holderIndex];   //i.e. "patient"
    var fieldName = idsArr[idsArr.length-fieldIndex];

    //console.log("name="+name+",fieldName="+fieldName);

    var patient = idsArr[4];
    var encounter = idsArr[6];
    var procedure = idsArr[8];
    var accession = idsArr[10];
    var part = idsArr[12];
    //var block = idsArr[14];
    //var slide = idsArr[16];

    if( inputId && inputId.indexOf("_slide_") != -1 ) {
        var block = idsArr[14];
        var slide = idsArr[16];
    } else {
        var block = 0;
        var slide = 0;
    }

    if( btnpos && btnpos == "bottom" ) {
        var elementHolder = element.closest('.blockspecialstains');  //parent().parent().parent().parent().parent().parent().parent();
    } else {
        var elementHolder = element.parent().parent().parent().parent().parent().parent();
    }

    //console.log("elementHolder id="+elementHolder.attr("id")+",class="+elementHolder.attr("class"));

    var collHolders = elementHolder.find('.row');
    var collHoldersCount = collHolders.length;  //TODO: this will be used as id of this element, however, the input field with this id might already exist (solution: get the max id for this element for all existing fields)
    var maxId = getMaxIdFromRows(collHolders,fieldName);
    //console.log("maxId="+maxId);

    var ident = name+fieldName;
    //console.log("ident=" + ident + ", collHoldersCount="+collHoldersCount + ", patient="+patient );

    var prefix = "add"; //indicate that this field is added by + button, so we can get different data prototype

    //var newForm = getDiffdiagField( fieldName, type, patient, encounter, procedure, accession, part, block, slide, collInputsCount, false );
    var newForm = getCollField( ident, patient, encounter, procedure, accession, part, block, slide, maxId+1, prefix );
    //console.log("newForm="+newForm);

    if( btnpos && btnpos == "bottom" ) {    //TEXTAREA with button at the bottom

        //remove + and add - button
        var btnDel = getDelBtn(btnpos);
        var btnRow = element.closest('.addDelBtnColl');

        //replace + by -
        btnRow.find('.addbtnCollField').replaceWith(btnDel);

        //remove - button if there are more than one
        if( btnRow.find('.delbtnCollField').length > 1 ) {
            btnRow.find('.delbtnCollField').last().remove();
        }

        //apend new form to the end of input-group-oleg
        var lastcollHolder = collHolders.last();
        //console.log("lastcollHolder=");
        //console.log(lastcollHolder);
        lastcollHolder.after(newForm);

        expandTextarea();

    } else {    //INPUT with attached button

        var btnDel = getDelBtn(btnpos);

        var collInputs = elementHolder.find(findCollectionStr);
        var lastInput = collInputs.eq(collHoldersCount-1);
        //console.log("attach to el id="+lastInput.attr("id")+",class="+lastInput.attr("class"));

        //add to last input field
        var lastcollHolder = collHolders.last();    //eq(collHoldersCount-1);

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
        //var addBtnElement = elementHolder.find('.addbtnCollField');   //find + button
        //addBtnElement.before(btnDel);

    }

    //populate the combobox by Ajax
    if( btnpos && btnpos == "bottom" ) {
        getComboboxSpecialStain(new Array(patient,encounter,procedure,accession,part,block,maxId+1),true);
    }
}

//elements is a row element
function getMaxIdFromRows( elements, field ) {
    var maxId = 0;
    //console.log("elements.length="+elements.length + ", field="+field);
    for( var i = 0; i < elements.length; i++ ) {

        var element = elements.eq(i);
        var inputField = element.find(findCollectionStr);
        var fieldId = inputField.attr("id");
        //console.log("get Max: inputField id="+fieldId+",class="+inputField.attr("class"));
        if( typeof fieldId !== 'undefined' ) {
            var idArr = fieldId.split("_"+field+"_");
            var idValueStr = idArr[1].split("_")[0];
            var idValue = parseInt(idValueStr);
            //console.log( "idValue=" + idValue );
            //console.log( idValue+"=?"+maxId );
            if( idValue > maxId ) {
                maxId = idValue;
            }
        }
    }
    return maxId;
}

//get input field only
function getDiffdiagField( name, type, patient, encounter, procedure, accession, part, block, slide, diffdiag, noDelBtn ) {

    //inputGroupId_patient_0_encounter_0_procedure_0_accession_0_part_0_diffDisident_0_diffDisident
    var ending = "_" + name + "_" + diffdiag + "_" + name;

    var dataholder = "#form-prototype-data"; //fixed data holder
    //console.log(dataholder);
    var collectionHolder =  $(dataholder);

    if( name == "diffDisident" ) {
        var prototype = collectionHolder.data('prototype-diffdisident');
        //console.log("diffDisident prototype="+prototype);
        var newForm = prototype.replace(/__patient__/g, patient);
        newForm = newForm.replace(/__encounter__/g, encounter);
        newForm = newForm.replace(/__procedure__/g, procedure);
        newForm = newForm.replace(/__accession__/g, accession);
        newForm = newForm.replace(/__part__/g, part);
        newForm = newForm.replace(/__partdiffdisident__/g, diffdiag);
    }

    if( name == "relevantScans" ) {
        var prototype = collectionHolder.data('prototype-relevantscans');
        //console.log("prototype="+prototype);
        var newForm = prototype.replace(/__patient__/g, patient);
        newForm = newForm.replace(/__encounter__/g, encounter);
        newForm = newForm.replace(/__procedure__/g, procedure);
        newForm = newForm.replace(/__accession__/g, accession);
        newForm = newForm.replace(/__part__/g, part);
        newForm = newForm.replace(/__block__/g, block);
        newForm = newForm.replace(/__slide__/g, slide);
        newForm = newForm.replace(/__relevantScans__/g, diffdiag);
    }

    var inputGroupId = 'inputGroupId_patient_'+patient+'_encounter_'+encounter+'_procedure_'+procedure+'_accession_'+accession+'_part_'+part+'_block_'+block+'_slide_'+slide+ending;
    //console.log("inputGroupId="+inputGroupId);

    var header = '<div class="input-group input-group-reg" id="'+inputGroupId+'">';

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
        var parent = element.closest('.blockspecialstains');//parent().parent().parent(); //field parent i.e. blockspecialstains
        var elementHolder = element.closest(".row");
        var holder = parent;
    } else {
        var parent = element.parent();
        var elementHolder = element.parent().parent().parent().parent().parent();
        var holder = elementHolder.parent();
    }

    //console.log("holder id="+holder.attr("id")+", class="+holder.attr("class"));
    //console.log("elementHolder id="+elementHolder.attr("id")+", class="+elementHolder.attr("class"));

    elementHolder.remove();

    if( btnpos && btnpos == "bottom" ) {
        var elementsEnabled = holder.find('textarea:enabled:not([readonly])');
        var elementsEnabledCount = elementsEnabled.length;
    } else {
        //var elementsEnabled = holder.find('input[type=text]:enabled:not([readonly]),textarea:enabled:not([readonly])');
        var elementsEnabled = holder.find(findCollectionEnabledStr);
        var elementsEnabledCount = elementsEnabled.length;
    }

    //console.log("elementsEnabledCount="+elementsEnabledCount);

    //if( !btnpos || btnpos != "bottom" ) { //only for attached field-button
        //add '+' button if the last visible field doesn't have it
        var lastEnabledEl = elementsEnabled.last();
        //console.log("lastEnabledEl id="+lastEnabledEl.attr("id")+", class="+lastEnabledEl.attr("class"));
        if( lastEnabledEl.parent().find('.addbtnCollField').length == 0 ) {
            //console.log("add + btn");
            var delBtnEl = lastEnabledEl.parent().find('.delbtnCollField');
            delBtnEl.after( getAddBtn(btnpos) );
        }
    //}

    //remove '-' button if only one visible field left
    if( elementsEnabledCount == 1 ) {
        //console.log('remove - btn, count='+elementsEnabledCount);
        if( btnpos && btnpos == "bottom" ) {
            //console.log('button at the bottom => text area');
            //element.remove();
            elementsEnabled.first().parent().find('.delbtnCollField').remove();
        } else {
            elementsEnabled.first().parent().find('.delbtnCollField').remove();
        }
    }

    return false;
}

function getAddBtn(btnpos) {
    //var addbtnId = 'addbtn_patient_'+patient+'_encounter_'+encounter+'_procedure_'+procedure+'_accession_'+accession+'_part_'+part+'_block_'+block+'_slide_'+slide+'_'+ident+'_'+collInt+'_'+ident;
    if( btnpos && btnpos == "bottom" ) {
        var btn = '<button onClick="addCollectionField(this,\''+btnpos+'\')" type="button" class="btn btn-sm addbtnCollField"><span class="glyphicon glyphicon-plus-sign"></span></button>';
    } else {
        var btn = '<span onClick="addCollectionField(this)"'+
            'class="input-group-addon btn addbtnCollField" data-toggle="datepicker" type="button"><i class="glyphicon glyphicon-plus-sign"></i></span>';
    }

    return btn;
}

function getDelBtn(btnpos) {
    if( btnpos && btnpos == "bottom" ) {
        //var btn = '<button onClick="delCollectionField(this,\''+btnpos+'\')" class="btn btn-sm delbtnCollField '+addClass+'" type="button">-</button>';
        var btn = '<button onClick="delCollectionField(this,\''+btnpos+'\')" type="button" class="btn btn-sm delbtnCollField"><span class="glyphicon glyphicon-minus-sign"></span></button>';
    } else {
        var btn = '<span onClick="delCollectionField(this)"'+
            'class="input-group-addon btn delbtnCollField" data-toggle="datepicker" type="button"><i class="glyphicon glyphicon-minus-sign"></i></span>';
    }

    return btn;
}
