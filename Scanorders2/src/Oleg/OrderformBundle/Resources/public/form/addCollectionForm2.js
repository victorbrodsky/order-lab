//problem: nested form collection - data prototype
//http://comments.gmane.org/gmane.comp.php.symfony.symfony2/2552
//http://forum.symfony-project.org/viewtopic.php?f=23&t=70069&p=170055&hilit=nested+form+collection#p170055

/// Get the ul that holds the collection of tags
var collectionHolder_specimen = $('ul.specimen');
// setup an "add a tag" link
var $addTagLink_specimen = $('<a href="#" class="btn btn-primary add_tag_link">Add Specimen</a>');
var $newLinkLi_specimen = $('<li></li>').append($addTagLink_specimen);


$(document).ready(function() {

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

});

function addTagForm_specimen(collectionHolder_specimen, $newLinkLi_specimen) {
    // Get the data-prototype explained earlier
    var prototype = collectionHolder_specimen.data('prototype-specimen');

    // get the new index
    var index = collectionHolder_specimen.data('index');

    // Replace '__name__' in the prototype's HTML to
    // instead be a number based on how many items we have
    //var newForm = prototype.replace(/__name__/g, index);
    var newForm = prototype-specimen.replace(/__specimen__/g, index);

    // increase the index with one for the next item
    collectionHolder_specimen.data('index', index + 1);

    // Display the form in the page in an li, before the "Add a tag" link li
    var $newFormLi_specimen = $('<li></li>').append(newForm);
    $newLinkLi_specimen.before($newFormLi_specimen);

//    collectionHolder.append($newLinkLi_specimen);
    // add a delete link to the new form
    addTagFormDeleteLink_specimen($newFormLi_specimen);

}



