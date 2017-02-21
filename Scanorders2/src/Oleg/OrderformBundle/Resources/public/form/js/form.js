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
 * User: oli2002
 * Date: 8/14/13
 * Time: 12:36 PM
 */




//prevent exit modified form
function windowCloseAlert() {

    //console.log("cycle="+cycle);
    //console.log("_cycleShow="+_cycleShow);

    if( _cycleShow ) {
        return;
    }

    window.onbeforeunload = confirmModifiedFormExit;

    function confirmModifiedFormExit() {

        var modified = false;

        if( $('#scanorderform').length != 0 ) {
            modified = checkIfOrderWasModified();
        }

        if( $('#table-scanorderform').length != 0 ) {
            modified = checkIfTableWasModified();
        }

        if( $('#table-slidereturnrequests').length != 0 ) {
            modified = checkIfTableWasModified();
        }

        //console.log("modified="+modified);
        if( modified === true ) {

            //set back institution
            var institution_original_id = localStorage.getItem("institution_original_id");
            if( typeof institution_original_id !== 'undefined' && institution_original_id != "" && institution_original_id != null ) {
                $('.combobox-institution').select2('val', institution_original_id);
            }

            //console.log("modified msg");
            //http://stackoverflow.com/questions/37727870/window-confirm-message-before-reload
            //'Custom text support removed' in Chrome 51.0 and Firefox 44.0.
            return "Are you sure you would like to navigate away from this page? Text you may have entered has not been saved yet.";
        } else {
            //console.log("non modified msg");
            return;
        }
    }

    $('form').submit(function() {
        window.onbeforeunload = null;
    });
}


function changeInstitution() {

    var institution_original_id = $('.combobox-institution').select2('val');
    window.localStorage.setItem("institution_original_id", institution_original_id);

    var institution_changed_id = localStorage.getItem("institution_changed_id");
    //console.log('institution_changed_id='+institution_changed_id);

    if( institution_original_id == institution_changed_id ) {
        //console.log('remove institution_changed_id');
        institution_changed_id = null;
        window.localStorage.removeItem('institution_changed_id');
    }

    //var institution_changed_id = localStorage.getItem("institution_changed_id");
    //console.log('institution_changed_id='+institution_changed_id);

    if( typeof institution_changed_id !== 'undefined' && institution_changed_id != "" && institution_changed_id != null ) {
        $('.combobox-institution').select2('val', institution_changed_id);
    }

    $('.combobox-institution').change(function(e) {
        var inst = $('.combobox-institution').select2('val');
        window.localStorage.setItem("institution_changed_id", inst);
        window.location.reload();
    });

}

//add all element to listeners again, the same as in ready
function initAdd() {

    //console.log("init Add");

    expandTextarea();

    regularCombobox();

    initDatepicker();

    //clean validation elements
    cleanValidationAlert();

//    //tooltip
//    $(".element-with-tooltip").tooltip();
//
//    $('.element-with-select2-tooltip').parent().tooltip({
//        title: function() {
//            var titleText = $(this).find('select.element-with-select2-tooltip').attr('title');
//            return titleText;
//        }
//    });

    initTooltips();

    //attach dob-encounter date calculation
    setPatientAndEncounterAgeListener();

}

//confirm delete
function deleteItem(id) {
    //check if this is not the last element in the parent tree
    var thisId = "formpanel_"+id;
    //console.log("replace thisId="+thisId);
    var thisParent = $("#"+thisId).parent();
    //console.log("replace thisParent="+thisParent.attr('id'));
    var elements = thisParent.children( ".panel" );
    //console.log("replace elements.length="+elements.length);

    if( confirm("This action will affect this element and all its child elements. Are you sure?") ) {

        if( elements.length > 1 ) {

            $('#formpanel_'+id).remove();

            //check if it is only one element left
            if( (elements.length-1) == 1 ) {
                //change "delete" to "clear"
                var element = thisParent.children( ".panel" );
                //console.log("rename element="+element.attr('id'));
                var delBtnToReplace = element.children(".panel-heading").children(".form-btn-options").children(".delete_form_btn");
                //console.log("rename delBtnToReplace="+delBtnToReplace.attr('id'));
                //delBtnToReplace.html('Clear');
                delBtnToReplace.remove();
            }

        }
    }

    //clean validation elements
    cleanValidationAlert();

    return false;
}

