<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

use Oleg\OrderformBundle\Helper\FormHelper;

class ClinicalHistoryType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('clinicalHistory', 'textarea', array(
            'label' => 'Clinical History:',
            'max_length'=>10000,
            'required' => false,
            'attr' => array('class'=>'textarea form-control'),
        ));

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\ClinicalHistory'
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_clinicalhistorytype';
    }
}
