import React from 'react';
import '../../css/index.css';

const DeactivateButton = () => {
    const url = Routing.generate('employees_update_users_date');

    function disableAccounts({addDeactivateElement}) {
        //alert("To be implemented");
        var rows = $('.'+"table-row-"+data.id);
        rows.each(function(e) {
            console.log("row=",$(this));
        });
        
    }

    return (
        <p>
            <a className="btn btn-warning" href={ url } onClick={disableAccounts}
            >Deactivate selected accounts and save entered start and end dates</a>
        </p>
    );
};

export default DeactivateButton;

