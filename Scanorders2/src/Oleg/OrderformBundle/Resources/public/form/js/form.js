/**
 * User: oli2002
 * Date: 8/14/13
 * Time: 12:36 PM
 */

$(document).ready(function() {

    setNavBar();

    initAdd();

    customCombobox();

    originOptionMulti( new Array("0","0","0","0") );
    primaryOrganOptionMulti( new Array("0","0","0","0") );

    //toggle check "Neoplastic": oleg_orderformbundle_orderinfotype_patient_0_specimen_0_accession_0_part_0_diseaseType_0
    checkValidate();
    checkValidatePrimaryOrgan();

    //hide buttons
    $("#orderinfo").hide();

    $("#optional_button").hide();

    $('#next_button').on('click', function(event) {        
       $("#next_button").hide();
       $("#optional_button").show();
    });

    //priority and disease type options
    priorityOption();
    originOption();
    primaryOrganOption();
   
    //tab
    $('#optional_param_tab a').click(function (e) {
        e.preventDefault();
        $(this).tab('show');
    })

    //multy form toggle button
    $('.form_body_toggle_btn').on('click', function(e) {
        var className = $(this).attr("class");
        var id = this.id;
        if( className == 'form_body_toggle_btn glyphicon glyphicon-folder-open') {
            $("#"+id).removeClass('glyphicon glyphicon-folder-open');
            $("#"+id).addClass('glyphicon glyphicon-folder-close');
        } else {
            $("#"+id).removeClass('glyphicon glyphicon-folder-close');
            $("#"+id).addClass('glyphicon glyphicon-folder-open');
        }
    });

    //multy form delete button
    $('.delete_form_btn').on('click', function(e) {
        //alert("on click");
        // prevent the link from creating a "#" on the URL
        //e.preventDefault();
        //alert( this.id );
        var id = this.id;
        //$('#formpanel_'+id).remove();
        deleteItem(id);
    });

});

//add all element to listeners again, the same as in ready
function initAdd() {

    expandTextarea();

//    $(".combobox").combobox();
    regularCombobox();

    initDatepicker();

}

//confirm delete
function deleteItem(id) {
    if( confirm("Are you sure?") ) {
        //var id = this.id;
        $('#formpanel_'+id).remove();
        //TODO: append new "Add" button to the same object type if it's not exists
    }
    return false;
}

//main input form from html button: add parent form (always type='multi')
function addSameForm( name, patientid, procedureid, accessionid, partid, blockid, slideid, scanid, stainid ) {

    var uid = patientid+"_"+procedureid+"_"+accessionid+"_"+partid+"_"+blockid+"_"+slideid+"_"+scanid+"_"+stainid;  //+"_"+diffdiag+"_"+specstain+"_"+image;

    //alert("addSameForm="+uid);

    //place the form in the html page
    var holder = "#formpanel_"+name+"_"+uid;

    //prepare form ids and pass it as array
    //increment by 1 current object id
    var btnids = getIds(name, patientid, procedureid, accessionid, partid, blockid, slideid, scanid, stainid);
    var id = btnids['id'];
    var idsorig = btnids['orig'];
    var ids = btnids['ids'];
    var idsm = btnids['idsm'];

    //attach form
    $(holder).after( getForm( name, id, idsorig, ids, idsm ) );
    
    if( name == "part" ) {
        addDiffdiagFieldFirstTime( name, ids );
    }
    if( name == "slide" ) {
        //addCollFieldFirstTime( "relevantScans", ids );
        addDiffdiagFieldFirstTime( name, ids );
        addCollFieldFirstTime( "specialStains", ids );
    }

    //bind listener to the toggle button
    bindToggleBtn( name + '_' + ids.join("_") );
    bindDeleteBtn( name + '_' + ids.join("_") );
    originOptionMulti(ids);
    primaryOrganOptionMulti(ids);
    initComboboxJs(ids);
    initAdd();

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
            addChildForms( parentName, 'stain', nameArray[i-1], patientid, procedureid, accessionid, partid, blockid, slideid, scanid, stainid );
            addChildForms( parentName, 'scan', nameArray[i-1], patientid, procedureid, accessionid, partid, blockid, slideid, scanid, stainid );
        } else {
            addChildForms( parentName, nameArray[i], nameArray[i-1], patientid, procedureid, accessionid, partid, blockid, slideid, scanid, stainid );
        }

    }

    //remove previous form add button only for parent object
    var uid = idsorig.join("_");
    //console.log("remove="+'#form_add_btn_'+name+'_'+uid);
    $('#form_add_btn_'+name+'_'+uid).remove();
}

