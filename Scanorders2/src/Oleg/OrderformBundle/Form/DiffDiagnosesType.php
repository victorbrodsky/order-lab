<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

use Oleg\OrderformBundle\Helper\FormHelper;

class DiffDiagnosesType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('name', 'text', array(
            'label' => 'Different Diagnoses:',
            'required' => true,
            //'attr' => array('class' => 'combobox combobox-width')
            'attr' => array('class'=>'form-control form-control-modif')
        ));

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\DiffDiagnoses'
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_diffdiagnosestype';
    }
}
