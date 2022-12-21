import React from 'react'
import { useState, useEffect, useRef } from 'react';
//import { Form } from 'react-bootstrap';
//import Form from "react-bootstrap/Form";
//import '../../../../node_modules/bootstrap/dist/css/bootstrap.min.css';

const DatepickerComponent = ({ data, cycle, name, dateRef, componentid, updateModifiedRowRefs }) => {

    const [originalDate, setOriginalDate] = useState();
    const dateCalendarBtnRef = useRef();
    const dateGroupRef = useRef();

    useEffect(() => {
        //console.log( "useEffect, cycle="+cycle );
        initSingleDatepicker( $(dateRef.current) );

        if( cycle != 'edit' ) {
            dateRef.current.disabled = true;
        }

        if( name == 'datepicker-start-date' ) {
            $(dateRef.current).datepicker('update', data.StartDate);
        }
        if( name == 'datepicker-end-date' ) {
            $(dateRef.current).datepicker('update', data.EndDate);
        }

        setOriginalDate(dateRef.current.value);

    }, []);

    //TODO: date onchange
    //var i = 0; //bug: fired 6 times
    function handleDateChange(event) {
        //console.log("handleDateChange");
        //event.preventDefault();
        event.stopPropagation();

        var currentDate = dateRef.current.value;
        var type = 'add';
        if( currentDate == originalDate ) {
            type = 'remove';
        }
        updateModifiedRowRefs(dateRef,type);
    }
    $(dateRef.current).datepicker().on('changeDate', function (event) {
        //console.log(i+": changeDate",$(dateRef.current));
        //$('#date-daily').change();
        event.stopPropagation();
        handleDateChange(event);
    });
    // $(dateRef.current).datepicker().on('hide', function(event) {
    //     console.log("hide",$(dateRef.current));
    //     handleDateChange(event);
    // });
    // $("#"+componentid).on("changeDate", function(e) {
    //     //e.preventDefault();
    //     e.stopPropagation();
    //     console.log("changeDate",$(dateRef.current));
    // });

    function handleClickCalendarButton() {
        //console.log( "react click calendar icon", dateRef.current );
        //dateRef.current.click();
        //return;
        //var inputField = $('#'+componentid).closest('.input-group').find('input.datepicker');
        var inputField = $(dateRef.current);
        console.log("inputField=",inputField);
        if( inputField.hasClass("datepicker-status-open") ) {
            console.log( "hide datepicker" );
            $(".datepicker-dropdown").remove();
            inputField.removeClass("datepicker-status-open");
        } else {
            console.log( "show datepicker" );
            inputField.addClass("datepicker-status-open");
            //inputField.addClass('datepicker-dropdown').addClass('dropdown-menu'); // datepicker-orient-left datepicker-orient-bottom
        }
    }

    if(0) {
        return(
            <div className="input-group input-group-reg date">
                <input
                    ref={dateRef}
                    type="text"
                    id={componentid}
                    name={name}
                    className="datepicker111 form-control allow-future-date"
                />
            </div>
        )
    } else {
        return(
            <div ref={dateGroupRef} className="input-group input-group-reg date">
                <input
                    ref={dateRef}
                    type="text"
                    id={componentid}
                    name={name}
                    className="datepicker form-control allow-future-date"
                    //onChange={handleDateChange}
                />
                 <span
                     ref={dateCalendarBtnRef}
                     className="input-group-addon calendar-icon-button"
                     id={"calendar-icon-button-"+componentid}
                     //onClick={handleClickCalendarButton}
                     //onClick={() => handleClickCalendarButton()}
                 ><i className="glyphicon glyphicon-calendar"></i></span>

            </div>
        )
    }
}

export default DatepickerComponent;


//onClick={openCalendar}
//<Form.Label>Select Date</Form.Label>

// <div>
//     <div className="row">
//         <div className="col-md-4">
//             <Form.Group controlId="dob">
//                 <Form.Control type="date" name="dob" placeholder="Start Date" />
//             </Form.Group>
//         </div>
//     </div>
// </div>

//useEffect2(() => {
//console.log( "useEffect" );
//update datepicker
//initSingleDatepicker( $('.datepicker') );
//initSingleDatepicker( $('#'+componentid) );
//initSingleDatepicker( $(this).find('.datepicker') );
//initSingleDatepicker( $(this).find('.datepicker') );

// $('#'+componentid).datepicker({
//     autoclose: true,
//     clearBtn: true,
//     //todayBtn: datepickertodayBtn,
//     todayHighlight: true,
//     startDate: false,
//     endDate: false,
//     orientation: "auto", //"auto top"
//     ////minDate: new Date(1902, 1, 1)   //null
//     format: "mm/dd/yyyy",
//     minViewMode: "days",
//     viewMode: null,
//     multidate: false,
// });

//var calendarIconBtn = $('.datepicker').find('.calendar-icon-button');
//var calendarIconBtn = $('#'+componentid).find('.calendar-icon-button');
// var calendarIconBtn = $('#'+"calendar-icon-button-"+componentid);
// console.log("calendarIconBtn:",calendarIconBtn);
// calendarIconBtn.on( "click", function(event) {
//     event.stopPropagation();
//     console.log( "click calendar icon" );
//     //var inputField = $('.datepicker').closest('.input-group').find('input.datepicker');
//     var inputField = $('#'+componentid);
//     console.log("react inputField:",inputField);
//     if( inputField.hasClass("datepicker-status-open") ) {
//         console.log( "hide datepicker" );
//         //$(".datepicker-dropdown").remove();
//         //inputField.removeClass("datepicker-status-open");
//         inputField.addClass("datepicker-status-open");
//     } else {
//         console.log( "show datepicker" );
//         inputField.addClass("datepicker-status-open");
//     }
// });

//$('#'+componentid).datepicker('update', data.StartDate);
//$('#'+componentid).prop('disabled', true);
//}, []);
