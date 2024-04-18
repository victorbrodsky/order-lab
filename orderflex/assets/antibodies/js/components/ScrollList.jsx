import React from 'react';
import axios from 'axios';
import { useEffect, useState, useRef } from 'react';
import ProductCard from "./ProductCard";
import Loading from "./Loading";

import { API_URL } from "../constants";


//const TOTAL_PAGES = 0; //2; //0; //3;
//let TOTAL_PAGES = 1;

//https://dev.to/hey_yogini/infinite-scrolling-in-react-with-intersection-observer-22fh

const ScrollList = () => {

    const [loading, setLoading] = useState(true);
    const [allProducts, setAllProducts] = useState([]);
    const [pageNum, setPageNum] = useState(1);
    const [lastElement, setLastElement] = useState(null);
    const [TOTAL_PAGES, setTotalPages] = useState(1);
    const [totalProducts, setTotalProducts] = useState(null);
    const [matchMessage, setMatchMessage] = useState('Loading ...');
    const [rowRefs, setRowRefs] = useState([]);
    const [isShown, setIsShown] = useState(true);

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
    //console.log("queryString="+queryString); //?filter%5Bsearch%5D=aaa&filter%5Bsubmit%5D=&filter%5Bstartdate%5D=&filter%5Benddate%5D=&filter%5Bstatus%5D=

    const callProduct = async () => {
        //console.log("callProduct, pageNum="+pageNum);
        setLoading(true);
        let url = '';
        //let url = API_URL+'?page='+pageNum
        if( queryString ) {
            queryString = queryString.replace('?','');
            url = API_URL+'?page='+pageNum+'&'+queryString
        }
        else {
            url = API_URL+'?page='+pageNum
        }
        //url = API_URL;
        //console.log("url=["+url+"]");

        let response = await axios.get(
            url
        );
        //console.log("response",response);
        //console.log("num_pages",response.data.num_pages);
        let all = new Set([...allProducts, ...response.data.products]);
        setAllProducts([...all]);
        setTotalPages(response.data.num_pages);
        setLoading(false);

        //console.log("callProduct: totalPages=" + response.data.totalPages);
        //setTotalPages(response.data.totalPages);
        //setTotalProducts(response.data.totalProducts);
        //console.log("totalPages="+TOTAL_PAGES+", totalProducts="+totalProducts);

        //let matchMessage = "Page " + pageNum + "/" + response.data.totalPages + "; Total matching users " + response.data.totalProducts;
        //setMatchMessage(matchMessage);
        //console.log("matchMessage="+matchMessage);

        //const matchingInfo = ReactDOM.createRoot(document.getElementById("matching-info"));
        //matchingInfo.innerHTML = "(Matching 1258, Total 1361)";

        // let updateButton = ReactDOM.createRoot(document.getElementById("update-users-button"));
        // updateButton.style.display = 'block';

    };

    useEffect(() => {
        if (TOTAL_PAGES && pageNum <= TOTAL_PAGES) {
        callProduct();
    } else {
        setMatchMessage("Total matching users " + totalProducts);
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
        //var componentid = '3';
        console.log("users len=",allProducts.length);

        if(1)
            return (
                <div>

                {allProducts.length > 0 && allProducts.map((product, i) => {
                    return(

                <ProductCard
        key={product.id}
        product={product}
            />

    )
    })}

    </div>
    )
    } else {

        return (
            //<div className="card-group">
            <div className="row row-cols-1 row-cols-md-3 g-4">

            {allProducts.length > 0 && allProducts.map((product, i) => {
                return i === allProducts.length - 1 && !loading && (pageNum <= TOTAL_PAGES && TOTAL_PAGES) ?
                (
                <ProductCard
                    key={product.id}
        product={product}
        setref={setLastElement}
            />
    ) : (
        <ProductCard
        key={product.id}
        product={product}
            />
    );
    })}

        {loading && <Loading page={pageNum}/>}

    </div>
    );
    }

};

export default ScrollList;
