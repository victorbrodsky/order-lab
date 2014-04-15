/**
 * User: oli2002
 * Date: 8/14/13
 * Time: 12:36 PM
 */

//add all element to listeners again, the same as in ready
function initAdd() {

    expandTextarea();

//    $(".combobox").combobox();
    regularCombobox();

    initDatepicker();

    //clean validation elements
    //console.log("clean initAdd");
    cleanValidationAlert();

    fieldInputMask();

    setResearch();

    setEducational();

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

    //clean validation elements
    cleanValidationAlert();

    return false;
}

//main input form from html button: add parent form (always type='multi')
//TODO: remove add parent and use only addChildForms in loop including adding parent
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

    if( name == "slide" ) {
        //addCollFieldFirstTime( "relevantScans", ids );
        //addDiffdiagFieldFirstTime( name, ids );
        //addCollFieldFirstTime( "specialStains", ids );
    }

    //bind listener to the toggle button
    bindToggleBtn( name + '_' + ids.join("_") );
    bindDeleteBtn( name + '_' + ids.join("_") );
    //originOptionMulti(ids);
    diseaseTypeListener();
    initComboboxJs(ids);
    initAdd();
    addKeyListener();

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

    //var addbtn = '<button id="form_add_btn_' + name + '_' + ids.join("_") + '" type="button" class="testjs add_form_btn btn btn-xs btn_margin" onclick="addSameForm(\'' + name + '\''+ ',' + ids.join(",") + ')">Add</button>';
    var addbtn =  getHeaderAddBtn( name, ids );

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

    initAllElements();
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
    //console.debug(name+": ADD CHILDS to="+holder+" uid="+idsu);

    //attach children form
    $(holder).append( getForm( name, id, idsorig, ids, idsm  ) );

    if( name == "slide" ) {
        //addCollFieldFirstTime( "relevantScans", ids );
        //addDiffdiagFieldFirstTime( name, ids );
        //addCollFieldFirstTime( "specialStains", ids );
    }
    
    bindToggleBtn( name + '_' + ids.join("_") );
    bindDeleteBtn( name + '_' + ids.join("_") );
    //originOptionMulti(ids);
    diseaseTypeListener();
    initComboboxJs(ids);
    initAdd();
    addKeyListener();
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
        var addbtn = getHeaderAddBtn( name, ids );
        var deletebtn = getHeaderDeleteBtn( name, ids, deleteStr );
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
    newForm = newForm.replace(/__paper__/g, 0);   //add only one clinical history to the new form

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
        //alert("clicked delete!");
        deleteItem(id);
    });
}

function setNavBar() {

    $('ul.li').removeClass('active');

    var full = window.location.pathname;

    var id = 'scanorderhome';

    if( full.indexOf("scan-order/multi-slide-clinical") !== -1 ) {
        id = 'placescanorder';
    }

    if ( full.indexOf("scan-order/multi-slide-educational") !== -1 ) {
        id = 'placescanorder';
    }

    if ( full.indexOf("scan-order/multi-slide-research") !== -1 ) {
        id = 'placescanorder';
    }

    if( full.indexOf("scan-order/one-slide") !== -1 ) {
        id = 'placescanorder';
    }

    if( full.indexOf("scan-order/multi-slide-table-view") !== -1 ) {
        id = 'placescanorder';
    }

    if( full.indexOf("my-scan-orders") !== -1 ) {
        id = 'myscanorders';
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
    
    if( full.indexOf("/users/") !== -1 ) {
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
        e.preventDefault();
        $('#priority_option').collapse('toggle');
    });

    var checked = $('#oleg_orderformbundle_orderinfotype_priority').find('input[type=radio]:checked').val();
    //console.log("checked="+checked);
    if( checked == 'Stat' ) {
        $('#priority_option').collapse('show');
    }
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
            this.style.height = 0;
            var newH = this.scrollHeight + 10;
            //console.log("cur h="+this.style.height+", newH="+newH);
            this.style.height = newH + 'px';
        }, false);
    }

}


function initDatepicker() {

    var datepickers = $('.input-group.date');

    if( cicle != "show" ) {
        initSingleDatepicker( datepickers );
    
        //make sure the masking is clear when input is cleared by datepicker
        datepickers.datepicker().on("clearDate", function(e){
                var inputField = $(this).find('input');
                //printF(inputField,"Clear input:");
                clearErrorField( inputField );
        });
    }

}

