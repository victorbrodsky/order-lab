/**
 * User: oli2002
 * Date: 8/14/13
 * Time: 12:36 PM
 */




//prevent exit modified form
function windowCloseAlert() {

    if( cycle == "show" ) {
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

            return "The changes you have made will not be saved if you navigate away from this page.";
        } else {
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

    //tooltip
    $(".element-with-tooltip").tooltip();

    //attach dob-encounter date calculation
    setPatientAndProcedureAgeListener();

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
function addSameForm( name, patientid, procedureid, accessionid, partid, blockid, slideid, scanid, stainid ) {

    var uid = patientid+"_"+procedureid+"_"+accessionid+"_"+partid+"_"+blockid+"_"+slideid+"_"+scanid+"_"+stainid;  //+"_"+diffdiag+"_"+specstain+"_"+image;
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
    var btnids = getIds(name, countTotal, patientid, procedureid, accessionid, partid, blockid, slideid, scanid, stainid);
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
    fieldInputMask( newHolder ); //setDefaultMask(btnObj);
    //comboboxes init
    initComboboxJs(idsNext, newHolder);
    //file upload
    initFileUpload(newHolder);

    //setDefaultMask(btnObj);

    //create children nested forms
    //var nameArray = ['patient', 'procedure', 'accession', 'part', 'block', 'slide', 'stain_scan' ];
    var nameArray = ['patient', 'procedure', 'part', 'block', 'slide' ];
    var length = nameArray.length
    var index = nameArray.indexOf(name);
    //console.log("index="+index+" len="+length);
    var parentName = name;
    for (var i = index+1; i < length; i++) {
        //console.log("=> name="+nameArray[i]);

        if( nameArray[i] == 'stain_scan' ) {
            addChildForms( parentName, idsNext, 'stain', nameArray[i-1], patientid, procedureid, accessionid, partid, blockid, slideid, scanid, stainid );
            addChildForms( parentName, idsNext, 'scan', nameArray[i-1], patientid, procedureid, accessionid, partid, blockid, slideid, scanid, stainid );
        } else {
            addChildForms( parentName, idsNext, nameArray[i], nameArray[i-1], patientid, procedureid, accessionid, partid, blockid, slideid, scanid, stainid );
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
function addChildForms( parentName, parentIds, name, prevName, patientid, procedureid, accessionid, partid, blockid, slideid, scanid, stainid ) {

//    //attach to previous object (prevName)
//    var btnids = getIds( parentName, null, patientid, procedureid, accessionid, partid, blockid, slideid, scanid, stainid );
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
    var formbody = getFormBody( name, ids[0], ids[1], ids[2], ids[3], ids[4], ids[5], ids[6], ids[7] );

    //console.log("getForm: "+name+"_"+", id="+id+", ids="+ids+', idsm='+idsm+", withDelBtn="+withDelBtn);

    if( name == "scan" || name == "stain" ) {
        var addbtn = "";
        var deletebtn = "";
        //var itemCount = (id+2);
    } else {
        var addbtn = getHeaderAddBtn( name, ids );
        var deletebtn = getHeaderDeleteBtn( name, ids, deleteStr );
    }

    if( !withDelBtn ) {
        deletebtn = "";
    }

    //get itemCount from partialId
    var itemCount = getIdByName(name,ids) + 1;
    //console.log('itemCount='+itemCount);

    var title = name;
    if( name == "procedure" ) {
        title = "accession";
    }

    var formhtml =
        '<div id="formpanel_' +name + '_' + idsu + '" class="panel panel-'+name+' panel-multi-form">' +
            '<div class="panel-heading">' +

                '<button id="form_body_toggle_'+ name + '_' + idsu +'" type="button"' +
                    'class="btn btn-default btn-xs form_body_toggle_btn glyphicon glyphicon-folder-open pull-left"' +
                    'data-toggle="collapse" data-target="#form_body_'+name+'_'+idsu+'">'+
                '</button>'+

//            '<button id="form_body_toggle_'+ name + '_' + idsu +'" type="button" class="btn btn-default btn-xs form_body_toggle_btn glyphicon glyphicon-folder-open black" data-toggle="collapse" data-target="#form_body_'+name+'_'+idsu+'"></button>' +
//            '&nbsp;' +
//            '<div class="element-title">' + capitaliseFirstLetter(title) + ' ' + itemCount + '</div>' +
                '<h4 class="panel-title element-title">' + capitaliseFirstLetter(title) + ' ' + itemCount + '</h4>' +
                '<div class="form-btn-options">' +
                    addbtn +
                    deletebtn +
                '</div>' +
                '<div class="clearfix"></div>' +
            '</div>' +  //panel-heading
            '<div id="form_body_' + name + '_' + idsu + '" class="panel-body collapse in">' +
                formbody +
            '</div>' +
            '</div>';

    return formhtml;
}

function getFormBody( name, patientid, procedureid, accessionid, partid, blockid, slideid, scanid, stainid ) {

    //console.log("name="+name+",patient="+patientid+ ",procedure="+procedureid+",accession="+accessionid+",part="+partid+",block="+blockid+",slide="+slideid);

    var collectionHolder =  $('#form-prototype-data');

    // Get the data-prototype explained earlier
    var prototype = collectionHolder.data('prototype-'+name);
    //console.log("prototype="+prototype);

    //console.log("before replace patient...");
    var newForm = prototype.replace(/__patient__/g, patientid);
    //console.log("before replace procedure... NewForm="+newForm);
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
    var newOriginId = "origin_option_multi_patient_"+patientid+"_procedure_"+procedureid+"_accession_"+accessionid+"_part_"+partid+"_origintag";
    newForm = newForm.replace(/origin_option_multi_patient_0_procedure_0_accession_0_part_0_origintag/g, newOriginId);

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

function getIds( name, nextIndex, patientid, procedureid, accessionid, partid, blockid, slideid, scanid, stainid ) {
    var id = 0;
    var nextName = "";

    var patientidm = patientid;
    var procedureidm = procedureid;
    var accessionidm = accessionid;
    var partidm = partid;
    var blockidm = blockid;
    var slideidm = slideid;
    var scaniddm = scanid;
    var stainidm = stainid;

    var patientidp = patientid;
    var procedureidp = procedureid;
    var accessionidp = accessionid;
    var partidp = partid;
    var blockidp = blockid;
    var slideidp = slideid;
    var scanidp = scanid;
    var stainidp = stainid;

    var patientNext = patientid;
    var procedureNext = procedureid;
    var accessionNext = accessionid;
    var partNext = partid;
    var blockNext = blockid;
    var slideNext = slideid;
    var scanNext = scanid;
    var stainNext = stainid;

    var partialId = "";

    var orig = [patientid, procedureid, accessionid, partid, blockid, slideid, scanid, stainid];

    switch(name)
    {
        case "patient":
            patientidm = patientid-1;
            patientid++;
            patientidp = patientid+1;
            id = patientid;
            nextName = "procedure";
            partialId = "";
            patientNext = nextIndex;
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
            partialId = patientid+"_"+procedureid;
            accessionNext = nextIndex;
            break;
        case "part":
            partidm = partid-1;
            partid++;
            partidp = partid+1;
            id = partid;
            nextName = "block";
            partialId = patientid+"_"+procedureid+"_"+accessionid;
            partNext = nextIndex;
            break;
        case "block":
            blockidm = blockid-1;
            blockid++;
            blockidp = blockid+1;
            id = blockid;
            nextName = "slide";
            partialId = patientid+"_"+procedureid+"_"+accessionid+"_"+partid;
            blockNext = nextIndex;
            break;
        case "slide":
            slideidm = slideid-1;
            slideid++;
            slideidp = slideid+1;
            id = slideid;
            nextName = "";
            partialId = patientid+"_"+procedureid+"_"+accessionid+"_"+partid+"_"+blockid;
            slideNext = nextIndex;
            break;
        case "scan":
            scaniddm = scanid-1;
            scanid++;
            scanidp = scanid+1;
            id = scanid;
            nextName = "";
            partialId = patientid+"_"+procedureid+"_"+accessionid+"_"+partid+"_"+blockid;
            scanNext = nextIndex;
            break;
        case "stain":
            stainidm = stainid-1;
            stainid++;
            stainidp = stainid+1;
            id = stainid;
            nextName = "";
            partialId = patientid+"_"+procedureid+"_"+accessionid+"_"+partid+"_"+blockid;
            stainNext = nextIndex;
            break;
        default:
            id = 0;
    }

    var idsArray = [patientid, procedureid, accessionid, partid, blockid, slideid, scanid, stainid];
    var idsArrayM = [patientidm, procedureidm, accessionidm, partidm, blockidm, slideidm, scaniddm, stainidm];
    var idsArrayP = [patientidp, procedureidp, accessionidp, partidp, blockidp, slideidp, scanidp, stainidp];
    var idsArrayNext = [patientNext, procedureNext, accessionNext, partNext, blockNext, slideNext, scanNext, stainidp];

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
        case "procedure":
            id = ids[1];
            break;
        case "accession":
            id = ids[2];
            break;
        case "part":
            id = ids[3];
            break;
        case "block":
            id = ids[4];
            break;
        case "slide":
            id = ids[5];
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
        var className = $(this).attr("class");
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

function setNavBar() {

    $('ul.li').removeClass('active');

    var full = window.location.pathname;

    var id = 'scanorderhome';

    if( full.indexOf("scan-order/multi-slide") !== -1 ) {
        id = 'placescanorder';
    }

    if( full.indexOf("scan-order/one-slide") !== -1 ) {
        id = 'placescanorder';
    }

    if( full.indexOf("scan-order/multi-slide-table-view") !== -1 ) {
        id = 'placescanorder';
    }

    if( full.indexOf("scan/slide-return-request") !== -1 ) {
        id = 'placescanorder';
    }

    if( full.indexOf("my-scan-orders") !== -1 ) {
        id = 'myrequesthistory';
    }

    if( full.indexOf("my-slide-return-requests") !== -1 ) {
        id = 'myrequesthistory';
    }

    //Admin
    if( full.indexOf("/user/listusers") !== -1 ) {
        id = 'admin';
    }
    if( full.indexOf("/admin/") !== -1 ) {
        id = 'admin';
    }
    if( full.indexOf("/incoming-scan-orders") !== -1 ) {
        id = 'admin';
    }
    if( full.indexOf("/incoming-slide-return-requests") !== -1 ) {
        id = 'admin';
    }
    if( full.indexOf("/access-requests") !== -1 ) {
        id = 'admin';
    }
    if( full.indexOf("/account-requests") !== -1 ) {
        id = 'admin';
    }
    if( full.indexOf("/listusers") !== -1 ) {
        id = 'admin';
    }
    if( full.indexOf("/users/") !== -1 ) {
        id = 'admin';
    }
    if( full.indexOf("/event-log") !== -1 ) {
        id = 'admin';
    }
    if( full.indexOf("/settings") !== -1 ) {
        id = 'admin';
    }
    if( full.indexOf("/user-directory") !== -1 ) {
        id = 'admin';
    }
    
    if( full.indexOf("/users/") !== -1 || full.indexOf("/edit-user-profile/") !== -1 ) {
        if( $('#nav-bar-admin').length > 0 ) {
           id = 'admin';
        } else {
           id = 'user';
        }
    }

    //console.log("id="+id);
    //console.info("full="+window.location.pathname+", id="+id + " ?="+full.indexOf("multi/clinical"));

    $('#nav-bar-'+id).addClass('active');
}


function priorityOption() {

    if( $('#priority_option').is(':visible') ) {
        $('#priority_option').collapse('hide');
    }

    $('#oleg_orderformbundle_orderinfotype_priority').change(function(e) {
        //e.preventDefault();
        //$('#priority_option').collapse('toggle');
        var checked = $('#oleg_orderformbundle_orderinfotype_priority').find('input[type=radio]:checked').val();
        if( checked == 'Stat' ) {
            var param = 'show';
        } else {
            var param = 'hide';
        }
        $('#priority_option').collapse(param);
    });

    var checked = $('#oleg_orderformbundle_orderinfotype_priority').find('input[type=radio]:checked').val();
    //console.log("checked="+checked);
    if( checked == 'Stat' ) {
        $('#priority_option').collapse('show');
    }
}

function purposeOption() {

    if( $('#purpose_option').is(':visible') ) {
        $('#purpose_option').collapse('hide');
    }

    $('#oleg_orderformbundle_orderinfotype_purpose').change(function(e) {
        //e.preventDefault();
        //$('#purpose_option').collapse('toggle');
        var checked = $('#oleg_orderformbundle_orderinfotype_purpose').find('input[type=radio]:checked').val();
        if( checked == 'For External Use (Invoice Fund Number)' ) {
            var param = 'show';
        } else {
            var param = 'hide';
        }
        $('#purpose_option').collapse(param);
    });

    var checked = $('#oleg_orderformbundle_orderinfotype_purpose').find('input[type=radio]:checked').val();
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

    if( cycle != "show" ) {

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


function setResearch() {
    //get value of project title field on change
    $('.combobox-research-projectTitle').on("change", function(e) {
        //console.log('listener: project Title changed');
        getSetTitle();
        getOptionalUserResearch();
    });
}

function setEducational() {
    //get value of project title field on change
    $('.combobox-educational-courseTitle').on("change", function(e) {
        //console.log('listener: course Title changed');
        getLessonTitle();
        getOptionalUserEducational();
    });
}