//main input form from html button: add parent form (always type='multi')
function addSameForm( name, patientid, encounterid, procedureid, accessionid, partid, blockid, slideid, scanid, stainid ) {

    var uid = patientid+"_"+encounterid+"_"+procedureid+"_"+accessionid+"_"+partid+"_"+blockid+"_"+slideid+"_"+scanid+"_"+stainid;  //+"_"+diffdiag+"_"+specstain+"_"+image;
    //console.log("addSameForm="+name+"_"+uid);

    //get total number of existing similar elements in the holder => get next index
    if( name == "patient") {
        var parentClass = "order-content";
    } else {
        var parentClass = "panel-body";
    }
    var currPanelId = "#formpanel_"+name+"_"+uid;
    var currHolder = $(currPanelId).closest('.'+parentClass);
    //var countTotal = currHolder.find('.panel-'+name).length;
    //console.log("countTotal="+countTotal);
    //make sure this ids has the higher index, so the same ids does not repeat. It can happened if the first element was deleted.

    //get the higher index from the siblings
    var maxIndex = 0;
    currHolder.find('.panel-'+name).each( function(){
        //id: formpanel_block_0_0_0_0_1_0_0_0
        var id = $(this).attr('id');
        var idsArr = id.split("_"+name+"_");
        var ids = idsArr[1].split("_");
        var index = parseInt( getIdByName(name,ids) );
        //console.log("ids="+ids+", index="+index+", maxIndex="+maxIndex);
        if( index > maxIndex ) {
            maxIndex = index;
        }
    });
    var countTotal = maxIndex + 1;
    //console.log("countTotal="+countTotal);

    //prepare form ids and pass it as array
    //increment by 1 current object id
    var btnids = getIds(name, countTotal, patientid, encounterid, procedureid, accessionid, partid, blockid, slideid, scanid, stainid);
    var id = btnids['id'];
    var idsorig = btnids['orig'];
    var ids = btnids['ids'];
    var idsm = btnids['idsm'];
    var idsp = btnids['idsp'];
    var idsNext = btnids['idsNext'];    //index based on the total count of siblings

    //place the form in the html page after the last similar element: use parent
    //    var holder = "#formpanel_"+name+"_"+uid;
    //formpanel_slide_0_0_0_0_0_0_0_0
    var partialId = "formpanel_"+name+"_"+btnids['partialId'];
    var elements = $('[id^='+partialId+']');
    var holder = elements.eq(elements.length-1);
    //console.log( "id="+holder.attr('id') );

    //console.log("holder="+holder);
    //console.log("idsNext="+idsNext);

    //attach form
    var withDelBtn = true;
    $(holder).after( getForm( name, idsNext, withDelBtn ) );

    if( name == "slide" ) {
        //addCollFieldFirstTime( "relevantScans", ids );
        //addDiffdiagFieldFirstTime( name, ids );
        //addCollFieldFirstTime( "specialStains", ids );
    }

    //bind listener to the toggle button
    bindToggleBtn( name + '_' + idsNext.join("_") );
    bindDeleteBtn( name + '_' + idsNext.join("_") );
    //originOptionMulti(ids);
    diseaseTypeListener();
    initAdd();
    addKeyListener();

    //mask init
    var newHolder = $('#formpanel_'+name + '_' + idsNext.join("_"));

    contentToggleHierarchyButton(newHolder);
    fieldInputMask( newHolder ); //setDefaultMask(btnObj);
    //comboboxes init
    initComboboxJs(idsNext, newHolder);
    //file upload
    initFileUpload(newHolder);

    //setDefaultMask(btnObj);

    //create children nested forms
    //var nameArray = ['patient', 'procedure', 'accession', 'part', 'block', 'slide', 'stain_scan' ];
    var nameArray = ['patient', 'encounter', 'part', 'block', 'slide' ];
    var length = nameArray.length
    var index = nameArray.indexOf(name);
    //console.log("index="+index+" len="+length);
    var parentName = name;
    for (var i = index+1; i < length; i++) {
        //console.log("=> name="+nameArray[i]);

        if( nameArray[i] == 'stain_scan' ) {
            addChildForms( parentName, idsNext, 'stain', nameArray[i-1], patientid, encounterid, procedureid, accessionid, partid, blockid, slideid, scanid, stainid );
            addChildForms( parentName, idsNext, 'scan', nameArray[i-1], patientid, encounterid, procedureid, accessionid, partid, blockid, slideid, scanid, stainid );
        } else {
            addChildForms( parentName, idsNext, nameArray[i], nameArray[i-1], patientid, encounterid, procedureid, accessionid, partid, blockid, slideid, scanid, stainid );
        }

    }

    //add Delete button if there are sibling objects
    var origId = "formpanel_"+name+"_"+idsorig.join("_");
    //console.log("origId="+origId);
    var origBtnGroup = $("#"+origId).find('.panel-heading').find('.form-btn-options').first();
    //var origPanelHeading = $("#"+origId).find('.panel-heading').first();
    //console.log(origPanelHeading);
    var origDelLen = origBtnGroup.find('.delete_form_btn').length;
    //console.log("origDelLen="+origDelLen);
    if( origDelLen == 0 ) {
        //console.log("generate delete button for name="+name+", idsorig="+idsorig);
        var deletebtn = getHeaderDeleteBtn( name, idsorig, "Delete" );
        //origBtnGroup.find('.add_form_btn').before(deletebtn);
        origBtnGroup.append( deletebtn );
        bindDeleteBtn( name + '_' + idsorig.join("_") );
    }

    //initial disabling
    initAllElements(newHolder);
}

//add children forms triggered by parent form
function addChildForms( parentName, parentIds, name, prevName, patientid, encounterid, procedureid, accessionid, partid, blockid, slideid, scanid, stainid ) {

//    //attach to previous object (prevName)
//    var btnids = getIds( parentName, null, patientid, encounterid, procedureid, accessionid, partid, blockid, slideid, scanid, stainid );
//    //var idsorig = btnids['orig'];
//    var ids = btnids['ids'];
//    var idsm = btnids['idsm'];
//    var id = btnids['id'];
//    id = id - 1;
//    var idsu = ids.join("_");

    var ids = parentIds;

    var uid = prevName+"_"+parentIds.join("_");
    var holder = "#form_body_"+uid;
    //console.debug(name+": ADD CHILDS to="+holder);

    //attach children form
    var withDelBtn = false;
    $(holder).append( getForm( name, ids, withDelBtn  ) );
    
    bindToggleBtn( name + '_' + ids.join("_") );
    bindDeleteBtn( name + '_' + ids.join("_") );
    //originOptionMulti(ids);
    diseaseTypeListener();
    initAdd();
    addKeyListener();

    //mask init
    var newHolder = $( '#formpanel_' + name + '_' + ids.join("_") );

    contentToggleHierarchyButton(newHolder);
    fieldInputMask( newHolder );
    //comboboxes init
    initComboboxJs(ids, newHolder);
    //file upload
    initFileUpload(newHolder);

}