//use "eternicode/bootstrap-datepicker": "dev-master"
//process Datepicker: add or remove click event to the field and its siblings calendar button
//element: null or jquery object. If null, all element with class datepicker will be assign to calendar click event
//remove null or "remove"
function processDatepicker( element, remove ) {

    if( cicle != "show" ) {

        //replace element (input field) by a parent with class .input-group .date
        if( !element ) {
            element = $('.input-group.date');
        } else {
            element = element.closest('.input-group.date');
        }

        //var btn = element.parent().find('.input-group-addon');
        //printF(btn,"Datepicker Btn:");

        if( remove == "remove" ) {
            //printF(element,"Remove datepicker:");
            element.datepicker("remove");

            //make sure the masking is clear when input is cleared by datepicker
            var inputField = element.find('input');
            clearErrorField( inputField );

            //btn.attr( "disabled", true );
        } else {
            initSingleDatepicker(element);
            //btn.attr( "disabled", false );
        }

    }
}

function initSingleDatepicker( datepickerElement ) {
    datepickerElement.datepicker({
        autoclose: true,
        clearBtn: true,
        //todayBtn: "linked",
        todayHighlight: true
    });
}

function setResearch() {
    //get value of project title field on change
    $('.combobox-research-projectTitle').on("change", function(e) {
        //console.log('listener: project Title changed');
        getSetTitle();
    });
}

function setEducational() {
    //get value of project title field on change
    $('.combobox-research-courseTitle').on("change", function(e) {
        //console.log('listener: course Title changed');
        getLessonTitle();
    });
}

function inArrayCheck( arr, needle ) {
    //console.log('len='+arr.length+", needle: "+needle+"?="+parseInt(needle));

    if( needle == '' ) {
        return -1;
    }

    if( needle == parseInt(needle) ) {
        return needle;
    }

    for( var i = 0; i < arr.length; i++ ) {
        //console.log(arr[i]['text']+'?='+needle);
        if( arr[i]['text'] === needle ) {
            return arr[i]['id'];
        }
    }
    return -1;
}


function printF(element,text) {
    var str = "id="+element.attr("id") + ", class=" + element.attr("class")
    if( text ) {
        str = text + " : " + str;
    }
    console.log(str);
}



/**
 * Created with JetBrains PhpStorm.
 * User: oli2002
 * Date: 9/13/13
 * Time: 5:50 PM
 * To change this template use File | Settings | File Templates.
 */

var combobox_width = '100%'; //'element'
//var urlCommon = "http://collage.med.cornell.edu/order/scanorder/Scanorders2/web/app_dev.php/util/";
//var urlCommon = "http://collage.med.cornell.edu/order/util/";
var urlBase = $("#baseurl").val();
var urlCommon = "http://"+urlBase+"/util/";
//var type = $("#formtype").val();
var cicle = $("#formcicle").val();
var user_name = $("#user_name").val();
var user_id = $("#user_id").val();
var proxyuser_name = $("#proxyuser_name").val();
var proxyuser_id = $("#proxyuser_id").val();
//console.log("urlCommon="+urlCommon);
var orderinfoid = $(".orderinfo-id").val();

var _mrntype = new Array();
var accessiontype = new Array();
var partname = new Array();
var blockname = new Array();
var stain = new Array();
var scanregion = new Array();
var procedure = new Array();
var organ = new Array();
var delivery = new Array();
var returnslide = new Array();
var pathservice = new Array();
var userpathservice = new Array();
var optionaluserEducational = new Array();
var optionaluserResearch = new Array();
var _projectTitle = new Array();
var _setTitle = new Array();
var _courseTitle = new Array();
var _lessonTitle = new Array();

var userpathserviceflag = false;

var asyncflag = true;

function regularCombobox() {
    //resolve
    $("select.combobox").select2({
        width: combobox_width,
        dropdownAutoWidth: true,
        placeholder: "Choose an option",
        allowClear: true
        //selectOnBlur: true
        //readonly: true
        //selectOnBlur: true,
        //containerCssClass: 'combobox-width'
    });

    //set amd make provider read only
    $("#s2id_oleg_orderformbundle_orderinfotype_provider").select2("readonly", true);
    $("#s2id_oleg_orderformbundle_orderinfotype_provider").select2('data', {id: user_id, text: user_name});

    //preselect with current user
    if( proxyuser_id ) {
//        proxyuser_id = user_id;
//        proxyuser_name = user_name;
        $("#s2id_oleg_orderformbundle_orderinfotype_proxyuser").select2('data', {id: proxyuser_id, text: proxyuser_name});
    }

    //research
    populateSelectCombobox( ".combobox-research-setTitle", _setTitle, "Research Set Title", false );
    //$(".combobox-research-setTitle").select2("readonly", true);

    //educational
    populateSelectCombobox( ".combobox-research-lessonTitle", _lessonTitle, "Lesson Title", false );
    //$(".combobox-research-lessonTitle").select2("readonly", true);

}

