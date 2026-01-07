//https://cloudnweb.dev/2021/06/react-table-pagination/


import React from 'react';
import axios from 'axios';
import { useEffect, useState, useRef } from 'react';
import UserTableRow from './UserTableRow';
import UserTableTest from './UserTableTest';
import Loading from './Loading';
import DeactivateButton from './DeactivateButton';
import EditButton from './EditButton';
import MatchInfo from './MatchInfo';
import DatepickerComponent from './DatepickerComponent';

import '../../css/index.css';
import '../../../../public/orderassets/AppUserdirectoryBundle/form/js/user-common.js';




//const TOTAL_PAGES = 0; //2; //0; //3;
//let TOTAL_PAGES = 0;

//https://dev.to/hey_yogini/infinite-scrolling-in-react-with-intersection-observer-22fh

const UserTable = ({cycle}) => {

    const [loading, setLoading] = useState(true);
    const [allUsers, setAllUsers] = useState([]);
    const [pageNum, setPageNum] = useState(1);
    const [lastElement, setLastElement] = useState(null);
    const [TOTAL_PAGES, setTotalPages] = useState(1);
    const [totalUsers, setTotalUsers] = useState(null);
    const [matchMessage, setMatchMessage] = useState('Loading ...');
    const [deactivateRowRefs, setDeactivateRowRefs] = useState([]);
    const [modifiedRowRefs, setModifiedRowRefs] = useState([]);
    //const [modifiedRowIds, setModifiedRowIds] = useState([]);
    //const [deactivateElements, addDeactivateElement] = useState([]);
    const [isShown, setIsShown] = useState(true);

    const tableBodyRef = useRef();
    var _counter = 0;

    var modifiedRowIds = [];

    const observer = useRef(
        new IntersectionObserver((entries) => {
            const first = entries[0];
            if (first.isIntersecting) {
                setPageNum((no) => no + 1);
            }
        })
    );

    function updateDeactivateRowRefs( deactivateRowRef, type ) {
        //console.log("type:",type);
        //console.log("deactivateRowRef=",deactivateRowRef); //tr#"table-row-"+data.id
        //console.log("deactivateRowRef id=",deactivateRowRef.current.id);

        //updateList(deactivateRowRef.filter(item => item.name !== name));
        //updateList(deactivateRowRef.filter(item => item.current.id !== deactivateRowRef.current.id));

        function filterDeactivateRowRef(itemRef) {
            //console.log("filterDeactivateRowRef itemRef: ["+itemRef.current.id+"] ?= ["+deactivateRowRef.current.id+"]");
            if( itemRef.current.id === deactivateRowRef.current.id ) {
                return false;
            }
            return true;
        }

        if( type === 'add' ) {
            //console.log("add",rowRef.current.id);
            deactivateRowRefs.push(deactivateRowRef);
            setDeactivateRowRefs( deactivateRowRefs );
            _counter = _counter + 1;
        }
        if( type === 'remove' ) {
            //console.log("remove",rowRef.current.id);
            const newDeactivateRowRefs = [...deactivateRowRefs];
            //const removeId = rowRef.current.id;
            //setDeactivateRowRefs( newDeactivateRowRefs.filter((item) => { return item.current.id !== rowRef.current.id }) )
            setDeactivateRowRefs( newDeactivateRowRefs.filter(filterDeactivateRowRef) );
            //console.log("after deactivateRowRefs=",deactivateRowRefs);
            _counter = _counter - 1;
        }

        //console.log("after deactivateRowRefs",deactivateRowRefs);

        // console.log("_counter="+_counter);
        // if( _counter > 0 ) {
        //     setIsShown(true)
        // } else {
        //     setIsShown(false)
        // }
    }

    function updateModifiedRowRefs( modifiedRowRef, type ) {
        //console.log("updateModifiedRowRefs type:",type);

        //modifiedRowRefs.push(modifiedRowRef);
        //setModifiedRowRefs( modifiedRowRefs );

        function filterModifiedRowRef(itemRef) {
            //console.log("itemRef: ["+itemRef.current.id+"] ?= ["+modifiedRowRef.current.id+"]");
            if( itemRef.current.id === modifiedRowRef.current.id ) {
                return false;
            }
            return true;
        }
        // function filterIfExistsRef(itemRef) {
        //     //console.log("itemRef: ["+itemRef.current.id+"] ?= ["+modifiedRowRef.current.id+"]");
        //     if( itemRef.current.id === modifiedRowRef.current.id ) {
        //         return true;
        //     }
        //     return false;
        // }
        // function ifIdExist(itemRef) {
        //     console.log("modifiedRowIds",modifiedRowIds);
        //     var id = itemRef.current.id;
        //     if( modifiedRowIds.includes(id) == true ) {
        //         console.log("id exists",id);
        //         return true;
        //     }
        //     console.log("id does not exist",id);
        //     //setModifiedRowIds(id);
        //     modifiedRowIds.push(id);
        //     return false;
        // }
        if( type === 'add' ) {
            //console.log("add",rowRef.current.id);
            // if( modifiedRowRefs.length > 0 ) {
            //     const newModifiedRowRefs = [...modifiedRowRefs];
            //     if( ifIdExist(modifiedRowRef) == false ) {
            //         modifiedRowRefs.push(modifiedRowRef);
            //         setModifiedRowRefs(modifiedRowRefs);
            //     }
            // } else {
            //     modifiedRowRefs.push(modifiedRowRef);
            //     setModifiedRowRefs(modifiedRowRefs);
            // }
            modifiedRowRefs.push(modifiedRowRef);
            setModifiedRowRefs(modifiedRowRefs);
        }
        if( type === 'remove' ) {
            //console.log("remove",rowRef.current.id);
            if( modifiedRowRefs.length > 0 ) {
                const newModifiedRowRefs = [...modifiedRowRefs];
                setModifiedRowRefs(newModifiedRowRefs.filter(filterModifiedRowRef));
                //_counter = _counter - 1;
            }
        }
        //console.log("after modifiedRowRefs",modifiedRowRefs);
    }

    // function getData() {
    //     return deactivateRowRefs;
    // }

    // useEffect(() => {
    //     if( _counter > 0 ) {
    //         setIsShown(true)
    //     } else {
    //         setIsShown(false)
    //     }
    // }, [_counter]);

    let apiUrl = Routing.generate('employees_users_api');
    let url = '';
    //let url = window.location.href; //http://127.0.0.1/order/index_dev.php/directory/employment-dates
    //let url = window.location.pathname;
    //console.log("url=["+url+"]", ", pageNum="+pageNum);
    //console.log('current URL=', window.location.href);
    //console.log('current Pathname=', window.location.pathname);
    //console.log("url2="+url+'&page='+pageNum, ", pageNum="+pageNum);

    let queryString = window.location.search;
    if( queryString ) {
        queryString = queryString.replace('?','');
    }
    console.log("queryString="+queryString); //?filter%5Bsearch%5D=aaa&filter%5Bsubmit%5D=&filter%5Bstartdate%5D=&filter%5Benddate%5D=&filter%5Bstatus%5D=

    const callUser = async () => {
        //console.log("callUser!!!");
        setLoading(true);
        //let url = apiUrl+'/?page='+pageNum
        if( queryString ) {
            //queryString = queryString.replace('?','');
            url = apiUrl+'/?page='+pageNum+'&'+queryString
        }
        else {
            url = apiUrl+'/?page='+pageNum
        }
        console.log("url2=["+url+"]");

        let response = await axios.get(
            //?filter[searchId]=1&filter[startDate]=&filter[endDate]=&direction=DESC&page=3
            //'https://randomuser.me/api/?page=${pageNum}&results=25&seed=abc'
            //url+'/?page='+pageNum
            //url+'&page='+pageNum+'&'+queryString
            url
        );
        //console.log("FULL RESPONSE DATA:", response.data);
        console.log("response.data.results=",response.data.results);
        let all = new Set([...allUsers, ...response.data.results]);
        const merged = [...all];
        setAllUsers([...all]);
        setLoading(false);
        console.log("allUsers merged.length=",merged.length);

        //console.log("callUser: totalPages=" + response.data.totalPages);
        setTotalPages(response.data.totalPages);
        setTotalUsers(response.data.totalUsers);
        //console.log("totalPages="+TOTAL_PAGES+", totalUsers="+totalUsers);

        //Showing page(s) 1 to 5 of 136 | 2716 matching users
        let matchMessage = "Showing page(s) 1 to " + pageNum + " of " + response.data.totalPages + " | matching users " + response.data.totalUsers;
        if( parseInt(pageNum) == 1 ) {
            matchMessage = "Showing page 1 of " + response.data.totalPages + " | " + response.data.totalUsers + " matching users";
        }
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

    //TODO: sorting - make table col headers as the links with the sorting string attached "&sort=infos.email&direction=asc"
    //then change direction to "desc": &sort=user.id&direction=asc
    //test: http://127.0.0.1/order/index_dev.php/directory/employment-dates/view?sort=user.id&direction=desc
    function sortFunction(col,direction) {
        console.log("Sort by "+col+", direction="+direction);
    }

    let mainUrl = Routing.generate('employees_user_dates_show');
    if( cycle == 'edit') {
        mainUrl = Routing.generate('employees_user_dates_edit');
    }

    function getSortHref(mainUrl,queryString,sortPar) {
        //let sortDirection = 'desc';

        // let queryString = window.location.search;
        // if( queryString ) {
        //     queryString = queryString.replace('?','');
        // }

        let sortHref = '';
        let queryStringNew = '';
        if( queryString.includes('sort=') ) {

            //Change sorting column: remove old sort from queryString
            if( !queryString.includes(sortPar) ) {
                //Remove old sort from queryString:
                //...&sort=infos.firstName&direction=asc&page=1
                queryStringNew = replaceOldSortWithNewSort(queryString,'sort=','&direction=',sortPar);
                //...&sort=infos.email&direction=asc&page=1
                //console.log('Replace in queryStringNew='+queryStringNew+"; sortPar="+sortPar);

                //Reset default direction to desc
                if( queryStringNew.includes('direction=asc') ) {
                    queryStringNew = queryStringNew.replace('asc','desc');
                    //console.log('queryString replace asc'+queryStringNew);
                }

                sortHref = mainUrl+'?'+queryStringNew; //+'&'+'sort='+sortPar+'&direction=desc&page=1';
                sortHref = sortHref.replace('?&','?');
                return sortHref;
            }

            if( queryString.includes('direction=desc') ) {
                queryStringNew = queryString.replace('desc','asc');
                //console.log('queryString replace desc'+queryStringNew);
            }
            if( queryString.includes('direction=asc') ) {
                queryStringNew = queryString.replace('asc','desc');
                //console.log('queryString replace asc'+queryStringNew);
            }

            //replace sort=infos.firstName&direction by new sortPar: => sort=infos.email&direction
            //console.log('queryStringNew='+queryStringNew);
            sortHref = mainUrl+'?'+queryStringNew;
        } else {
            //sortHref = mainUrl+'?'+queryString+'&'+'sort='+sortPar+'&direction=desc&page=1';
            sortHref = mainUrl+'?'+queryString+'&'+'sort='+sortPar+'&direction=desc';
        }

        sortHref = sortHref.replace('?&','?');

        //console.log('sortHref='+sortHref);
        return sortHref;
    }

    function replaceOldSortWithNewSort(queryString,startStr,endStr,newSort) {
        //return queryString.replace(/(startStr).*(endStr)/, sortPar);
        return queryString.replace(queryString.substring(queryString.indexOf(startStr)+startStr.length, queryString.lastIndexOf(endStr)), newSort);
    }

    if(1) {

        return (
            <div>

                <MatchInfo message={matchMessage}/>

                {cycle == 'show' && <EditButton />}
                {cycle == 'edit' && isShown && <DeactivateButton deactivateRowRefs={deactivateRowRefs} modifiedRowRefs={modifiedRowRefs}/>}

                <table className="records_list table table-hover table-condensed table-striped text-left">
                    <thead>
                    <tr>
                        <th className="user-display-none">
                            ID
                        </th>
                        { cycle == 'edit' &&
                        <th>Deactivate </th>
                        }
                        <th>
                            <a
                                className="sortable"
                                href={getSortHref(mainUrl,queryString,'infos.lastName')}
                                title="Last Name"
                            >Last Name</a>
                        </th>
                        <th>
                            <a
                                className="sortable"
                                href={getSortHref(mainUrl,queryString,'infos.firstName')}
                                title="First Name"
                            >First Name</a>
                        </th>
                        <th>
                            <a
                                className="sortable"
                                href={getSortHref(mainUrl,queryString,'infos.salutation')}
                                title="Degree based on salutation"
                            >Degree</a>
                        </th>
                        <th>
                            <a
                                className="sortable"
                                href={getSortHref(mainUrl,queryString,'infos.email')}
                                title="Email"
                            >Email</a>
                        </th>
                        <th>
                            <a
                                className="sortable"
                                href={getSortHref(mainUrl,queryString,'institution.name')}
                                title="Organizational Group(s) based on the institution in the administrative, appointment, medical titles"
                            >Organizational Group(s)</a>
                        </th>
                        <th>
                            <a
                                className="sortable"
                                href={getSortHref(mainUrl,queryString,'trainingsdegree.name')}
                                title="Title(s) based on the training degree"
                            >Title(s)</a>
                        </th>
                        <th>
                            <a
                                className="sortable"
                                href={getSortHref(mainUrl,queryString,'user.lastLogin')}
                                title="Last successful log in"
                            >Last successful log in</a>
                        </th>
                        <th style={{width: "14.5rem", minWidth: "14.5rem"}}>
                            <a
                                className="sortable"
                                href={getSortHref(mainUrl,queryString,'employmentStatus.hireDate')}
                                title="Latest Employment Start Date"
                            >Latest Employment Start Date</a>
                        </th>
                        <th style={{width: "14.5rem", minWidth: "14.5rem"}}>
                            <a
                                className="sortable"
                                href={getSortHref(mainUrl,queryString,'employmentStatus.terminationDate')}
                                title="Latest Employment End Date"
                            >Latest Employment End Date</a>
                        </th>
                        <th>
                            <a
                                className="sortable"
                                href={getSortHref(mainUrl,queryString,'user.enabled')}
                                title="Site Access"
                            >Site Access</a>
                        </th>
                        <th>
                            <a
                                className="sortable"
                                href={getSortHref(mainUrl,queryString,'user.activeAD')}
                                title="Active Directory Account Status"
                            >Active Directory Account Status</a>
                        </th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody ref={tableBodyRef} data-link="row" className="rowlin">

                    {allUsers.length > 0 && allUsers.map((user, i) => {
                        return i === allUsers.length - 1 && !loading && (pageNum <= TOTAL_PAGES && TOTAL_PAGES) ?
                            //return i === allUsers.length - 1 && !loading ?
                            (
                                <UserTableRow
                                    data={user}
                                    key={ user.id+'-'+i }
                                    updateDeactivateRowRefs={updateDeactivateRowRefs}
                                    updateModifiedRowRefs={updateModifiedRowRefs}
                                    cycle={cycle}
                                    setfunc={setLastElement}
                                />
                            ) : (
                                <UserTableRow
                                    data={user}
                                    key={ user.id+'-'+i }
                                    cycle={cycle}
                                    updateDeactivateRowRefs={updateDeactivateRowRefs}
                                    updateModifiedRowRefs={updateModifiedRowRefs}
                                />
                            );
                    })}

                    {loading && <Loading page={pageNum}/>}

                    </tbody>
                </table>

                {cycle == 'edit' && !loading && <DeactivateButton deactivateRowRefs={deactivateRowRefs} modifiedRowRefs={modifiedRowRefs}/>}

            </div>
        );

    } else {
        //NOT USED
        var componentid = '3';
        //console.log("users:",allUsers);
        console.log("users len=",allUsers.length);

        <UserTableTest
            allUsers={allUsers}
            setfunc={setLastElement}
        />

        if(0) {
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
                            return (
                                <tr>
                                    <td className="rowlink-skip">
                                        <DatepickerComponent componentid={"datepicker-start-date-"+2}/>
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
        }
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

export default UserTable;

