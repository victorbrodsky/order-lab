import React from 'react';
import { useEffect } from 'react';
//import Form from "react-bootstrap/Form";
import '../../css/index.css';
//import '../../../../node_modules/bootstrap/dist/css/bootstrap.min.css';
//import BootstrapDatePickerComponent from './BootstrapDatePickerComponent.jsx'
import DatepickerComponent from './DatepickerComponent.jsx'


const UserTableRow = ({ data, setfunc }) => {

    //useEffect(() => {
        //console.log( "useEffect" );
        //update datepicker
        //initSingleDatepicker( $('#'+"datepicker-start-date-"+data.id) );
        //initSingleDatepicker( $(this).find('.datepicker') );
        //initSingleDatepicker( $(this).find('.datepicker') );

        // //var calendarIconBtn = $('.datepicker').find('.calendar-icon-button');
        // $('.calendar-icon-button').on( "click", function(event) {
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
    //}, []);

    // function openCalendar() {
    //     console.log( "click calendar icon" );
    //     var inputField = $(this).closest('.input-group').find('input.datepicker');
    //     if( inputField.hasClass("datepicker-status-open") ) {
    //         //console.log( "hide datepicker" );
    //         //$('body').off('click');
    //         //$('body').click();
    //         $(".datepicker-dropdown").remove();
    //         inputField.removeClass("datepicker-status-open");
    //     } else {
    //         inputField.addClass("datepicker-status-open");
    //         inputField.addClass('datepicker-dropdown').addClass('dropdown-menu'); // datepicker-orient-left datepicker-orient-bottom
    //     }
    // }
    
    
    return (
        <tr ref={setfunc}>
            <td className="user-display-none">
                <a target="_blank" href={data.showLink}>{data.id}</a>
            </td>
            <td className="rowlink-skip">
                <input type="checkbox" value=""></input>
            </td>
            <td>
                {data.LastName}
            </td>
            <td>
                {data.FirstName}
            </td>
            <td>
                {data.Degree}
            </td>
            <td>
                {data.Email}
            </td>
            <td>
                {data.Institution}
            </td>
            <td>
                {data.Title}
            </td>
            <td className="rowlink-skip">
                <DatepickerComponent componentid = {"datepicker-start-date-"+data.id}/>
            </td>
            <td className="rowlink-skip">
                <DatepickerComponent componentid = {"datepicker-end-date-"+data.id}/>
            </td>
            <td>
                {data.status}
            </td>
            <td className="rowlink-skip">
                <div className="btn-group">
                    <button type="button" className="btn btn-default dropdown-toggle" data-toggle="dropdown">
                        Action <span className="caret"></span>
                    </button>
                    <ul className="dropdown-menu dropdown-menu-right">
                        <li><a target="_blank" href={data.showLink}>View Profile</a></li>
                        <li><a target="_blank" href={data.editLink}>Edit Profile</a></li>
                        <li><a target="_blank" href={data.eventlogLink}>View event log</a></li>
                    </ul>
                </div>
            </td>
        </tr>
    );
};


// <div className="input-group input-group-reg date allow-future-date">
//     <input
//         type="text"
//         id={"datepicker-start-date-"+data.id}
//         name="oleg_userdirectorybundle_user[trainings][0][startDate]"
//         className="datepicker form-control allow-future-date"
//     />
//     <span className="input-group-addon calendar-icon-button" onClick={openCalendar}><i className="glyphicon glyphicon-calendar"></i></span>
// </div>
// {data.EndDate}
//<span className="input-group-addon calendar-icon-button" onClick={openCalendar}><i className="glyphicon glyphicon-calendar"></i></span>
//<span className="input-group-addon calendar-icon-button"><i className="glyphicon glyphicon-calendar"></i></span>
//<BootstrapDatePickerComponent />
// <div class="input-group input-group-reg date allow-future-date">
//     <input type="text" id="filter_startdate" name="filter[startdate]" class="datepicker form-control" placeholder="Start Date/Time">
//         <span class="add-on input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
// </div>
// <div class="container">
//     <div class="col-sm-6" style="height:130px;">
//         <div class="form-group">
//             <div class='input-group date' id='datetimepicker11'>
//                 <input type='text' class="form-control" />
//             <span class="input-group-addon">
//             <span class="glyphicon glyphicon-calendar">
//             </span>
//             </span>
//             </div>
//         </div>
//     </div>
//     <script type="text/javascript">
//         $(function () {
//         $('#datetimepicker11').datetimepicker({
//             daysOfWeekDisabled: [0, 6]
//         });
//     });
//     </script>
// </div>


//<input type="checkbox" value=""></input>
// className="user-display-none"
// <td>
//     {data.FirstName}
// </td>
// <td>
//     {data.LastName}
// </td>

// const UserCard_orig = ({ count, data }) => {
//     return (
//         <div className='p-4 border border-gray-500 rounded bg-white flex items-center'>
//             <div className='ml-3'>
//                 <p className='text-base font-bold'>
//                     {count} {data.FirstName} {data.LastName} {data.Email}
//                 </p>
//             </div>
//         </div>
//     );
// };

export default UserTableRow;
