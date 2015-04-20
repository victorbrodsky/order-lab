/**
 * Created by oli2002 on 8/22/14.
 */



//prevent exit modified form
function windowCloseAlert() {

    //console.log("window Close Alert");

    if( cycle == "show_user" ) {
        return;
    }

    window.onbeforeunload = confirmModifiedFormExit;

    function confirmModifiedFormExit() {

        var modified = false;

        if( $('#form-prototype-data').length != 0 ) {
            modified = true;    //checkIfUserWasModified();
        }

        //console.log("modified="+modified);
        if( modified === true ) {
            return "The changes you have made will not be saved if you navigate away from this page.";
        } else {
            return;
        }
    }

    $('form').submit(function() {
        window.onbeforeunload = null;
    });
}




//do not show the [X] (delete) button in the right upper corner of "Employment Period(s)" if it is the only one being displayed.
// When the user adds another one, then show an [X] next to each one.
function processEmploymentStatusRemoveButtons( btn, action ) {

    if( cycle == "show_user" ) {
        return;
    }

    var btnCountTreshold = 1;

    //calculate btnCountTreshold
    if( !action && typeof action != "undefined" ) {
        //init
        btnCountTreshold = 1;
    } else {
        if( action == 'remove' ) {
            btnCountTreshold = 2;   //this performs before button is deleted
        } else {
            btnCountTreshold = 1;
        }
    }
    //console.log('btnCountTreshold='+btnCountTreshold);

    if( typeof btn == "undefined" || !btn ) {
        //console.log('btn is not defined');
        $('.btn-remove-minimumone-collection').each( function(e){
            hideShowDeleteBtn(this,btnCountTreshold);
        });
    } else {
        hideShowDeleteBtn(btn,btnCountTreshold)
    }
}

//btn might be add or delete button
function hideShowDeleteBtn(btn,btnCountTreshold) {
    var btnEl = $(btn);
    var remBtns = btnEl.closest('.panel-body').find('.btn-remove-minimumone-collection');

    //console.log(remBtns);
    //console.log('remBtns.length='+remBtns.length+', btnCountTreshold='+btnCountTreshold);

    if( remBtns.length == 0 ) {
        return;
    }

    if( remBtns.length > btnCountTreshold ) {
        //more than one element: show all remove buttons
        remBtns.show();
    } else {
        //0 or 1 element: hide remove buttons
        remBtns.hide();
    }
}

//on user load take care of hidden wells
function positionTypeListener() {
    $('.appointmenttitle-position-field').not("*[id^='s2id_']").each(function(e) {
        positionTypeAction(this);
    });

    //pgy update listener
    $('.pgylevel-field,.pgystart-field').on('change',function(e) {
        updateExpectedPgyListener( $(this) );
    });

    //pgy expected field init
    $('.pgylevelexpected-field').each(function(e) {
        //console.log('update expectedPgyLevel');
        updateExpectedPgyListener( $(this) );
    });

}

function updateExpectedPgyListener( element ) {
    var holder = element.closest('.user-collection-holder');
    var expectedPgyLevel = calculateExpectedPgy( element );
    //console.log('expectedPgyLevel='+expectedPgyLevel);
    holder.find('.pgylevelexpected-field').val(expectedPgyLevel);
}

//In the section "Academic Appointment Title(s)", if "Resident" is selected in the "Position Type" dropdown menu,
// unfold a second drop down under it with a field called "Residency Track:" and show three choices: "AP", "CP", and "AP/CP".
function positionTypeAction(element) {
    var fieldEl = $(element);
    //console.log(fieldEl);

    var holder = fieldEl.closest('.user-collection-holder');
    //console.log(holder);

    if( !holder.hasClass('user-appointmentTitles') ) {
        return;
    }

    //printF(fieldEl,'field el:');

    var value = fieldEl.select2('val');
    //console.log('value='+value);

    holder.find('.appointmenttitle-residencytrack-field').hide();
    holder.find('.appointmenttitle-fellowshiptype-field').hide();
    holder.find('.appointmenttitle-pgy-field').hide();

    if( value == 'Resident' ) {
        holder.find('.appointmenttitle-residencytrack-field').show();
        holder.find('.appointmenttitle-pgy-field').show();
    }

    if( value == 'Fellow' ) {
        holder.find('.appointmenttitle-fellowshiptype-field').show();
        holder.find('.appointmenttitle-pgy-field').show();
    }
}


