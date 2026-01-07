import React from 'react';
import axios from 'axios';
import { useEffect, useState, useRef } from 'react';
import Grid from '@mui/material/Grid';
import ProductCard from "./ProductCard";
//import ReactCard from "./ReactCard";
import Loading from "./Loading";

//import { API_URL } from "../constants";
//export const API_URL = "http://localhost:8000/antibodies/api";

//const TOTAL_PAGES = 0; //2; //0; //3;
//let TOTAL_PAGES = 1;

//https://dev.to/hey_yogini/infinite-scrolling-in-react-with-intersection-observer-22fh

//let _fetchedPages = []; //to prevent double fetch for the same page

const ScrollList = () => {

    const [loading, setLoading] = useState(true);
    const [allProducts, setAllProducts] = useState([]);
    const [pageNum, setPageNum] = useState(1);
    const [lastElement, setLastElement] = useState(null);
    const [TOTAL_PAGES, setTotalPages] = useState(1);
    const [totalProducts, setTotalProducts] = useState(null);
    const [matchMessage, setMatchMessage] = useState('Loading ...');
    //const [rowRefs, setRowRefs] = useState([]);
    //const [isShown, setIsShown] = useState(true);

    //console.log("ScrollList: pageNum="+pageNum);

    const tableBodyRef = useRef();
    var _counter = 0;

    const observer = useRef(
        new IntersectionObserver((entries) => {
            const first = entries[0];
            if (first.isIntersecting) {
                setPageNum((no) => no + 1);
            }
        })
    );

    //let url = '';
    //console.log("url=["+url+"]", ", pageNum="+pageNum);

    let queryString = window.location.search;
    if( queryString ) {
        queryString = queryString.replace('?','');
    }
    //console.log("queryString="+queryString); //?filter%5Bsearch%5D=aaa&filter%5Bsubmit%5D=&filter%5Bstartdate%5D=&filter%5Benddate%5D=&filter%5Bstatus%5D=

    let apiUrl = Routing.generate('translationalresearch_antibodies_api');
    console.log("ScrollList: apiUrl=["+apiUrl+"]");

    const callProduct = async () => {
        //console.log("callProduct, pageNum="+pageNum);
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
        console.log("callProduct: url=["+url+"]");

        let response = await axios.get(
            url,
            {withCredentials: true}
        );
        //console.log("response",response);
        console.log("totalPages: pageNum="+pageNum+"; totalPages=",response.data.totalPages);
        let all = new Set([...allProducts, ...response.data.results]);
        setAllProducts([...all]);
        setTotalPages(response.data.totalPages);
        setLoading(false);
    };

    useEffect(() => {
        console.log("useEffect: callProduct: pageNum=" + pageNum + "; TOTAL_PAGES=" + TOTAL_PAGES);
        //if( _fetchedPages[pageNum] === undefined ) {
            if (TOTAL_PAGES && pageNum <= TOTAL_PAGES) {
                callProduct();
                //_fetchedPages[pageNum] = true;
            } else {
                setMatchMessage("Total matching antibodies " + totalProducts);
            }
        //}
    }, [pageNum]);

    useEffect(() => {
        const currentElement = lastElement;
        const currentObserver = observer.current;
        //console.log("useEffect: lastElement",lastElement);

        if (currentElement) {
            currentObserver.observe(currentElement);
        }

        return () => {
            if (currentElement) {
                currentObserver.unobserve(currentElement);
            }
        };
    }, [lastElement]);

    //console.log("ScrollList return");

    //<div className="card-group">
    //<div className="row row-cols-1 row-cols-md-3 g-4">

    //TODO: add filter, add view single antibody details

    return (
        <div>
            <Grid container spacing={1}>
                {allProducts.length > 0 && allProducts.map((product, i) => {
                    return i === allProducts.length - 1 && !loading && (pageNum <= TOTAL_PAGES && TOTAL_PAGES) ?
                        (
                            <Grid
                                key={"grid-"+product.id}
                                item xs={3}
                                ref={setLastElement}
                            >
                            <ProductCard
                                product={product}
                            />
                            </Grid>
                        ) : (
                            <Grid
                                key={"grid-"+product.id}
                                item xs={3}
                            >
                            <ProductCard
                                product={product}
                            />
                            </Grid>
                    );
                })}

                {loading && <Loading page={pageNum} pages={TOTAL_PAGES}/>}

                {pageNum - 1 === TOTAL_PAGES && (
                    <div>
                        <p className='text-center my-10'>End of list</p>
                    </div>
                )}
            </Grid>
        </div>
    );

};

export default ScrollList;
