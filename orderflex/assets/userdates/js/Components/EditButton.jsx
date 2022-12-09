import React from 'react';
import '../../css/index.css';

const EditButton = () => {
    const editUrl = Routing.generate('employees_user_dates_edit');

    return (
        <p>
            <a className="btn btn-info" href={editUrl}>Edit</a>
        </p>
    );
};

export default EditButton;

