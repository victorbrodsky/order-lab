import React, { Component } from "react";
import Carousel from 'react-bootstrap/Carousel';

//<img src="https://mdbcdn.b-cdn.net/img/new/standard/city/043.webp" className="card-img-top" alt="Hollywood Sign on The Hill" />

const ProductCarousel = ({ product, setref }) => {

    //https://sentry.io/answers/react-for-loops/
    // let imageList = [];
    // product.documents.forEach((imageUrl, index) => {
    //     imageList.push(
    //         <li key={index}>{imageUrl}</li>
    //         //<img src={imageUrl} className="card-img-top" alt="Antibody Image"/>
    //     );
    // });

    return (
        <Carousel>
            <Carousel.Item>
                <Carousel.Caption>
                    <h3>First slide label</h3>
                    <p>Nulla vitae elit libero, a pharetra augue mollis interdum.</p>
                </Carousel.Caption>
            </Carousel.Item>
            <Carousel.Item>
                <Carousel.Caption>
                    <h3>Second slide label</h3>
                    <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
                </Carousel.Caption>
            </Carousel.Item>
            <Carousel.Item>
                <Carousel.Caption>
                    <h3>Third slide label</h3>
                    <p>
                        Praesent commodo cursus magna, vel scelerisque nisl consectetur.
                    </p>
                </Carousel.Caption>
            </Carousel.Item>
        </Carousel>
    );

}

export default ProductCarousel;

