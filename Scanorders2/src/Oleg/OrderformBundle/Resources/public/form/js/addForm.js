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


function addSameForm( name, id, pid ) {

    alert("addSameForm="+name+" id="+id);

    id++;

    //var form = "{{ include('OlegOrderformBundle::MultyScanOrder/sameform.html.twig', {'name':'"+name+"','id':"+id+"}) }}";

    //append to existing id

//    var holder = "#formpanel_collection_"+name+"_"+(id-1);
//    var holder = "#formpanel";

//    $('#formpanel_collection_patient_1').append( sameForm( name, id ) );
//    $(holder).append( sameForm( name, id, pid ) );

//    var holder = ".data_"+name+"_"+(id-1)+"_"+pid;
    var holder = "#formpanel_"+name+"_"+(id-1)+"_"+pid;
//    alert(holder);
    $(holder).after( sameForm( name, id, pid ) );

    var formholder = "#form_body_" + name + "_" + id;

    addTagForm( name, id, formholder );
}

function sameForm( name, id, pid ) {

    var uid = name + "_" + id;

    var formhtml =
        '<div class="panel panel-primary">' +
            '<div class="panel-heading" align="left">' +
                '<a style="background-color:white;" data-toggle="collapse" href="#form_body_' + uid + '">+/-</a> &nbsp;' +
                capitaliseFirstLetter(name) + ' ' + id + '&nbsp;' +
                '<button id="form_add_btn" type="button" class="btn btn-mini btn_margin" onclick="addSameForm(\'' + name + '\''+ id + ',' + pid + ')">Add ' + capitaliseFirstLetter(name) + '</button>' +
            '</div>' +
            '<div id="form_body_' + uid + '" class="panel-body collapse in"></div>' +
        '</div>';
//        '<button id="form_add_btn" type="button" class="btn btn_margin" onclick="addSameForm(\'' + name + '\')">Add ' + name + '</button>';

    return formhtml;
}

function addTagForm( name, id, formholder ) {

    var collectionHolder =  $('#'+name+'-data');

    // Get the data-prototype explained earlier
    var prototype = collectionHolder.data('prototype');

    // get the new index
    //var index = collectionHolder.data('index');

    var myRegExp = new RegExp("__"+name+"__",'gi');
    var newForm = prototype.replace(myRegExp, id);

    //In order to have a correct form here replace all parents name with ids...

    alert("strtoreplace="+strtoreplace+" :"+newForm);

    // Display the form in the page in an li, before the "Add a tag" link li
    var $newFormLi = $(formholder).append(newForm);

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