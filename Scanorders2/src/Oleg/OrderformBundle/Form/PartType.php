<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Oleg\OrderformBundle\Helper\FormHelper;

class PartType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $helper = new FormHelper();  
        
        $builder->add('name', 'choice', array(        
            'choices' => $helper->getPart(),
            'required'=>true,
            'label'=>'* Part:',        
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
        
//        $builder->add( 'accession', new AccessionType(), array(
//            'label'=>' ',
//            'required'=>false,
//            //'hidden'=>true,
//        ));
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
