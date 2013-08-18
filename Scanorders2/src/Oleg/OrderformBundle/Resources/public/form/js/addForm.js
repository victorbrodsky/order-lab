/**
 * Created with JetBrains PhpStorm.
 * User: oli2002
 * Date: 8/14/13
 * Time: 12:36 PM
 * To change this template use File | Settings | File Templates.
 */

//var id = 0;

//var collectionHolder = $('.orderinfo-data');

//$(document).ready(function() {
//
//    //addSameForm( 'patient' );
//
//});


function addSameForm( name, patientid, procedureid, accessionid, partid, blockid, slideid ) {

    var uid = patientid+"_"+procedureid+"_"+accessionid+"_"+partid+"_"+blockid+"_"+slideid;

    //alert("addSameForm="+uid);

    //place the form in the html page
    var holder = "#formpanel_"+name+"_"+uid;
    
    //prepare form ids and pass it as array
    
    $(holder).after( getForm( name, patientid, procedureid, accessionid, partid, blockid, slideid ) );

    //populate form with html data
    //addTagForm( name, patientid, procedureid, accessionid, partid, blockid, slideid );

    //append to form_body_ the rest of forms
//    if( name == 'patient') {
//        var uid = name+"_"+(patientid+1)+"_"+procedureid+"_"+accessionid+"_"+partid+"_"+blockid+"_"+slideid;
//        var holder = "#form_body_"+uid;
//        $(holder).after( getForm( 'procedure', patientid+1, procedureid, accessionid, partid, blockid, slideid ) );
//    }

    var nameArray = ['patient', 'procedure', 'accession', 'part', 'block', 'slide'];
    var length = nameArray.length
    var index = nameArray.indexOf(name);
    //console.log("index="+index+" len="+length);
    var parentName = name;
    for (var i = index+1; i < length; i++) {
        console.log("=> name="+nameArray[i]);
        addChildForms( parentName, nameArray[i], nameArray[i-1], patientid, procedureid, accessionid, partid, blockid, slideid );
    }

    //addRestForms( name, patientid, procedureid, accessionid, partid, blockid, slideid );
}

function addChildForms( parentName, name, prevName, patientid, procedureid, accessionid, partid, blockid, slideid ) {
    var btnids = getIds(name, patientid, procedureid, accessionid, partid, blockid, slideid);
    var idsu = btnids[2];
    //var nextName = btnids[3];

    //add 1 to preceding name only, the rest are 0s
    console.log(name + " " + prevName );
    //var idsu = patientid+"_"+procedureid+"_"+accessionid+"_"+partid+"_"+blockid+"_"+slideid;
    
    //idsu - +1 for the parent object (parentName) and -1 for current iterated object (name) (getForm will increment it by 1)
    //attach to previous object (prevName)
    var btnids = getIds( parentName, patientid, procedureid, accessionid, partid, blockid, slideid );
    var idsu = btnids[2];
    
//    var nameArr = btnids[2].split("_"); 
//    var btnids2 = getIds( name, nameArr[0], nameArr[1], nameArr[2], nameArr[3], nameArr[4], nameArr[5] );
//    var idsu = btnids2[2];

    var uid = prevName+"_"+idsu;
    var holder = "#form_body_"+uid;
    console.log(name+": add childs to="+holder+" uid="+idsu);

    //now use idsu and minus 1 for inserted object (name)
    var idArr = idsu.split("_"); 
    var btnids2 = getIdsMinus( name, idArr[0], idArr[1], idArr[2], idArr[3], idArr[4], idArr[5] );  
    var resArr = btnids2[2].split("_"); 

    $(holder).append( getForm( name, resArr[0], resArr[1], resArr[2], resArr[3], resArr[4], resArr[5] ) );
//    $(holder).after( getForm( name, patientid, procedureid, accessionid, partid, blockid, slideid ) );

}

//input: current form ids
function getForm( name, patientid, procedureid, accessionid, partid, blockid, slideid ) {

    console.log("getForm: "+name+"_"+patientid+"_"+procedureid+"_"+accessionid+"_"+partid+"_"+blockid+"_"+slideid);

    //increment by 1 current object id
    var btnids = getIds(name, patientid, procedureid, accessionid, partid, blockid, slideid);
    var id = btnids[0];
    var ids = btnids[1];
    var idsu = btnids[2];

    //alert(idsu);

    var formbody = getFormBody( name, patientid, procedureid, accessionid, partid, blockid, slideid );

    var formhtml =
        '<div id="formpanel_' +name + '_' + idsu + '" class="panel panel-'+name+'">' +
            '<div class="panel-heading" align="left">' +
                '<a style="background-color:white;" data-toggle="collapse" href="#form_body_' + name + '_' + idsu + '">+/-</a> &nbsp;' +
                capitaliseFirstLetter(name) + ' ' + id + '&nbsp;' +
                '<button id="form_add_btn_' + idsu + '" type="button" class="btn btn-mini btn_margin" onclick="addSameForm(\'' + name + '\''+ ',' + ids + ')">Add ' + capitaliseFirstLetter(name) + '</button>' +
            '</div>' +
            '<div id="form_body_' + name + '_' + idsu + '" class="panel-body collapse in">' + formbody + '</div>' +
        '</div>';
//        '<button id="form_add_btn" type="button" class="btn btn_margin" onclick="addSameForm(\'' + name + '\')">Add ' + name + '</button>';

    //remove previous form add button
    var uid =patientid+"_"+procedureid+"_"+accessionid+"_"+partid+"_"+blockid+"_"+slideid;
    //alert("remove uid="+uid);
    $('#form_add_btn_'+name+'_'+uid).remove();

    return formhtml;
}

