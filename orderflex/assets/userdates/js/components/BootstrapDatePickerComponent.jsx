import React from 'react'
//import { Form } from 'react-bootstrap';
import Form from "react-bootstrap/Form";
//import '../../../../node_modules/bootstrap/dist/css/bootstrap.min.css';

class BootstrapDatePickerComponent extends React.Component{

    render(){
        return(
            <Form.Group controlId="dob">
                <Form.Control type="date" name="dob" placeholder="Start Date" />
            </Form.Group>
        )
    }

}

export default BootstrapDatePickerComponent;


//<Form.Label>Select Date</Form.Label>

// <div>
//     <div className="row">
//         <div className="col-md-4">
//             <Form.Group controlId="dob">
//                 <Form.Control type="date" name="dob" placeholder="Start Date" />
//             </Form.Group>
//         </div>
//     </div>
// </div>