function initUpdateExpectedPgy() {
    $('.update-pgy-btn').each( function() {

        var expectedPgyLevel = calculateExpectedPgy( $(this) );

        if( expectedPgyLevel != null ) {

            var holder = $(this).closest('.user-collection-holder');
            //console.log(holder);

            if( !holder.hasClass('user-appointmentTitles') ) {
                return;
            }

            //console.log( 'pgylevel='+pgylevel+', curYear='+curYear);
            holder.find('.pgylevelexpected-field').val(expectedPgyLevel);
        }
    });
}

function updatePgy(btn) {

    var btnEl = $(btn);

    var holder = btnEl.closest('.user-collection-holder');
    //console.log(holder);

    if( !holder.hasClass('user-appointmentTitles') ) {
        return;
    }

    var pgystart = holder.find('.pgystart-field').val();
    var pgylevel = holder.find('.pgylevel-field').val();
    var pgylevelexpected = holder.find('.pgylevelexpected-field').val();

    //console.log( 'pgystart='+pgystart+', pgylevel='+pgylevel+', pgylevelexpected='+pgylevelexpected);

    //A- If both field have no value - the button does nothing
    if( pgystart == "" && pgylevel == "" ) {
        return;
    }

    //C- If only the PGY level has value, the button does nothing
    if( pgystart == "" && pgylevel != "" ) {
        return;
    }

    var today = new Date();
    var curYear = today.getFullYear();

    //B- If only the date has value - the button updates the year of the date to current (does not change month of date)
    if( pgystart != "" && pgylevel == "" ) {
        var pgyDate = new Date(pgystart);
        pgyDate.setFullYear(curYear);
        //console.log( 'pgyDate='+pgyDate);

        holder.find('.pgystart-field').datepicker( 'setDate', pgyDate );
        holder.find('.pgystart-field').datepicker( 'update');
    }


    //During academic year that started on: [July 1st 2011]
    //The Post Graduate Year (PGY) level was: [1]
    //Expected Current Post Graduate Year (PGY) level: [4] (not a true fleld in the database, not editble)
    //
    //D- If both the date and the PGY have value and the academic year is not current
    // (meaning the current date is later than listed date +1 year (in the example above, if current date is later than July 1st 2012) ,
    // the function takes the current year (for example 2014), subtracts the year in the date field (let's say 2011), and add the result to the current PGY level value
    // (let's say 1, replacing it with 4), then updates the year of the field with current (2011->2014).
    if( pgystart != "" && pgylevel != "" ) {

        var pgyDate = new Date(pgystart);

        var expectedPgyLevel = calculateExpectedPgy(btnEl);

        if( expectedPgyLevel != null ) {

            //console.log( 'pgylevel='+pgylevel+', curYear='+curYear);
            holder.find('.pgylevel-field').val(expectedPgyLevel);
            holder.find('.pgylevelexpected-field').val(expectedPgyLevel);

            //updates the year of the field with current (2011->2014)
            pgyDate.setFullYear(curYear);
            holder.find('.pgystart-field').datepicker( 'setDate', pgyDate );
            holder.find('.pgystart-field').datepicker( 'update');
        }

    }

}

//element is any element of the pgy well holder
function calculateExpectedPgy(element) {

    var newPgyLevel = null;

    var holder = element.closest('.user-collection-holder');
    //console.log(holder);

    if( holder.length == 0 || !holder.hasClass('user-appointmentTitles') ) {
        //console.log('holder is null => return newPgyLevel null');
        return newPgyLevel;
    }

    var pgystart = holder.find('.pgystart-field').val();
    var pgylevel = holder.find('.pgylevel-field').val();

    if( pgylevel != "" ) {
        newPgyLevel = pgylevel;
    }

    //During academic year that started on: [July 1st 2011]
    //The Post Graduate Year (PGY) level was: [1]
    //Expected Current Post Graduate Year (PGY) level: [4] (not a true fleld in the database, not editble)
    //
    //D- If both the date and the PGY have value and the academic year is not current
    // (meaning the current date is later than listed date +1 year (in the example above, if current date is later than July 1st 2012) ,
    // the function takes the current year (for example 2014), subtracts the year in the date field (let's say 2011), and add the result to the current PGY level value
    // (let's say 1, replacing it with 4), then updates the year of the field with current (2011->2014).
    if( pgystart != "" && pgylevel != "" ) {

        var today = new Date();
        var curYear = today.getFullYear();

        var pgyDate = new Date(pgystart);
        var pgyYear = pgyDate.getFullYear();

        var diffYear = getYearByDiff(null,pgystart);

        //console.log( 'diffYear='+diffYear);

        if( diffYear >= 1 ) {

            //add the result to the current PGY level value
            newPgyLevel = parseInt(pgylevel) + ( parseInt(curYear)-parseInt(pgyYear) );
        }

    }

    //console.log( 'res: newPgyLevel='+newPgyLevel);

    return newPgyLevel;
}


