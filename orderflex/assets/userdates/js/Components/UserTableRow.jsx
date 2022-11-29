import React from 'react';
import '../../css/index.css';

const UserTableRow = ({ data, setfunc }) => {
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
            <td>
                {data.StartDate}
            </td>
            <td>
                {data.EndDate}
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
