<?php

namespace Oleg\UserdirectoryBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class AdministrativeTitleType extends AbstractType
{


    public function buildForm(FormBuilderInterface $builder, array $options)
    {



        $builder->add( 'name', 'text', array(
            'label'=>'Admnistrative Title:',
            'required'=>false,
            'attr' => array('class' => 'form-control')
        ));

        $builder->add('startDate', 'date', array(
            'label' => "Administrative Title Start Date:",
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM-dd-yyyy',
            'attr' => array('class' => 'datepicker form-control patientdob-mask'),
        ));

        $builder->add('endDate', 'date', array(
            'label' => "Administrative Title End Date:",
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM-dd-yyyy',
            'attr' => array('class' => 'datepicker form-control patientdob-mask'),
        ));

//        $builder->add('institution', null, array(
//            'label' => "Institution:",
//            'required' => false,
//            'attr' => array('class' => 'form-control'),
//        ));
//        $builder->add('department', null, array(
//            'label' => "Institution:",
//            'required' => false,
//            'attr' => array('class' => 'form-control'),
//        ));


    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\UserdirectoryBundle\Entity\AdministrativeTitle',
        ));
    }

    public function getName()
    {
        return 'oleg_userdirectorybundle_administrativetitletype';
    }
}
