<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AccessionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {  
        
        $builder->add( 'accession', 'text', array(
                'label'=>'Accession#:', 
                'max_length'=>100,
                'required'=>true
        ));
        //$builder->add( 'date', null ,array('max_length'=>100,'required'=>false) );
        
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