function getFormBody( name, patientid, procedureid, accessionid, partid, blockid, slideid ) {

    //var uid = name+"_"+patientid+"_"+procedureid+"_"+accessionid+"_"+partid+"_"+blockid+"_"+slideid;
    //var formbody = "#form_body_" + uid;

    var btnids = getIds(name, patientid, procedureid, accessionid, partid, blockid, slideid);
    var id = btnids[0];

    var collectionHolder =  $('#'+name+'-data');

    console.log("prot name = "+name);

    // Get the data-prototype explained earlier
    var prototype = collectionHolder.data('prototype');

    // get the new index
    //var index = collectionHolder.data('index');

    //var myRegExp = new RegExp("__"+name+"__",'gi');
    //var newForm = prototype.replace(myRegExp, id);
     
    var resArr = btnids[2].split("_"); 
    
    var newForm = prototype.replace(/__patient__/g, resArr[0]);
    newForm = newForm.replace(/__specimen__/g, resArr[1]);
    newForm = newForm.replace(/__accession__/g, resArr[2]);
    newForm = newForm.replace(/__part__/g, resArr[3]);
    newForm = newForm.replace(/__block__/g, resArr[4]);
    newForm = newForm.replace(/__slide__/g, resArr[5]);

//    var newForm = prototype.replace(/__patient__/g, patientid);
//    newForm = newForm.replace(/__specimen__/g, procedureid);
//    newForm = newForm.replace(/__accession__/g, accessionid);
//    newForm = newForm.replace(/__part__/g, partid);
//    newForm = newForm.replace(/__block__/g, blockid);
//    newForm = newForm.replace(/__slide__/g, slideid);

    //In order to have a correct form here replace all parents name with ids...

    //alert(newForm);

    // Display the form in the page in an li, before the "Add a tag" link li
    //var $newFormLi = $(formbody).append(newForm);

    return newForm;

    //$newFormLi.prepend("<h3>"+patientCount+") Patient:</h3>");

    //specimen
    //$newFormLi = addSpecimenBtn($newFormLi);
    //addSpecimenBtnTest();


    //var $addTagLink = $('<a href="#" class="btn btn-primary add_tag_link">Add Patient</a>');
    //var $newLinkLi = $('<div class="patient-data"></div>').append($addTagLink);
    //var $newLinkLi = $('<div class="patient-data"></div>');

    //$newLinkLi.before($newFormLi);

    // add a delete link to the new form
    //addTagFormDeleteLink($newFormLi);
}

//Helpers
function capitaliseFirstLetter(string)
{
    return string.charAt(0).toUpperCase() + string.slice(1);
}

function getIds( name, patientid, procedureid, accessionid, partid, blockid, slideid ) {
    var id = 0;
    var nextName = "";

    switch(name)
    {
        case "patient":           
            patientid++;
            id = patientid;
            nextName = "procedure";
            break;
        case "procedure":           
            procedureid++;
            id = procedureid;
            nextName = "accession";
            break;
        case "accession":           
            accessionid++;
            id = accessionid;
            nextName = "part";
            break;
        case "part":           
            partid++;
            id = partid;
            nextName = "block";
            break;
        case "block":           
            blockid++;
            id = blockid;
            nextName = "slide";
            break;
        case "slide":           
            slideid++;
            id = slideid;
            nextName = "";
            break;
        default:
            id = 0;
    }

    var ids = patientid+","+procedureid+","+accessionid+","+partid+","+blockid+","+slideid;
    var idsu = patientid+"_"+procedureid+"_"+accessionid+"_"+partid+"_"+blockid+"_"+slideid;

    var res_array = [];
    res_array.push( id );       //0
    res_array.push( ids );      //1
    res_array.push( idsu );     //2
    res_array.push( nextName ); //3

    return res_array;
}

function getIdsMinus( name, patientid, procedureid, accessionid, partid, blockid, slideid ) {
    var id = 0;
    var nextName = "";

    switch(name)
    {
        case "patient":
            id = patientid;
            patientid--;
            nextName = "procedure";
            break;
        case "procedure":
            id = procedureid;
            procedureid--;
            nextName = "accession";
            break;
        case "accession":
            id = accessionid;
            accessionid--;
            nextName = "part";
            break;
        case "part":
            id = partid;
            partid--;
            nextName = "block";
            break;
        case "block":
            id = blockid;
            blockid--;
            nextName = "slide";
            break;
        case "slide":
            id = slideid;
            slideid--;
            nextName = "";
            break;
        default:
            id = 0;
    }

    var ids = patientid+","+procedureid+","+accessionid+","+partid+","+blockid+","+slideid;
    var idsu = patientid+"_"+procedureid+"_"+accessionid+"_"+partid+"_"+blockid+"_"+slideid;

    var res_array = [];
    res_array.push( id );       //0
    res_array.push( ids );      //1
    res_array.push( idsu );     //2
    res_array.push( nextName ); //3

    return res_array;
}