//add children forms triggered by parent form
function addChildForms( parentName, name, prevName, patientid, procedureid, accessionid, partid, blockid, slideid, scanid, stainid ) {

    //idsu: +1 for the parent object (parentName)
    //attach to previous object (prevName)
    var btnids = getIds( parentName, patientid, procedureid, accessionid, partid, blockid, slideid, scanid, stainid );
    var idsorig = btnids['orig'];
    var ids = btnids['ids'];
    var idsm = btnids['idsm'];
    var id = btnids['id'];
    id = id - 1;
    var idsu = ids.join("_");

    var uid = prevName+"_"+idsu;
    var holder = "#form_body_"+uid;
    //console.log(name+": add childs to="+holder+" uid="+idsu);

    //attach children form
    $(holder).append( getForm( name, id, idsorig, ids, idsm  ) );
    if( name == "part" ) {
        addDiffdiagFieldFirstTime( name, ids );
    }

    if( name == "slide" ) {
        //addCollFieldFirstTime( "relevantScans", ids );
        addDiffdiagFieldFirstTime( name, ids );
        addCollFieldFirstTime( "specialStains", ids );
    }
    
    bindToggleBtn( name + '_' + ids.join("_") );
    bindDeleteBtn( name + '_' + ids.join("_") );
    originOptionMulti(ids);
    primaryOrganOptionMulti(ids);
    initComboboxJs(ids);
    initAdd();
}

//input: current form ids
function getForm( name, id, idsorig, ids, idsm ) {

    //console.log("getForm: "+name+"_"+", id="+id+", ids="+ids+', idsm='+idsm);

    var idsu = ids.join("_");
    var idsc = ids.join(",");

    //increment by 1 current object id
    var formbody = getFormBody( name, idsu, ids[0], ids[1], ids[2], ids[3], ids[4], ids[5], ids[6], ids[7] );


    if( name == "scan" || name == "stain" ) {
        var addbtn = "";
        var deletebtn = "";
        var itemCount = (id+2);
    } else {
        var addbtn = '<button id="form_add_btn_' + name + '_' + idsu + '" type="button" class="btn btn-xs btn_margin" onclick="addSameForm(\'' + name + '\''+ ',' + idsc + ')">Add</button>';
        var deletebtn = ' <button id="delete_form_btn_'+name+'_'+idsu+'" type="button" class="delete_form_btn btn btn-danger btn_margin btn-xs">Delete</button>';
        var itemCount = (id+1);
    }

    var title = name;
    if( name == "procedure" ) {
        title = "accession";
    }

    var formhtml =
        '<div id="formpanel_' +name + '_' + idsu + '" class="panel panel-'+name+'">' +
            '<div class="panel-heading" align="left">' +
            '<div id="form_body_toggle_'+ name + '_' + idsu +'" class="form_body_toggle_btn glyphicon glyphicon-folder-open" data-toggle="collapse" data-target="#form_body_'+name+'_'+idsu+'"></div>' +
            '&nbsp;' + capitaliseFirstLetter(title) + ' ' + itemCount +
            '<div class="form-btn-options">' +
            addbtn +
            deletebtn +
            '</div>' +
            '</div>' +
            '<div id="form_body_' + name + '_' + idsu + '" class="panel-body collapse in">' + formbody + '</div>' +
            '</div>';

    return formhtml;
}

function getFormBody( name, idsu, patientid, procedureid, accessionid, partid, blockid, slideid, scanid, stainid ) {

    //console.log("name="+name+",patient="+patientid+ ",specimen="+procedureid+",accession="+accessionid+",part="+partid+",block="+blockid+",slide="+slideid);

    var collectionHolder =  $('#form-prototype-data');

    // Get the data-prototype explained earlier
    var prototype = collectionHolder.data('prototype-'+name);
    //console.log("prototype="+prototype);

    //console.log("before replace patient...");
    var newForm = prototype.replace(/__patient__/g, patientid);
    //console.log("before replace specimen... NewForm="+newForm);
    newForm = newForm.replace(/__specimen__/g, procedureid);
    newForm = newForm.replace(/__accession__/g, accessionid);
    newForm = newForm.replace(/__part__/g, partid);
    newForm = newForm.replace(/__block__/g, blockid);
    newForm = newForm.replace(/__slide__/g, slideid);
    newForm = newForm.replace(/__scan__/g, scanid);
    newForm = newForm.replace(/__stain__/g, stainid);

    newForm = newForm.replace(/__clinicalHistory__/g, 0);   //add only one clinical history to the new form
    newForm = newForm.replace(/__paper__/g, 0);   //add only one clinical history to the new form

    //replace origin_option_multi_patient_0_specimen_0_accession_0_part_0_origintag with correct ids
    //origin_option_multi_patient_0_specimen_0_accession_0_part_0_origintag
    var newOriginId = "origin_option_multi_patient_"+patientid+"_specimen_"+procedureid+"_accession_"+accessionid+"_part_"+partid+"_origintag";
    newForm = newForm.replace(/origin_option_multi_patient_0_specimen_0_accession_0_part_0_origintag/g, newOriginId);

    //console.log("newForm="+newForm);

    return newForm;
}

