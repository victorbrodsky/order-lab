/**
 * Created with JetBrains PhpStorm.
 * User: oli2002
 * Date: 8/14/13
 * Time: 12:36 PM
 * To change this template use File | Settings | File Templates.
 */

//var id = 0;

var collectionHolder = $('.orderinfo-data');

//$(document).ready(function() {
//
//    //addSameForm( 'patient' );
//
//});


function addSameForm( name, id ) {

    alert("addSameForm="+name+" id="+id);

    //append to existing id
    id = id - 1;

    //var form = "{{ include('OlegOrderformBundle::MultyScanOrder/sameform.html.twig', {'name':'"+name+"','id':"+id+"}) }}";

    var holder = "#formpanel_collection_"+name+"_"+id;

//    $('#formpanel_collection_patient_1').append( sameForm( name, id ) );
    $(holder).append( sameForm( name, id ) );

    var formholder = "#form_body_" + name + "_" + id;

    addTagForm( name, id, formholder );
}

function sameForm( name, id ) {

    var uid = name + "_" + id;

    var formhtml =
        '<div class="panel panel-primary">' +
            '<div class="panel-heading">' +
                name + ' ' + id +
                    '<button id="form_body_btn_' + uid + '" type="button" class="btn btn_margin" data-toggle="collapse" data-target="#form_body_' + uid + '">+/-</button>'+
            '</div>' +
            '<div id="form_body_' + uid + '" class="panel-body collapse in"></div>' +
        '</div>';
//        '<button id="form_add_btn" type="button" class="btn btn_margin" onclick="addSameForm(\'' + name + '\')">Add ' + name + '</button>';

    return formhtml;
}

function addTagForm( name, id, formholder ) {

    // Get the data-prototype explained earlier
    var prototype = collectionHolder.data('prototype-patient');

    // get the new index
    var index = collectionHolder.data('index');
    //alert('index='+index);
    // Replace '__name__' in the prototype's HTML to
    // instead be a number based on how many items we have
    //var newForm = prototype.replace(/__name__/g, index);
    var newForm = prototype.replace(/__"+name+"__/g, id);

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
