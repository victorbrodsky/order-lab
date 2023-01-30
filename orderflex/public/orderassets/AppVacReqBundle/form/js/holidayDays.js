/**
 * Created by oli2002 on 1/13/2023.
 */

function importHolidayDates(btn) {
    //console.log("url:",$(btn).closest('.row').find('.holidayDatesUrl'));
    var holidayDatesUrl = $(btn).closest('.row').find('.holidayDatesUrl').val();
    console.log("holidayDatesUrl="+holidayDatesUrl);

    var l = Ladda.create($(btn).get(0));
    l.start();

    var url = Routing.generate('vacreq_import_holiday_dates');

    $.ajax({
        url: url,
        timeout: _ajaxTimeout,
        //type: "GET",
        type: "GET",
        data: {holidayDatesUrl: holidayDatesUrl },
        //dataType: 'json',
        async: asyncflag
    }).success(function(response) {
        console.log(response);
        if( response == "OK" ) {
            console.log("response OK");
        } else {
            console.log("response not OK");
        }
    }).done(function() {
        l.stop();
    }).error(function(jqXHR, textStatus, errorThrown) {
        console.log('Error : ' + errorThrown);
    });
}

function saveObservedHolidays(btn) {
    console.log("saveObservedHolidays");

    $('.alert-success').hide();
    $('.alert-success').html("");
    $('.alert-danger').hide();
    $('.alert-danger').html("");

    //$('.alert-success').show();

    var l = Ladda.create($(btn).get(0));
    l.start();

    //get checked ids
    var checkedHolidays = [];
    var unCheckedHolidays = [];
    var inputElements = document.getElementsByClassName('observed-holidays-checkbox');
    for(var i=0; inputElements[i]; ++i){
        if(inputElements[i].checked){
            checkedHolidays.push(inputElements[i].value);
        } else {
            unCheckedHolidays.push(inputElements[i].value);
        }
    }
    console.log("checkedHolidays:",checkedHolidays);
    console.log("unCheckedHolidays:",unCheckedHolidays);

    var url = Routing.generate('vacreq_save_observed_holidays_ajax');

    $.ajax({
        url: url,
        timeout: _ajaxTimeout,
        dataType: 'json',
        type: "GET",
        data: {checkedHolidays: checkedHolidays, unCheckedHolidays: unCheckedHolidays },
        async: asyncflag
    }).success(function(response) {
        console.log(response);
        console.log("flag=["+response['flag']+"]");
        if( response['flag'] == "OK" ) {
            console.log("response OK: "+response['note']);
            $('.alert-success').show();
            $('.alert-success').html(response['note']);
        } else {
            $('.alert-danger').show();
            $('.alert-danger').html(response['note']);
            console.log("response not OK: "+response['note']);
        }
    }).done(function() {
        l.stop();
    }).error(function(jqXHR, textStatus, errorThrown) {
        l.stop();
        $('.alert-danger').show();
        $('.alert-danger').html(errorThrown);
        console.log('Error : ' + errorThrown);
    });
}