//input: current form ids
function getForm( name, ids, withDelBtn ) {

    var deleteStr = "Delete";
    var idsu = ids.join("_");

    //increment by 1 current object id
    var formbody = getFormBody( name, ids[0], ids[1], ids[2], ids[3], ids[4], ids[5], ids[6], ids[7], ids[8] );

    //console.log("getForm: "+name+"_"+", ids="+ids+', idsu='+idsu+", withDelBtn="+withDelBtn);

    var addHeaderBtn = true;
    //don't show add for scan and stain sections
    if( name == "scan" || name == "stain" ) {
        addHeaderBtn = false;
    }
    //don't show add patient, except scanorder form where there is '_patient_'
    //if( name == 'patient' && ids.indexOf("_patient_") === -1 ) {
    //    addHeaderBtn = false;
    //}

    if( addHeaderBtn ) {
        var addbtn = getHeaderAddBtn( name, ids );
        var deletebtn = getHeaderDeleteBtn( name, ids, deleteStr );
    } else {
        var addbtn = "";
        var deletebtn = "";
    }

    if( !withDelBtn ) {
        deletebtn = "";
    }

    //get itemCount from partialId
    var itemCount = getIdByName(name,ids) + 1;
    //console.log('itemCount='+itemCount);

    var title = name;
    if( name == "encounter" || name == "procedure" ) {
        title = "accession";
    }

    var formhtml =
        '<div id="formpanel_' +name + '_' + idsu + '" class="panel panel-'+name+' panel-multi-form">' +
            '<div class="panel-heading panel-heading-hierarchy">' +

                '<button id="form_body_toggle_'+ name + '_' + idsu +'" type="button"' +
                    'class="btn btn-default btn-xs form_body_toggle_btn glyphicon glyphicon-folder-open pull-left"' +
                    'aria-expanded="true"'+
                    'data-toggle="collapse" data-target="#form_body_'+name+'_'+idsu+'">'+
                '</button>'+


            '<button style="margin-left: 5px;" type="button"'+
                'class="btn btn-default btn-xs form_body_content_toggle_btn glyphicon glyphicon-list pull-left"'+
                'aria-expanded="true"'+
                'data-toggle="collapse" data-target="#'+title+'_'+idsu+'">'+
            '</button>'+

//            '<button id="form_body_toggle_'+ name + '_' + idsu +'" type="button" class="btn btn-default btn-xs form_body_toggle_btn glyphicon glyphicon-folder-open black" data-toggle="collapse" data-target="#form_body_'+name+'_'+idsu+'"></button>' +
//            '&nbsp;' +
//            '<div class="element-title">' + capitaliseFirstLetter(title) + ' ' + itemCount + '</div>' +
                '<h4 class="panel-title element-title element-title-extended-width">' +
                    '<div style="float:left;">' +
                        capitaliseFirstLetter(title) + ' ' + itemCount +
                    '</div>' +
                    '<div class="element-title-object-name" style="float:left; margin-left:10px;">' +
                        //to be inserted by JS
                    '</div>' +
                '</h4>' +
                '<div class="form-btn-options">' +
                    addbtn +
                    deletebtn +
                '</div>' +
                '<div class="clearfix"></div>' +
            '</div>' +  //panel-heading
            '<div id="form_body_' + name + '_' + idsu + '" class="panel-body panel-body-multi-form collapse in">' +
                formbody +
            '</div>' +
            '</div>';

    return formhtml;
}

function getFormBody( name, patientid, encounterid, procedureid, accessionid, partid, blockid, slideid, scanid, stainid ) {

    //console.log("name="+name+",patient="+patientid+",encounter="+encounterid+",procedure="+procedureid+",accession="+accessionid+",part="+partid+",block="+blockid+",slide="+slideid);

    var collectionHolder =  $('#form-prototype-data');

    // Get the data-prototype explained earlier
    var prototype = collectionHolder.data('prototype-'+name);
    //console.log("prototype="+prototype);

    //console.log("before replace patient...");
    var newForm = prototype.replace(/__patient__/g, patientid);
    //console.log("before replace procedure... NewForm="+newForm);
    newForm = newForm.replace(/__encounter__/g, encounterid);
    newForm = newForm.replace(/__procedure__/g, procedureid);
    newForm = newForm.replace(/__accession__/g, accessionid);
    newForm = newForm.replace(/__part__/g, partid);
    newForm = newForm.replace(/__block__/g, blockid);
    newForm = newForm.replace(/__slide__/g, slideid);
    newForm = newForm.replace(/__scan__/g, scanid);
    newForm = newForm.replace(/__stain__/g, stainid);

    //newForm = newForm.replace(/__clinicalHistory__/g, 0);   //add only one clinical history to the new form
    newForm = newForm.replace(/__paper__/g, 0);   //add only one paper to the new form

    //replace origin_option_multi_patient_0_procedure_0_accession_0_part_0_origintag with correct ids
    //origin_option_multi_patient_0_procedure_0_accession_0_part_0_origintag
    var newOriginId = "origin_option_multi_patient_"+patientid+"_encounter_"+encounterid+"_procedure_"+procedureid+"_accession_"+accessionid+"_part_"+partid+"_origintag";
    newForm = newForm.replace(/origin_option_multi_patient_0_encounter_0_procedure_0_accession_0_part_0_origintag/g, newOriginId);

    newForm = newForm.replace(/__[a-zA-Z0-9]+__/g, 0); //replace everything what is left __*__ by 0 => replace all array fields by 0

    //remove required
    newForm = newForm.replace(/required="required"/g, "");

    //console.log("newForm="+newForm);

    return newForm;
}

function getHeaderAddBtn( name, ids ) {
    var addbtn = '<button id="form_add_btn_' + name + '_' + ids.join("_") + '" type="button"'+
                    'class="btn btn-default btn-xs add_form_btn pull-right"' +
                    'onclick="addSameForm(\'' + name + '\''+ ',' + ids.join(",") + ')">Add'+
                '</button>';
    return addbtn;
}

