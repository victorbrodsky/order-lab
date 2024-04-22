import React, { Component } from "react";
//import Button from 'react-bootstrap/Button';
//import Card from 'react-bootstrap/Card';
//import ProductCarousel from "./ProductCarousel";
import MediaCard from "./MediaCard";

//<img src="https://mdbcdn.b-cdn.net/img/new/standard/city/043.webp" className="card-img-top" alt="Hollywood Sign on The Hill" />

const ProductCard = ({ product, setref }) => {

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
            key={product.id}
            product={product}
        >
        </MediaCard>
    )

    // return (
    //
    //     <Card
    //         style={{ width: '18rem' }}
    //         bg='light'
    //         text='dark'
    //         className="mb-2"
    //         key={product.id}
    //     >
    //         <Card.Body>
    //             <ProductCarousel
    //                 key={product.id}
    //                 product={product}
    //             ></ProductCarousel>
    //             <Card.Title>{product.name}</Card.Title>
    //             <Card.Text>
    //                 {product.publictext}
    //             </Card.Text>
    //             <Button variant="secondary">Go somewhere</Button>
    //         </Card.Body>
    //     </Card>
    // );

}

export default ProductCard;

