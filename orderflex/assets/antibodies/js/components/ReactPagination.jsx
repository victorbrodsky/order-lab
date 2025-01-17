import React from 'react';
import ReactPaginate from "react-paginate"; // for pagination
import { AiFillLeftCircle, AiFillRightCircle } from "react-icons/ai"; // icons form react-icons
import { IconContext } from "react-icons"; // for customizing icons
import { useEffect, useState } from "react"; // useState for storing data and useEffect for changing data on click
//import "../../css/pagination.css"; // stylesheet


//https://dev.to/documatic/building-pagination-in-react-with-react-paginate-4nol

const ReactPagination = props => {
    const {
        postsPerPage,
        totalPosts,
        handlePageClick
    } = props;

    //console.log("totalPosts="+totalPosts);
    //console.log("postsPerPage="+postsPerPage);
    //console.log("count="+(totalPosts / postsPerPage));

    const pageCount = Math.ceil(totalPosts / postsPerPage);
    //const pageCount = totalPosts==0 ? 0 : (totalPosts - 1) / postsPerPage + 1;
    //console.log("pageCount="+pageCount);
    //If count=62.05 => pageCount = 63

    // <IconContext.Provider value={{ color: "#B8C1CC", size: "36px" }}>
    //     <li className="page-number">
    //         Prev
    //     </li>
    // </IconContext.Provider>

    // if(0)
    // return (
    //     <>
    //     <ReactPaginate
    //         nextLabel={"Next"}
    //         onPageChange={handlePageClick}
    //         pageRangeDisplayed={3}
    //         marginPagesDisplayed={2}
    //         pageCount={pageCount}
    //         previousLabel="< previous"
    //         pageClassName="page-number"
    //         previousClassName="page-number"
    //         nextClassName="page-number"
    //         breakLabel="..."
    //         breakClassName="page-number"
    //         containerClassName="pagination"
    //         activeClassName="active"
    //         renderOnZeroPageCount={null}
    //     />
    //     </>
    // );

    //marginPagesDisplayed={2}

    //Use pageLinkClassName instead of pageClassName to make c
    //lickable the highlighted background of the page buttons.

    return (
        <div className="pagination-container">
            <ReactPaginate
                containerClassName={"pagination"}
                pageLinkClassName={"page-number"}
                activeClassName={"active"}
                pageRangeDisplayed={3}
                onPageChange={(event) => handlePageClick(event.selected)}
                pageCount={pageCount}
                breakLabel="..."
                previousClassName="page-number"
                nextClassName="page-number"
                previousLabel={ "Prev" }
                nextLabel={ "Next" }

            />
        </div>
    );
};

export default ReactPagination;