function getHeaderDeleteBtn( name, ids, deleteStr ) {
    var deletebtn = '<button id="delete_form_btn_' + name + '_' + ids.join("_") + '" type="button"'+ ' style="margin-right:1%;" ' +
                        'class="btn btn-danger btn-xs delete_form_btn pull-right">' + deleteStr +
                    '</button>';
    return deletebtn;
}

function getIds( name, nextIndex, patientid, encounterid, procedureid, accessionid, partid, blockid, slideid, scanid, stainid ) {
    var id = 0;
    var nextName = "";

    patientid = parseInt(patientid);
    encounterid = parseInt(encounterid);
    procedureid = parseInt(procedureid);
    accessionid = parseInt(accessionid);
    partid = parseInt(partid);
    blockid = parseInt(blockid);
    slideid = parseInt(slideid);
    scanid = parseInt(scanid);
    stainid = parseInt(stainid);

    var patientidm = patientid;
    var encounteridm = encounterid;
    var procedureidm = procedureid;
    var accessionidm = accessionid;
    var partidm = partid;
    var blockidm = blockid;
    var slideidm = slideid;
    var scaniddm = scanid;
    var stainidm = stainid;

    var patientidp = patientid;
    var encounteridp = encounterid;
    var procedureidp = procedureid;
    var accessionidp = accessionid;
    var partidp = partid;
    var blockidp = blockid;
    var slideidp = slideid;
    var scanidp = scanid;
    var stainidp = stainid;

    var patientNext = patientid;
    var encounterNext = encounterid;
    var procedureNext = procedureid;
    var accessionNext = accessionid;
    var partNext = partid;
    var blockNext = blockid;
    var slideNext = slideid;
    var scanNext = scanid;
    var stainNext = stainid;

    var partialId = "";

    var orig = [patientid, encounterid, procedureid, accessionid, partid, blockid, slideid, scanid, stainid];

    switch(name)
    {
        case "patient":
            patientidm = patientid-1;
            patientid++;
            patientidp = patientid+1;
            id = patientid;
            nextName = "encounter";
            partialId = "";
            patientNext = nextIndex;
            break;
        case "encounter":
            encounteridm = encounterid-1;
            encounterid++;
            encounteridp = encounterid+1;
            id = encounterid;
            nextName = "procedure";
            partialId = patientid;
            encounterNext = nextIndex;
            break;
        case "procedure":
            procedureidm = procedureid-1;
            procedureid++;
            procedureidp = procedureid+1;
            id = procedureid;
            nextName = "accession";
            partialId = patientid;
            procedureNext = nextIndex;
            break;
        case "accession":
            accessionidm = accessionid-1;
            accessionid++;
            accessionidp = accessionid+1;
            id = accessionid;
            nextName = "part";
            partialId = patientid+"_"+encounterid+"_"+procedureid;
            accessionNext = nextIndex;
            break;
        case "part":
            partidm = partid-1;
            partid++;
            partidp = partid+1;
            id = partid;
            nextName = "block";
            partialId = patientid+"_"+encounterid+"_"+procedureid+"_"+accessionid;
            partNext = nextIndex;
            break;
        case "block":
            blockidm = blockid-1;
            blockid++;
            blockidp = blockid+1;
            id = blockid;
            nextName = "slide";
            partialId = patientid+"_"+encounterid+"_"+procedureid+"_"+accessionid+"_"+partid;
            blockNext = nextIndex;
            break;
        case "slide":
            slideidm = slideid-1;
            slideid++;
            slideidp = slideid+1;
            id = slideid;
            nextName = "";
            partialId = patientid+"_"+encounterid+"_"+procedureid+"_"+accessionid+"_"+partid+"_"+blockid;
            slideNext = nextIndex;
            break;
        case "scan":
            scaniddm = scanid-1;
            scanid++;
            scanidp = scanid+1;
            id = scanid;
            nextName = "";
            partialId = patientid+"_"+encounterid+"_"+procedureid+"_"+accessionid+"_"+partid+"_"+blockid;
            scanNext = nextIndex;
            break;
        case "stain":
            stainidm = stainid-1;
            stainid++;
            stainidp = stainid+1;
            id = stainid;
            nextName = "";
            partialId = patientid+"_"+encounterid+"_"+procedureid+"_"+accessionid+"_"+partid+"_"+blockid;
            stainNext = nextIndex;
            break;
        default:
            id = 0;
    }

    var idsArray = [patientid, encounterid, procedureid, accessionid, partid, blockid, slideid, scanid, stainid];
    var idsArrayM = [patientidm, encounteridm, procedureidm, accessionidm, partidm, blockidm, slideidm, scaniddm, stainidm];
    var idsArrayP = [patientidp, encounteridp, procedureidp, accessionidp, partidp, blockidp, slideidp, scanidp, stainidp];
    var idsArrayNext = [patientNext, encounterNext, procedureNext, accessionNext, partNext, blockNext, slideNext, scanNext, stainidp];

    var res_array = {
        'id' : id,
        'orig' : orig,
        'ids' : idsArray,
        'idsm' : idsArrayM,
        'idsp' : idsArrayP,
        'nextName' : nextName,
        'partialId' : partialId,
        'idsNext' : idsArrayNext
    };

    return res_array;
}

function getIdByName( name, ids ) {
    var id = -1;

    switch(name)
    {
        case "patient":
            id = ids[0];
            break;
        case "encounter":
            id = ids[1];
            break;
        case "procedure":
            id = ids[2];
            break;
        case "accession":
            id = ids[3];
            break;
        case "part":
            id = ids[4];
            break;
        case "block":
            id = ids[5];
            break;
        case "slide":
            id = ids[6];
            break;
        default:
            id = 0;
    }

    return id;
}

