/**
 * Created with JetBrains PhpStorm.
 * User: oli2002
 * Date: 8/14/13
 * Time: 12:36 PM
 * To change this template use File | Settings | File Templates.
 */

function addSameForm( name, patientid, procedureid, accessionid, partid, blockid, slideid ) {

    var uid = patientid+"_"+procedureid+"_"+accessionid+"_"+partid+"_"+blockid+"_"+slideid;

    //alert("addSameForm="+uid);

    //place the form in the html page
    var holder = "#formpanel_"+name+"_"+uid;
    
    //prepare form ids and pass it as array
    //increment by 1 current object id
    var btnids = getIds(name, patientid, procedureid, accessionid, partid, blockid, slideid);
    var id = btnids['id'];  
    var idsorig = btnids['orig'];
    var ids = btnids['ids'];
    var idsm = btnids['idsm'];
    
    $(holder).after( getForm( name, id, idsorig, ids, idsm ) );

    //create children nested forms
    var nameArray = ['patient', 'procedure', 'accession', 'part', 'block', 'slide'];
    var length = nameArray.length
    var index = nameArray.indexOf(name);
    //console.log("index="+index+" len="+length);
    var parentName = name;
    for (var i = index+1; i < length; i++) {
        //console.log("=> name="+nameArray[i]);
        addChildForms( parentName, nameArray[i], nameArray[i-1], patientid, procedureid, accessionid, partid, blockid, slideid );
    }

     //remove previous form add button only for parent object
    var uid = idsorig.join("_"); 
    //console.log("remove="+'#form_add_btn_'+name+'_'+uid);
    $('#form_add_btn_'+name+'_'+uid).remove();
}

function addChildForms( parentName, name, prevName, patientid, procedureid, accessionid, partid, blockid, slideid ) {
    
    //idsu: +1 for the parent object (parentName)
    //attach to previous object (prevName)
    var btnids = getIds( parentName, patientid, procedureid, accessionid, partid, blockid, slideid );
    var idsorig = btnids['orig'];
    var ids = btnids['ids']; 
    var idsm = btnids['idsm']; 
    var id = btnids['id'];
    id = id - 1;
    var idsu = ids.join("_");

    var uid = prevName+"_"+idsu;
    var holder = "#form_body_"+uid;
    //console.log(name+": add childs to="+holder+" uid="+idsu);

    $(holder).append( getForm( name, id, idsorig, ids, idsm  ) );  
}

//input: current form ids
function getForm( name, id, idsorig, ids, idsm ) {
  
    //console.log("getForm: "+name+"_"+", id="+id+", ids="+ids+', idsm='+idsm);

    //increment by 1 current object id
    var formbody = getFormBody( name, ids[0], ids[1], ids[2], ids[3], ids[4], ids[5] );

    var idsu = ids.join("_"); 
    var idsc = ids.join(",");

    var formhtml =
        '<div id="formpanel_' +name + '_' + idsu + '" class="panel panel-'+name+'">' +
            '<div class="panel-heading" align="left">' +
                '<a style="background-color:white;" data-toggle="collapse" href="#form_body_' + name + '_' + idsu + '">+/-</a> &nbsp;' +
                capitaliseFirstLetter(name) + ' ' + (id+1) + '&nbsp;' +
                '<button id="form_add_btn_' + name + '_' + idsu + '" type="button" class="btn btn-mini btn_margin" onclick="addSameForm(\'' + name + '\''+ ',' + idsc + ')">Add ' + capitaliseFirstLetter(name) + '</button>' +
            '</div>' +
            '<div id="form_body_' + name + '_' + idsu + '" class="panel-body collapse in">' + formbody + '</div>' +
        '</div>';
//        '<button id="form_add_btn" type="button" class="btn btn_margin" onclick="addSameForm(\'' + name + '\')">Add ' + name + '</button>';

    //remove previous form add button only for parent object
//    var uid = idsorig.join("_"); 
//    //console.log("remove="+'#form_add_btn_'+name+'_'+uid);
//    $('#form_add_btn_'+name+'_'+uid).remove();

    return formhtml;
}

function getFormBody( name, patientid, procedureid, accessionid, partid, blockid, slideid ) {

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
  
    console.log("prot name= "+name+", form="+newForm);
  
    return newForm;
}

//Helpers
function capitaliseFirstLetter(string)
{
    return string.charAt(0).toUpperCase() + string.slice(1);
}

function getIds( name, patientid, procedureid, accessionid, partid, blockid, slideid ) {
    var id = 0;
    var nextName = "";

    var patientidm = patientid;
    var procedureidm = procedureid;
    var accessionidm = accessionid;
    var partidm = partid;
    var blockidm = blockid;
    var slideidm = slideid;
    
    var orig = [patientid, procedureid, accessionid, partid, blockid, slideid];
    
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
        default:
            id = 0;
    }

    var idsArray = [patientid, procedureid, accessionid, partid, blockid, slideid];
    var idsArrayM = [patientidm, procedureidm, accessionidm, partidm, blockidm, slideidm];
    
    var res_array = { 
            'id' : id, 
            'orig' : orig,
            'ids' : idsArray, 
            'idsm' : idsArrayM, 
            'nextName' : nextName
        };  
    
    return res_array;
}
