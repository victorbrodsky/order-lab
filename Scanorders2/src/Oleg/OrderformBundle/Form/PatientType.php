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
