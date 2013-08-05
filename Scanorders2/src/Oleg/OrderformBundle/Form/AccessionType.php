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
                'required'=>true,
                'attr' => array('style' => 'width:100px'),
        ));      
        
        $builder->add('date','date',array(
            'widget' => 'single_text',
            'format' => 'MM-dd-yyyy',
            'attr' => array('class' => 'datepicker'),
            'required' => false,
            //'data' => new \DateTime(),
            'label'=>'Accession Date:',
        ));
        
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
