import React, { Component } from "react";
//import Button from 'react-bootstrap/Button';
//import Card from 'react-bootstrap/Card';
//import ProductCarousel from "./ProductCarousel";
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
        <MediaCard
            product={product}
        >
        </MediaCard>
    )
}

export default ProductCard;

