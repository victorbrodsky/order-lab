import React from 'react';
import '../../css/index.css';

const DeactivateButton = () => {
    const url = Routing.generate('employees_update_users_date');

    function disableAccounts() {
        alert("To be implemented");
    }

    return (
        <p>
            <a className="btn btn-warning" href={ url } onClick={disableAccounts}
            >Deactivate selected accounts and save entered start and end dates</a>
        </p>
    );
};

export default DeactivateButton;

