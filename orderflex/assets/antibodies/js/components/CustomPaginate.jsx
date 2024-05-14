import React from 'react';

function CustomPaginate({
    postsPerPage,
    totalPosts,
    currentPage,
    handlePageClick,
    previousPage,
    nextPage
})
{
    const pageNumbers = [];

    //console.log("CustomPaginate: totalPosts="+totalPosts+", postsPerPage="+postsPerPage+", currentPage="+currentPage);

    let totalPagesNumber = Math.ceil( (totalPosts / postsPerPage) );

    if( totalPagesNumber > 0 &&  totalPagesNumber < 5 ) {
        for (let i = 1; i <= totalPagesNumber; i++) {
            pageNumbers.push(i);
        }
    } else {
        //totalPagesNumber > 5: 1 2 3 ... totalPagesNumber
        pageNumbers.push(1);
        pageNumbers.push(2);
        pageNumbers.push(3);
        pageNumbers.push('...');
        pageNumbers.push(totalPagesNumber);
    }
    //console.log("pageNumbers:",pageNumbers);

    return (
        <div className="pagination-container">
            <ul className="pagination">
                <li onClick={previousPage} className="page-number">
                    Prev
                </li>
                {pageNumbers.map((number) => (
                    <li
                        key={number}
                        onClick={() => handlePageClick(number)}
                        className={
                                'page-number ' + (number === currentPage ? 'active' : '')
                            }
                    >
                        {number}
                    </li>
                ))}
                <li onClick={nextPage} className="page-number">
                    Next
                </li>
            </ul>
        </div>
    );
};

export default CustomPaginate;
