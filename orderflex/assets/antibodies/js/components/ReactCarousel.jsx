import * as React from 'react';
import Carousel from 'react-bootstrap/Carousel';
//import ExampleCarouselImage from 'components/ExampleCarouselImage';

function ReactCarousel({product}) {

    var images = [];
    product.documents.forEach((document, index) => {
        //console.log("document:",document);
        if( document.url ) {
            //var imageEl = {id: document.id, label: document.label, imgPath: document.url};
            images.push(
                {id: document.id+"-"+index, label: document.label, imgPath: document.url }
            );
        }
    });

    //className="d-block w-100"

    return (
        <Carousel fade interval={null}>
            {images.map((step, index) => (
                <Carousel.Item key={"carimage-"+index}>
                    <img
                        className="rounded"
                        style={{ width: '18rem', height: '18rem' }}
                        src={step.imgPath}
                        alt={step.label}
                    />
                    <Carousel.Caption>
                        <h3>{step.label}</h3>
                        <p>{step.label}</p>
                    </Carousel.Caption>
                </Carousel.Item>
            ))}
        </Carousel>
    );

    if(0)
    return (
        <Carousel fade>
            <Carousel.Item>
                <img
                    className="d-block w-100"
                    src="https://media.geeksforgeeks.org/wp-content/uploads/20210425122739/2-300x115.png"
                    alt="Image One"
                />
                <Carousel.Caption>
                    <h3>First slide label</h3>
                    <p>Nulla vitae elit libero, a pharetra augue mollis interdum.</p>
                </Carousel.Caption>
            </Carousel.Item>
            <Carousel.Item>
                <img
                    className="d-block w-100"
                    src="https://media.geeksforgeeks.org/wp-content/uploads/20210425122739/2-300x115.png"
                    alt="Image One"
                />
                <Carousel.Caption>
                    <h3>Second slide label</h3>
                    <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
                </Carousel.Caption>
            </Carousel.Item>
            <Carousel.Item>
                <img
                    className="d-block w-100"
                    src="https://media.geeksforgeeks.org/wp-content/uploads/20210425122739/2-300x115.png"
                    alt="Image One"
                />
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

export default ReactCarousel;

