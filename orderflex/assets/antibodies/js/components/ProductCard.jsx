import React, { Component } from "react";
//import Button from 'react-bootstrap/Button';
//import Card from 'react-bootstrap/Card';
import ReactCard from "./ReactCard";
import MediaCard from "./MediaCard";

//<img src="https://mdbcdn.b-cdn.net/img/new/standard/city/043.webp" className="card-img-top" alt="Hollywood Sign on The Hill" />

const ProductCard = ({ product }) => {

    //https://sentry.io/answers/react-for-loops/
    // let imageList = [];
    // product.documents.forEach((imageUrl, index) => {
    //     imageList.push(
    //         <li key={index}>{imageUrl}</li>
    //         //<img src={imageUrl} className="card-img-top" alt="Antibody Image"/>
    //     );
    // });


    return (
        <ReactCard
            product={product}
        >
        </ReactCard>
    )

    if(0)
    return (
        <MediaCard
            product={product}
        >
        </MediaCard>
    )
    
}

export default ProductCard;