//for positive year date1 > date2
function getYearByDiff(date1,date2) {

    if( date1 == null ) {
        var date1Date = new Date(); //today date
    } else {
        var date1Date = new Date(date1);
    }

    if( date2 == null ) {
        var date2Date = new Date(); //today date
    } else {
        var date2Date = new Date(date2);
    }

    var years = date1Date.getFullYear() - date2Date.getFullYear();
    var m = date1Date.getMonth() - date2Date.getMonth();
    if (m < 0 || (m === 0 && date1Date.getDate() < date2Date.getDate())) {
        years--;
    }
    return years;
}


function userCloneListener() {
    $('.user-userclone-field').on("change", function(e) {
        var userid = $(this).select2('val');
        //console.log('userid='+userid);
        if( userid && userid != "" ) {
            //reload page with userid
            var urlCreateUser = getCommonBaseUrl("user/new/clone/"+userid,"employees");
        } else {
            //reload page regular new user
            var urlCreateUser = getCommonBaseUrl("user/new","employees");
        }
        window.location = urlCreateUser;
    });
}



//identifier type listener
function identifierTypeListener( holder ) {

    var targetClass = ".ajax-combobox-identifierkeytype";   //".identifier-keytypemrn-field";

    var identifiersTypes = $(targetClass);

    if( identifiersTypes.length == 0 ) {
        return;
    }

    if( typeof holder !== 'undefined' && holder.length > 0 ) {
        identifiersTypes = holder.find(targetClass);

        if( identifiersTypes.length == 0 ) {
            return;
        }
    }

    identifiersTypes.on("change", function(e) {

        var type = $(this).select2('data');
        //console.log("type="+type);

        if( type && type.text == "MRN" ) {
            $(this).closest('.user-identifiers').find('.identifier-keytypemrn-field-holder').show();
            //$(this).closest('.user-identifiers').find('.identifier-keytypemrn-field-holder').removeClass('hideComplex');
        } else {
            $(this).closest('.user-identifiers').find('.identifier-keytypemrn-field-holder').hide();
            //$(this).closest('.user-identifiers').find('.identifier-keytypemrn-field-holder').addClass('hideComplex');
        }

    });

}

function degreeListener( holder ) {

    var targetClass = ".ajax-combobox-trainingdegree";

    var degrees = $(targetClass);

    if( degrees.length == 0 ) {
        return;
    }

    if( typeof holder !== 'undefined' && holder.length > 0 ) {
        degrees = holder.find(targetClass);

        if( degrees.length == 0 ) {
            return;
        }
    }

    degrees.on("change", function(e) {
        var degree = $(this);
        var degreeObject = degree.select2('data');

        if( degreeObject ) {
            if( degreeObject.text == "MD" || degreeObject.text == "PhD" ) {
                var subject = degree.closest('.user-trainings').find('.training-field-appenddegreetoname');
                subject.prop('checked', true);
            } else {
                //subject.prop('checked', false);
            }
        }
    });

}



/////////////////////////// researchLab type ///////////////////////////
function researchLabListener( holder ) {

    var targetClass = ".ajax-combobox-researchlab";

    var labs = $(targetClass);

    if( labs.length == 0 ) {
        return;
    }

    if( typeof holder !== 'undefined' && holder.length > 0 ) {
        labs = holder.find(targetClass);

        if( labs.length == 0 ) {
            return;
        }
    }

    labs.on("change", function(e) {

        var labName = $(this);

        var labObject = labName.select2('data');

        //console.log(labObject);

        if( labObject ) {
            //console.log("id="+labObject.id+", text="+labObject.text+', user_id='+user_id);

            var url = getCommonBaseUrl("util/common/researchlab/"+labObject.id+"/"+user_id,"employees");

            $.ajax({
                url: url,
                timeout: _ajaxTimeout,
                async: asyncflag
            }).success(function(data) {
                populateResearchlabData(data,labName);
            });

        } else {
            populateResearchlabData(null,labName);
        }

    });

}