//Helpers
function capitaliseFirstLetter(string)
{
    return string.charAt(0).toUpperCase() + string.slice(1);
}

function getIds( name, patientid, procedureid, accessionid, partid, blockid, slideid, scanid, stainid ) {
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

    var orig = [patientid, procedureid, accessionid, partid, blockid, slideid, scanid, stainid];

    switch(name)
    {
        case "patient":
            patientidm = patientid-1;
            patientid++;
            id = patientid;
            nextName = "procedure";
            break;
        case "procedure":
            procedureidm = procedureid-1;
            procedureid++;
            id = procedureid;
            nextName = "accession";
            break;
        case "accession":
            accessionidm = accessionid-1;
            accessionid++;
            id = accessionid;
            nextName = "part";
            break;
        case "part":
            partidm = partid-1;
            partid++;
            id = partid;
            nextName = "block";
            break;
        case "block":
            blockidm = blockid-1;
            blockid++;
            id = blockid;
            nextName = "slide";
            break;
        case "slide":
            slideidm = slideid-1;
            slideid++;
            id = slideid;
            nextName = "";
            break;
        case "scan":
            scaniddm = scanid-1;
            scanid++;
            id = scanid;
            nextName = "";
            break;
        case "stain":
            stainidm = stainid-1;
            stainid++;
            id = stainid;
            nextName = "";
            break;
        default:
            id = 0;
    }

    var idsArray = [patientid, procedureid, accessionid, partid, blockid, slideid, scanid, stainid];
    var idsArrayM = [patientidm, procedureidm, accessionidm, partidm, blockidm, slideidm, scaniddm, stainidm];

    var res_array = {
        'id' : id,
        'orig' : orig,
        'ids' : idsArray,
        'idsm' : idsArrayM,
        'nextName' : nextName
    };

    return res_array;
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

        if( className == 'form_body_toggle_btn glyphicon glyphicon-folder-open') {
            $("#"+id).removeClass('glyphicon glyphicon-folder-open');
            $("#"+id).addClass('glyphicon glyphicon-folder-close');
        } else {
            $("#"+id).removeClass('glyphicon glyphicon-folder-close');
            $("#"+id).addClass('glyphicon glyphicon-folder-open');
        }
    });
}
//multy form delete
function bindDeleteBtn( uid ) {
    //console.log('delete uid='+uid);
    $('#delete_form_btn_'+uid).on('click', function(e) {
        var id = uid;
        //alert("clicked delete!");
        deleteItem(id);
    });
}

function setNavBar() {

    var index_arr = window.location.pathname.split('/');

    $('ul.li').removeClass('active');

    var full = window.location.pathname;

    var id = 0;

    if( full.indexOf("/index") !== -1 || full.indexOf("/multi/") !== -1 ) {
        id = 4;
    }

    if( full.indexOf("multi/clinical") !== -1 ) {
        id = 1;
    }

    if ( full.indexOf("multi/educational") !== -1 ) {
        id = 2;
    }

    if( full.indexOf("multi/research") !== -1 ) {
        id = 3;
    }

    if( full.indexOf("login") !== -1 ) {
        id = 5;
    }

    //console.info("full="+window.location.pathname+", id="+id + " ?="+full.indexOf("multi/clinical"));

    $('#'+id).addClass('active');
}


function priorityOption() {
    $('#priority_option').collapse({
        toggle: false
    })
    $('#oleg_orderformbundle_orderinfotype_priority').change(function(e) {
        e.preventDefault();
        $('#priority_option').collapse('toggle');
    });

    var checked = $('form input[type=radio]:checked').val();
    if( checked == 1 ) {
        $('#priority_option').collapse('toggle');
    }
}

