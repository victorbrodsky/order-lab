//problem: nested form collection - data prototype
//http://comments.gmane.org/gmane.comp.php.symfony.symfony2/2552
//http://forum.symfony-project.org/viewtopic.php?f=23&t=70069&p=170055&hilit=nested+form+collection#p170055

/// Get the ul that holds the collection of tags
var collectionHolder = $('ul.tags');
// setup an "add a tag" link
var $addTagLink = $('<a href="#" class="btn btn-primary add_tag_link">Add a tag</a>');
var $newLinkLi = $('<li></li>').append($addTagLink);


// Get the ul that holds the collection of Specimen
var collectionHolder_specimen = $('ul.li.div.specimen');
// setup an "add a tag" link
var $addSpecimenLink = $('<a href="#" class="btn btn-primary btn-success add_specimen_link">Add specimen</a>');
var $newLinkLi_specimen = $('<li></li>').append($addSpecimenLink);
//alert(collectionHolder_specimen);

$(document).ready(function() {
    
    // add a delete link to all of the existing tag form li elements
    collectionHolder.find('li').each(function() {
        addTagFormDeleteLink($(this));
    });
    
    collectionHolder_specimen.append($newLinkLi_specimen);
    
    // add the "add a tag" anchor and li to the tags ul
    collectionHolder.append($newLinkLi);

    // count the current form inputs we have (e.g. 2), use that as the new
    // index when inserting a new item (e.g. 2)
    collectionHolder.data('index', collectionHolder.find(':input').length);

    $addTagLink.on('click', function(e) {
        // prevent the link from creating a "#" on the URL
        e.preventDefault();

        // add a new tag form (see next code block)
        addTagForm(collectionHolder, $newLinkLi);
    });
    
                     
});

function addTagForm(collectionHolder, $newLinkLi) {
    // Get the data-prototype explained earlier
    var prototype = collectionHolder.data('prototype');

    // get the new index
    var index = collectionHolder.data('index');

    // Replace '__name__' in the prototype's HTML to
    // instead be a number based on how many items we have
    var newForm = prototype.replace(/__name__/g, index);

    // increase the index with one for the next item
    collectionHolder.data('index', index + 1);

    // Display the form in the page in an li, before the "Add a tag" link li
    var $newFormLi = $('<li></li>').append(newForm);
    $newLinkLi.before($newFormLi);
    
    // add a delete link to the new form
    addTagFormDeleteLink($newFormLi);
    
    
    
    //specimen  
    // add the "add a tag" anchor and li to the tags ul
    collectionHolder_specimen.append($newLinkLi_specimen);
    // count the current form inputs we have (e.g. 2), use that as the new
    // index when inserting a new item (e.g. 2)
    collectionHolder_specimen.data('index', collectionHolder_specimen.find(':input').length);
    $addSpecimenLink.on('click', function(e) {
        // prevent the link from creating a "#" on the URL
        e.preventDefault();
        // add a new tag form (see next code block)
        addTagForm(collectionHolder_specimen, $newLinkLi_specimen);
    });
}

//specimen
function addTagForm_specimen(collectionHolder, $newLinkLi) {
    // Get the data-prototype explained earlier
    var prototype = collectionHolder.data('prototype_spec');

    // get the new index
    var index = collectionHolder.data('index');

    // Replace '__name__' in the prototype's HTML to
    // instead be a number based on how many items we have
    var newForm = prototype.replace(/__name__/g, index);

    // increase the index with one for the next item
    collectionHolder.data('index', index + 1);

    // Display the form in the page in an li, before the "Add a tag" link li
    var $newFormLi = $('<li></li>').append(newForm);
    $newLinkLi.before($newFormLi);
    
    // add a delete link to the new form
    addTagFormDeleteLink($newFormLi);
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


