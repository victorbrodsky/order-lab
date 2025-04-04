import '../css/index.css'

import React from 'react'
import ReactDOM from "react-dom/client"
import { BrowserRouter as Router, Route, Routes } from 'react-router-dom'
import SingleAntibody from "./components/SingleAntibody";


var _antibodyid = $('#antibody-id').val();
console.log("_antibodyid="+_antibodyid);

const root = ReactDOM.createRoot(document.getElementById("root"));

//const element = <h1>Hello, world</h1>;
//root.render(element);

// <ProductCard
//     product={product}
// />

root.render(
    <React.StrictMode>
        <Router>
            <SingleAntibody antibodyid={_antibodyid} />
        </Router>
    </React.StrictMode>
);

if(0) {
    root.render(
        <React.StrictMode>
            <Router>
                <div>Show details for antibody with ID {_antibodyid}</div>
                <p>
                    Details to be implemented
                </p>
            </Router>
        </React.StrictMode>
    );
}
