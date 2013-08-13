//problem: nested form collection - data prototype
//http://comments.gmane.org/gmane.comp.php.symfony.symfony2/2552
//http://forum.symfony-project.org/viewtopic.php?f=23&t=70069&p=170055&hilit=nested+form+collection#p170055

/// Get the ul that holds the collection of tags
var collectionHolder = $('.patient');
// setup an "add a tag" link
var $addTagLink = $('<a href="#" class="btn btn-primary add_tag_link">Add Patient</a>');
var $newLinkLi = $('<div class="patient-data"></div>').append($addTagLink);


/////specimen
var collectionHolder_specimen = $('.specimen');
var $addTagLink_specimen = $('<a href="#" class="btn btn-primary add_tag_link">Add Specimen</a>');
var $newLinkLi_specimen = $('<div class="specimen-data"></div>').append($addTagLink_specimen);

var patientCount = 0;   //'{{ form.patient | length }}';
var specimenCount = 0;  //'{{ form.patient.specimen | length }}';

$(document).ready(function() {

    addPatientBtn();

    //addSpecimenBtnTest();

    $addTagLink.on('click', function(e) {
        //alert("on click");
        // prevent the link from creating a "#" on the URL
        e.preventDefault();

        //add a new tag form (see next code block)
        addTagForm(collectionHolder, $newLinkLi);
    });

    $addTagLink_specimen.on('click', function(e) {
        // prevent the link from creating a "#" on the URL
        e.preventDefault();
        // add a new tag form (see next code block)
        addTagForm_specimen(collectionHolder_specimen, $newLinkLi_specimen);
    });

});


function addPatientBtn() {
    // add a delete link to all of the existing tag form li elements
    collectionHolder.find('.patient-data').each(function() {
        addTagFormDeleteLink($(this));
    });

    // add the "add a tag" anchor and li to the tags ul
    collectionHolder.append($newLinkLi);

    // count the current form inputs we have (e.g. 2), use that as the new
    // index when inserting a new item (e.g. 2)
    collectionHolder.data('index', collectionHolder.find(':input').length);
}

function addSpecimenBtnTest() {

    //alert("add specimen");

    //create specimen div
    $(".patient-data").append("<div class='specimen'>QQQQQQQQQ</div>");
    //collectionHolder_specimen = $('.patient-data');

    //$('<div class="patient"></div>').append('DDD');
    // add the "add a tag" anchor and li to the tags ul
    collectionHolder.append($newLinkLi_specimen);

    // count the current form inputs we have (e.g. 2), use that as the new
    // index when inserting a new item (e.g. 2)
    collectionHolder.data('index', collectionHolder_specimen.find(':input').length);
    //specimenCount = collectionHolder_specimen.find(':input').length;
}
function addSpecimenBtn( inputform ) {

    //alert("add specimen");

    //create specimen div
    $(".patient-data").append("<div class='specimen'>QQQQQQQQQ</div>");
    //collectionHolder_specimen = $('.patient-data');

    //$('<div class="patient"></div>').append('DDD');
    // add the "add a tag" anchor and li to the tags ul
    //collectionHolder_specimen.append($newLinkLi_specimen);
    inputform.append($newLinkLi_specimen);

    // count the current form inputs we have (e.g. 2), use that as the new
    // index when inserting a new item (e.g. 2)
    collectionHolder_specimen.data('index', collectionHolder_specimen.find(':input').length);
    //specimenCount = collectionHolder_specimen.find(':input').length;

    return inputform;
}


function addTagForm(collectionHolder, $newLinkLi) {

    // increase the index with one for the next item
    collectionHolder.data('index', index + 1);
    patientCount++;

    // Get the data-prototype explained earlier
    var prototype = collectionHolder.data('prototype-patient');

    // get the new index
    var index = collectionHolder.data('index');
    //alert('index='+index);
    // Replace '__name__' in the prototype's HTML to
    // instead be a number based on how many items we have
    //var newForm = prototype.replace(/__name__/g, index);
    var newForm = prototype.replace(/__patient__/g, patientCount);

    // Display the form in the page in an li, before the "Add a tag" link li
    var $newFormLi = $('<div class="patient-data"></div>').append(newForm);

    $newFormLi.prepend("<h3>"+patientCount+") Patient:</h3>");

    //specimen
    //$newFormLi = addSpecimenBtn($newFormLi);
    addSpecimenBtnTest();

    $newLinkLi.before($newFormLi);

    // add a delete link to the new form
    addTagFormDeleteLink($newFormLi);

}

//specimen
function addTagForm_specimen(collectionHolder_specimen, $newLinkLi_specimen) {

    // increase the index with one for the next item
    collectionHolder_specimen.data('index', index + 1);
    specimenCount++;

    //alert('add specimen');
    // Get the data-prototype explained earlier
    var prototype = collectionHolder.data('prototype-specimen');

    // get the new index
    var index = collectionHolder_specimen.data('index');

    //alert("patientCount="+patientCount+", index"+index);

    // Replace '__name__' in the prototype's HTML to
    // instead be a number based on how many items we have
    var newForm_specimen = prototype.replace(/__patient__/g, patientCount);
    newForm_specimen = newForm_specimen.replace(/__specimen__/g, specimenCount);

    alert(newForm_specimen);

    // Display the form in the page in an li, before the "Add a tag" link li
    var $newFormLi = $('.specimen-data').append(newForm_specimen);

    $newFormLi.prepend("<h3>"+specimenCount+") Specimen:</h3>")

    $newLinkLi_specimen.before( $newFormLi );
    
    // add a delete link to the new form
    //addTagFormDeleteLink_specimen($newFormLi);
}

function addTagFormDeleteLink($tagFormLi) {
    var $removeFormA = $('<a class="btn btn-primary btn-danger" href="#">Delete</a>');
    $tagFormLi.append($removeFormA);

    $removeFormA.on('click', function(e) {
        // prevent the link from creating a "#" on the URL
        e.preventDefault();

        // remove the li for the tag form
        $tagFormLi.remove();
    });
}


