import React from 'react';
import axios from 'axios';
import { useRef } from 'react';
//import  { useNavigate } from 'react-router-dom'
import '../../css/index.css';

const DeactivateButton = ({deactivateRowRefs}) => {
    const buttonRef = useRef();
    const updateUrl = Routing.generate('employees_update_users_date');
    const redircetUrl = Routing.generate('employees_user_dates_show');

    function disableAccounts() {
        //alert("To be implemented");
        //var rows = $('.'+"table-row-"+data.id);
        //var rows = $(tableBodyRef).find();

        var deactivateDataArr = [];

        for( let i = 0; i < deactivateRowRefs.length; i++ ) {
            console.log("deactivateRowRefs len="+deactivateRowRefs.length);
            console.log("row=",deactivateRowRefs[i]);

            var row = deactivateRowRefs[i].current;
            var userId = row.id;
            userId = userId.replace('table-row-', '');

            var startDate = $(row).find("#"+"datepicker-start-date-"+userId).val();
            var endDate = $(row).find("#"+"datepicker-end-date-"+userId).val();

            var thisData = {'userId': userId, 'startDate': startDate, 'endDate': endDate};
            deactivateDataArr.push(thisData);
        };

        if( deactivateDataArr.length > 0 ) {
            //const navigate = useNavigate();
            var l = Ladda.create(buttonRef.current);
            l.start();
            console.log("deactivateDataArr",deactivateDataArr);
            //return;

            axios({
                method: 'post',
                url: updateUrl,
                data: {deactivateData: deactivateDataArr}
            })
                .then((response) => {
                    console.log("response.data=[" + response.data + "]");
                    l.stop();
                    if (response.data == "ok") {
                        console.log("Active");
                        //navigate('/directory/employment-dates/view', { replace: true });
                        window.location.href = redircetUrl;
                    } else {
                        alert(response.data);
                    }
                }, (error) => {
                    //console.log(error);
                    var errorMsg = "Unexpected Error. " +
                        "Please make sure that your session is not timed out and you are still logged in. " + error;
                    //this.addErrorLine(errorMsg,'error');
                    alert(errorMsg);
                    l.stop();
                });
        }
    }

    return (
        <p>
            <button ref={buttonRef} className="btn btn-warning" onClick={disableAccounts}
            >Deactivate selected accounts and save entered start and end dates</button>
        </p>
    );
};

export default DeactivateButton;