//use for new: add listeners for disease type holder for Single Form
function originOption() {

    var holder = "#origin_option";

    $(holder).collapse({
        toggle: false
    })

    $('#oleg_orderformbundle_parttype_diseaseType_0').on('click', function(e) {
        //console.log("1 open!!!!!!!!!!!!!!");
        $(holder).collapse('show');
    });
    
    $('#oleg_orderformbundle_parttype_diseaseType_1').on('click', function(e) {
        //console.log("1 close?????????????????");
        if( $(holder).is(':visible') ) {
            $(holder).collapse('hide');
        }
    });

    $('#oleg_orderformbundle_parttype_diseaseType_placeholder').on('click', function(e) {
        //console.log("1 close?????????????????");
        if( $(holder).is(':visible') ) {
            $(holder).collapse('hide');
        }
    });

    var checked = $('form input[type=radio]:checked').val();
    if( checked == 1 ) {
        $(holder).collapse('toggle');
    }

}

//use for new: add listeners for disease type holder for Multy Form
function originOptionMulti( ids ) { //patient, specimen, accession, part ) {

//    var uid = "";   //'patient_'+patient+'_specimen_'+specimen+'_accession_'+accession+'_part_'+part;
//    var holder = "";    //'#origin_option_multi_'+uid;

    var patient = ids[0];
    var specimen = ids[1];
    var accession = ids[2];
    var part = ids[3];

    var uid = 'patient_'+patient+'_specimen_'+specimen+'_accession_'+accession+'_part_'+part;
    var holder = '#origin_option_multi_'+uid+'_origintag';
    //console.log("on change:"+'#oleg_orderformbundle_orderinfotype_'+uid+'_diseaseType_0');

    //var curid = "";

    //id of Neoplastic radio button: oleg_orderformbundle_orderinfotype_patient_0_specimen_0_accession_0_part_0_diseaseType_0

    //set not required
    //$('#oleg_orderformbundle_orderinfotype_'+uid+'_diseaseType').attr('required', false);

    $('#oleg_orderformbundle_orderinfotype_'+uid+'_diseaseType').change(function(e) {
//    $('div[id^="oleg_orderformbundle_orderinfotype_"]').change(function(e) {
        var curid = $(this).attr('id');
        //console.log("click id="+curid);

        //oleg_orderformbundle_orderinfotype_patient_0_specimen_0_accession_0_part_0_diseaseType
        var arr1 = curid.split("oleg_orderformbundle_orderinfotype_");
        //patient_0_specimen_0_accession_0_part_0_diseaseType
        var arr2 = arr1[1].split("_");
        //get ids
        var patient = arr2[1];
        var specimen = arr2[3];
        var accession = arr2[5];
        var part = arr2[7];

        uid = 'patient_'+patient+'_specimen_'+specimen+'_accession_'+accession+'_part_'+part;
        holder = '#origin_option_multi_'+uid+'_origintag';
        //console.log(holder);

        e.preventDefault();

        //toggle if Neoplastic is choosen id = *diseaseType_0

        //var neoplasticId = "#oleg_orderformbundle_orderinfotype_"+uid+"_diseaseType_0";
        //var neoplasticIfChecked = $(neoplasticId).is(':checked');
//       //console.log("neoplasticId:"+neoplasticId+", neoplasticIfChecked="+neoplasticIfChecked);

        if( $("#oleg_orderformbundle_orderinfotype_"+uid+"_diseaseType_0").is(':checked') ) {
            //console.log("toggle!!!!!!!!!!!!!!!!!");
            $(holder).collapse('show');
        }

        if( $("#oleg_orderformbundle_orderinfotype_"+uid+"_diseaseType_1").is(':checked') ) {
            //console.log("1 close?????????????????");
            if( $(holder).is(':visible') ) {
                $(holder).collapse('hide');
            }
        }

        if( $("#oleg_orderformbundle_orderinfotype_"+uid+"_diseaseType_2").is(':checked') ) {
            //console.log("placeholder close?????????????????");
            if( $(holder).is(':visible') ) {
                $(holder).collapse('hide');
            }
        }

    });

}

