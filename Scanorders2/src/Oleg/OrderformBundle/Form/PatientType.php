<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PatientType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('mrn','text',array('label'=>'* MRN:'))
            ->add('name')
            ->add('age')
            ->add('sex')
            ->add('dob')
            ->add('clinicalHistory')
        ;
        
        $builder->add('specimen', 'collection', array(
            'type' => new SpecimenType(),
            'allow_add' => true,
            'allow_delete' => true,
            'label' => "Specimen Entity:",
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__specimen__',
        ));          
        
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
