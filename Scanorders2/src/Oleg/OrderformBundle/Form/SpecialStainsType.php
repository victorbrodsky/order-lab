<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

use Oleg\OrderformBundle\Helper\FormHelper;

class SpecialStainsType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('field', 'textarea', array(
            'label' => 'Result of Special Stains',
            'required' => false,
            'attr' => array('class'=>'textarea form-control form-control-modif')
        ));

        $builder->add('stainothers', new ArrayFieldType(), array(
            'data_class' => 'Oleg\OrderformBundle\Entity\SpecialStains',
            'label' => false
        ));

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\SpecialStains'
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_specialstainstype';
    }
}