function customCombobox() {

    //console.log("cicle="+cicle);

    if( cicle && urlBase && cicle != 'edit_user' && cicle != 'accountreq' ) {
        getComboboxMrnType(urlCommon,new Array("0","0","0","0","0","0"));
        getComboboxAccessionType(urlCommon,new Array("0","0","0","0","0","0"));
        getComboboxPartname(urlCommon,new Array("0","0","0","0","0","0"));
        getComboboxBlockname(urlCommon,new Array("0","0","0","0","0","0"));
        getComboboxScanregion(urlCommon,new Array("0","0","0","0","0","0"));
        getComboboxStain(urlCommon,new Array("0","0","0","0","0","0"));
        getComboboxSpecialStain(urlCommon,new Array("0","0","0","0","0","0","0"),false);
        getComboboxProcedure(urlCommon,new Array("0","0","0","0","0","0"));
        getComboboxOrgan(urlCommon,new Array("0","0","0","0","0","0"));
        getComboboxDelivery(urlCommon,new Array("0","0","0","0","0","0"));
        getComboboxReturn(urlCommon,new Array("0","0","0","0","0","0"));
        getComboboxPathService(urlCommon,new Array("0","0","0","0","0","0"));
        getOptionalUserEducational(urlCommon,new Array("0","0","0","0","0","0"));
        getOptionalUserResearch(urlCommon,new Array("0","0","0","0","0","0"));
        slideType(new Array("0","0","0","0","0","0"));
        getProjectTitle(urlCommon,new Array("0","0","0","0","0","0"));
        getCourseTitle(urlCommon,new Array("0","0","0","0","0","0"));
    }

    if( cicle && urlBase && ( cicle == 'edit_user' || cicle == 'accountreq' )  ) {
        getComboboxPathService(urlCommon,new Array("0","0","0","0","0","0"));
    }
}

function populateSelectCombobox( target, data, placeholder, multiple ) {

    if( placeholder ) {
        var allowClear = true;
    } else {
        var allowClear = false;
    }

    if( multiple ) {
        var multiple = true;
    } else {
        var multiple = false;
    }

    $(target).select2({
        placeholder: placeholder,
        allowClear: allowClear,
        width: combobox_width,
        dropdownAutoWidth: true,
        selectOnBlur: true,
        dataType: 'json',
        quietMillis: 100,
        multiple: multiple,
        data: data,
        createSearchChoice:function(term, data) {
            //if( term.match(/^[0-9]+$/) != null ) {
            //    console.log("term is digit");
            //}
            return {id:term, text:term};
        }
    });
}


//#############  stains  ##############//
function getComboboxStain(urlCommon, ids) {

    var url = urlCommon+"stain";

    if( cicle == "new" || cicle == "create" ) {
        url = url + "?opt=default";
    }

    //console.log("stain.length="+stain.length);
    if( stain.length == 0 ) {
        //console.log("stain 0");
        $.ajax({
            url: url,
            async: asyncflag,
            timeout: _ajaxTimeout
        }).success(function(data) {
            stain = data;
            populateSelectCombobox( ".ajax-combobox-stain", stain, null );
            populateSelectCombobox( ".ajax-combobox-staintype", stain, null );
        });
    } else {
        //console.log("stain exists");
        populateSelectCombobox( ".ajax-combobox-stain", stain, null );
        populateSelectCombobox( ".ajax-combobox-staintype", stain, null );
    }

    if( cicle == "new"  ) {
        var uid = 'patient_'+ids[0]+'_procedure_'+ids[1]+'_accession_'+ids[2]+'_part_'+ids[3]+'_block_'+ids[4]+'_slide_'+ids[5];
        var id= "#oleg_orderformbundle_orderinfotype_"+uid+"_";
        var targetid = id+"stain_0_field";
        //$(targetid).select2('val', '1');
        setToFirstElement( targetid, stain );
    }

}