//bind listener to the toggle button
function bindToggleBtn( uid ) {
    //console.log("toggle uid="+uid);
    $('#form_body_toggle_'+ uid).on('click', function(e) {
        // prevent the link from creating a "#" on the URL
        //e.preventDefault();
        //var className = $(this).attr("class");
        var id = this.id;
        //alert(className);

//        if( className == 'form_body_toggle_btn glyphicon glyphicon-folder-open') {
        if( $(this).hasClass('glyphicon-folder-open') ) {
            $("#"+id).removeClass('glyphicon-folder-open');
            $("#"+id).addClass('glyphicon-folder-close');
        } else {
            $("#"+id).removeClass('glyphicon-folder-close');
            $("#"+id).addClass('glyphicon-folder-open');
        }
    });
}
//multy form delete
function bindDeleteBtn( uid ) {
    //console.log('delete uid='+uid);
    $('#delete_form_btn_'+uid).on('click', function(e) {
        var id = uid;
        //console.log('click delete uid='+uid);
        deleteItem(id);
    });
}


function priorityOption() {

    if( $('#priority_option').is(':visible') ) {
        $('#priority_option').collapse('hide');
    }

    $('#oleg_orderformbundle_messagetype_priority').change(function(e) {
        //e.preventDefault();
        //$('#priority_option').collapse('toggle');
        var checked = $('#oleg_orderformbundle_messagetype_priority').find('input[type=radio]:checked').val();
        if( checked == 'Stat' ) {
            var param = 'show';
        } else {
            var param = 'hide';
        }
        $('#priority_option').collapse(param);
    });

    var checked = $('#oleg_orderformbundle_messagetype_priority').find('input[type=radio]:checked').val();
    //console.log("checked="+checked);
    if( checked == 'Stat' ) {
        $('#priority_option').collapse('show');
    }
}

function purposeOption() {

    if( $('#purpose_option').is(':visible') ) {
        $('#purpose_option').collapse('hide');
    }

    $('#oleg_orderformbundle_messagetype_purpose').change(function(e) {
        //e.preventDefault();
        //$('#purpose_option').collapse('toggle');
        var checked = $('#oleg_orderformbundle_messagetype_purpose').find('input[type=radio]:checked').val();
        if( checked == 'For External Use (Invoice Fund Number)' ) {
            var param = 'show';
        } else {
            var param = 'hide';
        }
        $('#purpose_option').collapse(param);
    });

    var checked = $('#oleg_orderformbundle_messagetype_purpose').find('input[type=radio]:checked').val();
    //console.log("checked="+checked);
    if( checked == 'For External Use (Invoice Fund Number)' ) {
        $('#purpose_option').collapse('show');
    }
}


//use "eternicode/bootstrap-datepicker": "dev-master"
//process Datepicker: add or remove click event to the field and its siblings calendar button
//element: null or jquery object. If null, all element with class datepicker will be assign to calendar click event
//remove null or "remove"
function processDatepicker( element, remove ) {

    if( !_cycleShow ) {

        //replace element (input field) by a parent with class .input-group .date
        if( !element ) {
            element = $('.input-group.date');
        } else {
            element = element.closest('.input-group.date');
        }

        //printF(element,"process Datepicker: Datepicker Btn:");

        if( remove == "remove" ) {
            //printF(element,"Remove datepicker:");
            element.datepicker("remove");

            //make sure the masking is clear when input is cleared by datepicker
            var inputField = element.find('input');
            clearErrorField( inputField );

            //remove lock-icon-button
            var inputGroup = element.parent().find('.input-group');
            if( inputGroup.find('input').hasClass('patient-dob-date') ) {
                inputGroup.find('.calendar-icon-button').show();
                inputGroup.find('.lock-icon-button').remove();
            }

        } else {
            initSingleDatepicker(element);
        }

    }
}


//function setResearch() {
//    //get value of project title field on change
////    $('.combobox-research-projectTitle').on("change", function(e) {
////        //console.log('listener: project Title changed');
////        //getSetTitle();
////        getOptionalUserResearch();
////    });
//}

//function setEducational() {
//    //get value of project title field on change
////    $('.combobox-educational-courseTitle').on("change", function(e) {
////        //console.log('listener: course Title changed');
////        //getLessonTitle();
////        getOptionalUserEducational();
////    });
//}

//collapse content of patient hierarchy
//Note: for bootstrap's "hide.bs.collapse" event use datepicker fix https://github.com/eternicode/bootstrap-datepicker/issues/978
function contentToggleHierarchyButton(holder) {

    //console.log('content ToggleHierarchy Button');

    var targetId = '.form-element-holder.collapse';
    if( typeof holder !== 'undefined' && holder.length > 0 ) {
        var targetElement = holder.find(targetId);
    } else {
        var targetElement = $(targetId);
    }

    targetElement.on('hide.bs.collapse', function (e) {

        //console.log('hide: collapse');
        //console.log($(this));
        //e.preventDefault();
        //hideORshowCollapsableBodies( $(this), 'hide' );

        if( $(this).hasClass('originradio') || $(this).hasClass('primaryorganradio') ) {
            return;
        }

        var folderBtn = $(this).closest('.panel-multi-form').find('.panel-heading-hierarchy').find('button.form_body_toggle_btn').first();
        //console.log("folderBtn.length="+folderBtn.length);
        //console.log(folderBtn);

        if( folderBtn.hasClass('glyphicon-folder-close') ) {
            //console.log('hidden: folderBtn is closed => open it');

            //open folder button: show me what i am trying to see
            folderBtn.trigger("click");

            //1 solution) don't close list
            e.preventDefault();
            //2 solution) re-open list's collapsableBody
            //$(this).collapse('show');

        } else {

            hideORshowCollapsableBodies( $(this), 'hide' );

        }

    });


//    $('.form-element-holder.collapse.in').on('show.bs.collapse', function (e) {
//        //e.preventDefault();
//        hideORshowCollapsableBodies( $(this), 'show' );
//    });

    targetElement.on('shown.bs.collapse', function (e) {

        var folderBtn = $(this).closest('.panel-multi-form').find('.panel-heading-hierarchy').find('button.form_body_toggle_btn').first();
        //console.log(folderBtn);

        //open folder button if closed
        if( folderBtn.hasClass('glyphicon-folder-close') ) {
            //console.log('folderBtn is closed => open it');
            //open folder button: show me what i am trying to see
            folderBtn.trigger("click");
        }
        //else {
            //console.log('folderBtn is open');
        //}

    });


//    $('.glyphicon-list').on('click', function (e) {
//        //e.preventDefault();
//        processListButtonClick(this);
//    });

}

