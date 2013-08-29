<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

class PatientType extends AbstractType
{
    protected $multy;

    public function __construct( $multy = false )
    {
        $this->multy = $multy;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        
        $builder->add( 'mrn', 'text', array(
                'label'=>'MRN:', 
                'max_length'=>100,
                'required'=>false,
                'attr' => array('class'=>'form-control form-control-modif'),
        ));
        
        $builder->add( 'name', 'text', array(
                'label'=>'Name:', 
                'max_length'=>500,
                'required'=>false,
                'attr' => array('class'=>'form-control form-control-modif'),
        ));
        
        
        $builder->add( 'age', 'text', array(
                'label'=>'Age:', 
                'max_length'=>3,
                'required'=>false,
                'attr' => array('class'=>'form-control form-control-modif'),
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
                'required'=>false,
                'attr' => array('class'=>'form-control'),
        )); 
           
        if( $this->multy ) {
            $builder->add('specimen', 'collection', array(
                'type' => new SpecimenType($this->multy),
                'required' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'label' => " ",
                'by_reference' => false,
                'prototype' => true,
                'prototype_name' => '__specimen__',
            ));  
        }


        $factory  = $builder->getFormFactory();
        $builder->addEventListener( FormEvents::PRE_SET_DATA, function(FormEvent $event) use($factory){

                $form = $event->getForm();
                $data = $event->getData();

                if( get_parent_class($data) == 'Oleg\OrderformBundle\Entity\Patient' || get_class($data) == 'Oleg\OrderformBundle\Entity\Patient' ) {
                    $name = $data->getName();

                    $arr = array("Male"=>"Male", "Female"=>"Female");

                    $param = array(
                        'label'=>'Sex:',
                        'max_length'=>20,
                        'required'=>false,
                        'choices' => $arr,
                        'multiple' => false,
                        'expanded' => true,
                        'attr' => array('class' => 'horizontal_type'),
                        'auto_initialize' => false,
                    );

                    $counter = 0;
                    foreach( $arr as $var ){
                        if( trim( $var ) == trim( $name ) ){
                            $key = $counter;
                            $param['data'] = $key;
                        }
                        $counter++;
                    }

                    // field name, field type, data, options
                    $form->add(
                        $factory->createNamed(
                            'sex',
                            'choice',
                            null,
                            $param
                        ));
                }

            }
        );

        
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
