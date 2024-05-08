//import * as React from 'react';
import React from 'react';
import axios from 'axios';
import Button from 'react-bootstrap/Button';
import Card from 'react-bootstrap/Card';
import CardGroup from 'react-bootstrap/CardGroup';

import ReactCarousel from "./ReactCarousel";
import { useEffect, useState, useRef } from 'react';
import ProductCard from "./ProductCard";
import ReactCard from "./ReactCard";


function SingleAntibody({antibodyid}) {

    const [product, setProduct] = useState(null);

    //let product = null;

    //const antibodyUrl = Routing.generate('translationalresearch_antibody_show', {id: product.id});
    const antibodyUrl = Routing.generate('translationalresearch_antibody_public_api', {id: antibodyid});

    const callProduct = async () => {
        //console.log("Set product: antibodyUrl="+antibodyUrl);
        let response = await axios.get(
            antibodyUrl
        );
        //console.log("Set product: response:",response);
        //console.log("Set product: product:",response.data[0]);
        setProduct(response.data[0]);
        //product = response.data[0];
        //console.log("product=",product);
        //console.log("product name=",product.name);
    };

    callProduct();

    //console.log("2 product=",product);
    //console.log("2 product name=",product.name);

    //<div style={{ padding: '3' }}>

    if(1)
    return (
        <div>
            <p>Show antibody!</p>
            {product &&
                <div>
                    <p>Documents: {product.documents.length}</p>
                    <CardGroup>
                        {product.documents.length > 0 && product.documents.map((image, i) =>
                            <div style={{ padding: '0.1rem' }} key={"sa-"+i}>
                                <Card>
                                    <Card.Img variant="top"
                                              src={image.url}
                                              className="rounded"
                                              style={{ width: '18rem', height: '18rem' }} />

                                        {image.comment &&
                                        <Card.Body>
                                        <Card.Text>
                                            {image.comment}
                                            {image.catalog &&
                                            <p>{image.catalog}</p>
                                            }
                                        </Card.Text>
                                        </Card.Body>
                                        }

                                </Card>
                            </div>
                        )}
                    </CardGroup>

                    <dl className="row boxesText g-1" style={{marginTop: '3rem'}}>
                        <dt className="col-sm-3">Name</dt>
                        <dd className="col-sm-9">{product.name}</dd>

                        <dt className="col-sm-3">Description</dt>
                        <dd className="col-sm-9">{product.description}</dd>

                        <dt className="col-sm-3">Tags</dt>
                        <dd className="col-sm-9">{product.tags}</dd>

                        <dt className="col-sm-3">Company</dt>
                        <dd className="col-sm-9">{product.company}</dd>

                        <dt className="col-sm-3">Clone</dt>
                        <dd className="col-sm-9">{product.clone}</dd>

                        <dt className="col-sm-3">Host</dt>
                        <dd className="col-sm-9">{product.host}</dd>

                        <dt className="col-sm-3">Reactivity</dt>
                        <dd className="col-sm-9">{product.reactivity}</dd>

                        <dt className="col-sm-3">Storage</dt>
                        <dd className="col-sm-9">{product.storage}</dd>

                        <dt className="col-sm-3">Associates</dt>
                        <dd className="col-sm-9">{product.associates}</dd>

                    </dl>


                </div>
            }
        </div>
    );

    if(0)
    return (
        <CardGroup>
            <Card>
                <Card.Img variant="top" src="holder.js/100px160" />
                <Card.Body>
                    <Card.Title>Card title</Card.Title>
                    <Card.Text>
                        This is a wider card with supporting text below as a natural lead-in
                        to additional content. This content is a little bit longer.
                    </Card.Text>
                </Card.Body>
                <Card.Footer>
                    <small className="text-muted">Last updated 3 mins ago</small>
                </Card.Footer>
            </Card>
            <Card>
                <Card.Img variant="top" src="holder.js/100px160" />
                <Card.Body>
                    <Card.Title>Card title</Card.Title>
                    <Card.Text>
                        This card has supporting text below as a natural lead-in to
                        additional content.{' '}
                    </Card.Text>
                </Card.Body>
                <Card.Footer>
                    <small className="text-muted">Last updated 3 mins ago</small>
                </Card.Footer>
            </Card>
            <Card>
                <Card.Img variant="top" src="holder.js/100px160" />
                <Card.Body>
                    <Card.Title>Card title</Card.Title>
                    <Card.Text>
                        This is a wider card with supporting text below as a natural lead-in
                        to additional content. This card has even longer content than the
                        first to show that equal height action.
                    </Card.Text>
                </Card.Body>
                <Card.Footer>
                    <small className="text-muted">Last updated 3 mins ago</small>
                </Card.Footer>
            </Card>
        </CardGroup>
    );

    // return (
    //     <ReactCard
    //         product={product}
    //     >
    //     </ReactCard>
    // );

    if(0)
    return (
        <div>
            {(() => {
                if( product ) {
                    <div>
                    console.log("view product name=",product.name);
                    <CardGroup>
                        console.log("view product.documents.length=",product.documents.length);
                        {product.documents.length > 0 && product.documents.map((image, i) =>
                            <div style={{ padding: '0.1rem' }}>
                                <Card>
                                    <Card.Img variant="top" src={image.url} />
                                    <Card.Body>
                                        {image.label &&
                                            <Card.Title>{image.label}</Card.Title>
                                        }
                                        {image.comment &&
                                            <Card.Text>
                                                {image.comment}
                                                {image.catalog &&
                                                    <p>{image.catalog}</p>
                                                }
                                            </Card.Text>
                                        }
                                    </Card.Body>
                                </Card>
                            </div>
                        )}
                    </CardGroup>
                    <p>{product.publictext}</p>
                    </div>
                } else {
                    console.log("view product is null");
                    <div>Please wait ...</div>
                }
            })()}
        </div>
    );
}

export default SingleAntibody;