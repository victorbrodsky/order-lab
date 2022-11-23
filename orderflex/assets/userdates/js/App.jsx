import React from 'react';
import axios from 'axios';
import { useEffect, useState, useRef } from 'react';
import UserCard from './Components/UserCard.jsx';
// import '../css/index.css';

const TOTAL_PAGES = 2; //0; //3;

//https://dev.to/hey_yogini/infinite-scrolling-in-react-with-intersection-observer-22fh

const App = () => {
    const [loading, setLoading] = useState(true);
    const [allUsers, setAllUsers] = useState([]);
    const [pageNum, setPageNum] = useState(1);
    const [lastElement, setLastElement] = useState(null);

    const observer = useRef(
        new IntersectionObserver((entries) => {
            const first = entries[0];
            if (first.isIntersecting) {
                setPageNum((no) => no + 1);
            }
        })
    );

    //let url = Routing.generate('employees_users_api');
    let url = window.location.href;
    console.log("url="+url, ", pageNum="+pageNum);
    console.log('current URL=', window.location.href);
    console.log('current Pathname=', window.location.pathname);

    console.log("url2="+url+'&page='+pageNum, ", pageNum="+pageNum);

    const callUser = async () => {
        setLoading(true);
        let response = await axios.get(
            //?filter[searchId]=1&filter[startDate]=&filter[endDate]=&direction=DESC&page=3
            //'https://randomuser.me/api/?page=${pageNum}&results=25&seed=abc'
            url+'/?page='+pageNum
            //url+'&page='+pageNum
        );
        let all = new Set([...allUsers, ...response.data.results]);
        setAllUsers([...all]);
        setLoading(false);

        let updateButton = ReactDOM.createRoot(document.getElementById("update-users-button"));
        updateButton.style.display = 'bloack';

    };

    useEffect(() => {
        if (TOTAL_PAGES && pageNum <= TOTAL_PAGES) {
            callUser();
        }
    }, [pageNum]);

    useEffect(() => {
        const currentElement = lastElement;
        const currentObserver = observer.current;

        if (currentElement) {
            currentObserver.observe(currentElement);
        }

        return () => {
            if (currentElement) {
                currentObserver.unobserve(currentElement);
            }
        };
    }, [lastElement]);

    return (
        <>
            {allUsers.length > 0 &&
            allUsers.map((user, i) => {

                return i === allUsers.length - 1 && !loading && (pageNum <= TOTAL_PAGES && TOTAL_PAGES) ?
                //return i === allUsers.length - 1 && !loading ?
                (
                    <div
                        //key={`${user.id}-${i}`}
                        key={ user.id+'-'+i }
                        ref={setLastElement}
                    >
                        <UserCard data={user} />
                    </div>
                ) : (
                    <UserCard
                        count={i+1}
                        data={user}
                        //key={`${user.id}-${i}`}
                        key={ user.id+'-'+i }
                    />
                );
            })}

            {loading && <p className='container text-center'>loading...</p>}

        </>
    );


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
};

export default App;
