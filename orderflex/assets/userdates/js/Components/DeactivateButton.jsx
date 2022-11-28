import React from 'react';
import '../../css/index.css';

const DeactivateButton = () => {
    let url = Routing.generate('employees_update_users_date');
    return (
        <p>
            <a className="btn btn-warning" href={ url }
            >Deactivate selected accounts and save entered start and end dates</a>
        </p>
    );
};

export default DeactivateButton;

