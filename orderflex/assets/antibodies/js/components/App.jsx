import React from 'react';
import axios from 'axios';
import { useEffect, useState, useRef } from 'react';
import Grid from '@mui/material/Grid';
import ProductCard from "./ProductCard";
import Loading from "./Loading";

const TOTAL_PAGES = 30;

const App = () => {
    const [loading, setLoading] = useState(true);
    const [allProducts, setAllProducts] = useState([]);
    const [pageNum, setPageNum] = useState(1);
    //const [TOTAL_PAGES, setTotalPages] = useState(1);
    const [lastElement, setLastElement] = useState(null);

    const observer = useRef(
        new IntersectionObserver(
            (entries) => {
                const first = entries[0];
                if (first.isIntersecting) {
                    setPageNum((no) => no + 1);
                }
            }
        )
    );

    let queryString = window.location.search;
    if( queryString ) {
        queryString = queryString.replace('?','');
    }
    console.log("queryString="+queryString); //?filter%5Bsearch%5D=aaa&filter%5Bsubmit%5D=&filter%5Bstartdate%5D=&filter%5Benddate%5D=&filter%5Bstatus%5D=


    let apiUrl = Routing.generate('translationalresearch_antibodies_api');
    console.log("apiUrl=["+apiUrl+"]");

    const callUser = async () => {
        setLoading(true);

        let url = '';
        //let url = API_URL+'?page='+pageNum
        if( queryString ) {
            //queryString = queryString.replace('?','');
            url = apiUrl+'/?page='+pageNum+'&'+queryString
        }
        else {
            url = apiUrl+'/?page='+pageNum
        }
        //url = API_URL;
        console.log("url=["+url+"]");

        let response = await axios.get(
            //`https://randomuser.me/api/?page=${pageNum}&results=25&seed=abc`
            apiUrl
        );
        let all = new Set([...allProducts, ...response.data.results]);
        setAllProducts([...all]);
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

    const UserCard = ({ data }) => {
        return (
            <div className='p-4 border border-gray-500 rounded bg-white flex items-center'>
            <div>
            <img
                src={data.picture.medium}
                className='w-16 h-16 rounded-full border-2 border-green-600'
                alt='user'
                    />
                    </div>

                    <div className='ml-3'>
                    <p className='text-base font-bold'>
                    {data.name.first} {data.name.last}
                    </p>
                    <p className='text-sm text-gray-800'>
                    {data.location.city}, {data.location.country}
                    </p>
                    <p className='text-sm text-gray-500 break-all'>
                    {data.email}
                    </p>
            </div>
        </div>
        );
    };

    if(0) {
        return (
            <div>
                <Grid container spacing={2}>
                    {allProducts.length > 0 && allProducts.map((product, i) => {
                        return i === allProducts.length - 1 && !loading && (pageNum <= TOTAL_PAGES && TOTAL_PAGES) ?
                            (
                                <Grid
                                    key={product.id}
                                    item xs={4}
                                >
                                    <ProductCard
                                        key={product.id}
                                        product={product}
                                        setref={setLastElement}
                                    />
                                </Grid>
                            ) : (
                            <Grid
                                key={product.id}
                                item xs={3}
                            >
                                <ProductCard
                                    key={product.id}
                                    product={product}
                                />
                            </Grid>
                        );
                    })}

                    {loading && <Loading page={pageNum}/>}
                </Grid>
            </div>
        );
    }

    if(1) {
        return (
            <div>
                <Grid container spacing={1}>
                    {allProducts.length > 0 && allProducts.map((product, i) => {
                        return i === allProducts.length - 1 && !loading && pageNum <= TOTAL_PAGES ? (
                            <Grid
                                key={`${product.id}-${i}`}
                                ref={setLastElement}
                                item xs={3}
                            >
                                <ProductCard product={product}/>
                            </Grid>
                        ) : (
                            <Grid
                                key={`${product.id}-${i}`}
                                item xs={3}
                            >
                                <ProductCard
                                    product={product}
                                    key={`${product.id}-${i}`}
                                />
                            </Grid>
                        );
                    })}

                    {loading && <p className='text-center'>loading...</p>}

                    {pageNum - 1 === TOTAL_PAGES && (
                        <p className='text-center my-10'>♥</p>
                    )}
                </Grid>
            </div>
        );
    }
};

export default App;