function getComboboxSpecialStain(urlCommon, ids, preset) {

    var url = urlCommon+"stain";

    if( cicle == "new" || cicle == "create" ) {
        url = url + "?opt=default";
    }

    var targetid = "";
    if( cicle == "new" || (cicle == "amend" && preset) || (cicle == "edit" && preset) ) {
        var uid = 'patient_'+ids[0]+'_procedure_'+ids[1]+'_accession_'+ids[2]+'_part_'+ids[3]+'_block_'+ids[4]+'_slide_'+ids[5];
        var id= "#oleg_orderformbundle_orderinfotype_"+uid+"_";
        targetid = id+"specialStains_"+ids[6]+"_staintype";
        //console.log("targetid="+targetid);
    }

    if( stain.length == 0 ) {
        $.ajax({
            url: url,
            timeout: _ajaxTimeout,
            async: asyncflag
        }).success(function(data) {
                stain = data;
                populateSelectCombobox( ".ajax-combobox-staintype", stain, null );
            });
    } else {
        populateSelectCombobox( targetid, stain, null );
    }

    //console.log("special stain preset="+preset);
    if( targetid != "" ) {
        //$(targetid).select2('val', '1');
        setToFirstElement( targetid, stain );
    }
}

//#############  scan regions  ##############//
function getComboboxScanregion(urlCommon,ids) {

    var url = urlCommon+"scanregion";
    //console.log("scanregion.length="+scanregion.length);

    if( cicle == "edit" || cicle == "show" || cicle == "amend" ) {
        url = url + "?opt="+orderinfoid;
    }

    if( scanregion.length == 0 ) {
        $.ajax({
            url: url,
            timeout: _ajaxTimeout,
            async: asyncflag
        }).success(function(data) {
            scanregion = data;
            populateSelectCombobox( ".ajax-combobox-scanregion", scanregion, null );
        });
    } else {
        populateSelectCombobox( ".ajax-combobox-scanregion", scanregion, null );
    }

    if( cicle == "new"  ) {
        var uid = 'patient_'+ids[0]+'_procedure_'+ids[1]+'_accession_'+ids[2]+'_part_'+ids[3]+'_block_'+ids[4]+'_slide_'+ids[5];
        var id= "#oleg_orderformbundle_orderinfotype_"+uid+"_";
        var targetid = id+"scan_0_scanregion";
        //$(targetid).select2('data', {id: 'Entire Slide', text: 'Entire Slide'});
        setToFirstElement( targetid, scanregion );
    }
}

//#############  source organs  ##############//
function getComboboxOrgan(urlCommon,ids) {
//    var uid = 'patient_'+ids[0]+'_procedure_'+ids[1]+'_accession_'+ids[2]+'_part_'+ids[3];   //+'_block_'+ids[4]+'_slide_'+ids[5];
//    var id= "#oleg_orderformbundle_orderinfotype_"+uid+"_";
    var url = urlCommon+"organ";

    if( cicle == "new" || cicle == "create" ) {
        url = url + "?opt=default";
    }

    if( organ.length == 0 ) {
        $.ajax({
            url: url,
            timeout: _ajaxTimeout,
            async: asyncflag
        }).success(function(data) {
            organ = data;
            populateSelectCombobox( ".ajax-combobox-organ", organ, "Source Organ" );
        });
    } else {
        populateSelectCombobox( ".ajax-combobox-organ", organ, "Source Organ" );
    }

}


//#############  procedure types  ##############//
function getComboboxProcedure(urlCommon,ids) {
//    var uid = 'patient_'+ids[0]+'_procedure_'+ids[1];    //+'_accession_'+ids[2]+'_part_'+ids[3]+'_block_'+ids[4]+'_slide_'+ids[5];
//    var id= "#oleg_orderformbundle_orderinfotype_"+uid+"_";
    var url = urlCommon+"procedure";
//    var targetid = id+"name_0_field";

    if( cicle == "new" || cicle == "create" ) {
        url = url + "?opt=default";
    }

    if( procedure.length == 0 ) {
        $.ajax({
            url: url,
            timeout: _ajaxTimeout,
            async: asyncflag
        }).success(function(data) {
            procedure = data;
            populateSelectCombobox( ".ajax-combobox-procedure", procedure, "Procedure Type" );
        });
    } else {
        populateSelectCombobox( ".ajax-combobox-procedure", procedure, "Procedure Type" );
    }

}

