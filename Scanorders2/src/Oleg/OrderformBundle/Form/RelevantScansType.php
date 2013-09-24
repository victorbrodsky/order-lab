<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

use Oleg\OrderformBundle\Helper\FormHelper;

class RelevantScansType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('name', 'text', array(
            'label' => 'Relevant Scanned Images:',
            'required' => false,
            //'attr' => array('class' => 'combobox combobox-width')
            'attr' => array('class'=>'form-control form-control-modif', 'style'=>'height: 34px;')
        ));

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\RelevantScans'
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_relevantscanstype';
    }
}
