import React from 'react';

const Items = ({ id, title, body }) => (
    <div key={id} className="card col-md-4" style={{width:200}}>
        <div className="card-body">
            <p>{id}</p>
            <h4 className="card-title">{title}</h4>
            <p className="card-text">{body}</p>
            <a href="https://jsonplaceholder.typicode.com/posts/" className="btn btn-primary">More Details</a>
        </div>
    </div>
);

export default Items;