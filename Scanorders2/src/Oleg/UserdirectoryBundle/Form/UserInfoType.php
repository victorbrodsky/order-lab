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
            'label' => 'Suffix:',
            'attr' => array('class'=>'form-control')
        ));
        $builder->add('firstName', null, array(
            'label' => '* First Name:',
            'attr' => array('class'=>'form-control user-firstName') //'required'=>'required'
        ));
        $builder->add('middleName', null, array(
            'label' => 'Middle Name:',
            'attr' => array('class'=>'form-control')
        ));
        $builder->add('lastName', null, array(
            'label' => '* Last Name:',
            'attr' => array('class'=>'form-control user-lastName') //'required'=>'required'
        ));
        $builder->add('email', 'email', array(
            'label' => '* Preferred Email:',
            'attr' => array('class'=>'form-control user-email')
        ));
        $builder->add('displayName', null, array(
            'label' => 'Preferred Full Name for Display:',
            'attr' => array('class'=>'form-control')
        ));
        $builder->add('preferredPhone', null, array(
            'label' => 'Preferred Phone Number:',
            'attr' => array('class'=>'form-control phone-mask')
        ));
        $builder->add('initials', null, array(
            'label' => 'Abbreviated name/Initials used by lab staff for deliveries:',
            'attr' => array('class'=>'form-control')
        ));
        $builder->add('salutation', null, array(
            'label' => 'Salutation:',
            'attr' => array('class'=>'form-control')
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
