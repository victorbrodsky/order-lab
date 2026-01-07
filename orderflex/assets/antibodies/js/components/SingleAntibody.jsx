//import * as React from 'react';
import React from 'react';
import axios from 'axios';
import Button from 'react-bootstrap/Button';
import Card from 'react-bootstrap/Card';
import CardGroup from 'react-bootstrap/CardGroup';

import Row from 'react-bootstrap/Row';
import Col from 'react-bootstrap/Col';

import ReactCarousel from "./ReactCarousel";
import { useEffect, useState, useRef } from 'react';
import ProductCard from "./ProductCard";
import ReactCard from "./ReactCard";


function SingleAntibody({antibodyid}) {

    const [product, setProduct] = useState(null);

    //let product = null;

    //const antibodyUrl = Routing.generate('translationalresearch_antibody_show', {id: product.id});
    const antibodyUrl = Routing.generate(
        'translationalresearch_antibody_public_api',
        {id: antibodyid}
    );

    const callAntibody = async () => {
        console.log("callAntibody: antibodyUrl="+antibodyUrl);
        let response = await axios.get(antibodyUrl,{withCredentials: true});
        //console.log("Set product: response:",response);
        //console.log("Set product: product:",response.data[0]);
        setProduct(response.data[0]);
    };

    // callAntibody() = {
    //     //urla = Routing.generate('translationalresearch_antibody_public_api', {id: antibodyid});
    //     axios.get(urla).then(product => {
    //         this.setProduct({ product: response.data[0], loading: false})
    //     })
    // }

    //callAntibody();

    useEffect(() => {
        console.log("useEffect: callAntibody");
        callAntibody();
    }, []);

    //console.log("2 product=",product);
    //console.log("2 product name=",product.name);

    //<div style={{ padding: '3' }}>

    // <Button
    //     size="small"
    //     href={Routing.generate('translationalresearch_antibody_public_api', {id: product.associate[i].id})}
    //     variant="light"
    //     target="_blank"
    //     style={{ marginLeft: '0.1rem' }}
    // >{product.associate[i].name}</Button>

    // {typeof product.associates !== 'undefined' && product.associates.length > 0 && product.associates.map((associate, i) =>
    //     <p>{product.associate.id} - {product.associate.name}</p>
    // )}

    if(1)
    return (
        <div>
            {product &&
                <div>
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

                    <dl className="row" style={{marginTop: '3rem'}}>
                        <dt className="col-sm-3 text-end">Name</dt>
                        <dd className="col-sm-9 text-start">{product.name}</dd>

                        <dt className="col-sm-3 text-end">Description</dt>
                        <dd className="col-sm-9 text-start">{product.description}</dd>

                        <dt className="col-sm-3 text-end">Tags</dt>
                        <dd className="col-sm-9 text-start">{product.tags}</dd>

                        <dt className="col-sm-3 text-end">Company</dt>
                        <dd className="col-sm-9 text-start">{product.company}</dd>

                        <dt className="col-sm-3 text-end">Clone</dt>
                        <dd className="col-sm-9 text-start">{product.clone}</dd>

                        <dt className="col-sm-3 text-end">Host</dt>
                        <dd className="col-sm-9 text-start">{product.host}</dd>

                        <dt className="col-sm-3 text-end">Reactivity</dt>
                        <dd className="col-sm-9 text-start">{product.reactivity}</dd>

                        <dt className="col-sm-3 text-end">Storage</dt>
                        <dd className="col-sm-9 text-start">{product.storage}</dd>

                        <dt className="col-sm-3 text-end">
                            Associates
                        </dt>
                        <dd className="col-sm-9 text-start">
                            {product.associates.length > 0 && product.associates.map((associate, i) =>
                                <div key={"saa-"+i}>
                                    <Button
                                        size="small"
                                        href={Routing.generate('translationalresearch_antibody_show_react', {id: associate.id})}
                                        variant="light"
                                        target="_blank"
                                        style={{ marginLeft: '0.1rem' }}
                                    >{associate.name}</Button>
                                </div>
                            )}
                        </dd>
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