//use for show: toggle origin well when Neoplastic is selected
function checkValidate() {

    function add() {

        if( this.name.indexOf("diseaseType") != -1 ) {

            var curid = $(this).attr('id');


            if($('#'+curid).is(':checked')) {
                //console.log("checked! add id="+curid);

                //1) oleg_orderformbundle_orderinfotype_patient_0_specimen_0_accession_0_part_0_diseaseType
                var arr1 = curid.split("oleg_orderformbundle_orderinfotype_");
                //2) patient_0_specimen_0_accession_0_part_0_diseaseType
                var arr2 = arr1[1].split("_");
                //3) get ids
                var patient = arr2[1];
                var specimen = arr2[3];
                var accession = arr2[5];
                var part = arr2[7];
                uid = 'patient_'+patient+'_specimen_'+specimen+'_accession_'+accession+'_part_'+part;
                //holder = '#origin_option_multi_'+uid+'_origintag';

                if( curid.indexOf("diseaseType_0") != -1 ) {
                    //use parent of this symfony's origin id=oleg_orderformbundle_orderinfotype_patient_0_specimen_0_accession_0_part_3_origin
                    var holder = '#oleg_orderformbundle_orderinfotype_'+uid+'_origin';
                    var originElement = $(holder).parent().parent().parent().parent();
                    //console.log("loop validate toggle="+holder);
                    $(originElement).collapse('show');
                }
            }

        }
    }

    var form = $('#multy_form'), remaining = {}, errors = [];

    form.find(':radio').each(add);

}

////////////////// different diagnoses (uses as generic collection field) ////////////////////////

//By html form: add different diagnoses input field
function addDiffdiagField( name, type, patient, specimen, accession, part, block, slide ) {

   //console.log("Add: name="+name+",type="+type+",patient="+patient+ ",specimen="+specimen+",accession="+accession+",part="+part+",block="+block+",slide="+slide);

    //var prefix = "oleg_orderformbundle_orderinfotype_";
    var prefix = "inputGroupId_";
    var fieldPrefix = "oleg_orderformbundle_orderinfotype";
    var partialUid = 'patient_'+patient+'_specimen_'+specimen+'_accession_'+accession+'_part_'+part+'_block_'+block+'_slide_'+slide;

    //Id Generated by Symfony: oleg_orderformbundle_orderinfotype_patient_0_specimen_0_accession_0_part_0_diffDiagnoses_0_name
    if( name == "diffDiagnoses" ) {
        var uid = 'patient_'+patient+'_specimen_'+specimen+'_accession_'+accession+'_part_'+part;
    }
    if( name == "relevantScans" ) {
        var uid = 'patient_'+patient+'_specimen_'+specimen+'_accession_'+accession+'_part_'+part+'_block_'+block+'_slide_'+slide;
    }
    var typeuid = uid + "_";

    if( type == "single" ) {
        if( name == "diffDiagnoses" ) {
            fieldPrefix = "oleg_orderformbundle_parttype";
        }
        if( name == "relevantScans" ) {
            fieldPrefix = "oleg_orderformbundle_slidetype";
        }
        typeuid = '';
    }

    //ger diffdiag count from id
    var partialId = prefix + partialUid + "_"+name;
    //console.log("partialId="+partialId);
    //inputGroupId_patient_0_specimen_0_accession_0_part_1_block_0_slide_0_diffDiagnoses
    //inputGroupId_patient_0_specimen_0_accession_0_part_0_block_1_slide_0_diffDiagnoses_0_diffDiagnoses
    
    var elements = $('[id^='+partialId+']');
    //console.log("elements length="+elements.length);

    diffdiagInt = elements.length;
    //console.log("diffdiagInt="+diffdiagInt);

    var newForm = getDiffdiagField( name, type, patient, specimen, accession, part, block, slide, diffdiagInt, false );

    //var ending = "_name";
    var ending = "_"+name;

    //add to last input field
    var addto = elements[elements.length-1].id
    //console.log("form addto="+addto);
    $("#"+addto).after(newForm);

    //add '-' button for the first input field if it is not existed yet
    if( diffdiagInt == 1 ) {
        //id of the input field: oleg_orderformbundle_orderinfotype_patient_0_specimen_0_accession_0_part_0_diffDiagnoses_4_name
        var firstPartialId = fieldPrefix + "_" + typeuid +  name + "_";
        //console.log("firstPartialId="+firstPartialId);
        var partialInputElements = $('[id^='+firstPartialId+']');
        var addId = partialInputElements[0].id;

        var currFiledCollId = getCollId(name, addId);
        var btnDel = getDelBtn(name, type, patient, specimen, accession, part, block, slide, currFiledCollId);

        //console.log("!!!! add '-' to the first input field: addId="+addId);
        $("#"+addId).after(btnDel);
    }

    //remove + for the previous button all the time
    //get array of all + buttons and remove the first one
    //delbtn_patient_0_specimen_0_accession_0_part_0_diffDiagnoses_3_diffDiagnoses
    var lastAddBtnPartialId = "addbtn_" + partialUid + "_" + name + "_";
    //console.log("lastAddBtnPartialId="+lastAddBtnPartialId);
    var lastAddBtnPartialElement = $('[id^='+lastAddBtnPartialId+']');
   //console.log("remove first + remid="+lastAddBtnPartialElement[0].id);
    if( lastAddBtnPartialElement.id != "undefined" ) {
        lastAddBtnPartialElement[0].remove();
    }

    expandTextarea();
}

