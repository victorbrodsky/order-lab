<?php

/*
 * This file is part of the FOSUserBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Security\Core\Validator\Constraint\UserPassword as OldUserPassword;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;

class UserType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (class_exists('Symfony\Component\Security\Core\Validator\Constraints\UserPassword')) {
            $constraint = new UserPassword();
        } else {
            // Symfony 2.1 support with the old constraint class
            $constraint = new OldUserPassword();
        }

        $builder->add('username', null, array(
            'label' => 'Username',
            'attr' => array('class'=>'form-control form-control-modif')
        ));
        $builder->add('firstName', null, array(
            'label' => 'First Name',
            'attr' => array('class'=>'form-control form-control-modif')
        ));
        $builder->add('lastName', null, array(
            'label' => 'Last Name',
            'attr' => array('class'=>'form-control form-control-modif')
        ));
        $builder->add('email', 'email', array(
            'label' => 'Email',
            'attr' => array('class'=>'form-control form-control-modif')
        ));
        $builder->add('displayName', null, array(
            'label' => 'Display Name',
            'attr' => array('class'=>'form-control form-control-modif')
        ));
        $builder->add('title', null, array(
            'label' => 'Title',
            'attr' => array('class'=>'form-control form-control-modif')
        ));
        $builder->add('phone', null, array(
            'label' => 'Phone',
            'attr' => array('class'=>'form-control form-control-modif')
        ));
        $builder->add('enabled', null, array(
            'label' => 'Enabled',
            'attr' => array('class'=>'form-control form-control-modif')
        ));
        $builder->add('locked', null, array(
            'label' => 'Locked',
            'attr' => array('class'=>'form-control form-control-modif')
        ));
        $builder->add('expired', null, array(
            'label' => 'Expired',
            'attr' => array('class'=>'form-control form-control-modif')
        ));
        $builder->add('fax', null, array(
            'label' => 'Fax',
            'attr' => array('class'=>'form-control form-control-modif')
        ));
        $builder->add('office', null, array(
            'label' => 'Office',
            'attr' => array('class'=>'form-control form-control-modif')
        ));


//        $builder->add('current_password', 'password', array(
//            'label' => 'form.current_password',
//            'translation_domain' => 'FOSUserBundle',
//            'mapped' => false,
//            'constraints' => $constraint,
//        ));

        $attr = array('class' => 'combobox');

        $builder->add('pathologyServices', null, array('attr'=>$attr));

        $builder->add('roles', 'choice', array(
            'choices'   => array(
                'ROLE_SUPER_ADMIN'   => 'ROLE_SUPER_ADMIN',
                'ROLE_ADMIN'   => 'ROLE_ADMIN',
                'ROLE_USER' => 'ROLE_USER',
            ),
            'attr'=>$attr,
//            'property_path' => false,
            'multiple'  => true,
        ));

    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\User'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'oleg_orderformbundle_user';
    }

}
