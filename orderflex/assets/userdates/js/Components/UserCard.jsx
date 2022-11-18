import React from 'react';
import '../../css/index.css';

const UserCard = ({ count, data }) => {
    return (
        <tr>
            <td className="user-display-none">
                <a target="_blank" href={data.showlink}>{data.id}</a>
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
        </tr>
    );
};

// className="user-display-none"
// <td>
//     {data.FirstName}
// </td>
// <td>
//     {data.LastName}
// </td>

const UserCard_orig = ({ count, data }) => {
    return (
        <div className='p-4 border border-gray-500 rounded bg-white flex items-center'>
            <div className='ml-3'>
                <p className='text-base font-bold'>
                    {count} {data.FirstName} {data.LastName} {data.Email}
                </p>
            </div>
        </div>
    );
};

export default UserCard;