//add different diagnoses input field first time by JS
function addDiffdiagFieldFirstTime( name, ids ) {

    var patient = ids[0];
    var specimen = ids[1];
    var accession = ids[2];
    var part = ids[3];
    var block = ids[4];
    var slide = ids[5];
    var type = 'multi';
    var currFiledCollId = 0;
    var noDelBtn = true;

   //console.log("name="+name+",ids="+ids+",patient="+patient+ ",specimen="+specimen+",accession="+accession+",part="+part+",block="+block+",slide="+slide);

    if( name == "part" ) {
        var ident = "diffDiagnoses";
        var title = "Differential Diagnoses:";
        var prevId = 'oleg_orderformbundle_orderinfotype_patient_'+patient+'_specimen_'+specimen+'_accession_'+accession+'_part_'+part+'_diagnosis';
    }

    if( name == "slide" ) {
        var ident = "relevantScans";
        var title = "Relevant Scanned Images:";
        var prevId = 'oleg_orderformbundle_orderinfotype_patient_'+patient+'_specimen_'+specimen+'_accession_'+accession+'_part_'+part+'_block_'+block+'_slide_'+slide+'_microscopicdescr';
    }

    var newForm = getDiffdiagField( ident, type, patient, specimen, accession, part, block, slide, currFiledCollId, noDelBtn );

    //var addBtnId = 'addbtn_patient_'+patient+'_specimen_'+specimen+'_accession_'+accession+'_part_'+part+'_'+ident+'_'+currFiledCollId+'_'+ident;
    var inputGroupId = 'inputGroupId_patient_'+patient+'_specimen_'+specimen+'_accession_'+accession+'_part_'+part+'_block_'+block+'_slide_'+slide+'_'+ident+'_'+currFiledCollId+'_'+ident;

    //create form with bs3 rows
    var finalForm = '<p><div class="row">'+

                            '<div class="col-xs-6" align="right">'+
                                '<b>' + title + '</b>' +
                            '</div>' +

                            '<div class="col-xs-6" align="left">' +

                                '<div class="fieldInputColl">' +
                                       newForm +
                                '</div>'+

                            '</div>'+
                    '</div></p>';


    //get addto dimamically: get parent and attach input to this parent
    //id=oleg_orderformbundle_orderinfotype_patient_0_specimen_0_accession_0_part_2_diagnosis
    //var prevId = 'oleg_orderformbundle_orderinfotype_patient_'+patient+'_specimen_'+specimen+'_accession_'+accession+'_part_'+part+'_diagnosis'; //TODO: add block, slide
   //console.log("prevId="+prevId);

    //get parent
    var parent = $('#'+prevId).parent();
   //console.log( "parent=" + parent.attr('class') );
    var grandParent = parent.parent();
   //console.log( "grandParent="+grandParent.attr('class') );

     //$('#'+addto).after(newForm);
    //$('#'+addto).append(newForm);
    grandParent.after(finalForm);
}

//get input field only
function getDiffdiagField( name, type, patient, specimen, accession, part, block, slide, diffdiag, noDelBtn ) {

    //inputGroupId_patient_0_specimen_0_accession_0_part_0_diffDiagnoses_0_diffDiagnoses
    var ending = "_" + name + "_" + diffdiag + "_" + name;

    var dataholder = "#form-prototype-data"; //fixed data holder
    //console.log(dataholder);
    var collectionHolder =  $(dataholder);

    if( name == "diffDiagnoses" ) {
        var prototype = collectionHolder.data('prototype-diffdiagnoses');
        //console.log("prototype="+prototype);
        var newForm = prototype.replace(/__patient__/g, patient);
        newForm = newForm.replace(/__specimen__/g, specimen);
        newForm = newForm.replace(/__accession__/g, accession);
        newForm = newForm.replace(/__part__/g, part);
        newForm = newForm.replace(/__diffDiagnoses__/g, diffdiag);
    }

    if( name == "relevantScans" ) {
        var prototype = collectionHolder.data('prototype-relevantscans');
        //console.log("prototype="+prototype);
        var newForm = prototype.replace(/__patient__/g, patient);
        newForm = newForm.replace(/__specimen__/g, specimen);
        newForm = newForm.replace(/__accession__/g, accession);
        newForm = newForm.replace(/__part__/g, part);
        newForm = newForm.replace(/__block__/g, block);
        newForm = newForm.replace(/__slide__/g, slide);
        newForm = newForm.replace(/__relevantScans__/g, diffdiag);
    }

    var inputGroupId = 'inputGroupId_patient_'+patient+'_specimen_'+specimen+'_accession_'+accession+'_part_'+part+'_block_'+block+'_slide_'+slide+ending;
   //console.log("inputGroupId="+inputGroupId);

    var header = '<div class="input-group" id="'+inputGroupId+'">';

    var btnAdd = getAddBtn(name, type, patient, specimen, accession, part, block, slide, diffdiag);

    var btnDel = "";
    if( noDelBtn != true ) {
        btnDel = getDelBtn(name, type, patient, specimen, accession, part, block, slide, diffdiag);
    }

    var footer = '</div>';

    newForm = header + newForm + btnDel + btnAdd + footer;

    //console.log("newForm="+newForm);
    return newForm;
}


