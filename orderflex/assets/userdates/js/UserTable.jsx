//https://cloudnweb.dev/2021/06/react-table-pagination/


import React from 'react';
//import ReactDOM from "react-dom/client";
import axios from 'axios';
import { useEffect, useState, useRef } from 'react';
import UserTableRow from './Components/UserTableRow.jsx';
import UserTable from './Components/UserTable.jsx';
import Loading from './Components/Loading.jsx';
import DeactivateButton from './Components/DeactivateButton.jsx';
import MatchInfo from './Components/MatchInfo.jsx';
import DatepickerComponent from './Components/DatepickerComponent.jsx';

import '../css/index.css';
import '../../../public/orderassets/AppUserdirectoryBundle/form/js/user-common.js';




//const TOTAL_PAGES = 0; //2; //0; //3;
//let TOTAL_PAGES = 0;

//https://dev.to/hey_yogini/infinite-scrolling-in-react-with-intersection-observer-22fh

const App = () => {

    const [loading, setLoading] = useState(true);
    const [allUsers, setAllUsers] = useState([]);
    const [pageNum, setPageNum] = useState(1);
    const [lastElement, setLastElement] = useState(null);
    const [TOTAL_PAGES, setTotalPages] = useState(1);
    const [totalUsers, setTotalUsers] = useState(null);
    const [matchMessage, setMatchMessage] = useState('Loading ...');

    const observer = useRef(
        new IntersectionObserver((entries) => {
            const first = entries[0];
            if (first.isIntersecting) {
                setPageNum((no) => no + 1);
            }
        })
    );

    let apiUrl = Routing.generate('employees_users_api');
    let url = '';
    //let url = window.location.href; //http://127.0.0.1/order/index_dev.php/directory/employment-dates
    //let url = window.location.pathname;
    //console.log("url=["+url+"]", ", pageNum="+pageNum);
    //console.log('current URL=', window.location.href);
    //console.log('current Pathname=', window.location.pathname);
    //console.log("url2="+url+'&page='+pageNum, ", pageNum="+pageNum);

    let queryString = window.location.search;
    //console.log("queryString="+queryString); //?filter%5Bsearch%5D=aaa&filter%5Bsubmit%5D=&filter%5Bstartdate%5D=&filter%5Benddate%5D=&filter%5Bstatus%5D=

    const callUser = async () => {
        //console.log("callUser!!!");
        setLoading(true);
        //let url = apiUrl+'/?page='+pageNum
        if( queryString ) {
            queryString = queryString.replace('?','');
            url = apiUrl+'/?page='+pageNum+'&'+queryString
        }
        else {
            url = apiUrl+'/?page='+pageNum
        }
        //console.log("url2=["+url+"]");

        let response = await axios.get(
            //?filter[searchId]=1&filter[startDate]=&filter[endDate]=&direction=DESC&page=3
            //'https://randomuser.me/api/?page=${pageNum}&results=25&seed=abc'
            //url+'/?page='+pageNum
            //url+'&page='+pageNum+'&'+queryString
            url
        );
        let all = new Set([...allUsers, ...response.data.results]);
        setAllUsers([...all]);
        setLoading(false);

        //console.log("callUser: totalPages=" + response.data.totalPages);
        setTotalPages(response.data.totalPages);
        setTotalUsers(response.data.totalUsers);
        //console.log("totalPages="+TOTAL_PAGES+", totalUsers="+totalUsers);

        let matchMessage = "Page " + pageNum + "/" + response.data.totalPages + "; Total matching users " + response.data.totalUsers;
        setMatchMessage(matchMessage);
        //console.log("matchMessage="+matchMessage);

        //const matchingInfo = ReactDOM.createRoot(document.getElementById("matching-info"));
        //matchingInfo.innerHTML = "(Matching 1258, Total 1361)";

        // let updateButton = ReactDOM.createRoot(document.getElementById("update-users-button"));
        // updateButton.style.display = 'block';

    };

    useEffect(() => {
        if (TOTAL_PAGES && pageNum <= TOTAL_PAGES) {
            callUser();
        } else {
            setMatchMessage("Total matching users " + totalUsers);
        }
    }, [pageNum]);

    useEffect(() => {
        const currentElement = lastElement;
        const currentObserver = observer.current;
        //console.log("lastElement",lastElement);

        if (currentElement) {
            currentObserver.observe(currentElement);
        }

        return () => {
            if (currentElement) {
                currentObserver.unobserve(currentElement);
            }
        };
    }, [lastElement]);

    if(0) {
        var componentid = '3';
        //console.log("users:",allUsers);
        console.log("users len=",allUsers.length);

        <UserTable
            allUsers={allUsers}
            setfunc={setLastElement}
        />

        if(0)
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
                    <div className="input-group input-group-reg date allow-future-date">
                        <input
                            type="text"
                            id={componentid}
                            name={componentid}
                            className="datepicker form-control allow-future-date"
                        />
                                    <span
                                        className="input-group-addon calendar-icon-button"
                                        id={"calendar-icon-button-"+componentid}
                                    ><i className="glyphicon glyphicon-calendar"></i></span>
                    </div>

                    {allUsers.length > 0 && allUsers.map((user, i) => {
                        return(
                        <tr>
                            <td className="rowlink-skip">
                                <DatepickerComponent componentid = {"datepicker-start-date-"+2}/>
                                <div className="input-group input-group-reg date allow-future-date">
                                    <input
                                        type="text"
                                        id={componentid}
                                        name={componentid}
                                        className="datepicker form-control allow-future-date"
                                    />
                                    <span
                                        className="input-group-addon calendar-icon-button"
                                        id={"calendar-icon-button-"+componentid}
                                    ><i className="glyphicon glyphicon-calendar"></i></span>
                                </div>
                            </td>
                        </tr>
                        )
                    })}

                    </tbody>
                </table>
            </div>
        )
    } else {

        return (
            <div>

                <MatchInfo message={matchMessage}/>

                <DeactivateButton />

                <table className="records_list table table-hover table-condensed table-striped text-left">
                    <thead>
                    <tr>
                        <th className="user-display-none">
                            ID
                        </th>
                        <th>
                            Deactivate
                        </th>
                        <th>
                            LastName
                        </th>
                        <th>
                            FirstName
                        </th>
                        <th>Degree</th>
                        <th>Email</th>
                        <th>Institution</th>
                        <th>Title(s)</th>
                        <th>Latest Employment Start Date</th>
                        <th>Latest Employment End Date</th>
                        <th>Account Status</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody data-link="row" className="rowlink">

                    {allUsers.length > 0 && allUsers.map((user, i) => {
                        return i === allUsers.length - 1 && !loading && (pageNum <= TOTAL_PAGES && TOTAL_PAGES) ?
                            //return i === allUsers.length - 1 && !loading ?
                            (
                                <UserTableRow
                                    data={user}
                                    key={ user.id+'-'+i }
                                    setfunc={setLastElement}
                                />
                            ) : (
                            <UserTableRow
                                data={user}
                                key={ user.id+'-'+i }
                                //setfunc={null}
                            />
                        );
                    })}

                    {loading && <Loading page={pageNum}/>}

                    </tbody>
                </table>

                {!loading && <DeactivateButton />}

            </div>
        );
    }

};

//records_list table table-hover table-condensed text-left sortable
//records_list table table-hover table-condensed table-striped text-left

        // <tr>
        //     <th>
        //         <UserTableRow
        //             data={1}
        //             key={2}
        //             setfunc={setLastElement}
        //         />
        //     </th>
        // </tr>


// <div
//     //key={`${user.id}-${i}`}
//     key={ user.id+'-'+i }
//     ref={setLastElement}
// >
//     <UserTableRow data={user} key={ user.id+'-'+i } />
// </div>

// {loading && <p className='container text-center'>loading...</p>}


    // return (
    //     <div className="row">
    //         {this.state.entries.map(
    //             ({ id, title, url, thumbnailUrl }) => (
    //                 <Items
    //                     key={id}
    //                     title={title}
    //                     url={url}
    //                     thumbnailUrl={thumbnailUrl}
    //                 >
    //                 </Items>
    //             )
    //         )}
    //     </div>
    // );

export default App;

