//import * as React from 'react';
import React from 'react';
import axios from 'axios';
import Button from 'react-bootstrap/Button';
import Card from 'react-bootstrap/Card';
import ReactCarousel from "./ReactCarousel";
import { useEffect, useState, useRef } from 'react';


function SingleAntibody({antibodyid}) {

    //const [product, setProduct] = useState(null);

    let product = null;

    //const antibodyUrl = Routing.generate('translationalresearch_antibody_show', {id: product.id});
    const antibodyUrl = Routing.generate('translationalresearch_antibody_public_api', {id: antibodyid});

    const callProduct = async () => {
        console.log("Set product: antibodyUrl="+antibodyUrl);
        let response = await axios.get(
            antibodyUrl
        );
        console.log("Set product: response:",response);
        console.log("Set product: product:",response.data[0]);
        //setProduct(response.data[0]);
        product = response.data[0];
        console.log("product=",product);
        console.log("product name=",product.name);
    };

    callProduct();

    console.log("2 product=",product);
    console.log("2 product name=",product.name);

    //<div style={{ padding: '3' }}>

    return (
        <div>
            <Card
                style={{ width: '18rem', height: '30rem' }}
            >
                <ReactCarousel product={product}/>
                <Card.Body>
                    <Card.Title>{product.name}</Card.Title>
                    <Card.Text>
                        {product.publictext}
                    </Card.Text>
                </Card.Body>
                <Card.Footer>
                    <Button size="small"
                            href={product.datasheet}
                            variant="light"
                            target="_blank"
                            disabled={product.disableDatasheet}
                    >Datasheet</Button>
                    <Button
                        size="small"
                        href={antibodyUrl}
                        variant="light"
                        target="_blank"
                        style={{ marginLeft: '0.1rem' }}
                    >Learn More</Button>
                </Card.Footer>
            </Card>
        </div>
    );
}

export default SingleAntibody;