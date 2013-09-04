/**
 * User: oli2002
 * Date: 8/14/13
 * Time: 12:36 PM
 */

$(document).ready(function() {

    setNavBar();

    init();

    //hide buttons
    $("#orderinfo").hide();

    $("#optional_button").hide();

    $('#next_button').on('click', function(event) {        
       $("#next_button").hide();
       $("#optional_button").show();
    });

    //priority options
    $('#priority_option').collapse({
        toggle: false
    })
    $('#oleg_orderformbundle_orderinfotype_priority').change(function(e) {
        e.preventDefault();  
        $('#priority_option').collapse('toggle');
    });

    var checked = $('form input[type=radio]:checked').val();
    if( checked == 1 ) {
//        alert(checked);
        $('#priority_option').collapse('toggle');
    }


    
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


function init() {

//    $(".combobox").combobox();

    //resolve
    $("select.combobox").select2({
        width: 'element',
        dropdownAutoWidth: true
        //selectOnBlur: true,
        //containerCssClass: 'combobox-width'
    });
       
 
////////////////keep user input but can't get it on controller/////////////////////
//     function format(state) {
//        alert(state.text); 
//        if (!state.id) return state.text; // optgroup
//        return state.text;
//    }
//    $(".combobox").select2({
//      placeholder:"Enter any tag",
//      createSearchChoice: function(term, data) { 
//          //alert(term);
//          var myVar = $("#start").find('.myClass').val();
//          if ($(data).filter(function() { 
//              //alert(term);
//              return this.text.localeCompare(term)===0; }).length===0) {return {id:term, text:term};
//          } 
//      },
//      selectOnBlur: true,
//      formatSelection: format,
//      formatSelectionTooBig: function (limit) { return "Only one tag"; },
//    });
/////////////########################################################///////////////

    // new dynamic tags work even after select2 init o/
    //tags.push({id: "cool", text: "cool"});
  
    
//    $('.combobox').select2({
//        //placeholder: 'Please select',
//        //width: 200,
//        selectOnBlur: true,
//        matcher: function(term, text) {
////            $.fn.select2.defaults.matcher.apply(this, arguments);
//            return true;          
//        },     
////        sortResults: function(results) {
////            if (results.length > 1) results.pop();
////            return results;
////        }
//    });

          
    
//    $(".combobox").select2({
//        formatNoMatches: function(term) {
//            alert("term="+term);
////        $('.select2-input').keyup(function(e) {
////        if(e.keyCode == 13) {
////            $('#my_modal').modal({show: true , backdrop : true , keyboard: true});
////                    // etc......
////        }
////    });
//            return "Press enter to do something";
//        }
//    });
    
//    $("#e23").select2({
//        width: 'element',
//        dropdownAutoWidth: true,
//        tags:["red", "green", "blue"],
//        
//    });
//     $("#e23").select2({
//        minimumInputLength: 1,
//        query: function (query) {
//            var data = {results: []}, i, j, s;
//            for (i = 1; i < 5; i++) {
//                s = "";
//                for (j = 0; j < i; j++) {s = s + query.term;}
//                data.results.push({id: query.term + i, text: s});
//            }
//            query.callback(data);
//        }
//    });


    //datepicker. TODO: cause minor error Cannot call method 'split' of undefined; var parts = date.split(format.separator) => preset date by js?
    if( $(".datepicker")[0] ) {
        $('.datepicker').datepicker();
    }

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

function addSameForm( name, patientid, procedureid, accessionid, partid, blockid, slideid, scanid, stainid ) {

    var uid = patientid+"_"+procedureid+"_"+accessionid+"_"+partid+"_"+blockid+"_"+slideid+"_"+scanid+"_"+stainid;

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

    $(holder).after( getForm( name, id, idsorig, ids, idsm ) );

    //bind listener to the toggle button
    bindToggleBtn( name + '_' + ids.join("_") );
    bindDeleteBtn( name + '_' + ids.join("_") );

    //create children nested forms
    var nameArray = ['patient', 'procedure', 'accession', 'part', 'block', 'slide', 'stain_scan' ];
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

    //add all element to listeners again, the same as in ready
    init();
}

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
    console.log(name+": add childs to="+holder+" uid="+idsu);

    $(holder).append( getForm( name, id, idsorig, ids, idsm  ) );
    bindToggleBtn( name + '_' + ids.join("_") );
    bindDeleteBtn( name + '_' + ids.join("_") );
}

//input: current form ids
function getForm( name, id, idsorig, ids, idsm ) {

    //console.log("getForm: "+name+"_"+", id="+id+", ids="+ids+', idsm='+idsm);

    //increment by 1 current object id
    var formbody = getFormBody( name, ids[0], ids[1], ids[2], ids[3], ids[4], ids[5], ids[6], ids[7] );

    var idsu = ids.join("_");
    var idsc = ids.join(",");

    if( name == "scan" || name == "stain" ) {
        var addbtn = "";
        var deletebtn = "";
        var itemCount = (id+2);
    } else {
        var addbtn = '<button id="form_add_btn_' + name + '_' + idsu + '" type="button" class="btn btn-xs btn_margin" onclick="addSameForm(\'' + name + '\''+ ',' + idsc + ')">Add</button>';
        var deletebtn = ' <button id="delete_form_btn_'+name+'_'+idsu+'" type="button" class="delete_form_btn btn btn-danger btn_margin btn-xs">Delete</button>';
        var itemCount = (id+1);
    }


    var formhtml =
        '<div id="formpanel_' +name + '_' + idsu + '" class="panel panel-'+name+'">' +
            '<div class="panel-heading" align="left">' +
            '<div id="form_body_toggle_'+ name + '_' + idsu +'" class="form_body_toggle_btn glyphicon glyphicon-folder-open" data-toggle="collapse" data-target="#form_body_'+name+'_'+idsu+'"></div>' +
            '&nbsp;' + capitaliseFirstLetter(name) + ' ' + itemCount +
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

//    var collectionHolder =  $('#'+name+'-data');
    var collectionHolder =  $('#patient-data');

    // Get the data-prototype explained earlier
    var prototype = collectionHolder.data('prototype-'+name);

    var newForm = prototype.replace(/__patient__/g, patientid);
    newForm = newForm.replace(/__specimen__/g, procedureid);
    newForm = newForm.replace(/__accession__/g, accessionid);
    newForm = newForm.replace(/__part__/g, partid);
    newForm = newForm.replace(/__block__/g, blockid);
    newForm = newForm.replace(/__slide__/g, slideid);
    newForm = newForm.replace(/__scan__/g, scanid);
    newForm = newForm.replace(/__stain__/g, stainid);

    //console.log("prot name= "+name+", form="+newForm);

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


//function onCwid(){
//    window.open("http://weill.cornell.edu/its/identity-security/identity/cwid/")
//}






