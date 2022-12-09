import React from 'react';
import { useEffect } from 'react';
//import Form from "react-bootstrap/Form";
import '../../css/index.css';
//import '../../../../node_modules/bootstrap/dist/css/bootstrap.min.css';
//import BootstrapDatePickerComponent from './BootstrapDatePickerComponent.jsx'
import DatepickerComponent from './DatepickerComponent'
import UserTableRow from './UserTableRow';


const UserTable = ({ allUsers, setfunc }) => {

    var componentid = 1;

    return (
        <div>
            <table className="records_list table1 table-hover table-condensed text-left sortable">
                <thead>
                <tr>
                    <th>
                        Date
                    </th>
                </tr>
                </thead>
                <tbody data-link="row" className="rowlink">

                <UserTableRow
                    data={1}
                    key={ 1 + '-' + 0 }
                    //setfunc={setLastElement}
                />
                <DatepickerComponent componentid = {"datepicker-start-date-"+2}/>

                {allUsers.length > 0 && allUsers.map((user, i) => {
                    return i === allUsers.length - 1 ?
                        //return i === allUsers.length - 1 && !loading ?
                        (
                            <UserTableRow
                                data={user}
                                key={ user.id+'-'+i }
                                setfunc={setfunc}
                            />
                        ) : (
                        <UserTableRow
                            data={user}
                            key={ user.id+'-'+i }
                            //setfunc={null}
                        />
                    );
                })}

                </tbody>
            </table>
        </div>
    )

};



export default UserTable;
