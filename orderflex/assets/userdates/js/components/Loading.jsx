import React from 'react';
import '../../css/index.css';

const Loading = ({page}) => {
    return (
        <tr>
            <td>
                Loading page {page}...
            </td>
        </tr>
    );
};

export default Loading;


