//problem: nested form collection - data prototype
//http://comments.gmane.org/gmane.comp.php.symfony.symfony2/2552
//http://forum.symfony-project.org/viewtopic.php?f=23&t=70069&p=170055&hilit=nested+form+collection#p170055

/// Get the ul that holds the collection of tags
var collectionHolder = $('.patient');
// setup an "add a tag" link
var $addTagLink = $('<a href="#" class="btn btn-primary add_tag_link">Add Patient</a>');
var $newLinkLi = $('<div class="patient-data"></div>').append($addTagLink);


///specimen
var collectionHolder_specimen = $('.patient');
var $addTagLink_specimen = $('<a href="#" class="btn btn-primary add_tag_link">Add Specimen</a>');
var $newLinkLi_specimen = $('<div class="specimen-data"></div>').append($addTagLink_specimen);

var emailCount = '{{ form.patient | length }}';
var specimenCount = '{{ form.patient.specimen | length }}';

$(document).ready(function() {

    /////////test ////////////
    $('#add-another-email').click(function() {
        var emailList = $('#email-fields-list');

        // grab the prototype template
        var newWidget = emailList.attr('data-prototype');
        // replace the "__name__" used in the id and name of the prototype
        // with a number that's unique to your emails
        // end name attribute looks like name="contact[emails][2]"
        newWidget = newWidget.replace(/__patient__/g, emailCount);
        emailCount++;

        // create a new list element and add it to the list
        var newLi = $('<li></li>').html(newWidget);
        newLi.appendTo($('#email-fields-list'));

        //add specimen button
//        $(newWidget).append('<a href="#" id="add-another-specimen">Add another specimen</a>');
        $('<p><a href="#" id="add-another-specimen">Add another specimen</a></p>').appendTo("#email-fields-list");
        return false;
    });
//    $('#add-another-specimen').click(function() {
    $('#add-another-specimen').on('click', function(e) {
        alert('add specimen');
        var emailList = $('#email-fields-list');

        // grab the prototype template
        var newWidget = emailList.attr('data-prototype-specimen');
        // replace the "__name__" used in the id and name of the prototype
        // with a number that's unique to your emails
        // end name attribute looks like name="contact[emails][2]"
        newWidget = newWidget.replace(/__specimen__/g, specimenCount);
        specimenCount++;

        // create a new list element and add it to the list
        var newLi = $('<li></li>').html(newWidget);
        newLi.appendTo($('#email-fields-list'));

        return false;
    });
    /////////// end of test ///////////////


    // add a delete link to all of the existing tag form li elements
    collectionHolder.find('.patient-data').each(function() {
        addTagFormDeleteLink($(this));
    });  
    
    // add the "add a tag" anchor and li to the tags ul
    collectionHolder.append($newLinkLi);

    // count the current form inputs we have (e.g. 2), use that as the new
    // index when inserting a new item (e.g. 2)
    collectionHolder.data('index', collectionHolder.find(':input').length);

    $addTagLink.on('click', function(e) {
        //alert("on click");
        // prevent the link from creating a "#" on the URL
        e.preventDefault();

        //add a new tag form (see next code block)
        addTagForm(collectionHolder, $newLinkLi);
    });


                       
});

function addTagForm(collectionHolder, $newLinkLi) {  
    // Get the data-prototype explained earlier
    var prototype = collectionHolder.data('prototype-patient');

    // get the new index
    var index = collectionHolder.data('index');

    // Replace '__name__' in the prototype's HTML to
    // instead be a number based on how many items we have
    //var newForm = prototype.replace(/__name__/g, index);
    var newForm = prototype.replace(/__patient__/g, index);

    // increase the index with one for the next item
    collectionHolder.data('index', index + 1);

    // Display the form in the page in an li, before the "Add a tag" link li
    var $newFormLi = $('<div class="patient-data"></div>').append(newForm);
    $newLinkLi.before($newFormLi);
    
//    collectionHolder.append($newLinkLi_specimen); 
    // add a delete link to the new form
    addTagFormDeleteLink($newFormLi);
//    $newLinkLi_specimen.on('click', function(e) {
//        // prevent the link from creating a "#" on the URL
//        e.preventDefault();
//        // add a new tag form (see next code block)
//        addTagForm_specimen(collectionHolder, $newLinkLi_specimen);
//    });


    //specimen
    //$('<div class="patient"></div>').append('DDD');
    // add the "add a tag" anchor and li to the tags ul
    collectionHolder_specimen.append($newLinkLi_specimen);
    // count the current form inputs we have (e.g. 2), use that as the new
    // index when inserting a new item (e.g. 2)
    collectionHolder_specimen.data('index', collectionHolder_specimen.find(':input').length);
    $addTagLink_specimen.on('click', function(e) {
        // prevent the link from creating a "#" on the URL
        e.preventDefault();
        // add a new tag form (see next code block)
        addTagForm_specimen(collectionHolder_specimen, $newLinkLi_specimen);
    });
}

//specimen
function addTagForm_specimen(collectionHolder_specimen, $newLinkLi_specimen) {
    //alert('add specimen');
    // Get the data-prototype explained earlier
    var prototype = collectionHolder_specimen.data('prototype-specimen');

    // get the new index
    var index = collectionHolder_specimen.data('index');

    // Replace '__name__' in the prototype's HTML to
    // instead be a number based on how many items we have
    var newForm_specimen = prototype.replace(/__specimen__/g, index);

    // increase the index with one for the next item
    collectionHolder_specimen.data('index', index + 1);

    // Display the form in the page in an li, before the "Add a tag" link li
//    var $newFormLi = $('.specimen-data').append(newForm);
    var $newFormLi_specimen = $('<div class="test"></div>').append(newForm_specimen);
    $newLinkLi_specimen.before($newFormLi_specimen);
    
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