function populateResearchlabData( data, elementName ) {
    //console.log(data);

    var holder = elementName.closest('.user-researchlabs');
    //console.log(holder);
    //printF(holder,'holder=');

    var idfield = holder.find('.researchlab-id-field');
    var weblink = holder.find('.researchlab-weblink-field');
    var foundedDate = holder.find('.researchlab-foundedDate-field');
    var dissolvedDate = holder.find('.researchlab-dissolvedDate-field');
    var location = holder.find('.ajax-combobox-location');
    var commentDummy = holder.find('.researchlab-commentDummy-field');
    var piDummy = holder.find('.researchlab-piDummy-field');

    //commentDummy.attr("readonly", false);
    //disableCheckbox(piDummy,false);

    if( data && data.length > 1 ) {
        throw new Error('More than 1 object found. count='+data.length);
    }

    if( !data ) {
        //console.log("data is null => empty lab");

        //set null
        idfield.val(null);
        weblink.val(null);
        foundedDate.val(null);
        dissolvedDate.val(null);
        location.select2('val',null);
        commentDummy.val(null);
        piDummy.prop('checked', false);

        //disable
        weblink.attr("readonly", true);
        foundedDate.attr("readonly", true);
        dissolvedDate.attr("readonly", true);
        location.select2("readonly", true);
        commentDummy.attr("readonly", true);
        disableCheckbox(piDummy,true);

        initDatepicker(holder);

        return;
    }

    if( data.length == 0 ) {
        //console.log("data is empty => new lab");

        //set null
        idfield.val(null);
        weblink.val(null);
        foundedDate.val(null);
        dissolvedDate.val(null);
        location.select2('val',null);
        commentDummy.val(null);
        piDummy.prop('checked', false);

        //enable
        weblink.attr("readonly", false);
        foundedDate.attr("readonly", false);
        dissolvedDate.attr("readonly", false);
        location.select2("readonly", false);
        commentDummy.attr("readonly", false);
        disableCheckbox(piDummy,false);

        initDatepicker(holder);

        return;
    }

    if( data && data.length > 0) {

        data = data[0];
        //console.log("existing lab: idfield="+data.id);

        //set data
        idfield.val(data.id);
        weblink.val(data.weblink);
        foundedDate.val(data.foundedDate);
        dissolvedDate.val(data.dissolvedDate);
        location.select2('val',data.lablocation);

        //no comment or pi is attached to a new research lab
        //commentDummy.val(data.commentDummy);
//        if( data.piDummy && data.piDummy == user_id ) {
//            piDummy.prop('checked', true);
//        } else {
//            piDummy.prop('checked', false);
//        }

        //enable
        weblink.attr("readonly", true);
        foundedDate.attr("readonly", true);
        dissolvedDate.attr("readonly", true);
        location.select2("readonly", true);
        commentDummy.attr("readonly", false);
        disableCheckbox(piDummy,false);

        initDatepicker(holder);

        return;
    }

    function disableCheckbox( checkboxEl, disable ) {
        if( disable ) {
            checkboxEl.prop("disabled", true);
        } else {
            checkboxEl.prop("disabled", false);
        }
    }

    return;
}

//delete research lab from user in DB
function deleteObjectFromDB(btn) {
    var btnEl = $(btn);
    var holder = btnEl.closest('.user-researchlabs');

    if( holder.length == 0 ) {
        return true;
    }

    var idfield = holder.find('.researchlab-id-field').val();
    //console.log("remove lab with idfield="+idfield);

    if( !idfield || idfield == "" ) {
        //console.log('empty lab title');
        return true;
    }

    var res = false;

    var url = getCommonBaseUrl("util/common/researchlab/deletefromuser/"+idfield+"/"+user_id,"employees");

    $.ajax({
        url: url,
        timeout: _ajaxTimeout,
        async: false,
        type: 'DELETE'
    }).success(function(data) {
        if( data == 'ok' ) {
            console.log('research lab with id='+idfield+' deleted from user with id='+user_id);
        } else {
            console.log('Failed: research lab with id='+idfield+' not deleted from user with id='+user_id);
        }
        res = true;
    });

    return res;
}
/////////////////////////// EOF researchLab type ///////////////////////////




