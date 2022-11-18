import React from 'react';

const Photos = ({ id, title, url, thumbnailUrl }) => (
    <div key={id} className="card col-md-4" style={{width:200}}>
        <div className="card-body">
            <p>{id}</p>
            <h4 className="card-title">{title}</h4>
            <p className="card-text">
                <img src={thumbnailUrl} height="100px" width="200px" />
            </p>
            <a href={url} className="btn btn-primary">More Details</a>
        </div>
    </div>
);

export default Photos;