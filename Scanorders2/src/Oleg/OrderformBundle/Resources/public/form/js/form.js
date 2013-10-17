/**
 * User: oli2002
 * Date: 8/14/13
 * Time: 12:36 PM
 */

$(document).ready(function() {

    setNavBar();

    initAdd();

    disableAllElements(); //init disable all fields

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
    //check if this is not the last element in the parent tree
    var thisId = "formpanel_"+id;
    //console.log("replace thisId="+thisId);
    var thisParent = $("#"+thisId).parent();
    //console.log("replace thisParent="+thisParent.attr('id'));
    var elements = thisParent.children( ".panel" );
    //console.log("replace elements.length="+elements.length);

    if( confirm("This auction will affect this element and all its children. Are you sure?") ) {

        if( elements.length > 1 ) {

            $('#formpanel_'+id).remove();

            //check if it is only one element left
            if( (elements.length-1) == 1 ) {
                //change "delete" to "clear"
                var element = thisParent.children( ".panel" );
                //console.log("rename element="+element.attr('id'));
                var delBtnToReplace = element.children(".panel-heading").children(".form-btn-options").children(".delete_form_btn");
                //console.log("rename delBtnToReplace="+delBtnToReplace.attr('id'));
                delBtnToReplace.html('Clear');
            }

        } else {
            //clear the form and all children
            var ids = id.split("_");
            //alert("You can't delete only one left " + ids[0]);

            //console.log("id="+id);
            //console.log("rename elements.length="+elements.length);
            addSameForm(ids[0], ids[1], ids[2], ids[3], ids[4], ids[5], ids[6], ids[7], ids[7], ids[8]);

            $('#formpanel_'+id).remove();

            //make sure to rename delete button to "Clear" if it is only one element left
            if( elements.length == 1 ) {
                //change "delete" to "clear"
                var element = thisParent.children( ".panel" );
                var delBtnToReplace = element.children(".panel-heading").children(".form-btn-options").children(".delete_form_btn");
                delBtnToReplace.html('Clear');
            }
        }
    }

    return false;
}

//main input form from html button: add parent form (always type='multi')
function addSameForm( name, patientid, procedureid, accessionid, partid, blockid, slideid, scanid, stainid ) {

    var uid = patientid+"_"+procedureid+"_"+accessionid+"_"+partid+"_"+blockid+"_"+slideid+"_"+scanid+"_"+stainid;  //+"_"+diffdiag+"_"+specstain+"_"+image;

    //console.log("addSameForm="+name+"_"+uid);

    //prepare form ids and pass it as array
    //increment by 1 current object id
    var btnids = getIds(name, patientid, procedureid, accessionid, partid, blockid, slideid, scanid, stainid);
    var id = btnids['id'];
    var idsorig = btnids['orig'];
    var ids = btnids['ids'];
    var idsm = btnids['idsm'];
    var idsp = btnids['idsp'];

    //place the form in the html page after the last similar element: use parent
    //    var holder = "#formpanel_"+name+"_"+uid;
    //formpanel_slide_0_0_0_0_0_0_0_0
    var partialId = "formpanel_"+name+"_"+btnids['partialId'];
    var elements = $('[id^='+partialId+']');
    var holder = elements.eq(elements.length-1);
    //console.log( "id="+holder.attr('id') );

    //console.log("holder="+holder);

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

    //replace all "add" buttons of this branch with "add" buttons for the next element. use parent and children
    var thisId = "formpanel_"+name+"_"+ids.join("_");
    //console.log("thisId="+thisId);
    var thisParent = $("#"+thisId).parent();
    var childrens = thisParent.children( ".panel" );

    var addbtn = '<button id="form_add_btn_' + name + '_' + ids.join("_") + '" type="button" class="add_form_btn btn btn-xs btn_margin" onclick="addSameForm(\'' + name + '\''+ ',' + ids.join(",") + ')">Add</button>';
    for (var i = 0; i < childrens.length; i++) {
        var addBtnToReplace = childrens.eq(i).children(".panel-heading").children(".form-btn-options").children(".add_form_btn");
        addBtnToReplace.replaceWith( addbtn );

        //rename "clear" to "Delete"
        if( childrens.length > 1 ) {
            //console.log("childrens.length="+childrens.length);
            var delBtnToRename = childrens.eq(i).children(".panel-heading").children(".form-btn-options").children(".delete_form_btn");
            delBtnToRename.html('Delete');
        }
    }

    disableAllMulti();
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

    var deleteStr = "Clear";
    var idsu = ids.join("_");
    var idsc = ids.join(",");
    //increment by 1 current object id
    var formbody = getFormBody( name, ids[0], ids[1], ids[2], ids[3], ids[4], ids[5], ids[6], ids[7] );

    //console.log("getForm: "+name+"_"+", id="+id+", ids="+ids+', idsm='+idsm);

    if( name == "scan" || name == "stain" ) {
        var addbtn = "";
        var deletebtn = "";
        //var itemCount = (id+2);
    } else {
        var addbtn = '<button id="form_add_btn_' + name + '_' + idsu + '" type="button" class="add_form_btn btn btn-xs btn_margin" onclick="addSameForm(\'' + name + '\''+ ',' + idsc + ')">Add</button>';
        var deletebtn = ' <button id="delete_form_btn_'+name+'_'+idsu+'" type="button" class="delete_form_btn btn btn-danger btn_margin btn-xs">'+deleteStr+'</button>';
        //var itemCount = (id+1);
    }

    //get itemCount from partialId
    var itemCount = getIdByName(name,ids) + 1;
    //console.log('itemCount='+itemCount);

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

function getFormBody( name, patientid, procedureid, accessionid, partid, blockid, slideid, scanid, stainid ) {

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

    var patientidp = patientid;
    var procedureidp = procedureid;
    var accessionidp = accessionid;
    var partidp = partid;
    var blockidp = blockid;
    var slideidp = slideid;
    var scaniddp = scanid;
    var stainidp = stainid;

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
            break;
        case "procedure":
            procedureidm = procedureid-1;
            procedureid++;
            procedureidp = procedureid+1;
            id = procedureid;
            nextName = "accession";
            partialId = patientid;
            break;
        case "accession":
            accessionidm = accessionid-1;
            accessionid++;
            accessionidp = accessionid+1;
            id = accessionid;
            nextName = "part";
            partialId = patientid+"_"+procedureid;
            break;
        case "part":
            partidm = partid-1;
            partid++;
            partidp = partid+1;
            id = partid;
            nextName = "block";
            partialId = patientid+"_"+procedureid+"_"+accessionid;
            break;
        case "block":
            blockidm = blockid-1;
            blockid++;
            blockidp = blockid+1;
            id = blockid;
            nextName = "slide";
            partialId = patientid+"_"+procedureid+"_"+accessionid+"_"+partid;
            break;
        case "slide":
            slideidm = slideid-1;
            slideid++;
            slideidp = slideid+1;
            id = slideid;
            nextName = "";
            partialId = patientid+"_"+procedureid+"_"+accessionid+"_"+partid+"_"+blockid;
            break;
        case "scan":
            scaniddm = scanid-1;
            scanid++;
            scaniddp = scanid+1;
            id = scanid;
            nextName = "";
            partialId = patientid+"_"+procedureid+"_"+accessionid+"_"+partid+"_"+blockid;
            break;
        case "stain":
            stainidm = stainid-1;
            stainid++;
            stainidp = stainid+1;
            id = stainid;
            nextName = "";
            partialId = patientid+"_"+procedureid+"_"+accessionid+"_"+partid+"_"+blockid;
            break;
        default:
            id = 0;
    }

    var idsArray = [patientid, procedureid, accessionid, partid, blockid, slideid, scanid, stainid];
    var idsArrayM = [patientidm, procedureidm, accessionidm, partidm, blockidm, slideidm, scaniddm, stainidm];
    var idsArrayP = [patientidp, procedureidp, accessionidp, partidp, blockidp, slideidp, scaniddp, stainidp];

    var res_array = {
        'id' : id,
        'orig' : orig,
        'ids' : idsArray,
        'idsm' : idsArrayM,
        'idsp' : idsArrayP,
        'nextName' : nextName,
        'partialId' : partialId
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

//initDatepicker: add or remove click event to the field and its siblings calendar button
//element: null or jquery object. If null, all element with class datepicker will be assign to calendar click event
//remove null or "remove"
function initDatepicker(element,remove) {
    //console.debug("init datepicker, cicle="+cicle);
    if( cicle != "show" ) {
        if( !element ) {
            element = $(".datepicker");
        }
        //console.debug("init datepicker, cicle="+cicle+", class="+element.attr("class"));
        if( remove == "remove" ) {
            element.datepicker("remove");
        } else {
            element.datepicker({autoclose: true});
        }
        var icons = element.parent().find("span").each(function( index  ) {
            //console.log( index + ": " + $( this ).attr("class") );
            if( remove == "remove" ) {
                $( this ).unbind("click");
            } else {
                $( this ).click(function() {
                    $(this).siblings('.datepicker').datepicker('show');
                });
            }
        });
    }
}

