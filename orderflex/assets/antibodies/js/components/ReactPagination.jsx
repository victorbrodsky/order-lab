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
        currentPage,
        childToParent,
        previousPage,
        nextPage
    } = props;

    const pageCount = Math.ceil(totalPosts / postsPerPage);


    return (
        <div className="pagination-container">
            <ReactPaginate
                containerClassName={"pagination"}
                pageClassName={"page-number"}
                activeClassName={"active"}
                onPageChange={(event) => childToParent(event.selected)}
                pageCount={Math.ceil(totalPosts / postsPerPage)}
                breakLabel="..."
                previousLabel={
                                <IconContext.Provider value={{ color: "#B8C1CC", size: "36px" }}>
                                    <li className="page-number">
                                        Prev
                                    </li>
                                </IconContext.Provider>
                              }
                nextLabel={
                                <IconContext.Provider value={{ color: "#B8C1CC", size: "36px" }}>
                                    <div className="page-number">
                                        Next
                                    </div>
                                </IconContext.Provider>
                           }
            />
        </div>
    );
};

export default ReactPagination;

