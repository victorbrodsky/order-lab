<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AccessionType extends AbstractType
{
    
    protected $multy;
    
    public function __construct( $multy = false )
    {
        $this->multy = $multy;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {  
        
        $builder->add( 'accession', 'text', array(
                'label'=>'Accession#:', 
                'max_length'=>100,
                'required'=>true,
                'attr' => array('style' => 'width:100px', 'required' => 'required'),
        ));      
        
        $builder->add('date','date',array(
            'widget' => 'single_text',
            'format' => 'MM-dd-yyyy',
            'attr' => array('class' => 'datepicker'),
            'required' => false,
            //'data' => new \DateTime(),
            'label'=>'Accession Date:',
        ));
        
        if( $this->multy ) {   
            
            $builder->add('part', 'collection', array(
                'type' => new PartType($this->multy),
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
                'label' => "Part:",
                'by_reference' => false,
                'prototype' => true,
                'prototype_name' => '__part__',
            )); 
            
        }
        
        
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\Accession'
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_accessiontype';
    }
}
