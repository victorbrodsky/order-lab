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
//use Symfony\Component\Security\Core\Validator\Constraint\UserPassword as OldUserPassword;
//use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Doctrine\ORM\EntityRepository;

class UserType extends AbstractType
{

    protected $cicle;
    protected $roleAdmin;
    protected $user;
    protected $roles;

    public function __construct( $cicle = null, $user, $roles, $roleAdmin = false )
    {
        $this->cicle = $cicle;
        $this->user = $user;
        $this->roleAdmin = $roleAdmin;
        $this->roles = $roles;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
//        if (class_exists('Symfony\Component\Security\Core\Validator\Constraints\UserPassword')) {
//            $constraint = new UserPassword();
//        } else {
//            // Symfony 2.1 support with the old constraint class
//            $constraint = new OldUserPassword();
//        }

        $read_only = false;
        if( !$this->roleAdmin ) {
            $read_only = true;
        }

        $builder->add('username', null, array(
            'label' => 'Username:',
            'read_only' => $read_only,
            'attr' => array('class'=>'form-control form-control-modif')
        ));
        $builder->add('firstName', null, array(
            'label' => 'First Name:',
            'attr' => array('class'=>'form-control form-control-modif')
        ));
        $builder->add('lastName', null, array(
            'label' => 'Last Name:',
            'attr' => array('class'=>'form-control form-control-modif')
        ));
        $builder->add('email', 'email', array(
            'label' => 'Email:',
            'attr' => array('class'=>'form-control form-control-modif')
        ));
        $builder->add('displayName', null, array(
            'label' => 'Display Name:',
            'attr' => array('class'=>'form-control form-control-modif')
        ));
        $builder->add('title', null, array(
            'label' => 'Title:',
            'attr' => array('class'=>'form-control form-control-modif')
        ));
        $builder->add('phone', null, array(
            'label' => 'Phone:',
            'attr' => array('class'=>'form-control form-control-modif')
        ));
        $builder->add('fax', null, array(
            'label' => 'Fax:',
            'attr' => array('class'=>'form-control form-control-modif')
        ));
        $builder->add('office', null, array(
            'label' => 'Office Location:',
            'attr' => array('class'=>'form-control form-control-modif')
        ));


        $attr = array('class' => 'ajax-combobox-pathservice', 'type' => 'hidden');    //new
        $builder->add('pathologyServices', 'custom_selector', array(
            'label' => 'Pathology Service(s):',
            'attr' => $attr,
            'required' => false,
            'classtype' => 'userPathologyServices'
        ));


        //Roles
        $attr = array('class' => 'combobox combobox-width');

        if( $this->roleAdmin ) {

            $builder->add('roles', 'choice', array(
                'choices' => $this->roles,
                'label' => 'Roles:',
                'attr'=>$attr,
                'multiple'  => true,
            ));

//            $builder->add('enabled', null, array(
//                'label' => 'Enabled',
//                'attr' => array('class'=>'form-control form-control-modif')
//            ));
            $builder->add('locked', null, array(
                'label' => 'Prevent user from logging in (lock):',
                'attr' => array('class'=>'form-control form-control-modif')
            ));
//            $builder->add('expired', null, array(
//                'label' => 'Expired',
//                'attr' => array('class'=>'form-control form-control-modif')
//            ));
        }

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