function hideORshowCollapsableBodies( bodyElement, toggleValue ) {
    //console.log("hide OR showCollapsableBodies bodyElement:");
    //console.log(bodyElement);
    bodyElement.closest('.panel-body').find('.panel-multi-form').find('.form-element-holder.collapse').collapse(toggleValue);

    //toggle all slide's lists
    if( bodyElement.hasClass('slide-form-element-holder') ) {
        bodyElement.closest('.panel-body').find('.panel-body-imaging.collapse').collapse(toggleValue);
    }
}

function toggleSinglePanel(btn,panel) {

    //console.log("toggle SinglePanel");

    var btnEl = $(btn);
    var panelEl = $(panel);

    if( btnEl.hasClass('glyphicon-folder-close') ) {
        //console.log("toggle SinglePanel: open");
        //open
        panelEl.show(400);
        //panelEl.slideUp();
        //panelEl.show("slide", {direction: "right" }, "slow");
        //panelEl.show( "slide", { direction: "down"  }, 400 );
        //panelEl.show(400).animation({direction:"down"});
        //panelEl.show("slide", {direction: "right" }, 500);
        //panelEl.effect('slide', { direction: 'down', mode: 'show' }, 400);
    }

    if( btnEl.hasClass('glyphicon-folder-open') ) {
        //close
        //console.log("toggle SinglePanel: close");
        panelEl.hide(400);
    }
}

//toggle the folder button from "closed" to "open" state (only if it is in the closed state).
//btn - list button
//function processListButtonClick(btn) {
//
//    return;
//
//    var listBtn = $(btn);
//    var folderBtn = listBtn.parent().find('button.form_body_toggle_btn').first();
//
//    if( listBtn.hasClass('glyphicon-list-close') ) {   //list_button = closed
//
//        if( folderBtn.hasClass('glyphicon-folder-close') ) {
//            //folder_button.open()  ///show me what i am trying to see
//            folderBtn.trigger('click');
//            //listBtn.trigger('click');
//        } else {
//            //listBtn.trigger('click'); //list_button.open()
//            var collapsableBody = listBtn.closest('.panel-multi-form').find('.panel-body').first().find('.form-element-holder').first();
//            collapsableBody.collapse('show');
//        }
//
//        //change class
//        listBtn.removeClass('glyphicon-list-close');
//        listBtn.addClass('glyphicon-list-open');
//
//    } else {    //list_button = open
//
//        if( folderBtn.hasClass('glyphicon-folder-close') ) {
//            //folder_button.open()  ///list is already open, but i can not see it - show me what I am trying to see
//            folderBtn.trigger('click'); //folder_button.open()
//        } else {
//            //listBtn.trigger('click'); //list_button.close()
//        }
//
//        //change class
//        listBtn.removeClass('glyphicon-list-open');
//        listBtn.addClass('glyphicon-list-close');
//
//    }
//
//}


//TESTING + button
function addPrototypeField( btn, classname ) {

    //console.log("add field: classname=" + classname);

    var holder = $(btn).closest('.' + classname);

    //get indexes by patientdob and replace __patient__ and __patientdob__
    // id=oleg_orderformbundle_messagetype_patient_0_dob_0_field
    // id=oleg_orderformbundle_messagetype_patient___patient___dob___patientdob___field
    // name=oleg_orderformbundle_messagetype[patient][0][dob][0][field]
    // name=oleg_orderformbundle_messagetype[patient][__patient__][dob][__patientdob__][field]

    ////get previous id
    //var fieldEl = holder.find('.patient-dob-date').first();
    //var fieldId = fieldEl.attr('id');
    //console.log("fieldId=" + fieldId);
    ////var fieldName = fieldEl.attr('name');
    ////console.log("fieldName=" + fieldName);

    //get array of replace indexes
    var indexArr = processPatientHierarchyPrototypeField(classname,holder,"index");

    if( indexArr ) {

        //data-prototype-patientdob
        var collectionHolder = $('#form-prototype-data');
        //console.log("collectionHolder:");
        //console.log(collectionHolder);
        var prototype = collectionHolder.data('prototype-' + classname);
        //console.log("prototype="+prototype);
        if( !prototype ) {
            alert("Prototype not found. classname="+classname);
            return;
        }

        for( var k in indexArr ) {
            if( indexArr.hasOwnProperty(k) ) {
                //alert("Key is " + k + ", value is" + indexArr[k]);
                //console.log("index="+indexArr[k]);
                prototype = prototype.replace(new RegExp("__" + k + "__", 'g'), indexArr[k]);
            }
        }

        //$(holder).append( getForm( name, ids, withDelBtn  ) );
        $(btn).closest('.field-button').before(prototype);

        //init JS for select2 and datepicker
        processPatientHierarchyPrototypeField(classname,holder,"jsinit");

    } else {
        alert(classname+": case not implemented!");
    }
}

