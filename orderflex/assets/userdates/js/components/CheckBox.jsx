/*
Create a checkbox functional component.
    Toggle the text of a paragraph with the checkbox using the 'useState' hook.
*/

import React, {useState} from 'react';

function Checkbox() {

    const [checked, setChecked] = useState(false);
    const handleChange = () => {
        setChecked(!checked);
    };

    return (
        <input type="checkbox" onChange={handleChange}/>
    );

};

export default Checkbox;
