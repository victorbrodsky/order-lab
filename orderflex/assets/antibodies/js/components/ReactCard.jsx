import * as React from 'react';
import Button from 'react-bootstrap/Button';
import Card from 'react-bootstrap/Card';
import ReactCarousel from "./ReactCarousel";

//<Card.Img variant="top" src="holder.js/100px180" />
//<ReactCarousel product={product}/>

//style={{ width: '18rem', height: '30rem' }}
//disabled={ !product.datasheet }

function ReactCard({product}) {

    //const antibodyUrl = Routing.generate('translationalresearch_antibody_show', {id: product.id});
    const antibodyUrl = Routing.generate('translationalresearch_antibody_show_react', {id: product.id});

    //<div style={{ padding: '3' }}>
    //disabled={product.disableDatasheet}

    return (
        <div>
            <Card
                style={{ width: '18rem', height: '30rem' }}
            >
                <ReactCarousel product={product}/>
                <Card.Body>
                    <Card.Title>{product.name}</Card.Title>
                    <Card.Text>
                        {cardTextTruncate(product.publictext)}
                    </Card.Text>
                </Card.Body>
                <Card.Footer>
                    {product.datasheet &&
                        <Button size="small"
                                href={product.datasheet}
                                variant="light"
                                target="_blank"
                                disabled={false}
                        >Datasheet</Button>
                    }
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

function cardTextTruncate(str) {
    var maxlen = 90;
    return str.length > maxlen ? str.substring(0, maxlen) + "..." : str;
}

export default ReactCard;