//#############  Accession Type  ##############//
function getComboboxAccessionType(urlCommon,ids) {

    var url = urlCommon+"accessiontype";

    //console.log("orderformtype="+orderformtype);

    if( cicle == "new" || cicle == "create" ) {
        url = url + "?opt=default&type="+orderformtype;
    }

    if( accessiontype.length == 0 ) {
        $.ajax({
            url: url,
            timeout: _ajaxTimeout,
            async: asyncflag
        }).success(function(data) {
                accessiontype = data;
                populateSelectCombobox( ".accessiontype-combobox", accessiontype, null );
                setAccessionMask();
            });
    } else {
        populateSelectCombobox( ".accessiontype-combobox", accessiontype, null );
    }

    if( cicle == "new"  ) {
        var uid = 'patient_'+ids[0]+'_procedure_'+ids[1]+'_accession_'+ids[2];
        var id= "#oleg_orderformbundle_orderinfotype_"+uid+"_";
        var targetid = id+"accession_0_accessiontype";
        //console.log("targetid="+targetid);
        //$(targetid).select2('val', 1);
        setToFirstElement( targetid, accessiontype );
    }
}

//#############  Mrn Type  ##############//
function getComboboxMrnType(urlCommon,ids) {

    var url = urlCommon+"mrntype";

    //console.log("orderformtype="+orderformtype);

    if( cicle == "new" || cicle == "create" ) {
        url = url + "?opt=default&type="+orderformtype;
    }

    if( _mrntype.length == 0 ) {
        $.ajax({
            url: url,
            timeout: _ajaxTimeout,
            async: asyncflag
        }).success(function(data) {
                _mrntype = data;
                populateSelectCombobox( ".mrntype-combobox", _mrntype, null );
                setAccessionMask();
            });
    } else {
        populateSelectCombobox( ".mrntype-combobox", _mrntype, null );
    }

    if( cicle == "new"  ) {
        //oleg_orderformbundle_orderinfotype_patient_0_mrn_0_keytype
        var uid = 'patient_'+ids[0];
        var id= "#oleg_orderformbundle_orderinfotype_"+uid+"_";
        var targetid = id+"mrn_0_mrntype";
        //console.log("targetid="+targetid);
        setToFirstElement( targetid, _mrntype );
    }
}

//#############  partname types  ##############//
function getComboboxPartname(urlCommon,ids) {

    var url = urlCommon+"partname";

//    if( cicle == "new" || cicle == "create" ) {
//        url = url + "?opt=default";
//    }

    if( cicle == "edit" || cicle == "show" || cicle == "amend" ) {
        url = url + "?opt="+orderinfoid;
    }

    if( partname.length == 0 ) {
        $.ajax({
            url: url,
            timeout: _ajaxTimeout,
            async: asyncflag
        }).success(function(data) {
            partname = data;
            populateSelectCombobox( ".ajax-combobox-partname", partname, "Part Name" );
        });
    } else {
        populateSelectCombobox( ".ajax-combobox-partname", partname, "Part Name" );
    }

}

//#############  blockname types  ##############//
function getComboboxBlockname(urlCommon,ids) {

    var url = urlCommon+"blockname";

//    if( cicle == "new" || cicle == "create" ) {
//        url = url + "?opt=default";
//    }
    if( cicle == "edit" || cicle == "show" || cicle == "amend" ) {
        url = url + "?opt="+orderinfoid;
    }

    if( blockname.length == 0 ) {
        $.ajax({
            url: url,
            timeout: _ajaxTimeout,
            async: asyncflag
        }).success(function(data) {
            blockname = data;
            populateSelectCombobox( ".ajax-combobox-blockname", blockname, "Block Name" );
        });
    } else {
        populateSelectCombobox( ".ajax-combobox-blockname", blockname, "Block Name" );
    }

}

//#############  project title  ##############//
function getProjectTitle(urlCommon,ids) {

    var url = urlCommon+"projecttitle";

//    if( cicle == "new" || cicle == "create" ) {
//        url = url + "?opt=default";
//    }
    if( cicle == "edit" || cicle == "show" || cicle == "amend" ) {
        url = url + "?opt="+orderinfoid;
    }

    if( _projectTitle.length == 0 ) {
        $.ajax({
            url: url,
            timeout: _ajaxTimeout,
            async: asyncflag
        }).success(function(data) {
                _projectTitle = data;
                populateSelectCombobox( ".combobox-research-projectTitle", _projectTitle, "Research Project Title", false );

                //get id if set
                var projectTitleVal = $(".combobox-research-projectTitle").select2('val');
                //console.log("finished: projectTitleVal="+projectTitleVal);
                if( projectTitleVal != "" ) {
                    getSetTitle();
                }

            });
    } else {
        populateSelectCombobox( ".combobox-research-projectTitle", _projectTitle, "Research Project Title", false );
    }

}

