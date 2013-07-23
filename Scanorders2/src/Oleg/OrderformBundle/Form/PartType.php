<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PartType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
//        $builder
//            ->add('name')
//            ->add('sourceOrgan')
//            ->add('description')
//            ->add('diagnosis')
//            ->add('diffDiagnosis')
//            ->add('diseaseType')
//            ->add('accession')
//        ;
        
        $builder->add( 'name', 'text', array(
                'label'=>'* Part:', 
                'max_length'=>'3', 
                'required'=>true
        ));
        
        $builder->add( 'sourceOrgan', 'text', array(
                'label'=>'Source Organ:', 
                'max_length'=>'100', 
                'required'=>false
        ));
        
        $builder->add( 'description', 'textarea', array(
                'label'=>'Description :', 
                'max_length'=>'10000', 
                'required'=>false
        ));
        
        $builder->add( 'diagnosis', 'textarea', array(
                'label'=>'Diagnosis :', 
                'max_length'=>'10000', 
                'required'=>false
        ));
        
        $builder->add( 'diffDiagnosis', 'textarea', array(
                'label'=>'Different Diagnosis:', 
                'max_length'=>'10000', 
                'required'=>false
        ));
        
        $builder->add( 'diseaseType', 'text', array(
                'label'=>'Disease Type:', 
                'max_length'=>'100', 
                'required'=>false
        ));
        
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\Part'
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_parttype';
    }
}
