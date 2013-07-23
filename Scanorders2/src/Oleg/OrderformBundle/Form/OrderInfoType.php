<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Oleg\OrderformBundle\Helper as Helper;

class OrderInfoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $helper = new Helper\FormHelper();
        
        $builder->add( 'provider', 'text', array(
                'label'=>'* Ordering Provider:', 
                'max_length'=>'200', 
                'required'=>true
        ));
        $builder->add( 'pathologyService', 'text', array(
                'label'=>'Pathology Service:', 
                'max_length'=>200,'required'=>false
        ));
        
        $builder->add( 'priority', 'choice', array(
                'label'=>'* Priority:', 
                'max_length'=>200,
                'required'=>true,
                'choices' => $helper->getPriority(),
        ));
        
        $builder->add( 'slideDelivery', 
                'choice', array(  
                'label'=>'* Slide Delivery:',
                'max_length'=>200,
                'choices' => $helper->getSlideDelivery(),
                'required'=>true
        ));
                
        $builder->add( 'returnSlide', 'text', array(
                'label'=>'* Return Slides to:', 
                'max_length'=>200,
                'required'=>true
        ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\OrderInfo'
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_orderinfotype';
    }
}