//#############  set title  ##############//
function getSetTitle() {

    //get ProjectTitle value
    var projectTitleVal = $(".combobox-research-projectTitle").select2('val');
    //console.log("setTitle: projectTitleVal="+projectTitleVal);
    //console.log(_projectTitle);
    //var projectTitleData = $(".combobox-research-projectTitle").select2('data');
    //console.log("id="+projectTitleData.id+", text="+projectTitleData.text);

    var idInArr = inArrayCheck( _projectTitle, projectTitleVal );
    //console.log('idInArr='+idInArr);

    if( idInArr == -1 ) {
        //console.log('not in array');
        populateSelectCombobox( ".combobox-research-setTitle", _setTitle, "Research Set Title", false );
        return;
    }

    var url = urlCommon+"settitle";

    url = url + "?opt="+idInArr;

    $.ajax({
        url: url,
        timeout: _ajaxTimeout,
        async: asyncflag
    }).success(function(data) {
            if( data ) {
                //console.log("id="+data[0].id+", text="+data[0].text);
                populateSelectCombobox( ".combobox-research-setTitle", data);
                $(".combobox-research-setTitle").select2("readonly", false);
                //$(".combobox-research-setTitle").select2('data', {id: data[0].id, text: data[0].text});
                setToFirstElement( ".combobox-research-setTitle", data );
            }
    });

}


//#############  course title  ##############//
function getCourseTitle(urlCommon,ids) {

    var url = urlCommon+"coursetitle";

//    if( cicle == "new" || cicle == "create" ) {
//        url = url + "?opt=default";
//    }
    if( cicle == "edit" || cicle == "show" || cicle == "amend" ) {
        url = url + "?opt="+orderinfoid;
    }

    if( _courseTitle.length == 0 ) {
        $.ajax({
            url: url,
            timeout: _ajaxTimeout,
            async: asyncflag
        }).success(function(data) {
                _courseTitle = data;
                populateSelectCombobox( ".combobox-research-courseTitle", _courseTitle, "Course Title", false );

                //get id if set
                var courseTitleVal = $(".combobox-research-courseTitle").select2('val');
                //console.log("finished: courseTitleVal="+courseTitleVal);
                if( courseTitleVal != "" ) {
                    getLessonTitle();
                }

            });
    } else {
        populateSelectCombobox( ".combobox-research-courseTitle", _courseTitle, "Course Title", false );
    }

}

//#############  lesson title  ##############//
function getLessonTitle() {

    //get CourseTitle value
    var courseTitleVal = $(".combobox-research-courseTitle").select2('val');
    //console.log("lessonTitle: courseTitleVal="+courseTitleVal);

    var idInArr = inArrayCheck( _courseTitle, courseTitleVal );
    //console.log('idInArr='+idInArr);

    if( idInArr == -1 ) {
        //console.log('not in array');
        populateSelectCombobox( ".combobox-research-lessonTitle", _lessonTitle, "Lesson Title", false );
        return;
    }

    var url = urlCommon+"lessontitle";

    url = url + "?opt="+idInArr;

    $.ajax({
        url: url,
        timeout: _ajaxTimeout,
        async: asyncflag
    }).success(function(data) {
            if( data ) {
                //console.log("id="+data[0].id+", text="+data[0].text);
                populateSelectCombobox( ".combobox-research-lessonTitle", data);
                $(".combobox-research-lessonTitle").select2("readonly", false);
                setToFirstElement( ".combobox-research-lessonTitle", data );
            }

        });

}