//delete input field and modify +/- buttons accordingly:
//1) delete the field: remove "-" button for the first field if it's not only one field
//2) delete the last field: add "+" button to the previous field
function delDiffdiagField( name, type, patient, specimen, accession, part, block, slide, diffdiag ) {
    
    //console.log("name="+name+",type="+type+",patient="+patient+ ",specimen="+specimen+",accession="+accession+",part="+part);
    
    //Id Generated by Symfony: oleg_orderformbundle_orderinfotype_patient_0_specimen_0_accession_0_part_0_diffDiagnoses_0_name
    //var uid = 'patient_'+patient+'_specimen_'+specimen+'_accession_'+accession+'_part_'+part;
    var fullUid = 'patient_'+patient+'_specimen_'+specimen+'_accession_'+accession+'_part_'+part+'_block_'+block+'_slide_'+slide;
    if( name == "diffDiagnoses" ) {
        var uid = 'patient_'+patient+'_specimen_'+specimen+'_accession_'+accession+'_part_'+part;
    }
    if( name == "relevantScans" ) {
        var uid = 'patient_'+patient+'_specimen_'+specimen+'_accession_'+accession+'_part_'+part+'_block_'+block+'_slide_'+slide;
    }
    var typeuid = uid + "_";

    //var prefix = "oleg_orderformbundle_orderinfotype_";
    var prefix = "inputGroupId_";
    var fieldPrefix = "oleg_orderformbundle_orderinfotype";

    if( type == "single" ) {
        if( name == "diffDiagnoses" ) {
            fieldPrefix = "oleg_orderformbundle_parttype";
        }
        if( name == "relevantScans" ) {
            fieldPrefix = "oleg_orderformbundle_slidetype";
        }
        typeuid = '';
    }
   
    //ger diffdiag count from id
    var partialId = prefix+fullUid+"_"+name+"_";
   //console.log("partialId="+partialId);
    
    var elements = $('[id^='+partialId+']');
    //console.log("elements length="+elements.length);

    diffdiagInt = elements.length - 1;
    //console.log("length="+elements.length+",diffdiagInt="+diffdiagInt+", diffdiag="+diffdiag);

    //don't delete if there is only one field
    if( diffdiagInt == 0 ) {
        return false;
    }

    //delete without asking confirmation if the input field is empty
    //remove id: oleg_orderformbundle_orderinfotype_patient_0_specimen_0_accession_0_part_0_diffDiagnoses_1_name
    //inputGroupId_patient_0_specimen_0_accession_0_part_0_diffDiagnoses_4_diffDiagnoses
    var delId = partialId + diffdiag + '_' + name;
   //console.log("inputGroupId delId="+delId);

    //oleg_orderformbundle_orderinfotype_patient_0_specimen_0_accession_0_part_0_diffDiagnoses_1_name
    var textId = fieldPrefix + "_" + typeuid + name + "_" + diffdiag + "_name";  //TODO: use variable instead of "name"
    var text = $('#'+textId).val();
    //console.log("textId="+textId);

    //console.log("text="+text);
    var remove = false;
    if( text === undefined || text == "" || text.trim() == "undefined" ) {
        remove = true;
    } else {
        if( confirm("Are you sure?") || text == "" ) {
            remove = true;
        }
    }

    if( remove == true ) {

        //1) remove input and all buttons
        //console.log("remove field with id="+delId);
        $('#'+delId).remove();

        //case 1: check and remove "-" button for the first field if it's not only one field
        if( diffdiagInt == 1 ) {
            //console.log("!!! remove'-'");
            //delbtn_patient_0_specimen_0_accession_0_part_0_diffDiagnoses_3_diffDiagnoses
            var lastDelBtnPartialId = "delbtn_" + fullUid + "_" + name + "_";
            var lastDelBtnPartialElement = $('[id^='+lastDelBtnPartialId+']');
            lastDelBtnPartialElement.remove();
        }

        //case 2: add + button to the last input field if it is not existed yet
        //+ button id
        //get collection id
        var elements = $('[id^='+partialId+']');
        var lastElementId = elements[elements.length-1].id;
        var currFiledCollId = getCollId(name, lastElementId);

        var lastAddBtnId = "addbtn_"+fullUid+"_"+name+"_"+currFiledCollId+'_'+name;
        if( $("#"+lastAddBtnId).length > 0 ) {
            //don't add because it already exists
           //console.log("don't add because it already exists, length="+$("#"+lastAddBtnId).length);
        } else {
            //add + button
            if( diffdiagInt == 1 ) {
                var attToId = fieldPrefix + "_" + typeuid + name + "_" + currFiledCollId + "_name";  //TODO: use variable
            } else {
                var attToId = "delbtn_" + fullUid + "_" + name + "_" + currFiledCollId + '_' + name;
            }
            var addBtn = getAddBtn(name, type, patient, specimen, accession, part, block, slide, currFiledCollId);
           //console.log("add + button: attToId="+attToId);
            $("#"+attToId).after(addBtn);
        }


    }

    return false;
}

