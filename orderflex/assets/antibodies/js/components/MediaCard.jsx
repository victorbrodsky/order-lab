import * as React from 'react';
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

