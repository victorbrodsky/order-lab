<?php

namespace Oleg\UserdirectoryBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class CodeNYPHType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {


        $builder->add('field', null, array(
            'label' => 'NYPH Code:',
            'attr' => array('class'=>'form-control')
        ));

        $builder->add('startDate', 'date', array(
            'label' => "Start Date:",
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM-dd-yyyy',
            'attr' => array('class' => 'datepicker form-control allow-future-date'),
        ));

        $builder->add('endDate', 'date', array(
            'label' => "End Date:",
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM-dd-yyyy',
            'attr' => array('class' => 'datepicker form-control allow-future-date'),
        ));

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\UserdirectoryBundle\Entity\CodeNYPH',
        ));
    }

    public function getName()
    {
        return 'oleg_userdirectorybundle_codenyph';
    }
}
