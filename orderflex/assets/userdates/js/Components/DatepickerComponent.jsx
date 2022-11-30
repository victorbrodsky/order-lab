import React from 'react'
import { useEffect } from 'react';
//import { Form } from 'react-bootstrap';
//import Form from "react-bootstrap/Form";
//import '../../../../node_modules/bootstrap/dist/css/bootstrap.min.css';

const DatepickerComponent = ({ componentid }) => {

    useEffect(() => {
        //console.log( "useEffect" );
        //update datepicker
        initSingleDatepicker( $('#'+componentid) );
        //initSingleDatepicker( $(this).find('.datepicker') );
        //initSingleDatepicker( $(this).find('.datepicker') );

        //var calendarIconBtn = $('.datepicker').find('.calendar-icon-button');
        // $('#'+"datepicker-start-date-"+dataid).find('.calendar-icon-button').on( "click", function(event) {
        //     event.stopPropagation();
        //     console.log( "click calendar icon" );
        //     var inputField = $('.datepicker').closest('.input-group').find('input.datepicker');
        //     if( inputField.hasClass("datepicker-status-open") ) {
        //         //console.log( "hide datepicker" );
        //         //$('body').off('click');
        //         //$('body').click();
        //         $(".datepicker-dropdown").remove();
        //         inputField.removeClass("datepicker-status-open");
        //     } else {
        //         inputField.addClass("datepicker-status-open");
        //     }
        // });

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

    }, []);

    function openCalendar() {
        return;
        console.log( "react click calendar icon" );
        var inputField = $('#'+componentid).closest('.input-group').find('input.datepicker');
        console.log("inputField=",inputField);
        if( inputField.hasClass("datepicker-status-open") ) {
            //console.log( "hide datepicker" );
            //$('body').off('click');
            //$('body').click();
            $(".datepicker-dropdown").remove();
            inputField.removeClass("datepicker-status-open");
        } else {
            inputField.addClass("datepicker-status-open");
            //inputField.addClass('datepicker-dropdown').addClass('dropdown-menu'); // datepicker-orient-left datepicker-orient-bottom
        }
    }

    return(
        <div className="input-group input-group-reg date allow-future-date">
            <input
                type="text"
                id={componentid}
                name={componentid}
                className="datepicker form-control allow-future-date"
            />
            <span className="input-group-addon calendar-icon-button" onClick={openCalendar}><i className="glyphicon glyphicon-calendar"></i></span>
        </div>
    )
}

export default DatepickerComponent;


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

