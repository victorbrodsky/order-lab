<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Oleg\OrderformBundle\Helper\FormHelper;

class OrderInfoType extends AbstractType
{
    
    protected $multy;
    
    public function __construct( $multy = false )
    {
        $this->multy = $multy;
    }
        
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        
        $helper = new FormHelper();
                   
        $builder->add( 'type', 'hidden' ); 
        
        if( $this->multy ) {          
            $builder->add('patient', 'collection', array(
                'type' => new PatientType($this->multy),
                'required' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'label' => " ",
                'by_reference' => false,
                'prototype' => true,
                'prototype_name' => '__patient__',
            ));                  
        }
        
        $builder->add( 'provider', 'text', array(
                'label'=>'* Ordering Provider:', 
                'max_length'=>'200', 
                'required'=>true,
                'attr' => array('required' => 'required')
        ));
               
        $builder->add( 'pathologyService', 'choice', array(
                'label' => 'Pathology Service:', 
                'max_length'=>200,
                'choices' => $helper->getPathologyService(),
                'required'=>false,
                'attr' => array('class' => 'combobox')
                //'data' => 0, 
        ));
        
        $builder->add( 'priority', 'choice', array(
                'label' => '* Priority:', 
                //'max_length'=>200,
                'required' => true,
                'choices' => $helper->getPriority(),
                'data' => 'Routine',  
                'multiple' => false,
                'expanded' => true,
                'attr' => array('class' => 'horizontal_type', 'required' => 'required')
        ));
        
        $builder->add( 'slideDelivery', 
                'choice', array(  
                'label'=>'* Slide Delivery:',
                'max_length'=>200,
                'choices' => $helper->getSlideDelivery(),
                'required'=>true,
                'data' => 0,     
                'attr' => array('class' => 'combobox', 'required' => 'required')
        ));
                
        $builder->add( 'returnSlide', 
                'choice', array(
                'label'=>'* Return Slides to:', 
                'max_length'=>200,
                'choices' => $helper->getReturnSlide(),
                'required'=>true,
                'data' => 'Filing Room',
                'attr' => array('class' => 'combobox', 'required' => 'required')
        ));
        
        $builder->add('scandeadline','date',array(
            'widget' => 'single_text',
            'format' => 'MM-dd-yyyy',
            'attr' => array('class' => 'datepicker'),
            'required' => false,
            'data' => new \DateTime(),
            'label'=>'Scan Deadline:',
        ));
        
        $builder->add('returnoption', 'checkbox', array(
            'label'     => 'Return slide(s) by this date even if not scanned',
            'required'  => false,
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
