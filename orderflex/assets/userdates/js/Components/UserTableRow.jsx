import React from 'react';
import { useState, useEffect, useRef } from 'react';
import axios from 'axios';
//import Form from "react-bootstrap/Form";
import '../../css/index.css';
//import '../../../../node_modules/bootstrap/dist/css/bootstrap.min.css';
//import BootstrapDatePickerComponent from './BootstrapDatePickerComponent.jsx'
import DatepickerComponent from './DatepickerComponent'
//import Checkbox from './Checkbox.jsx'


const UserTableRow = ({ data, updateRowRefs, setfunc, cycle }) => {

    //const [isDisabled, setIsDisabled] = useState(false);
    const [originalEndDate, setOriginalEndDate] = useState();

    const rowRef = useRef();
    const checkBoxRef = useRef();
    const checkBoxStatusRef = useRef();
    const startDateRef = useRef();
    const endDateRef = useRef();

    function handleCheckBox(event) {
        //console.log("handleCheckBox");
        //event.preventDefault();
        event.stopPropagation();
        if( checkBoxRef.current.checked ) {
            //alert("checked");
            //event.preventDefault();
            //console.log("enable",startDateRef);
            startDateRef.current.disabled = false;
            endDateRef.current.disabled = false;
            updateRowRefs(rowRef,'add');//"table-row-"+data.id,'add');

            var originalStartDate = startDateRef.value;
            setOriginalEndDate(endDateRef.current.value);
            if( !originalStartDate ) {
                //endDateRef.current.value =
                $(endDateRef.current).datepicker("update", new Date());
            }
        } else {
            //alert("unchecked");
            //event.preventDefault();
            //console.log("disable",startDateRef);
            startDateRef.current.disabled = true;
            endDateRef.current.disabled = true;
            updateRowRefs(rowRef,'remove');

            //endDateRef.current.value = originalEndDate;
            $(endDateRef.current).datepicker("update", originalEndDate);
        }
    }

    function handleCheckLdapStatus(userId, userCwid) {
        let checkLdapUrl = Routing.generate('employees_check_ldap-usertype-userid');
        console.log("checkLdapStatus userId="+userId+", userCwid="+userCwid);

        //var checkButton = $('#'+"ldap-status-"+userId);
        var l = Ladda.create(checkBoxStatusRef.current);
        l.start();

        //axios: params for get, data for post
        axios({
            method: 'get',
            url: checkLdapUrl,
            params: {
                userId: userCwid,
            }
        })
        .then((response) => {
            console.log("response.data=["+response.data+"]");
            l.stop();
            if( response.data == "ok" ) {
                console.log("Active");
                $(checkBoxStatusRef.current).replaceWith("<div class='text-success'>Confirmed: Active in AD</div>");
            }
            if( response.data == "notok" ) {
                console.log("Inactive");
                $(checkBoxStatusRef.current).replaceWith("<div class='text-danger'>Confirmed: Inactive in AD</div>");
            }
        }, (error) => {
            //console.log(error);
            var errorMsg = "Unexpected Error. " +
                "Please make sure that your session is not timed out and you are still logged in. "+error;
            //this.addErrorLine(errorMsg,'error');
            alert(errorMsg);

            l.stop();
        });

    }
    
    return (
        <tr ref={rowRef} id={"table-row-"+data.id}>
            <td className="user-display-none">
                 <a target="_blank" href={data.showLink}>{data.id}</a>
            </td>
            {cycle == 'edit' &&
                <td className="rowlink-skip">
                    <input
                        ref={checkBoxRef}
                        type="checkbox"
                        className="check-input"
                        id={"checkbox-"+data.id}
                        name={"checkbox-"+data.id}
                        value={"value-"+data.id}
                        onChange={handleCheckBox}
                    >
                    </input>
                </td>
            }
            <td ref={setfunc}>
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
                <DatepickerComponent data={data} name={"datepicker-start-date"} dateRef={startDateRef} componentid={"datepicker-start-date-"+data.id}/>
            </td>
            <td className="rowlink-skip">
                <DatepickerComponent data={data} name={"datepicker-end-date"} dateRef={endDateRef} componentid={"datepicker-end-date-"+data.id}/>
            </td>
            <td className="rowlink-skip">
                {data.status}
                { data.checkLdapStatus && (data.keytype == "ldap-user" || data.keytype == "ldap2-user") &&
                    <span
                        className="glyphicon glyphicon-question-sign ml-1"
                        style={{marginLeft: ".25rem"}}
                        ref={checkBoxStatusRef}
                        title="Check Active Directory status"
                        onClick={() => handleCheckLdapStatus(data.id, data.cwid)}
                    ></span>
                }
            </td>
            <td className="rowlink-skip">
                <div className="btn-group">
                    <button
                            type="button"
                            className="btn btn-default dropdown-toggle"
                            data-toggle="dropdown"
                    >
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


// <p>
//     <button
//         ref={checkBoxStatusRef}
//         id={"ldap-status-"+data.id}
//         className="btn btn-sm btn-default"
//         title="Check Active Directory status"
//         onClick={() => handleCheckLdapStatus(data.id, data.cwid)}
//     >Check Status</button>
// </p>

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
