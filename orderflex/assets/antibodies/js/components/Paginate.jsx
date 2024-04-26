import React from 'react';

const Paginate = ({
    postsPerPage,
    totalPosts,
    currentPage,
    paginate,
    previousPage,
    nextPage,
}) => {
    const pageNumbers = [];

    console.log("Paginate: totalPosts="+totalPosts+", postsPerPage="+postsPerPage);

    let totalPagesNumber = Math.ceil(totalPosts / postsPerPage);

    for (let i = 1; i <= totalPagesNumber; i++) {
        if( i < 3 ) {
            pageNumbers.push(i);
        }
        if( i > totalPagesNumber-3  ) {
            pageNumbers.push(i);
        }
    }
    return (
        <div className="pagination-container">
            <ul className="pagination">
                <li onClick={previousPage} className="page-number">
                    Prev
                </li>
                {pageNumbers.map((number) => (
                    <li
                        key={number}
                        onClick={() => paginate(number)}
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
