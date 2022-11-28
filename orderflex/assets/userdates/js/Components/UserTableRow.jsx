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
