import * as React from 'react';
import { Link } from 'react-router-dom';
import Card from '@mui/material/Card';
import CardActions from '@mui/material/CardActions';
import CardContent from '@mui/material/CardContent';
//import CardMedia from '@mui/material/CardMedia';
import Button from '@mui/material/Button';
import Typography from '@mui/material/Typography';
import SwipeableTextMobileStepper from './SwipeableTextMobileStepper'

//image="/static/images/cards/contemplative-reptile.jpg"

export default function MediaCard({product}) {
    //console.log('MediaCard: id='+product.id+'; image='+product.image);

    //image={product.image}
    // <CardMedia
    //     sx={{ height: 140 }}
    //     title="green iguana"
    //     component='img'
    // />

    //sx={{ maxWidth: 345, minHeight: 500 }}
    //height: '475px'
    //minHeight: '475px'
    
    const antibodyUrl = Routing.generate('translationalresearch_antibody_show', {id: product.id});
    //console.log('MediaCard: antibodyUrl='+antibodyUrl);

    return (
        <Card sx={{ maxWidth: 345 }}>
            <CardContent>
                <SwipeableTextMobileStepper
                    product={product}
                ></SwipeableTextMobileStepper>
                <Typography variant="body2" color="text.secondary">
                    {product.publictext}
                </Typography>
            </CardContent>
            <CardActions>
                <Button size="small"
                        href={product.datasheet} variant="contained" target="_blank"
                        disabled={ !product.datasheet }
                >Datasheet</Button>
                <Button
                    size="small"
                    href={antibodyUrl} variant="contained" target="_blank"
                >Learn More</Button>
            </CardActions>
        </Card>
    );

    if(0)
    return (
        <Card sx={{ maxWidth: 345, minHeight: 500 }}>
            <CardContent>
                <SwipeableTextMobileStepper
                    product={product}
                ></SwipeableTextMobileStepper>
                <Typography gutterBottom variant="h5" component="div">
                    {product.name}
                </Typography>
                <Typography variant="body2" color="text.secondary">
                    {product.publictext}
                </Typography>
            </CardContent>
            <CardActions>
                <Button size="small">Share</Button>
                <Button size="small">Learn More</Button>
            </CardActions>
        </Card>
    );
}

