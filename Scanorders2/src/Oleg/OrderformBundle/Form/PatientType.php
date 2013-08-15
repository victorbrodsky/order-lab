<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PatientType extends AbstractType
{
    protected $multy;
    
    public function __construct( $multy = false )
    {
        $this->multy = $multy;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        
        //$multi = false;
        
        $builder->add( 'mrn', 'text', array(
                'label'=>'MRN:', 
                'max_length'=>100,
                'required'=>false
        ));
        
        $builder->add( 'name', 'text', array(
                'label'=>'Name:', 
                'max_length'=>500,
                'required'=>false
        ));
        
        
        $builder->add( 'age', 'text', array(
                'label'=>'Age:', 
                'max_length'=>3,
                'required'=>false
        ));               
        
        $builder->add( 'sex', 'choice', array(
                'label'=>'Sex:', 
                'max_length'=>20,
                'required'=>false,
                'choices' => array("Male"=>"Male", "Female"=>"Female"),
                'multiple' => false,
                'expanded' => true,
                'attr' => array('class' => 'horizontal_type')  
                //'data' => 'Male',             
        ));
             
        $builder->add( 'dob', 'date', array(
                'label'=>'DOB:',
                'widget' => 'single_text',
                'required'=>false,
                'format' => 'MM-dd-yyyy',
                'attr' => array('class' => 'datepicker'),
        ));
        
        $builder->add( 'clinicalHistory', 'textarea', array(
                'label'=>'Clinical History:', 
                'max_length'=>10000,
                'required'=>false
        )); 
           
        if( $this->multy ) {
            $builder->add('specimen', 'collection', array(
                'type' => new SpecimenType($this->multy), //testing only up to specimen
                'required' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'label' => " ",
                'by_reference' => false,
                'prototype' => true,
                'prototype_name' => '__specimen__',
            ));  
        }
        
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\Patient'
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_patienttype';
    }
}