//#############  slide delivery  ##############//
function getComboboxDelivery(urlCommon,ids) {
    //var uid = "";   //'patient_'+ids[0]+'_procedure_'+ids[1]+'_accession_'+ids[2]+'_part_'+ids[3]+'_block_'+ids[4]+'_slide_'+ids[5];
//    var id= "#oleg_orderformbundle_orderinfotype_";
    var url = urlCommon+"delivery";
    var target = ".ajax-combobox-delivery";

    if( cicle == "edit" || cicle == "show" || cicle == "amend" ) {
        url = url + "?opt="+orderinfoid;
    }

    //console.log("scanregion.length="+organ.length);
    if( delivery.length == 0 ) {
        $.ajax({
            url: url,
            timeout: _ajaxTimeout,
            async: asyncflag
        }).success(function(data) {
            delivery = data;
            populateSelectCombobox( ".ajax-combobox-delivery", delivery, null );
            if( cicle == "new"  ) {
                setToFirstElement( target, delivery );
            }
        });
    } else {
        populateSelectCombobox( ".ajax-combobox-delivery", delivery, null );
        if( cicle == "new"  ) {
            setToFirstElement( target, delivery );
        }
    }

}

//#############  return slides to  ##############//
function getComboboxReturn(urlCommon,ids) {
    //var uid = 'patient_'+ids[0]+'_procedure_'+ids[1]+'_accession_'+ids[2]+'_part_'+ids[3]+'_block_'+ids[4]+'_slide_'+ids[5];
    //var id= "#oleg_orderformbundle_orderinfotype_";
    var url = urlCommon+"return";
    //var targetid = id+"returnSlide";

    if( cicle == "edit" || cicle == "show" || cicle == "amend" ) {
        url = url + "?opt="+orderinfoid;
    }

    //console.log("scanregion.length="+organ.length);
    if( returnslide.length == 0 ) {
        $.ajax({
            url: url,
            timeout: _ajaxTimeout,
            async: asyncflag
        }).success(function(data) {
            returnslide = data;
            populateSelectCombobox( ".ajax-combobox-return", returnslide, null );
            if( cicle == "new"  ) {
                //$(".ajax-combobox-return").select2('data', {id: "Filing Room", text: "Filing Room"});
                setToFirstElement( ".ajax-combobox-return", returnslide );
            }
        });
    } else {
        populateSelectCombobox( ".ajax-combobox-return", returnslide, null );
        if( cicle == "new"  ) {
            //$(".ajax-combobox-return").select2('data', {id: "Filing Room", text: "Filing Room"});
            setToFirstElement( ".ajax-combobox-return", returnslide );
        }
    }

}

//#############  pathology service for user and orderinfo  ##############//
function getComboboxPathService(urlCommon,ids) {

    //******************* order pathology service *************************//
    //var uid = 'patient_'+ids[0]+'_procedure_'+ids[1]+'_accession_'+ids[2]+'_part_'+ids[3]+'_block_'+ids[4]+'_slide_'+ids[5];
    //var id= "#oleg_orderformbundle_orderinfotype_";
    var targetid = ".ajax-combobox-pathservice";
    var url = urlCommon+"pathservice";

    if( cicle == "new" || cicle == "create" || cicle == "accountreq" || cicle == "edit_user" || cicle == "amend" || cicle == "show" ) {
        var optStr = user_id;
        if( !optStr || typeof optStr === 'undefined' ) {
            optStr = "default";
        }
        url = url + "?opt=" + optStr;
    }

    //console.log("cicle="+cicle+", url="+url+", targetid="+targetid+", user_id="+user_id);
    if( cicle == "accountreq" || cicle == "edit_user" ) {
        var multiple = true;
    } else {
        var multiple = false;
    }

    if( pathservice.length == 0 ) {
        $.ajax({
            url: url,
            timeout: _ajaxTimeout,
            async: asyncflag
        }).success(function(data) {
            pathservice = data;
            populateSelectCombobox( targetid, pathservice, "Departmental Division(s) / Service(s)", multiple );
        });
    } else {
        populateSelectCombobox( targetid, pathservice, "Departmental Division(s) / Service(s)", multiple );
    }

//    $(targetid).select2("container").find("ul.select2-choices").sortable({
//        containment: 'parent',
//        start: function() { $(targetid).select2("onSortStart"); },
//        update: function() { $(targetid).select2("onSortEnd"); }
//    });

}

//#############  optional users  ##############//
function getOptionalUserEducational(urlCommon,ids) {

    var targetid = ".ajax-combobox-optionaluser-educational";
    var url = urlCommon+"optionalusereducational";

    var optStr = user_id;
    url = url + "?opt=" + optStr;

    if( optionaluserEducational.length == 0 ) {
        $.ajax({
            url: url,
            timeout: _ajaxTimeout,
            async: asyncflag
        }).success(function(data) {
                optionaluserEducational = data;
                populateSelectCombobox( targetid, optionaluserEducational, "Choose an option" );
            });
    } else {
        populateSelectCombobox( targetid, optionaluserEducational, "Choose and option" );
    }

}

