import React from 'react';

function Paginate({
    postsPerPage,
    totalPosts,
    currentPage,
    childToParent,
    previousPage,
    nextPage
})
{
    const pageNumbers = [];

    console.log("Paginate: totalPosts="+totalPosts+", postsPerPage="+postsPerPage+", currentPage="+currentPage);

    let totalPagesNumber = Math.ceil( (totalPosts / postsPerPage) - 1 );

    for (let i = 1; i <= totalPagesNumber; i++) {
        if( i < 4 ) {
            pageNumbers.push(i);
        }
        //else
        if( i > totalPagesNumber-3  ) {
            pageNumbers.push(i);
        }
        //else {
            //pageNumbers.push('...');
        //}
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
                        onClick={() => childToParent(number)}
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

export default Paginate;
