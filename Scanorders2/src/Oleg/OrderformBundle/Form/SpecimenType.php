<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SpecimenType extends AbstractType
{
    
    protected $multy;

    public function __construct( $multy = false )
    {
        $this->multy = $multy;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        
        $builder
            ->add('proceduretype')
            ->add('paper');
        
//        $builder->add( 'proceduretype', 'text', array(
//                'label'=>'Procedure Type:',
//                'max_length'=>300,'required'=>false,
//                'attr' => array('class'=>'form-control form-control-modif'),
//        ));
        $builder->add('proceduretype', null, array(
            'label' => 'Procedure Type:',
            'attr' => array('class' => 'combobox combobox-width')
        ));
        
        $builder->add( 'paper', 'text', array(
                'label'=>'Paper:', 
                'max_length'=>300,'required'=>false,
                'attr' => array('class'=>'form-control form-control-modif'),
        ));
        
        if( $this->multy ) {          
            $builder->add('accession', 'collection', array(
                'type' => new AccessionType($this->multy),
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
                'label' => " ",                         
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