function getCollId(name, collId) {
    //console.log("lastElementId="+lastElementId);
    var lastElementIdArr=collId.split(name+"_");
    var lastPart = lastElementIdArr[1];
    //console.log("lastPart="+lastPart);
    var currFiledCollIdArr = lastPart.split("_");
    return currFiledCollIdArr[0];
}

function remDelBtnDiffDiag_TODEL(ident, type, patient, specimen, accession, part, collInt) { //TODO: delete this function

    //remove - button for the last field
    //console.log("last field!!!! del collInt="+collInt);
    //if( collInt == 0 ) {
        //remove - button
        var delbtnId = 'delbtn_patient_'+patient+'_specimen_'+specimen+'_accession_'+accession+'_part_'+part+'_'+ident+'_'+collInt+'_'+ident;
       //console.log("delbtnId="+delbtnId);
        $('#'+delbtnId).remove();
    //}
}

function getAddBtn(ident, type, patient, specimen, accession, part, block, slide, collInt) {
    var addbtnId = 'addbtn_patient_'+patient+'_specimen_'+specimen+'_accession_'+accession+'_part_'+part+'_block_'+block+'_slide_'+slide+'_'+ident+'_'+collInt+'_'+ident;
    var btn = '<span id="'+addbtnId+'" onClick="addDiffdiagField(\''+ident+'\',\'' + type + '\',' + patient + ',' +specimen+','+accession+','+part+','+block+','+slide+')"'+
        'class="input-group-addon btn" data-toggle="datepicker" type="button"><i class="glyphicon glyphicon-plus-sign"></i></span>';
    return btn;
}

function getDelBtn(ident, type, patient, specimen, accession, part, block, slide, collInt) {
    var addbtnId = 'delbtn_patient_'+patient+'_specimen_'+specimen+'_accession_'+accession+'_part_'+part+'_block_'+block+'_slide_'+slide+'_'+ident+'_'+collInt+'_'+ident;
    var btn = '<span id="'+addbtnId+'" onClick="delDiffdiagField(\''+ident+'\',\'' + type + '\',' + patient + ',' +specimen+','+accession+','+part+','+block+','+slide+','+collInt+')"'+
        'class="input-group-addon btn" data-toggle="datepicker" type="button"><i class="glyphicon glyphicon-minus-sign"></i></span>';
    return btn;
}

//function onCwid(){
//    window.open("http://weill.cornell.edu/its/identity-security/identity/cwid/")
//}

function expandTextarea() {
//    var $element = $('.textarea').get(0);
    var elements = document.getElementsByClassName('textarea');
    for (var i = 0; i < elements.length; ++i) {
        var element = elements[i];
        element.addEventListener('keyup', function() {
            this.style.overflow = 'hidden';
            //this.style.height = 0;
            this.style.height = this.scrollHeight + 'px';
        }, false);
    }

}


function initDatepicker() {
    //console.debug("init datepicker");
    //datepicker. caused minor error Cannot call method 'split' of undefined; var parts = date.split(format.separator) => preset date by js? add: date = date + "";
    if( $(".datepicker")[0] ) {
        $('.datepicker').datepicker();
    }
    $(".input-group-addon").click(function() {
        $(this).siblings('.datepicker').datepicker('show')
    });
}

