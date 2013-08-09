<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Oleg\OrderformBundle\Helper\FormHelper;

class BlockType extends AbstractType
{
    
    protected $multy;
    
    public function __construct( $multy = false )
    {
        $this->multy = $multy;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {     
        $helper = new FormHelper();
        
        $builder->add( 'name', 'choice', array(
                'label'=>' ', 
                'max_length'=>'3', 
                'choices' => $helper->getBlock(),
                'required'=> true,
                'data' => 0,
                'attr' => array('style' => 'width:70px'),
        ));
        
        if( $this->multy ) { 
            $builder->add('slide', 'collection', array(
                'type' => new SlideType($this->multy),
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
                'label' => "Slide:",
                'by_reference' => false,
                'prototype' => true,
                'prototype_name' => '__slide__',
            ));
        }
        
        
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\Block'
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_blocktype';
    }
}
