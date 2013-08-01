<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SpecimenType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $multi = false;
        
        $builder
            ->add('proceduretype')
            ->add('paper');
        
        $builder->add( 'proceduretype', 'text', array(
                'label'=>'Procedure Type:', 
                'max_length'=>300,'required'=>false
        )); 
        
        $builder->add( 'paper', 'text', array(
                'label'=>'Paper:', 
                'max_length'=>300,'required'=>false
        ));
        
        if( $multi ) {          
            $builder->add('accession', 'collection', array(
                'type' => new AccessionType(),
                'allow_add' => true,
                'allow_delete' => true,
                'label' => "Accession:",
                'by_reference' => false,
                'prototype' => true,
                'prototype_name' => '__accession__',
            )); 
        }
        
        
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\Specimen'
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_specimentype';
    }
}
