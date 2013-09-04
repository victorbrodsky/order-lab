<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SlideType extends AbstractType
{
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {      
        $builder->add( 'id', 'hidden' );
        
        //$builder->add( 'stain', new StainType(), array('label'=>'Stain:') ); \
        $builder->add('stain', 'collection', array(
            'type' => new StainType(),
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'label' => " ",
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__stain__',
        ));
        
//        $builder->add( 'scan', new ScanType(), array('label'=>'Scan:') );
        $builder->add('scan', 'collection', array(
            'type' => new ScanType(),
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'label' => " ",
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__scan__',
        ));
                    
        $builder->add('diagnosis', 'textarea', array(
                'max_length'=>10000,
                'required'=>false,
                'label'=>'Diagnosis / Reason for scans:',
                'attr' => array('class'=>'form-control'),
                //'attr'=>array('readonly'=>true)
        ));
        
        $builder->add('microscopicdescr', 'textarea', array(
                'max_length'=>10000,
                'required'=>false,
                'label'=>'Microscopic Description:',
                'attr' => array('class'=>'form-control'),
        ));
        
        $builder->add('specialstain', 'text', array(
                'max_length'=>100,
                'required'=>false,
                'label'=>'Special Stain Results:',
                'attr' => array('class'=>'form-control form-control-modif'),
        ));
        
        $builder->add('relevantscan', 'text', array(
                'max_length'=>100,
                'required'=>false,
                'label'=>'Relevant Scanned Images:',
                'attr' => array('class'=>'form-control form-control-modif'),
        ));
        
        //$builder->add('barcode', 'text', array('max_length'=>200,'required'=>false)); 
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\Slide'
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_slidetype';
    }
}