//     0          1             2         3    4  5  6   7
// id=oleg_orderformbundle_messagetype_patient_0_dob_0_field
// id=oleg_orderformbundle_patienttype_dob_0_field
// id=oleg_orderformbundle_messagetype_patient___patient___dob___patientdob___field
// name=oleg_orderformbundle_messagetype[patient][0][dob][0][field]
// name=oleg_orderformbundle_messagetype[patient][__patient__][dob][__patientdob__][field]
function processPatientHierarchyPrototypeField( classname, holder, action ) {

    var resArr = [];

    switch(classname) {

        case 'patientdob':

            var target = '.patient-dob-date';
            if( action == "index" ) {
                var nameArr = {patient:'', patientdob:'dob'};
                resArr = getMaxIndexByArrayName(holder, target, nameArr);
            }
            if( action == "jsinit" ) {
                //do nothing for this field
                var fieldEl = holder.find(target).last();
                initDatepicker(fieldEl.closest('.row'));
            }
        break;


        case 'patienttrackerspot':

            var target = '.user-location-name-field';
            if( action == "index" ) {
                var nameArr = {patient:'', spots:''};
                resArr = getMaxIndexByArrayName(holder, target, nameArr);
            }
            if( action == "jsinit" ) {
                //do nothing for this field
                //fieldEl = holder.find('.patient-dob-date').last();
                //initDatepicker(fieldEl.closest('.row'));
                //fieldEl = holder.find('.encountersex-field').last();
                regularCombobox(holder);
                var btnEl = holder.find('.form_body_toggle_btn ').last();
                //btnId='form_body_toggle_patientcontactinfo_1'
                var btnId = btnEl.attr("id");
                //'#form_body_toggle_'+ uid
                //var btnIdArr = btnId.split("form_body_toggle_")
                //var uid = "patientcontactinfo_1";
                var uid = btnId.replace("form_body_toggle_", "");
                bindToggleBtn( uid );
                getComboboxGeneric(holder,'floor',_floors,false);
                getComboboxGeneric(holder,'building',_buildings,false,'');
                getComboboxGeneric(holder,'city',_cities,false);
            }
        break;

        case 'encounterpatlastname':
            var target = '.encounter-lastName';
            if( action == "index" ) {
                var nameArr = {patient:'', encounter:'', encounterpatlastname:'patlastname'};
                resArr = getMaxIndexByArrayName(holder, target, nameArr);
            }
        break;

        case 'encounterpatfirstname':
            var target = '.encounter-firstName';
            if( action == "index" ) {
                var nameArr = {patient:'', encounter:'', encounterpatfirstname:'patfirstname'};
                resArr = getMaxIndexByArrayName(holder, target, nameArr);
            }
        break;

        case 'encounterpatmiddlename':
            var target = '.encounter-middleName';
            if( action == "index" ) {
                var nameArr = {patient:'', encounter:'', encounterpatmiddlename:'patmiddlename'};
                resArr = getMaxIndexByArrayName(holder, target, nameArr);
            }
        break;

        case 'encounterpatsuffix':
            var target = '.encounter-suffix';
            if( action == "index" ) {
                var nameArr = {patient:'', encounter:'', encounterpatsuffix:'patsuffix'};
                resArr = getMaxIndexByArrayName(holder, target, nameArr);
            }
        break;

        case 'encounterpatsex':
            var target = '.encountersex-field';
            if( action == "index" ) {
                var nameArr = {patient:'', encounter:'', encounterpatsex:'patsex'};
                resArr = getMaxIndexByArrayName(holder, target, nameArr);
            }
            if( action == "jsinit" ) {
                //getComboboxProcedure(fieldEl.parent());
                var fieldEl = holder.find(target).last();
                specificRegularCombobox(fieldEl);
            }
        break;

        case 'encounterdate':
            var target = '.encounter-date';
            if( action == "index" ) {
                //id=oleg_orderformbundle_patienttype_encounter_0_date_0_field
                var nameArr = {patient:'', encounter:'', encounterdate:'date'};
                resArr = getMaxIndexByArrayName(holder, target, nameArr);
            }
            if( action == "jsinit" ) {
                var fieldEl = holder.find(target).last();
                initDatepicker(fieldEl.closest('.row'));
            }
        break;

        case 'encounterpatage':
            var target = '.encounterage-field';
            if( action == "index" ) {
                //oleg_orderformbundle_patienttype_encounter_0_patage_0_field
                var nameArr = {patient:'', encounter:'', encounterpatage:'patage'};
                resArr = getMaxIndexByArrayName(holder, target, nameArr);
            }
        break;

        case 'encounterpathistory':
            var target = '.encounterhistory-field';
            if( action == "index" ) {
                //oleg_orderformbundle_patienttype_encounter_0_pathistory_0_field
                var nameArr = {patient:'', encounter:'', encounterpathistory:'pathistory'};
                resArr = getMaxIndexByArrayName(holder, target, nameArr);
            }
            if( action == "jsinit" ) {
                var fieldEl = holder.find(target).last();
                expandTextarea(fieldEl.closest('.row'));
            }
        break;

        case 'procedurename':
            //var fieldEl = holder.find('.ajax-combobox-procedure').first();
            var target = '.ajax-combobox-procedure';
            if( action == "index" ) {
                //id=oleg_orderformbundle_patienttype_encounter_0_procedure_0_name_0_field
                var nameArr = {patient:'', encounter:'', procedure:'', procedurename:'name'};
                resArr = getMaxIndexByArrayName(holder,target,nameArr);
            }
            if( action == "jsinit" ) {
                //specificRegularCombobox(fieldEl);
                //regularCombobox(holder);
                //console.log("cycle="+cycle);
                //console.log("urlBase="+urlBase);
                //initComboboxJs(null, fieldEl.parent());
                var fieldEl = holder.find(target).last();
                getComboboxProcedure(fieldEl.parent());
            }
        break;

        case 'proceduredate':
            var target = '.procedure-date';
            if( action == "index" ) {
                var nameArr = {patient:'', encounter:'', procedure:'', proceduredate:'date'};
                resArr = getMaxIndexByArrayName(holder,target,nameArr);
            }
            if( action == "jsinit" ) {
                var fieldEl = holder.find(target).last();
                initDatepicker(fieldEl.closest('.row'));
            }
            break;

        case 'accessionaccessiondate':

            //var fieldEl = holder.find('.accessionaccessiondate').first();
            var target = '.accessionaccessiondate';
            if( action == "index" ) {
                //id=oleg_orderformbundle_patienttype_encounter_0_procedure_0_accession_0_accessionDate_0_field
                var nameArr = {patient:'', encounter:'', procedure:'', 'accession':'', accessionaccessiondate:'accessionDate'};
                resArr = getMaxIndexByArrayName(holder,target,nameArr);
            }
            if( action == "jsinit" ) {
                var fieldEl = holder.find(target).last();
                initDatepicker(fieldEl.closest('.row'));
            }
        break;

        default:
            return null;
    }

    //for all fields
    if( action == "jsinit" ) {
        //init select box
        var statusFieldEl = holder.find('select.other-status').last();
        //console.log("statusFieldEl="+statusFieldEl.attr("id"));
        specificRegularCombobox(statusFieldEl);

        //set status to invalid
        statusFieldEl.select2('val','invalid');

        //attach event listener to this combobox to set value
        listenerComboboxStatusField(statusFieldEl); //,holder,'.other-status');
    }

    return resArr;
}
function getMaxIndexByArrayName(holder,target,nameArr) {
    var resArr = [];

    var nameArrLength = getKeyValueArrayLength(nameArr);
    //console.log(target+": nameArrLength="+nameArrLength);
    var count = 1;
    for( var classname in nameArr ) {
        if( nameArr.hasOwnProperty(classname) ) {
            //alert("Key is " + classname + ", value is" + nameArr[classname]);
            var fieldIdName = classname;
            if( nameArr[classname] ) {
                fieldIdName = nameArr[classname];
            }
            //console.log("fieldIdName="+fieldIdName);

            var last = false;
            if (count == nameArrLength) {
                last = true;
            }

            var maxIndex = getMaxIndexByName(holder,target,fieldIdName,last);
            if( maxIndex != null ) {
                //increament the last index
                if (count == nameArrLength) {
                    maxIndex = maxIndex + 1;
                    //console.log(fieldIdName + ": increament maxIndex=" + maxIndex);
                }
                resArr[classname] = maxIndex;
            }
            count++;
        }
    }

    //for( var i = 0; i < nameArr.length; i++ ) {
    //    var maxIndex = getMaxIndexByName(holder,target,nameArr[i]);
    //    //increament the last index
    //    if( i+1 == nameArr.length ) {
    //        maxIndex = maxIndex + 1;
    //        console.log(nameArr[i]+": increament maxIndex="+maxIndex);
    //    }
    //    resArr[nameArr[i]] = maxIndex;
    //}

    return resArr;
}
function getKeyValueArrayLength(arr) {
    // http://stackoverflow.com/a/6700/11236
    var size = 0, key;
    for(key in arr) {
        if(arr.hasOwnProperty(key)) {
            size++;
        }
    }
    return size;
};
//find max index the name
//id=oleg_orderformbundle_patienttype_encounter_0_patage_2_field
//name="patage" => index is the next value => index=2
function getMaxIndexByName(holder,target,name,last) {
    var maxIndex = 0;
    var targetElements = holder.find(target);
    if( targetElements.length == 0 ) {
        //alert('ERROR getMaxIndexByName: target elements are not found. target='+target);
        return maxIndex;
    }
    targetElements.each( function(){
        var fieldId = $(this).attr('id');
        //console.log(name+": fieldId="+fieldId);
        if( !fieldId ) {
            alert('ERROR getMaxIndexByName: fieldId is not defined');
            return maxIndex;
        }
        var splitter = "_"+name+"_"; //_patage_
        //console.log("splitter="+splitter);
        var idsArr = fieldId.split(splitter);
        if( idsArr.length == 2 ) { //must be 2
            var secondPart = idsArr[1].split("_");
            //secondPart='2_field'
            if( secondPart[0] == null ) {
                alert('ERROR getMaxIndexByName: index cannot be calculated. secondPart[0]='+secondPart[0]);
                return maxIndex;
            }
            var index = parseInt(secondPart[0]); //2
            //console.log("index="+index);
            if( index == null ) {
                alert('ERROR getMaxIndexByName: index cannot be calculated. index='+index);
                return maxIndex;
            }
            if( index > maxIndex ) {
                maxIndex = index;
            }
            //console.log(fieldId+": maxIndex="+maxIndex);
        } else {
            var msg = 'getMaxIndexByName: id array should have exactly 2 parts. length='+idsArr.length+", split by "+splitter;
            if( last ) {
                alert("ERROR: "+msg);
            }
            console.log("WARNING: "+msg);
            return null;
        }
    });
    return maxIndex;
}

function deletePrototypeField( btn, classname ) {
    //console.log("delete field: classname=" + classname);

    //var holder = $(btn).closest('.' + classname);
    $(btn).closest('.row').remove();

}

function listenerComboboxStatusField( statusFieldEl, holder, target ) {

    if( holder && target ) {
        statusFieldEl = holder.find(target);
    }

    statusFieldEl.on("change", function(e) {
        var statusValue = $(this).select2('val');
        var statusId = $(this).attr('id');
        //console.log("status change to "+statusValue);

        if( statusValue == "valid" ) {
           var otherValue = "invalid";
        } else {
            var otherValue = "valid";
            alert("Please make sure to set at least one valid field.");
            return;
        }

        //1) get holder
        var holder = $(this).closest('.row').parent();
        printF(holder, "Holder:");

        //2) change status for all other fields in the holder
        holder.find('select.other-status').each(function (e) {
            //console.log("status change to " + otherValue + ": "+statusId+"?="+$(this).attr('id'));
            if( statusId != $(this).attr('id') ) {
                $(this).select2('val',otherValue);
            }
        });

    });
}
