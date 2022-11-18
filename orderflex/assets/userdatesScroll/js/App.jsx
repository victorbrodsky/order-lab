import React from 'react';
import axios from 'axios';
import { useEffect, useState, useRef } from 'react';
import UserCard from './Components/UserCard.jsx';

const TOTAL_PAGES = 3;

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

    const callUser = async () => {
        setLoading(true);
        let response = await axios.get(
            'https://randomuser.me/api/?page=${pageNum}&results=25&seed=abc'
        );
        let all = new Set([...allUsers, ...response.data.results]);
        setAllUsers([...all]);
        setLoading(false);
    };

    useEffect(() => {
        if (pageNum <= TOTAL_PAGES) {
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
        <div className='mx-44 bg-gray-100 p-6'>
            <h1 className='text-3xl text-center mt-4 mb-10'>All users</h1>

            <div className='grid grid-cols-3 gap-4'>
                {allUsers.length > 0 &&
                allUsers.map((user, i) => {
                    return i === allUsers.length - 1 &&
                    !loading &&
                    pageNum <= TOTAL_PAGES ? (
                        <div
                            key={`${user.name.first}-${i}`}
                            ref={setLastElement}
                        >
                            <UserCard data={user} />
                        </div>
                    ) : (
                        <UserCard
                            data={user}
                            key={`${user.name.first}-${i}`}
                        />
                    );
                })}
            </div>
            {loading && <p className='text-center'>loading...</p>}

            {pageNum - 1 === TOTAL_PAGES && (
                <p className='text-center my-10'>♥</p>
            )}
        </div>
    );

    return (
        <div className="row">
            {this.state.entries.map(
                ({ id, title, url, thumbnailUrl }) => (
                    <Items
                        key={id}
                        title={title}
                        url={url}
                        thumbnailUrl={thumbnailUrl}
                    >
                    </Items>
                )
            )}
        </div>
    );
};

export default App;
