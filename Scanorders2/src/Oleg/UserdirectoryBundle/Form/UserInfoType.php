<?php

namespace Oleg\UserdirectoryBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;


class UserInfoType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('suffix', null, array(
            'label' => 'Name Suffix:',
            'attr' => array('class'=>'form-control form-control-modif')
        ));
        $builder->add('firstName', null, array(
            'label' => '* First Name:',
            'attr' => array('class'=>'form-control form-control-modif') //'required'=>'required'
        ));
        $builder->add('middleName', null, array(
            'label' => 'Middle Name:',
            'attr' => array('class'=>'form-control form-control-modif')
        ));
        $builder->add('lastName', null, array(
            'label' => '* Last Name:',
            'attr' => array('class'=>'form-control form-control-modif') //'required'=>'required'
        ));
        $builder->add('email', 'email', array(
            'label' => 'Preferred Email:',
            'attr' => array('class'=>'form-control form-control-modif')
        ));
        $builder->add('displayName', null, array(
            'label' => 'Preferred Full Name for Display:',
            'attr' => array('class'=>'form-control form-control-modif')
        ));
        $builder->add('preferredPhone', null, array(
            'label' => 'Preferred Phone Number:',
            'attr' => array('class'=>'form-control form-control-modif phone-mask')
        ));
        $builder->add('initials', null, array(
            'label' => 'Abbreviated name/Initials used by lab staff for deliveries:',
            'attr' => array('class'=>'form-control form-control-modif')
        ));

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\UserdirectoryBundle\Entity\UserInfo',
        ));
    }

    public function getName()
    {
        return 'oleg_userdirectorybundle_userinfo';
    }
}