function getOptionalUserResearch(urlCommon,ids) {

    var targetid = ".ajax-combobox-optionaluser-research";
    var url = urlCommon+"optionaluserresearch";

    var optStr = user_id;
    url = url + "?opt=" + optStr;

    if( optionaluserResearch.length == 0 ) {
        $.ajax({
            url: url,
            timeout: _ajaxTimeout,
            async: asyncflag
        }).success(function(data) {
                optionaluserResearch = data;
                populateSelectCombobox( targetid, optionaluserResearch, "Choose an option" );
            });
    } else {
        populateSelectCombobox( targetid, optionaluserResearch, "Choose and option" );
    }

}
//############# end of optional users  ##############//


function initComboboxJs(ids) {

    if( urlBase ) {

        cicle = 'new';

        getComboboxMrnType(urlCommon,ids);
        getComboboxAccessionType(urlCommon,ids);
        getComboboxPartname(urlCommon,ids);
        getComboboxBlockname(urlCommon,ids);
        getComboboxStain(urlCommon,ids);
        getComboboxSpecialStain(urlCommon,ids,false);
        getComboboxScanregion(urlCommon,ids);
        getComboboxProcedure(urlCommon,ids);
        getComboboxOrgan(urlCommon,ids);
        getComboboxPathService(urlCommon,ids);
        getOptionalUserEducational(urlCommon,ids);
        getOptionalUserResearch(urlCommon,ids);
        slideType(ids);
        getProjectTitle(urlCommon,ids);
        getCourseTitle(urlCommon,ids);
    }
}


function slideType(ids) {    
    
    //oleg_orderformbundle_orderinfotype_patient_0_procedure_0_accession_0_part_0_block_1_slide_0_slidetype
    var uid = 'patient_'+ids[0]+'_procedure_'+ids[1]+'_accession_'+ids[2]+'_part_'+ids[3]+'_block_'+ids[4]+'_slide_'+ids[5];
    var id= "#oleg_orderformbundle_orderinfotype_"+uid+"_slidetype";

    $(id).change(function(e) {   //.slidetype-combobox
        //console.log("slidetype-combobox changed: this id="+$(this).attr('id')+",class="+$(this).attr('class'));
        //e.preventDefault();
        var parent = $(this).parent().parent().parent().parent().parent().parent().parent().parent();
        //console.log("parent: id="+parent.attr('id')+",class="+parent.attr('class'));
        var blockValue = parent.find('.element-title').first();
        //console.log("slidetype-combobox: id="+parent.find('.slidetype-combobox').first().attr('id')+",class="+parent.find('.slidetype-combobox').first().attr('class'));
        var slideType = parent.find('.slidetype-combobox').first().select2('val');
        //console.log("blockValue: id="+blockValue.attr('id')+",class="+blockValue.attr('class')+",slideType="+slideType);
        var keyfield = parent.find('#check_btn');
        if( slideType == 3 ) {   //'Cytopathology'
            //console.log("Cytopathology is chosen = "+slideType);
            keyfield.attr('disabled','disabled'); 
            disableInElementBlock(parent.find('#check_btn').first(), true, "all", null, null);
            var htmlDiv = '<div class="element-skipped">Block is not used for cytopathology slide</div>';
            parent.find('.element-skipped').first().remove();
            blockValue.after(htmlDiv);
            blockValue.hide();
            parent.find('.form-btn-options').first().hide();
            //parent.find('.panel-body').first().css("border-color", "#C0C0C0");
        } else {    
            //disableInElementBlock(parent.find('#check_btn').first(), false, "all", null, null);
            disableInElementBlock(parent.find('#check_btn').first(), true, null, "notkey", null);
            parent.find('.element-skipped').first().remove();
            blockValue.show();
            keyfield.removeAttr('disabled'); 
            parent.find('.form-btn-options').first().show();
            //parent.find('.panel-body').first().css("border-color", "#1268B3");
        }
        
    });   
}

function setToFirstElement( target, dataarr ) {
    if( dataarr == undefined || dataarr.length == 0 ) {
        return;
    }
    var firstObj = dataarr[0];
    var firstId = firstObj.id;
    //console.log("first="+firstId);
    $(target).select2('val', firstId);
    //$(".ajax-combobox-return").select2('data', {id: "Filing Room", text: "Filing Room"});
}
