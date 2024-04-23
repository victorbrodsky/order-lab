import * as React from 'react';
import { useTheme } from '@mui/material/styles';
import Box from '@mui/material/Box';
import MobileStepper from '@mui/material/MobileStepper';
import Paper from '@mui/material/Paper';
import Typography from '@mui/material/Typography';
import Button from '@mui/material/Button';
import KeyboardArrowLeft from '@mui/icons-material/KeyboardArrowLeft';
import KeyboardArrowRight from '@mui/icons-material/KeyboardArrowRight';
import SwipeableViews from 'react-swipeable-views';
import { autoPlay } from 'react-swipeable-views-utils';

//https://mui.com/material-ui/react-stepper/#text-with-carousel-effect

const AutoPlaySwipeableViews = autoPlay(SwipeableViews);

export default function SwipeableTextMobileStepper({ product }) {

    // const images1 = [
    //     {
    //         label: 'San Francisco – Oakland Bay Bridge, United States',
    //         imgPath:
    //             'https://images.unsplash.com/photo-1537944434965-cf4679d1a598?auto=format&fit=crop&w=400&h=250&q=60',
    //     },
    // ];

    //https://sentry.io/answers/react-for-loops/
    var images = [];

    // var imageEl = {label: 'image', imgPath: 'https://images.unsplash.com/photo-1512341689857-198e7e2f3ca8?auto=format&fit=crop&w=400&h=250&q=60' };
    // images.push(
    //     imageEl
    // );

    // product.documents.forEach((imageUrl, index) => {
    //     var imageEl = {label: 'image'+index, imgPath: imageUrl };
    //     images.push(
    //         imageEl
    //     );
    // });

    product.documents.forEach((document, index) => {
        //console.log("document:",document);
        if( document.url ) {
            //var imageEl = {id: document.id, label: document.label, imgPath: document.url};
            images.push(
                {id: document.id+"-"+index, label: document.label, imgPath: document.url }
            );
        }
    });

    //console.log("images:",images);

    const maxSteps = images.length;
    if( maxSteps == 0 ) {
        return;
    }

    const theme = useTheme();
    const [activeStep, setActiveStep] = React.useState(0);

    const handleNext = () => {
        setActiveStep((prevActiveStep) => prevActiveStep + 1);
    };

    const handleBack = () => {
        setActiveStep((prevActiveStep) => prevActiveStep - 1);
    };

    const handleStepChange = (step) => {
        setActiveStep(step);
    };

    //AutoPlaySwipeableViews -> enableMouseEvents
    return (
        <Box
            sx={{ maxWidth: 400, flexGrow: 1 }}
            key={"swip-"+product.id}
        >
            <Paper
                square
                elevation={0}
                sx={{
                  display: 'flex',
                  alignItems: 'center',
                  height: 50,
                  pl: 2,
                  bgcolor: 'background.default',
                }}
            >
                <Typography>
                    {(maxSteps > 0) ? images[activeStep].label : null}
                </Typography>
            </Paper>
            <AutoPlaySwipeableViews
                axis={theme.direction === 'rtl' ? 'x-reverse' : 'x'}
                index={activeStep}
                onChangeIndex={handleStepChange}
                enableMouseEvents
                interval={100000}
            >
                {images.map((step, index) => (
                    <div key={"image-"+step.id+"-"+step.label}>
                        {Math.abs(activeStep - index) <= 2 ? (
                            <Box
                                component="img"
                                sx={{
                                  height: 255,
                                  display: 'block',
                                  maxWidth: 400,
                                  overflow: 'hidden',
                                  width: '100%',
                                }}
                                src={step.imgPath}
                                alt={step.label}
                            />
                        ) : null}
                    </div>
                ))}
            </AutoPlaySwipeableViews>
            <MobileStepper
                steps={maxSteps}
                position="static"
                activeStep={activeStep}
                nextButton={
                  <Button
                    size="small"
                    onClick={handleNext}
                    disabled={ activeStep === maxSteps - 1 || maxSteps === 0 }
                  >
                    Next
                    {theme.direction === 'rtl' ? (
                            <KeyboardArrowLeft />
                        ) : (
                            <KeyboardArrowRight />
                    )}
                  </Button>
                }
                backButton={
                    <Button
                        size="small"
                        onClick={handleBack}
                        disabled={ activeStep === 0 || maxSteps === 0 }
                    >
                        {theme.direction === 'rtl' ? (
                          <KeyboardArrowRight />
                        ) : (
                          <KeyboardArrowLeft />
                        )}
                        Back
                    </Button>
                }
            />
        </Box>
    );
}

//export default SwipeableTextMobileStepper;

